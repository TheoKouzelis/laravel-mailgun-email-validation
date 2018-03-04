<?php

namespace Kouz\LaravelMailgunValidation;

use GuzzleHttp\Client;
use Exception;

class EmailRule
{
    protected $client;
    protected $key;
    protected $url = 'https://api.mailgun.net/v3/address/private/validate';

    public function __construct(Client $client, $key = '')
    {
        $this->client = $client;
        $this->key = $key;
    }

    public function validate($attribute, $value, $parameters)
    {
        if (filter_var($value, FILTER_VALIDATE_EMAIL) === false) {
            return false;
        }

        try {
            $mailgun = $this->getMailgunValidation($value, in_array('mailbox', $parameters));
        } catch (Exception $e) {
            return !in_array('api', $parameters);
        }

        if (!$mailgun->is_valid) {
            return false;
        }

        if (in_array('role', $parameters) && $mailgun->is_role_address) {
            return false;
        }

        if (in_array('disposable', $parameters) && $mailgun->is_disposable_address) {
            return false;
        }

        if (in_array('mailbox', $parameters) && !$mailgun->mailbox_verification) {
            return false;
        }

        return true;
    }

    protected function getMailgunValidation($email, $mailboxVerification = false)
    {
        $response = $this->client->get($this->url, [
            'query' => [
                'address'              => $email,
                'mailbox_verification' => $mailboxVerification,
            ],
            'auth' => [
                'api',
                $this->key
            ]
        ]);

        if (!$mailgun = json_decode($response->getBody())) {
            throw new Exception('Failed to decode Mailgun response');
        }

        return $mailgun;
    }
}
