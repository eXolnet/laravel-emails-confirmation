<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Exolnet\Foundation\Auth\ConfirmsEmails;

class ConfirmController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Email Confirmation Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling email confirmation requests
    | and uses a simple trait to include this behavior. You're free to
    | explore this trait and override any methods you wish to tweak.
    |
    */

    use ConfirmsEmails;

    /**
     * Where to redirect users after successful confirm.
     *
     * @var string
     */
    protected $redirectTo = '/home';

    /**
     * Where to redirect users after a failed confirm.
     *
     * @var string
     */
    protected $redirectFailureTo = '/login';

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
