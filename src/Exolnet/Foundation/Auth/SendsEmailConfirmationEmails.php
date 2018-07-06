<?php

namespace Exolnet\Foundation\Auth;

use Exolnet\Support\Facades\Email;
use Illuminate\Http\Request;

trait SendsEmailConfirmationEmails
{
    /**
     * Display the form to request an email confirmation link.
     *
     * @return \Illuminate\Http\Response
     */
    public function showLinkRequestForm()
    {
        return view('auth.emails.resend');
    }

    /**
     * Resend a email confirmation link to the given user.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    protected function resendConfirmLinkEmail(Request $request)
    {
        $this->validateEmail($request);

        // We will resend the email confirmation link to this user. Once we have
        // attempted to send the link, we will examine the response then see the
        // message we need to show to the user. Finally, we'll send out a proper
        // response.
        $response = $this->broker()->resendConfirmationLink(
            $request->only('email')
        );

        return $response == Email::CONFIRM_LINK_SENT
                    ? $this->sendConfirmLinkResponse($response)
                    : $this->sendConfirmLinkFailedResponse($request, $response);
    }

    /**
     * Send a email confirmation link to the given user.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  mixed                    $user
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    protected function sendConfirmLinkEmail(Request $request, $user)
    {
        $this->validateEmail($request);

        // We will send the email confirmation link to this user. Once we have attempted
        // to send the link, we will examine the response then see the message we
        // need to show to the user. Finally, we'll send out a proper response.
        $response = $this->broker()->sendConfirmationLink(
            $user, $request->get('email')
        );

        return $response == Email::CONFIRM_LINK_SENT
                    ? $this->sendConfirmLinkResponse($response)
                    : $this->sendConfirmLinkFailedResponse($request, $response);
    }

    /**
     * Validate the email for the given request.
     *
     * @param  \Illuminate\Http\Request $request
     * @return void
     */
    protected function validateEmail(Request $request)
    {
        $this->validate($request, ['email' => 'required|email']);
    }

    /**
     * Get the response for a successful email confirmation link.
     *
     * @param  string $response
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    protected function sendConfirmLinkResponse($response)
    {
        return back()->with('status', trans($response));
    }

    /**
     * Get the response for a failed email confirmation link.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  string                   $response
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    protected function sendConfirmLinkFailedResponse(Request $request, $response)
    {
        return back()->withErrors(
            ['email' => trans($response)]
        );
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
}
