<?php

namespace App\Notifications;

use App\Models\Application;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ShiftApplicationCancelledNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(private readonly Application $application) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Je shiftaanvraag werd geannuleerd')
            ->greeting('Update over je aanvraag')
            ->line('Je aanvraag voor de shift "'.$this->application->shift->title.'" werd door de coordinator geannuleerd.')
            ->line('Je kan je opnieuw kandidaat stellen voor deze shift.')
            ->action('Bekijk beschikbare shifts', route('dashboard'));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'application_id' => $this->application->id,
            'status' => 'cancelled',
            'message' => 'Je aanvraag voor "'.$this->application->shift->title.'" werd geannuleerd.',
            'link' => route('dashboard'),
        ];
    }
}
