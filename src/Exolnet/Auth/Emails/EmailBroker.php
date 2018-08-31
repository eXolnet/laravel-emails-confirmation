<?php

namespace Exolnet\Auth\Emails;

use Closure;
use Exolnet\Contracts\Auth\CanConfirmEmail as CanConfirmEmailContract;
use Exolnet\Contracts\Auth\EmailBroker as EmailBrokerContract;
use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Support\Arr;
use UnexpectedValueException;

class EmailBroker implements EmailBrokerContract
{
    /**
     * The email token repository.
     *
     * @var \Exolnet\Auth\Emails\TokenRepositoryInterface
     */
    protected $tokens;

    /**
     * The user provider implementation.
     *
     * @var \Illuminate\Contracts\Auth\UserProvider
     */
    protected $users;

    /**
     * Create a new email broker instance.
     *
     * @param  \Exolnet\Auth\Emails\TokenRepositoryInterface $tokens
     * @param  \Illuminate\Contracts\Auth\UserProvider       $users
     * @return void
     */
    public function __construct(TokenRepositoryInterface $tokens, UserProvider $users)
    {
        $this->users = $users;
        $this->tokens = $tokens;
    }

    /**
     * Send an email confirmation link to an email for any user.
     *
     * @param  \Exolnet\Contracts\Auth\CanConfirmEmail|null $user
     * @param  string                                       $email
     * @return string
     */
    public function sendConfirmationLink(?CanConfirmEmailContract $user, $email)
    {
        if (is_null($user)) {
            return static::INVALID_USER;
        }

        // Once we have the confirmation token, we are ready to send the message out
        // to this user with a link to confirm their email. We will then redirect back
        // to the current URI having nothing set in the session to indicate errors.
        $user->sendEmailConfirmationNotification(
            $email,
            $this->tokens->create($user, $email)
        );

        return static::CONFIRM_LINK_SENT;
    }

    /**
     * Resend an email confirmation link to an email for an unconfirmed user only.
     *
     * @param  array $credentials
     * @return string
     */
    public function resendConfirmationLink(array $credentials)
    {
        // First we will check to see if we found a user at the given credentials and
        // if we did not we will redirect back to this current URI with a piece of
        // "flash" data in the session to indicate to the developers the errors.
        $user = $this->getUser($credentials);

        if (is_null($user)) {
            return static::INVALID_USER;
        }

        // We only resend email confirmation for unconfirmed users. To send or resend
        // an email confirmation for an existing user, use sendConfirmationLink()
        // instead.
        if ($user->getConfirmedAtForEmailConfirmation()) {
            return static::INVALID_USER;
        }

        // Resume the normal sending flow.
        return $this->sendConfirmationLink($user, $user->getEmailForEmailConfirmation());
    }

    /**
     * Confirm the email with the given token for a user.
     *
     * @param  array    $credentials
     * @param  \Closure $callback
     * @return mixed
     */
    public function confirm(array $credentials, Closure $callback)
    {
        // If the responses from the validate method is not a user instance, we will
        // assume that it is a redirect and simply return it from this method and
        // the user is properly redirected having an error message on the post.
        $user = $this->validateConfirm($credentials);

        if (! $user instanceof CanConfirmEmailContract) {
            return $user;
        }

        $record = $this->tokens->find($user);
        $email = $record['email'];

        // Once the confirmation has been validated, we'll call the given callback
        // with the new email. This gives the user an opportunity to store the email
        // in their persistent storage. Then we'll delete the token and return.
        $callback($user, $email);

        $this->tokens->delete($user);

        return static::EMAIL_CONFIRMED;
    }

    /**
     * Validate an email confirmation for the given credentials.
     *
     * @param  array $credentials
     * @return \Illuminate\Contracts\Auth\CanResetPassword|string
     */
    protected function validateConfirm(array $credentials)
    {
        if (is_null($user = $this->getUser($credentials))) {
            return static::INVALID_USER;
        }

        if (! $this->tokens->exists($user, $credentials['token'])) {
            return static::INVALID_TOKEN;
        }

        return $user;
    }

    /**
     * Get the user for the given credentials.
     *
     * @param  array $credentials
     * @return \Exolnet\Contracts\Auth\CanConfirmEmail|null
     *
     * @throws \UnexpectedValueException
     */
    public function getUser(array $credentials)
    {
        $credentials = Arr::except($credentials, ['token']);

        $user = $this->users->retrieveByCredentials($credentials);

        if ($user && ! $user instanceof CanConfirmEmailContract) {
            throw new UnexpectedValueException('User must implement CanConfirmEmail interface.');
        }

        return $user;
    }

    /**
     * Create a new email confirmation token for the given user.
     *
     * @param  \Exolnet\Contracts\Auth\CanConfirmEmail $user
     * @param  string                                  $email
     * @return string
     */
    public function createToken(CanConfirmEmailContract $user, $email)
    {
        return $this->tokens->create($user, $email);
    }

    /**
     * Validate the given email confirmation token.
     *
     * @param  \Exolnet\Contracts\Auth\CanConfirmEmail $user
     * @param  string                                  $token
     * @return bool
     */
    public function tokenExists(CanConfirmEmailContract $user, $token)
    {
        return $this->tokens->exists($user, $token);
    }

    /**
     * Get an existing email confirmation token of the given user.
     *
     * @param  \Exolnet\Contracts\Auth\CanConfirmEmail $user
     * @return array|null
     */
    public function findToken(CanConfirmEmailContract $user)
    {
        return $this->tokens->find($user);
    }

    /**
     * Delete email confirmation tokens of the given user.
     *
     * @param  \Exolnet\Contracts\Auth\CanConfirmEmail $user
     * @return void
     */
    public function deleteToken(CanConfirmEmailContract $user)
    {
        $this->tokens->delete($user);
    }

    /**
     * Delete all expired tokens.
     *
     * @return void
     */
    public function deleteExpired()
    {
        $this->tokens->deleteExpired();
    }

    /**
     * Get the email confirmation token repository implementation.
     *
     * @return \Exolnet\Auth\Emails\TokenRepositoryInterface
     */
    public function getRepository()
    {
        return $this->tokens;
    }
}
