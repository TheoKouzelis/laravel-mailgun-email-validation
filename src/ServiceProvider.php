<?php

namespace Kouz\LaravelMailgunValidation;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\ServiceProvider as BaseServiceProvider;

class ServiceProvider extends BaseServiceProvider
{
    public function boot()
    {
        $this->publishes([
            __DIR__ . '/../config/mailgun-validation.php' => config_path('mailgun-validation.php')
        ], 'config');

        Validator::extend('mailgun_email', 'Kouz\LaravelMailgunValidation\EmailRule@validate');
    }

    public function register()
    {
        $this->app->bind(EmailRule::class, function ($app) {
            return new EmailRule(new Client(), config('mailgun-validation.key'));
        });
    }
}
