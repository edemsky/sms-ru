<?php

namespace NotificationChannels\SmsBee\Test;

use NotificationChannels\SmsBee\SmsMessage;
use PHPUnit\Framework\TestCase;

class SmsMessageTest extends TestCase
{
    public function test_it_can_accept_a_content_when_constructing_a_message(): void
    {
        $message = new SmsMessage('hello');

        $this->assertEquals('hello', $message->content);
    }

    public function test_it_can_accept_a_content_when_creating_a_message(): void
    {
        $message = SmsMessage::create('hello');

        $this->assertEquals('hello', $message->content);
    }

    public function test_it_can_set_the_content(): void
    {
        $message = (new SmsMessage())->content('hello');

        $this->assertEquals('hello', $message->content);
    }

    public function test_it_can_set_the_from(): void
    {
        $message = (new SmsMessage())->from('John_Doe');

        $this->assertEquals('John_Doe', $message->from);
    }

    public function test_it_can_set_the_send_at(): void
    {
        $message = (new SmsMessage())->sendAt($sendAt = \date_create());

        $this->assertEquals($sendAt, $message->sendAt);
    }
}
