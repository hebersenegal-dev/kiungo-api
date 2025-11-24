<?php

namespace App\Notifications;

use App\Models\TemporaryProject;
use App\Notifications\Channels\FcmChannel;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TempProjectVerified extends Notification
{


    protected TemporaryProject $tempProject;

    /**
     * Create a new notification instance.
     */
    public function __construct(TemporaryProject $tempProject)
    {
        $this->tempProject = $tempProject;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via($notifiable): array
    {
        return ['mail', 'database', FcmChannel::class];
    }

    /**
     * Get the mail representation of the notification.

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Nouveau projet temporaire à valider')
            ->line("Un nouveau projet temporaire intitulé \"{$this->tempProject->title}\" a été ajouté.");
    }
   */
    /**
     * Get the array representation of the notification.
     */
    public function toArray($notifiable): array
    {
        return [
            'title' => 'Nouveau projet temporaire à valider',
            'message' => "Le projet \"{$this->tempProject->title}\" a été soumis pour validation.",
            'temporary_project_id' => $this->tempProject->id,
        ];
    }

    /**
     * FCM push notification payload
     */
    public function toFcm($notifiable): array
    {
        return [
            'title' => 'Nouveau projet temporaire à valider',
            'body'  => "Le projet \"{$this->tempProject->title}\" a été soumis pour validation.",
            'data'  => [
                'type' => 'temp_project_verified',
                'temporary_project_id' => $this->tempProject->id,
            ],
        ];
    }
}
