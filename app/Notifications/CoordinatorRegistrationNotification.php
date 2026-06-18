<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CoordinatorRegistrationNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(private readonly User $applicant) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Nieuwe coordinator aanvraag')
            ->greeting('Hallo admin,')
            ->line('Er werd een nieuwe coordinator aanvraag ingediend.')
            ->line('Naam: '.$this->applicant->name)
            ->line('E-mail: '.$this->applicant->email)
            ->line('Organisatie: '.$this->applicant->coordinatorProfile?->organisation_name)
            ->action('Open coordinator aanvragen', url('/admin/coordinator-requests'));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'user_id' => $this->applicant->id,
            'name' => $this->applicant->name,
            'email' => $this->applicant->email,
            'organisation_name' => $this->applicant->coordinatorProfile?->organisation_name,
            'link' => url('/admin/coordinator-requests'),
        ];
    }
}
