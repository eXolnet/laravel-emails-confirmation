<?php

namespace Exolnet\Foundation\Auth;

use Exolnet\Auth\Events\Confirmed;
use Exolnet\Contracts\Auth\CanConfirmEmail;
use Exolnet\Support\Facades\Email;
use Illuminate\Foundation\Auth\RedirectsUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

trait ConfirmsEmails
{
    use RedirectsUsers, RedirectsFailures;

    /**
     * Confirm the given user's email.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function confirm(Request $request)
    {
        $credentials = $this->credentials($request);

        $this->validate2($credentials, $this->requestRules(), $this->validationErrorMessages());

        // Here we will attempt to confirm the user's email. If it is successful we
        // will update the email on an actual user model and persist it to the
        // database. Otherwise we will parse the error and return the response.
        $response = $this->broker()->confirm(
            $credentials,
            function ($user, $email) {
                $this->validate2(['email' => $email], $this->confirmRules($user), $this->validationErrorMessages());
                $this->confirmEmailAndUser($user, $email);
            }
        );

        // If the email was successfully confirmed, we will redirect the user back to
        // the application's home authenticated view. If there is an error we can
        // redirect them back to where they came from with their error message.
        return $response == Email::EMAIL_CONFIRMED
                    ? $this->sendConfirmedResponse($response)
                    : $this->sendConfirmFailedResponse($request, $response);
    }

    /**
     * Validate the given data with the given rules.
     *
     * @param  array $data
     * @param  array $rules
     * @param  array $messages
     * @param  array $customAttributes
     * @throws \Illuminate\Validation\ValidationException
     */
    protected function validate2(array $data, array $rules, array $messages = [], array $customAttributes = [])
    {
        try {
            Validator::make($data, $rules, $messages, $customAttributes)->validate();
        } catch (ValidationException $e) {
            $e->redirectTo($this->redirectFailurePath());

            throw $e;
        }
    }

    /**
     * Get the request validation rules.
     *
     * @return array
     */
    protected function requestRules()
    {
        return [
            'token' => 'required',
            'email' => 'required|email',
        ];
    }

    /**
     * Get the email confirmation validation rules. Use to check email uniqueness before saving.
     *
     * @param \Exolnet\Contracts\Auth\CanConfirmEmail $user
     * @return array
     */
    protected function confirmRules(CanConfirmEmail $user)
    {
        return [
            'email' => [
                'required',
                'email',
                Rule::unique('users')->ignore($user->getIdentifierForEmailConfirmation()),
            ],
        ];
    }

    /**
     * Get the email confirmation validation error messages.
     *
     * @return array
     */
    protected function validationErrorMessages()
    {
        return [];
    }

    /**
     * Get the email confirmation credentials from the request.
     *
     * @param  \Illuminate\Http\Request $request
     * @return array
     */
    protected function credentials(Request $request)
    {
        return [
            'email' => $request->route()->parameter('email'),
            'token' => $request->route()->parameter('token'),
        ];
    }

    /**
     * Confirm the given user's email.
     *
     * @param  \Exolnet\Contracts\Auth\CanConfirmEmail $user
     * @param  string                                  $email
     * @return void
     */
    protected function confirmEmailAndUser($user, $email)
    {
        $oldEmail = $user->getEmailForEmailConfirmation();

        if (!$user->getConfirmedAtForEmailConfirmation()) {
            $user->setConfirmedAtForEmailConfirmation(new Carbon);
        }
        $user->setEmailForEmailConfirmation($email);
        $user->save();

        event(new Confirmed($user, $oldEmail));

        if (!Auth::check()) {
            $this->guard()->login($user);
        }
    }

    /**
     * Get the response for a successful email confirmation.
     *
     * @param  string $response
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    protected function sendConfirmedResponse($response)
    {
        return redirect($this->redirectPath())
                            ->with('status', trans($response));
    }

    /**
     * Get the response for a failed email confirmation.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  string                   $response
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    protected function sendConfirmFailedResponse(Request $request, $response)
    {
        return redirect($this->redirectFailurePath())
                            ->withErrors(['email' => trans($response)]);
    }

    /**
     * Get the broker to be used during email confirmation.
     *
     * @return \Exolnet\Contracts\Auth\EmailBroker
     */
    public function broker()
    {
        return Email::broker();
    }

    /**
     * Get the guard to be used during email confirmation.
     *
     * @return \Illuminate\Contracts\Auth\StatefulGuard
     */
    protected function guard()
    {
        return Auth::guard();
    }
}
