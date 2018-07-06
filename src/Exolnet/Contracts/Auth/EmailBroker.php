<?php

namespace Exolnet\Contracts\Auth;

use Closure;
use Exolnet\Contracts\Auth\CanConfirmEmail as CanConfirmEmailContract;

interface EmailBroker
{
    /**
     * Constant representing a successfully sent confirmation.
     *
     * @var string
     */
    const CONFIRM_LINK_SENT = 'emails.sent';

    /**
     * Constant representing a successfully confirmed email.
     *
     * @var string
     */
    const EMAIL_CONFIRMED = 'emails.confirmed';

    /**
     * Constant representing the user not found response.
     *
     * @var string
     */
    const INVALID_USER = 'emails.user';

    /**
     * Constant representing an invalid token.
     *
     * @var string
     */
    const INVALID_TOKEN = 'emails.token';

    /**
     * Send an email confirmation link to an email for any user.
     *
     * @param  \Exolnet\Contracts\Auth\CanConfirmEmail|null $user
     * @param  string                                       $email
     * @return string
     */
    public function sendConfirmationLink(?CanConfirmEmailContract $user, $email);

    /**
     * Resend an email confirmation link to an email for an unconfirmed user only.
     *
     * @param  array $credentials
     * @return string
     */
    public function resendConfirmationLink(array $credentials);

    /**
     * Confirm the email with the given token for a user.
     *
     * @param  array    $credentials
     * @param  \Closure $callback
     * @return mixed
     */
    public function confirm(array $credentials, Closure $callback);

    /**
     * Get an existing email confirmation token of the given user.
     *
     * Can be used to display a pending email change in the user's profile.
     *
     * @param \Exolnet\Contracts\Auth\CanConfirmEmail $user
     * @return mixed
     */
    public function findToken(CanConfirmEmailContract $user);
}
