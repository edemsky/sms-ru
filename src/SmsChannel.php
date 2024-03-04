<?php

namespace NotificationChannels\SmsBee;

use Illuminate\Notifications\Notification;
use NotificationChannels\SmsBee\Exceptions\CouldNotSendNotification;

class SmsChannel
{
    /** @var SmsApi */
    protected $smsc;

    public function __construct(SmsApi $smsc)
    {
        $this->smsc = $smsc;
    }

    /**
     * Send the given notification.
     *
     * @param  mixed  $notifiable
     * @param  Notification  $notification
     *
     * @throws CouldNotSendNotification
     *
     * @return array|null
     */
    public function send($notifiable, Notification $notification): ?array
    {
        if (! ($to = $this->getRecipients($notifiable, $notification))) {
            return null;
        }

        $message = $notification->{'toSms'}($notifiable);

        if (\is_string($message)) {
            $message = new SmsMessage($message);
        }

        return $this->sendMessage($to, $message);
    }

    /**
     * Gets a list of phones from the given notifiable.
     *
     * @param  mixed  $notifiable
     * @param  Notification  $notification
     *
     * @return string[]
     */
    protected function getRecipients($notifiable, Notification $notification): array
    {
        $to = $notifiable->routeNotificationFor('sms', $notification);

        if (empty($to)) {
            return [];
        }

        return \is_array($to) ? $to : [$to];
    }

    protected function sendMessage($recipients, SmsMessage $message)
    {
        if (\mb_strlen($message->content) > 800) {
            throw CouldNotSendNotification::contentLengthLimitExceeded();
        }

        $params = [
            'target'  => \implode(',', $recipients),
            'message'     => $message->content,
            'sender'  => $message->from,
        ];

        if ($message->sendAt instanceof \DateTimeInterface) {
            $params['time'] = '0'.$message->sendAt->getTimestamp();
        }

        return $this->smsc->send($params);
    }
}
