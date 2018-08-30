# Laravel Emails Confirmation

[![Build Status](https://img.shields.io/travis/eXolnet/:package_name/master.svg?style=flat-square)](https://travis-ci.org/eXolnet/laravel-emails-confirmation)
[![Latest Release](https://img.shields.io/packagist/v/eXolnet/laravel-emails-confirmation.svg?style=flat-square)](https://packagist.org/packages/eXolnet/laravel-emails-confirmation)
[![Total Downloads](https://img.shields.io/packagist/dt/eXolnet/laravel-emails-confirmation.svg?style=flat-square)](https://packagist.org/packages/eXolnet/laravel-emails-confirmation)
[![Software License](https://img.shields.io/badge/license-MIT-8469ad.svg?style=flat-square)](LICENSE)

Emails confirmation like Laravel native password resets.

## Beforehand

You should already have bootstrap a Laravel 5.5 project and deployed the auth scaffolding:

```bash
composer create-project --prefer-dist laravel/laravel your-project-name "5.5.*"
```

```bash
php artisan make:auth
```

## Installation

Install `exolnet/laravel-emails-confirmation` for Laravel 5.5 using composer:

```bash
composer require "exolnet/laravel-emails-confirmation:5.5.*"
```

If you don't use package auto-discovery, add the service provider to the `providers` array in `config/app.php`:

```php
Exolnet\Auth\Emails\EmailServiceProvider::class,
```

And the facade to the `facades` array in `config/app.php`:

```php
'Email' => Exolnet\Support\Facades\Email::class,
```

## Publishing

This modules provides a lot of publishable files. Some of which are overriding standard Laravel app files
and others are overriding files (controllers and views) from the auth scaffolding.

If you just started a new project, you can simply force publish everything:

```bash
php artisan vendor:publish --provider="Exolnet\Auth\Emails\EmailServiceProvider" --force
```

If you have an already established project, you can either still force publish everything and look at the `git diff`
to fix anything important that might have been overwritten, or you can copy the publishable files by hand.

## Migrations

Run the migrations:

```bash
php artisan migrate
```

## Routes

Invoke the `Route::emails()` macro in your routes file:

```php
Route::emails();
```

Or define the following routes explicitly:

```php
// Email Confirmation Routes...
Route::get('confirm', 'Auth\ResendConfirmationController@showLinkRequestForm')->name('email.resend');
Route::post('confirm', 'Auth\ResendConfirmationController@resendConfirmLinkEmail');
Route::get('confirm/{email}/{token}', 'Auth\ConfirmController@confirm')->name('email.confirm');
```

If you want to define explicitly the auth routes instead of using the `Route::auth()` macro, use this:

```php
// Authentication Routes...
Route::get('login', 'Auth\LoginController@showLoginForm')->name('login');
Route::post('login', 'Auth\LoginController@login');
Route::post('logout', 'Auth\LoginController@logout')->name('logout');

// Registration Routes...
Route::get('register', 'Auth\RegisterController@showRegistrationForm')->name('register');
Route::post('register', 'Auth\RegisterController@register');

// Password Reset Routes...
Route::get('password/reset', 'Auth\ForgotPasswordController@showLinkRequestForm')->name('password.request');
Route::post('password/email', 'Auth\ForgotPasswordController@sendResetLinkEmail')->name('password.email');
Route::get('password/reset/{token}', 'Auth\ResetPasswordController@showResetForm')->name('password.reset');
Route::post('password/reset', 'Auth\ResetPasswordController@reset');
```

## Testing

To run the phpUnit tests, please use:

```bash
composer test
```

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) and [CODE OF CONDUCT](CODE_OF_CONDUCT.md) for details.

## Security

If you discover any security related issues, please email security@exolnet.com instead of using the issue tracker.

## Credits

- [Patrick Gagnon-Renaud](https://github.com/pgrenaud)
- [All Contributors](../../contributors)

## License

This code is licensed under the [MIT license](http://choosealicense.com/licenses/mit/).
Please see the [license file](LICENSE) for more information.
