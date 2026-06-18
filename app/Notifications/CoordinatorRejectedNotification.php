<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CoordinatorRejectedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(private readonly ?string $reason = null) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $mail = (new MailMessage)
            ->subject('Je coordinator aanvraag werd afgewezen')
            ->greeting('Update over je aanvraag')
            ->line('Je coordinator aanvraag werd afgewezen.');

        if ($this->reason) {
            $mail->line('Reden: '.$this->reason);
        }

        return $mail->action('Contact support', url('/'));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'status' => 'rejected',
            'message' => 'Je coordinator aanvraag werd afgewezen.',
            'reason' => $this->reason,
        ];
    }
}
