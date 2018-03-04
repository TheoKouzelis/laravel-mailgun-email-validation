<?php

use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\Psr7\Response;
use Kouz\LaravelMailgunValidation\EmailRule;
use PHPUnit\Framework\TestCase;

class EmailRuleTest extends TestCase
{
    protected $defaultMailgunData = [
        'address'               => 'tkouzelis@outlook.com',
        'did_you_mean'          => null,
        'is_disposable_address' => false,
        'is_role_address'       => false,
        'is_valid'              => false,
        'mailbox_verification'  => null,
        'parts'                 => [
            'display_name' => null,
            'domain'       => 'outlook.com',
            'local_part'   => 'tkouzelis',
        ],
        'reason' => null,
    ];

    /**
     * @test
     */
    public function rfc_822_invalid_email_addresses_dont_validate()
    {
        $client = new Client();

        $rule = new EmailRule($client);

        $this->assertFalse($rule->validate('email', 'bar', []));
    }

    /**
     * @test
     */
    public function mailgun_invalid_email_addresses_dont_validate()
    {
        $client = $this->getMockClient([
            $this->getMailgunResponse(['is_valid' => false]),
        ]);

        $rule = new EmailRule($client);

        $this->assertFalse($rule->validate('email', 'tkouzelis@outlook.com', []));
    }

    /**
     * @test
     */
    public function mailgun_valid_email_addresses_validate_when_other_checks_fail_but_arent_required()
    {
        $client = $this->getMockClient([
            $this->getMailgunResponse([
                'is_valid'              => true,
                'is_disposable_address' => true,
                'is_role_address'       => true,
                'mailbox_verification'  => "false",
            ]),
        ]);

        $rule = new EmailRule($client);

        $this->assertTrue($rule->validate('email', 'tkouzelis@outlook.com', []));
    }

    /**
     * @test
     */
    public function mailgun_role_email_addesses_dont_validate_when_check_required()
    {
        $client = $this->getMockClient([
            $this->getMailgunResponse([
                'is_valid'              => true,
                'is_disposable_address' => false,
                'is_role_address'       => true,
                'mailbox_verification'  => "true",
            ]),
        ]);

        $rule = new EmailRule($client);

        $this->assertFalse($rule->validate('email', 'tkouzelis@outlook.com', ['role']));
    }

    /**
     * @test
     */
    public function mailgun_disposable_email_addesses_dont_validate_when_check_required()
    {
        $client = $this->getMockClient([
            $this->getMailgunResponse([
                'is_valid'              => true,
                'is_disposable_address' => true,
                'is_role_address'       => false,
                'mailbox_verification'  => "true",
            ]),
        ]);

        $rule = new EmailRule($client);

        $this->assertFalse($rule->validate('email', 'tkouzelis@outlook.com', ['disposable']));
    }

    /**
     * @test
     */
    public function mailgun_failed_mailbox_verification_dont_validate_when_check_required()
    {
        $client = $this->getMockClient([
            $this->getMailgunResponse([
                'is_valid'              => true,
                'is_disposable_address' => false,
                'is_role_address'       => false,
                'mailbox_verification'  => "false",
            ]),
        ]);

        $rule = new EmailRule($client);

        $this->assertFalse($rule->validate('email', 'tkouzelis@outlook.com', ['mailbox']));
    }

    /**
     * @test
     */
    public function mailgun_unknown_mailbox_verification_validates_if_not_strict()
    {
        $client = $this->getMockClient([
            $this->getMailgunResponse([
                'is_valid'              => true,
                'is_disposable_address' => false,
                'is_role_address'       => false,
                'mailbox_verification'  => "unknown",
            ]),
        ]);

        $rule = new EmailRule($client);

        $this->assertTrue($rule->validate('email', 'tkouzelis@outlook.com', ['mailbox']));
    }

    /**
     * @test
     */
    public function mailgun_unknown_mailbox_verification_dont_validate_when_strict()
    {
        $client = $this->getMockClient([
            $this->getMailgunResponse([
                'is_valid'              => true,
                'is_disposable_address' => false,
                'is_role_address'       => false,
                'mailbox_verification'  => "unknown",
            ]),
        ]);

        $rule = new EmailRule($client);

        $this->assertFalse($rule->validate('email', 'tkouzelis@outlook.com', ['mailbox', 'strict']));
    }

    /**
     * @test
     */
    public function mailgun_valid_email_addresses_validate_when_all_checks_pass_and_are_required()
    {
        $client = $this->getMockClient([
            $this->getMailgunResponse([
                'is_valid'              => true,
                'is_disposable_address' => false,
                'is_role_address'       => false,
                'mailbox_verification'  => "true",
            ]),
        ]);

        $rule = new EmailRule($client);

        $this->assertTrue($rule->validate('email', 'tkouzelis@outlook.com', ['role', 'disposable', 'mailbox']));
    }

    /**
     * @test
     */
    public function rfc_822_valid_emails_validate_when_connection_to_mailgun_api_fails()
    {
        $client = $this->getMockClient([
            new Response(500, [], ''),
        ]);

        $rule = new EmailRule($client);

        $this->assertTrue($rule->validate('email', 'tkouzelis@outlook.com', ['role', 'disposable', 'mailbox']));
    }

    /**
     * @test
     */
    public function rfc_822_valid_emails_validate_when_mailformed_json_returned()
    {
        $client = $this->getMockClient([
            new Response(200, [], 'this, is not Valid {} Json'),
        ]);

        $rule = new EmailRule($client);

        $this->assertTrue($rule->validate('email', 'tkouzelis@outlook.com', ['role', 'disposable', 'mailbox']));
    }

    /**
     * @test
     */
    public function fail_connection_to_mailgun_api_dont_validate_if_required()
    {
        $client = $this->getMockClient([
            new Response(500, [], ''),
        ]);

        $rule = new EmailRule($client);

        $this->assertFalse($rule->validate('email', 'tkouzelis@outlook.com', ['strict']));
    }

    /**
     * @test
     */
    public function mailformed_json_responses_dont_validate_if_required()
    {
        $client = $this->getMockClient([
            new Response(200, [], 'this, is not Valid {} Json'),
        ]);

        $rule = new EmailRule($client);

        $this->assertFalse($rule->validate('email', 'tkouzelis@outlook.com', ['strict']));
    }

    protected function getMailgunResponse($data = [])
    {
        $json = json_encode($data + $this->defaultMailgunData);

        return new Response(200, [], $json);
    }

    protected function getMockClient($responses = [])
    {
        $mock = new MockHandler($responses);

        $handler = HandlerStack::create($mock);

        return new Client(['handler' => $handler]);
    }
}
