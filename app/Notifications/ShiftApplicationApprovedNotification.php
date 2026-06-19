<?php

namespace App\Notifications;

use App\Models\Application;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ShiftApplicationApprovedNotification extends Notification implements ShouldQueue
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
            ->subject('Je shiftaanvraag is goedgekeurd')
            ->greeting('Goed nieuws!')
            ->line('Je aanvraag voor de shift "'.$this->application->shift->title.'" is goedgekeurd.')
            ->line('Event: '.$this->application->shift->zone->event->title)
            ->action('Bekijk mijn shifts', route('crew.shifts.index'));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'application_id' => $this->application->id,
            'status' => 'approved',
            'message' => 'Je aanvraag voor "'.$this->application->shift->title.'" is goedgekeurd.',
            'link' => route('crew.shifts.index'),
        ];
    }
}
