<?php

namespace App\Notifications;

use App\Models\Application;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ShiftApplicationRejectedNotification extends Notification implements ShouldQueue
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
            ->subject('Je shiftaanvraag is afgewezen')
            ->greeting('Update over je aanvraag')
            ->line('Je aanvraag voor de shift "'.$this->application->shift->title.'" is afgewezen.')
            ->line('Event: '.$this->application->shift->zone->event->title)
            ->action('Bekijk andere shifts', route('dashboard'));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'application_id' => $this->application->id,
            'status' => 'rejected',
            'message' => 'Je aanvraag voor "'.$this->application->shift->title.'" is afgewezen.',
            'link' => route('dashboard'),
        ];
    }
}
