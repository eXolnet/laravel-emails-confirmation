<?php

namespace Exolnet\Auth\Events;

use Illuminate\Queue\SerializesModels;

class Confirmed
{
    use SerializesModels;

    /**
     * The user.
     *
     * @var \Exolnet\Contracts\Auth\CanConfirmEmail
     */
    public $user;

    /**
     * The previously used email address, if any.
     *
     * @var string
     */
    public $oldEmail;

    /**
     * Create a new event instance.
     *
     * @param  \Exolnet\Contracts\Auth\CanConfirmEmail $user
     * @param                                          $oldEmail
     * @return void
     */
    public function __construct($user, $oldEmail)
    {
        $this->user = $user;
        $this->oldEmail = $oldEmail;
    }
}
