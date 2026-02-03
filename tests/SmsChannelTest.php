<?php

namespace NotificationChannels\SmsBee\Test;

use Illuminate\Notifications\Notifiable;
use Illuminate\Notifications\Notification;
use Mockery as M;
use NotificationChannels\SmsBee\SmsApi;
use NotificationChannels\SmsBee\SmsChannel;
use NotificationChannels\SmsBee\SmsMessage;
use PHPUnit\Framework\TestCase;

class SmsChannelTest extends TestCase
{
    /** @var SmsApi|M\MockInterface */
    private $smsc;

    /** @var SmsMessage */
    private $message;

    /** @var SmsChannel */
    private $channel;

    /** @var \DateTime */
    public static $sendAt;

    public function setUp(): void
    {
        $this->smsc = M::mock(SmsApi::class, [
            'login' => 'test',
            'secret' => 'test',
            'sender' => 'John_Doe',
        ]);
        $this->channel = new SmsChannel($this->smsc);
        $this->message = M::mock(SmsMessage::class);
    }

    public function tearDown(): void
    {
        M::close();
    }

    public function test_it_can_send_a_notification(): void
    {
        $this->smsc->shouldReceive('send')
            ->once()
            ->with([
                'target'  => '+1234567890',
                'message'     => 'hello',
                'sender'  => 'John_Doe',
            ]);

        $this->channel->send(new TestNotifiable(), new TestNotification());
    }

    public function test_it_can_send_a_deferred_notification(): void
    {
        self::$sendAt = new \DateTime();

        $this->smsc->shouldReceive('send')
            ->once()
            ->with([
                'target'  => '+1234567890',
                'message'     => 'hello',
                'sender'  => 'John_Doe',
                'time'    => '0'.self::$sendAt->getTimestamp(),
            ]);

        $this->channel->send(new TestNotifiable(), new TestNotificationWithSendAt());
    }

    public function test_it_does_not_send_a_message_when_to_missed(): void
    {
        $this->smsc->shouldNotReceive('send');

        $this->channel->send(
            new TestNotifiableWithoutRouteNotificationForSms(), new TestNotification()
        );
    }

    public function test_it_can_send_a_notification_to_multiple_phones(): void
    {
        $this->smsc->shouldReceive('send')
            ->once()
            ->with([
                'target'  => '+1234567890,+0987654321,+1234554321',
                'message'     => 'hello',
                'sender'  => 'John_Doe',
            ]);

        $this->channel->send(new TestNotifiableWithManyPhones(), new TestNotification());
    }

    public function test_it_throws_when_notification_has_no_to_sms_method(): void
    {
        $this->smsc->shouldNotReceive('send');

        $this->expectException(\NotificationChannels\SmsBee\Exceptions\CouldNotSendNotification::class);

        $this->channel->send(new TestNotifiable(), new TestNotificationWithoutToSms());
    }
}

class TestNotifiable
{
    use Notifiable;

    // Laravel may pass the notification instance here.
    public function routeNotificationForSms(Notification $notification = null)
    {
        return '+1234567890';
    }
}

class TestNotifiableWithoutRouteNotificationForSms extends TestNotifiable
{
    public function routeNotificationForSms(Notification $notification = null)
    {
        return false;
    }
}

class TestNotifiableWithManyPhones extends TestNotifiable
{
    public function routeNotificationForSms(Notification $notification = null)
    {
        return ['+1234567890', '+0987654321', '+1234554321'];
    }
}

class TestNotification extends Notification
{
    public function toSms($notifiable)
    {
        return SmsMessage::create('hello')->from('John_Doe');
    }
}

class TestNotificationWithSendAt extends Notification
{
    public function toSms($notifiable)
    {
        return SmsMessage::create('hello')
            ->from('John_Doe')
            ->sendAt(SmsChannelTest::$sendAt);
    }
}

class TestNotificationWithoutToSms extends Notification
{
}
