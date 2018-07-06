<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Exolnet\Foundation\Auth\SendsEmailConfirmationEmails;

class ResendConfirmationController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Resend Confirmation Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling password reset emails and
    | includes a trait which assists in sending these notifications from
    | your application to your users. Feel free to explore this trait.
    |
    */

    use SendsEmailConfirmationEmails;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest');
    }
}
