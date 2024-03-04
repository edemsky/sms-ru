<?php

namespace NotificationChannels\SmsBee\Test;

use NotificationChannels\SmsBee\SmsApi;
use PHPUnit\Framework\TestCase;

class SmsApiTest extends TestCase
{
    public function test_it_has_config_with_default_endpoint(): void
    {
        $smsc = $this->getExtendedSmsApi([
            'login'  => $login = 'login',
            'secret' => $secret = 'secret',
            'sender' => $sender = 'sender',
        ]);

        $this->assertEquals($login, $smsc->getLogin());
        $this->assertEquals($secret, $smsc->getSecret());
        $this->assertEquals($sender, $smsc->getSender());
        $this->assertEquals('https://a2p-sms-https.beeline.ru/proto/http/', $smsc->getEndpoint());
    }

    public function test_it_has_config_with_custom_endpoint(): void
    {
        $smsc = $this->getExtendedSmsApi([
            'host' => $host = 'https://a2p-sms-https.beeline.ru/proto/http/',
        ]);

        $this->assertEquals('https://a2p-sms-https.beeline.ru/proto/http/', $smsc->getEndpoint());
    }

    private function getExtendedSmsApi(array $config)
    {
        return new class($config) extends SmsApi
        {
            public function getEndpoint(): string
            {
                return $this->endpoint;
            }

            public function getLogin(): string
            {
                return $this->login;
            }

            public function getSecret(): string
            {
                return $this->secret;
            }

            public function getSender(): string
            {
                return $this->sender;
            }
        };
    }
}
