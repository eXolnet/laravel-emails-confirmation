<?php

namespace Exolnet\Support\Facades;

use Exolnet\Contracts\Auth\EmailBroker;
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
    const CONFIRM_LINK_SENT = EmailBroker::CONFIRM_LINK_SENT;

    /**
     * Constant representing a successfully confirmed email.
     *
     * @var string
     */
    const EMAIL_CONFIRMED = EmailBroker::EMAIL_CONFIRMED;

    /**
     * Constant representing the user not found response.
     *
     * @var string
     */
    const INVALID_USER = EmailBroker::INVALID_USER;

    /**
     * Constant representing an invalid token.
     *
     * @var string
     */
    const INVALID_TOKEN = EmailBroker::INVALID_TOKEN;

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
