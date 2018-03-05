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
            __DIR__ . '/../config/mailgun-email-validation.php' => config_path('mailgun-email-validation.php')
        ], 'config');

        $this->loadTranslationsFrom(__DIR__ . '/../lang/', 'mailgun-email-validation');

        $message = $this->app->translator->trans('mailgun-email-validation::validation.mailgun_email');

        Validator::extend('mailgun_email', 'Kouz\LaravelMailgunValidation\EmailRule@validate', $message);
    }

    public function register()
    {
        $this->app->bind(EmailRule::class, function ($app) {
            return new EmailRule(new Client(), config('mailgun-email-validation.key'));
        });
    }
}
