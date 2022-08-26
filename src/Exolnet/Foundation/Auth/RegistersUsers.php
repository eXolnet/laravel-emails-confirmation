<?php

namespace Exolnet\Foundation\Auth;

use Illuminate\Auth\Events\Registered;
use Illuminate\Foundation\Auth\RegistersUsers as BaseRegistersUsers;
use Illuminate\Http\Request;

trait RegistersUsers
{
    use BaseRegistersUsers;
    use SendsEmailConfirmationEmails;

    /**
     * Handle a registration request for the application.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    public function register(Request $request)
    {
        $this->validator($request->all())->validate();

        event(new Registered($user = $this->create($request->all())));

        return $this->sendConfirmLinkEmail($request, $user);
    }
}
