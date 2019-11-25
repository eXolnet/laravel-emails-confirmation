<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Email Confirmation Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines are the default lines which match reasons
    | that are given by the email broker for a email confirmation attempt
    | has failed, such as for an invalid token or invalid user.
    |
    */

    'confirmed' => 'Your email address has been confirmed!',
    'unconfirmed' => 'You must verify your email before you can login. If you have not received the confirmation email, please check your spam folder. If you need a new confirmation email, <a href="' . route('email.resend') . '" class="alert-link">click here</a>.', // phpcs:ignore Generic.Files.LineLength.TooLong
    'sent' => 'We have e-mailed your email confirmation link!',
    'token' => 'This email confirmation token is invalid or has expired.',
    'user' => "We can't find a user with that e-mail address.",
    'throttled' => 'Please wait before retrying.',

];
