<?php

namespace Exolnet\Auth\Emails;

use Illuminate\Support\ServiceProvider;

class EmailServiceProvider extends ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = true;

    /**
     * Bootstrap the service provider.
     *
     * @return void
     */
    public function boot()
    {
        // Since we act like a native Laravel feature set, we want to publish
        // everything to the application workspace and let the developer extend,
        // change or rem ove any controllers, configurations, migrations, routes,
        // translations or views.

        $publishable = __DIR__ . '/../../../../publishable';

        $this->publishes([
            $publishable . '/app/ConfirmController.php'            => app_path('Http/Controllers/Auth/ConfirmController.php'),
            $publishable . '/app/LoginController.php'              => app_path('Http/Controllers/Auth/LoginController.php'),
            $publishable . '/app/RegisterController.php'           => app_path('Http/Controllers/Auth/RegisterController.php'),
            $publishable . '/app/ResendConfirmationController.php' => app_path('Http/Controllers/Auth/ResendConfirmationController.php'),
            $publishable . '/app/ResetPasswordController.php'      => app_path('Http/Controllers/Auth/ResetPasswordController.php'),
            $publishable . '/app/User.php'                         => app_path('User.php'),
        ], 'app');

        $this->publishes([
            $publishable . '/configurations/auth.php' => config_path('auth.php'),
        ], 'config');

        $this->publishes([
            $publishable . '/migrations/2018_07_01_000000_create_email_confirmations_table.php' => database_path('migrations/2018_07_01_000000_create_email_confirmations_table.php'),
            $publishable . '/migrations/2018_07_01_000001_add_confirmed_at_to_user_table.php'   => database_path('migrations/2018_07_01_000001_add_confirmed_at_to_user_table.php'),
        ], 'migrations');

        $this->publishes([
            $publishable . '/translations/en/emails.php'    => resource_path('lang/en/emails.php'),
            $publishable . '/translations/en/passwords.php' => resource_path('lang/en/passwords.php'),
        ], 'translations');

        $this->publishes([
            $publishable . '/views/login.blade.php'    => resource_path('views/auth/login.blade.php'),
            $publishable . '/views/register.blade.php' => resource_path('views/auth/register.blade.php'),
            $publishable . '/views/resend.blade.php'   => resource_path('views/auth/emails/resend.blade.php'),
        ], 'views');
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->registerPasswordBroker();
    }

    /**
     * Register the email broker instance.
     *
     * @return void
     */
    protected function registerPasswordBroker()
    {
        $this->app->singleton('auth.email', function ($app) {
            return new EmailBrokerManager($app);
        });

        $this->app->bind('auth.email.broker', function ($app) {
            return $app->make('auth.email')->broker();
        });
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return ['auth.email', 'auth.email.broker'];
    }
}
