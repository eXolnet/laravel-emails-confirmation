# Laravel Emails Confirmation

[![Travis](https://img.shields.io/travis/eXolnet/laravel-emails-confirmation.svg?style=flat-square)](https://travis-ci.org/eXolnet/laravel-emails-confirmation)
[![Packagist](https://img.shields.io/packagist/v/eXolnet/laravel-emails-confirmation.svg?style=flat-square)](https://packagist.org/packages/eXolnet/laravel-emails-confirmation)
[![Downloads](https://img.shields.io/packagist/dt/eXolnet/laravel-emails-confirmation.svg?style=flat-square)](https://packagist.org/packages/eXolnet/laravel-emails-confirmation)
[![MIT License](https://img.shields.io/badge/license-MIT-8469ad.svg?style=flat-square)](https://tldrlegal.com/license/mit-license)

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

If you are not using auto-discovery add the following provider and facade:

```php
Exolnet\Auth\Emails\EmailServiceProvider::class,
```

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
