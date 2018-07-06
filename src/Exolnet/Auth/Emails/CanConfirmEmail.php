<?php

namespace Exolnet\Auth\Emails;

use Exolnet\Auth\Notifications\ConfirmEmail as ConfirmEmailNotification;

trait CanConfirmEmail
{
    /**
     * The confirmed_at column for the user.
     *
     * @var string
     */
    protected $confirmedAtColumnName = 'confirmed_at';

    /**
     * The email column for the user.
     *
     * @var string
     */
    protected $emailColumnName = 'email';

    /**
     * Get the unique identifier for the user.
     *
     * @return mixed
     */
    public function getIdentifierForEmailConfirmation()
    {
        return $this->{$this->getKeyName()};
    }

    /**
     * Get the confirmed_at column for the user.
     *
     * @return string
     */
    public function getConfirmedAtColumnName()
    {
        return $this->confirmedAtColumnName;
    }

    /**
     * Get the email column for the user.
     *
     * @return string
     */
    public function getEmailColumnName()
    {
        return $this->emailColumnName;
    }

    /**
     * Get the confirmed at value for the user.
     *
     * @return \Illuminate\Support\Carbon|null
     */
    public function getConfirmedAtForEmailConfirmation()
    {
        return $this->{$this->getConfirmedAtColumnName()} ?? null;
    }

    /**
     * Set the confirmed at value for the user.
     *
     * @param \Illuminate\Support\Carbon|null $confirmed_at
     */
    public function setConfirmedAtForEmailConfirmation($confirmed_at)
    {
        $this->{$this->getConfirmedAtColumnName()} = $confirmed_at;
    }

    /**
     * Get the e-mail address where email confirmation links are sent.
     *
     * @return string
     */
    public function getEmailForEmailConfirmation()
    {
        return $this->{$this->getEmailColumnName()} ?? null;
    }

    /**
     * Get the e-mail address where email confirmation links are sent.
     *
     * @param string|null $email
     */
    public function setEmailForEmailConfirmation($email)
    {
        $this->{$this->getEmailColumnName()} = $email;
    }

    /**
     * Send the email confirmation notification.
     *
     * @param  string  $token
     * @return void
     */
    public function sendEmailConfirmationNotification($token)
    {
        $this->notify(new ConfirmEmailNotification($token));
    }
}
