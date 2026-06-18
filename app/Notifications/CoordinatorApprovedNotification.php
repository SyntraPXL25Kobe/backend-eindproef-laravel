<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CoordinatorApprovedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Je coordinator account is goedgekeurd')
            ->greeting('Goed nieuws!')
            ->line('Je coordinator aanvraag werd goedgekeurd. Je kan nu events beheren.')
            ->action('Log in', url('/login'));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'status' => 'approved',
            'message' => 'Je coordinator aanvraag werd goedgekeurd.',
            'link' => url('/login'),
        ];
    }
}
