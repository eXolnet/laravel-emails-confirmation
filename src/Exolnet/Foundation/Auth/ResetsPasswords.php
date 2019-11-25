<?php

namespace Exolnet\Foundation\Auth;

use Illuminate\Foundation\Auth\ResetsPasswords as BaseResetsPasswords;
use Illuminate\Support\Str;
use Illuminate\Auth\Events\PasswordReset;

trait ResetsPasswords
{
    use BaseResetsPasswords;

    /**
     * Reset the given user's password.
     *
     * @param  \Exolnet\Contracts\Auth\CanConfirmEmail $user
     * @param  string                                  $password
     * @return void
     */
    protected function resetPassword($user, $password)
    {
        $this->setUserPassword($user, $password);

        $user->setRememberToken(Str::random(60));

        $user->save();

        event(new PasswordReset($user));

        if ($user->getConfirmedAtForEmailConfirmation()) {
            $this->guard()->login($user);
        }
    }
}
