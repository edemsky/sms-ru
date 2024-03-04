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
            new TestNotifiableWithoutRouteNotificationForSmscru(), new TestNotification()
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
}

class TestNotifiable
{
    use Notifiable;

    // Laravel v5.6+ passes the notification instance here
    // So we need to add `Notification $notification` argument to check it when this project stops supporting < 5.6
    public function routeNotificationForSmscru()
    {
        return '+1234567890';
    }
}

class TestNotifiableWithoutRouteNotificationForSmscru extends TestNotifiable
{
    public function routeNotificationForSmscru()
    {
        return false;
    }
}

class TestNotifiableWithManyPhones extends TestNotifiable
{
    public function routeNotificationForSmscru()
    {
        return ['+1234567890', '+0987654321', '+1234554321'];
    }
}

class TestNotification extends Notification
{
    public function toSmscRu()
    {
        return SmsMessage::create('hello')->from('John_Doe');
    }
}

class TestNotificationWithSendAt extends Notification
{
    public function toSmscRu()
    {
        return SmsMessage::create('hello')
            ->from('John_Doe')
            ->sendAt(SmsChannelTest::$sendAt);
    }
}
