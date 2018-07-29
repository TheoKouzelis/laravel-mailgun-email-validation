# Laravel Mailgun Email Validation
Laravel email validation that uses the [Mailgun API](https://documentation.mailgun.com/en/latest/api-email-validation.html#email-validation) for 
a three-step validation check.

## Install
Require via composer.
```
composer require kouz/laravel-mailgun-email-validation
```
For Laravel >=5.5 the package will be discoverd. For Laravel <=5.4 add package to list of service providers in config/app.php
```
<?php
  //config/app.php
  
    'providers' => [
        Kouz\LaravelMailgunValidation\ServiceProvider::class,
    ],
```
Publish and fill out the config/mailgun-email-validation.php file with your Mailgun API key.
```
php artisan vendor:publish --provider="Kouz\LaravelMailgunValidation\ServiceProvider"
```

## Basic Usage
Use the following rule to validate your email fields. The rule will first check the address against 
PHP's [FILTER_VALIDATE_EMAIL](http://php.net/manual/en/filter.filters.validate.php) and then will call 
the Mailgun API.

**mailgun_email:role,disposable,mailbox,strict**
The field under validation must be formatted as an e-mail address. The following flags can be added to
apply additional validation:

* **role** Don't allow role-based addresses.
* **disposable** Don't allow disposable email domains.
* **mailbox** Verify mailbox. Add strict flag to ensure that Mailgun was able to verify a mailbox and didn't reutrn "Unknown".
* **strict** Always require a response from Mailgun to validate. By default if a API request fails the validation will pass. The strict flag ensures that a Mailgun response was recieved.

## License
This project is licensed under a MIT License which you can find
[in this LICENSE](https://github.com/TheoKouzelis/laravel-mailgun-email-validation/blob/master/LICENSE).

## Feedback
If you have any feedback, comments or suggestions, please feel free to open an issue within this repository.

## Laravel Validation Rules
This package is part of the Laravel Validation Rules collection. If you're after more useful validation rules, 
head to the [Laravel Validation Rules](https://laravel-validation-rules.github.io/) website.
