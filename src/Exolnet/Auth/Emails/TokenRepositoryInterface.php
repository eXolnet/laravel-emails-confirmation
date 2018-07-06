<?php

namespace Exolnet\Auth\Emails;

use Exolnet\Contracts\Auth\CanConfirmEmail as CanConfirmEmailContract;

interface TokenRepositoryInterface
{
    /**
     * Create a new token.
     *
     * @param  \Exolnet\Contracts\Auth\CanConfirmEmail $user
     * @param  string                                  $email
     * @return string
     */
    public function create(CanConfirmEmailContract $user, $email);

    /**
     * Determine if a token record exists and is valid.
     *
     * @param  \Exolnet\Contracts\Auth\CanConfirmEmail $user
     * @param  string                                  $token
     * @return bool
     */
    public function exists(CanConfirmEmailContract $user, $token);

    /**
     * Find a token record.
     *
     * @param  \Exolnet\Contracts\Auth\CanConfirmEmail $user
     * @return array|null
     */
    public function find(CanConfirmEmailContract $user);

    /**
     * Delete a token record.
     *
     * @param  \Exolnet\Contracts\Auth\CanConfirmEmail $user
     * @return void
     */
    public function delete(CanConfirmEmailContract $user);

    /**
     * Delete expired tokens.
     *
     * @return void
     */
    public function deleteExpired();
}
