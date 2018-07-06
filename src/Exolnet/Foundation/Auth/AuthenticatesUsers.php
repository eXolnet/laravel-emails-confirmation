<?php

namespace Exolnet\Foundation\Auth;

use Exolnet\Contracts\Auth\CanConfirmEmail as CanConfirmEmailContract;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Foundation\Auth\AuthenticatesUsers as BaseAuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use UnexpectedValueException;

trait AuthenticatesUsers
{
    use BaseAuthenticatesUsers, RedirectsOuts;

    /**
     * Handle a login request to the application.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\Response|\Illuminate\Http\JsonResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function login(Request $request)
    {
        $this->validateLogin($request);

        // If the class is using the ThrottlesLogins trait, we can automatically throttle
        // the login attempts for this application. We'll key this by the username and
        // the IP address of the client making these requests into this application.
        if ($this->hasTooManyLoginAttempts($request)) {
            $this->fireLockoutEvent($request);

            return $this->sendLockoutResponse($request);
        }

        if ($this->validateLogin($request)) {
            $user = $this->guard()->getLastAttempted();

            if ($this->validateConfirmed($user)) {
                $this->doLogin($request, $user);

                return $this->sendLoginResponse($request);
            }

            return $this->sendUnconfirmedEmailResponse($request);
        }

        // If the login attempt was unsuccessful we will increment the number of attempts
        // to login and redirect the user back to the login form. Of course, when this
        // user surpasses their maximum number of attempts they will get locked out.
        $this->incrementLoginAttempts($request);

        return $this->sendFailedLoginResponse($request);
    }

    /**
     * Attempt to log the user into the application.
     *
     * @param  \Illuminate\Http\Request $request
     * @return bool
     */
    protected function validateLogin(Request $request)
    {
        return $this->guard()->validate(
            $this->credentials($request)
        );
    }

    /**
     * Attempt to log the user into the application.
     *
     * @param  \Exolnet\Contracts\Auth\CanConfirmEmail $user
     * @return bool
     */
    protected function validateConfirmed($user)
    {
        if ($user && ! $user instanceof CanConfirmEmailContract) {
            throw new UnexpectedValueException('User must implement CanConfirmEmail interface.');
        }

        return (bool)$user->getConfirmedAtForEmailConfirmation();
    }

    /**
     * Attempt to log the user into the application.
     *
     * @param  \Illuminate\Http\Request                   $request
     * @param  \Illuminate\Contracts\Auth\Authenticatable $user
     */
    protected function doLogin(Request $request, Authenticatable $user)
    {
        $this->guard()->login(
            $user, $request->filled('remember')
        );
    }

    /**
     * Get the failed login response instance.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    protected function sendUnconfirmedEmailResponse(Request $request)
    {
        throw ValidationException::withMessages([
            $this->username() => [trans('emails.unconfirmed')],
        ]);
    }

    /**
     * Log the user out of the application.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function logout(Request $request)
    {
        $this->guard()->logout();

        $request->session()->invalidate();

        return redirect()->to($this->redirectOutPath());
    }
}
