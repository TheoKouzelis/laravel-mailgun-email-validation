<?php

namespace Kouz\LaravelMailgunValidation;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\ServiceProvider as BaseServiceProvider;

class ServiceProvider extends BaseServiceProvider
{
    protected $transKey = 'mailgun-email-validation::validation.mailgun_email';

    public function boot()
    {
        $this->publishes([
            __DIR__ . '/../config/mailgun-email-validation.php' => config_path('mailgun-email-validation.php')
        ], 'config');

        $this->loadTranslationsFrom(__DIR__ . '/../lang/', 'mailgun-email-validation');

        $message = $this->getMessage();

        Validator::extend('mailgun_email', 'Kouz\LaravelMailgunValidation\EmailRule@validate', $message);
    }

    public function register()
    {
        $this->app->bind(EmailRule::class, function ($app) {
            return new EmailRule(new Client(), $app['log'], config('mailgun-email-validation.key'));
        });
    }

    protected function getMessage()
    {
        if (method_exists($this->app->translator, 'trans')) {
            return $this->app->translator->trans($this->transKey);
        }
        
        return $this->app->translator->get($this->transKey);
    }
}
