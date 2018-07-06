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
     * Create a new event instance.
     *
     * @param  \Exolnet\Contracts\Auth\CanConfirmEmail $user
     * @return void
     */
    public function __construct($user)
    {
        $this->user = $user;
    }
}
