<?php

namespace Exolnet\Auth\Emails;

use Exolnet\Contracts\Auth\CanConfirmEmail as CanConfirmEmailContract;
use Illuminate\Contracts\Hashing\Hasher as HasherContract;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class DatabaseTokenRepository implements TokenRepositoryInterface
{
    /**
     * The database connection instance.
     *
     * @var \Illuminate\Database\ConnectionInterface
     */
    protected $connection;

    /**
     * The Hasher implementation.
     *
     * @var \Illuminate\Contracts\Hashing\Hasher
     */
    protected $hasher;

    /**
     * The token database table.
     *
     * @var string
     */
    protected $table;

    /**
     * The hashing key.
     *
     * @var string
     */
    protected $hashKey;

    /**
     * The number of seconds a token should last.
     *
     * @var int
     */
    protected $expires;

    /**
     * Minimum number of seconds before re-redefining the token.
     *
     * @var int
     */
    protected $throttle;

    /**
     * Create a new token repository instance.
     *
     * @param  \Illuminate\Database\ConnectionInterface $connection
     * @param  \Illuminate\Contracts\Hashing\Hasher     $hasher
     * @param  string                                   $table
     * @param  string                                   $hashKey
     * @param  int                                      $expires
     * @param  int                                      $throttle
     * @return void
     */
    public function __construct(
        ConnectionInterface $connection,
        HasherContract $hasher,
        $table,
        $hashKey,
        $expires = 60,
        $throttle = 60
    ) {
        $this->table = $table;
        $this->hasher = $hasher;
        $this->hashKey = $hashKey;
        $this->expires = $expires * 60;
        $this->connection = $connection;
        $this->throttle = $throttle;
    }

    /**
     * Create a new token record.
     *
     * @param  \Exolnet\Contracts\Auth\CanConfirmEmail $user
     * @param  string                                  $email
     * @return string
     */
    public function create(CanConfirmEmailContract $user, $email)
    {
        $id = $user->getIdentifierForEmailConfirmation();

        $this->deleteExisting($user);

        // We will create a new, random token for the user so that we can e-mail them
        // a safe link to the email confirmation form. Then we will insert a record in
        // the database so that we can verify the token within the actual confirmation.
        $token = $this->createNewToken();

        $this->getTable()->insert($this->getPayload($id, $email, $token));

        return $token;
    }

    /**
     * Delete all existing confirmation tokens from the database.
     *
     * @param  \Exolnet\Contracts\Auth\CanConfirmEmail $user
     * @return int
     */
    protected function deleteExisting(CanConfirmEmailContract $user)
    {
        return $this->getTable()->where('user_id', $user->getIdentifierForEmailConfirmation())->delete();
    }

    /**
     * Build the record payload for the table.
     *
     * @param  int    $id
     * @param  string $email
     * @param  string $token
     * @return array
     */
    protected function getPayload($id, $email, $token)
    {
        return [
            'user_id'    => $id,
            'email'      => $email,
            'token'      => $this->hasher->make($token),
            'created_at' => new Carbon
        ];
    }

    /**
     * Determine if a token record exists and is valid.
     *
     * @param  \Exolnet\Contracts\Auth\CanConfirmEmail $user
     * @param  string                                  $token
     * @return bool
     */
    public function exists(CanConfirmEmailContract $user, $token)
    {
        $record = $this->find($user);

        return $record && $this->hasher->check($token, $record['token']);
    }

    /**
     * Get a pending token record.
     *
     * @param \Exolnet\Contracts\Auth\CanConfirmEmail $user
     * @return array|null
     */
    public function find(CanConfirmEmailContract $user)
    {
        $record = (array) $this->getTable()->where(
            'user_id',
            $user->getIdentifierForEmailConfirmation()
        )->first();

        return ($record && ! $this->tokenExpired($record['created_at'])) ? $record : null;
    }

    /**
     * Determine if the token has expired.
     *
     * @param  string $createdAt
     * @return bool
     */
    protected function tokenExpired($createdAt)
    {
        return Carbon::parse($createdAt)->addSeconds($this->expires)->isPast();
    }

    /**
     * Determine if the given user recently created a confirm email token.
     *
     * @param  \Exolnet\Contracts\Auth\CanConfirmEmail $user
     * @return bool
     */
    public function recentlyCreatedToken(CanConfirmEmailContract $user)
    {
        $record = (array) $this->getTable()->where(
            'user_id',
            $user->getIdentifierForEmailConfirmation()
        )->first();

        return $record && $this->tokenRecentlyCreated($record['created_at']);
    }

    /**
     * Determine if the token was recently created.
     *
     * @param  string  $createdAt
     * @return bool
     */
    protected function tokenRecentlyCreated($createdAt)
    {
        if ($this->throttle <= 0) {
            return false;
        }

        return Carbon::parse($createdAt)->addSeconds(
            $this->throttle
        )->isFuture();
    }

    /**
     * Delete a token record by user.
     *
     * @param  \Exolnet\Contracts\Auth\CanConfirmEmail $user
     * @return void
     */
    public function delete(CanConfirmEmailContract $user)
    {
        $this->deleteExisting($user);
    }

    /**
     * Delete expired tokens.
     *
     * @return void
     */
    public function deleteExpired()
    {
        $expiredAt = Carbon::now()->subSeconds($this->expires);

        $this->getTable()->where('created_at', '<', $expiredAt)->delete();
    }

    /**
     * Create a new token for the user.
     *
     * @return string
     */
    public function createNewToken()
    {
        return hash_hmac('sha256', Str::random(40), $this->hashKey);
    }

    /**
     * Get the database connection instance.
     *
     * @return \Illuminate\Database\ConnectionInterface
     */
    public function getConnection()
    {
        return $this->connection;
    }

    /**
     * Begin a new database query against the table.
     *
     * @return \Illuminate\Database\Query\Builder
     */
    protected function getTable()
    {
        return $this->connection->table($this->table);
    }

    /**
     * Get the hasher instance.
     *
     * @return \Illuminate\Contracts\Hashing\Hasher
     */
    public function getHasher()
    {
        return $this->hasher;
    }
}
