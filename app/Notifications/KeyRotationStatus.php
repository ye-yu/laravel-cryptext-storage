<?php

namespace App\Notifications;

use DateTime;
use DateTimeZone;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class KeyRotationStatus extends Notification
{
    use Queueable;

    private string $timezoneString;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(private bool $success, private int $timestamp, private ?string $failReason = null)
    {
        $dateTime = new DateTime();
        $dateTime->setTimestamp($this->timestamp);
        $dateTime->setTimezone(new DateTimeZone('+0800'));
        $this->timezoneString = $dateTime->format('Y-m-d H:i:s') . ' MYT';
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array
     */
    public function via(): array
    {
        return ['database'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @return MailMessage
     */
    public function toMail(): MailMessage
    {
        if ($this->success) {
            return (new MailMessage)
                ->line('Your notes encryption has been successfully rotated!')
                ->line('The rotation job finished at ' . $this->timezoneString)
                ->line('Thank you for using our application.');

        }
        return (new MailMessage)
            ->line('Your notes encryption rotation failed!')
            ->line('The rotation job finished at ' . $this->timezoneString)
            ->line('The system reported the failure reason was: ' . $this->failReason)
            ->line('Thank you for using our application.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array
     */
    public function toArray(): array
    {
        if ($this->success)
            return [
                "success" => $this->success,
                "timestamp" => $this->timestamp,
            ];
        return [
            "success" => $this->success,
            "timestamp" => $this->timestamp,
            "fail_reason" => $this->failReason,
        ];
    }
}
