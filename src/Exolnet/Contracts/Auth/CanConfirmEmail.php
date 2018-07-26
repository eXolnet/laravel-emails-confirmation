<?php

namespace Exolnet\Contracts\Auth;

interface CanConfirmEmail
{
    /**
     * Get the unique identifier for the user.
     *
     * @return mixed
     */
    public function getIdentifierForEmailConfirmation();

    /**
     * Get the confirmed_at column for the user.
     *
     * @return string
     */
    public function getConfirmedAtColumnName();

    /**
     * Get the email column for the user.
     *
     * @return string
     */
    public function getEmailColumnName();

    /**
     * Get the confirmed at value for the user.
     *
     * @return \Illuminate\Support\Carbon|null
     */
    public function getConfirmedAtForEmailConfirmation();

    /**
     * Set the confirmed at value for the user.
     *
     * @param \Illuminate\Support\Carbon|null $confirmed_at
     */
    public function setConfirmedAtForEmailConfirmation($confirmed_at);

    /**
     * Get the e-mail address where email confirmation links are sent.
     *
     * @return string
     */
    public function getEmailForEmailConfirmation();

    /**
     * Get the e-mail address where email confirmation links are sent.
     *
     * @param string|null $email
     */
    public function setEmailForEmailConfirmation($email);

    /**
     * Send the email confirmation notification.
     *
     * @param  string  $email
     * @param  string  $token
     * @return void
     */
    public function sendEmailConfirmationNotification($email, $token);
}
