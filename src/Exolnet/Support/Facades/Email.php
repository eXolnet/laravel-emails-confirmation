<?php

namespace Exolnet\Support\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Exolnet\Auth\Emails\EmailBroker
 */
class Email extends Facade
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
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'auth.email';
    }
}
