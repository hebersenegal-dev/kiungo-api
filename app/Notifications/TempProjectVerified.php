<?php

namespace App\Notifications;

use App\Models\TemporaryProject;
use App\Notifications\Channels\FcmChannel;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class TempProjectVerified extends Notification
{
    protected TemporaryProject $tempProject;
    protected int $officialId;

    /**
     * Create a new notification instance.
     */
    public function __construct(TemporaryProject $tempProject, int $officialId)
    {
        $this->tempProject = $tempProject;
        $this->officialId = $officialId;
    }

    /**
     * Notification delivery channels.
     */
    public function via($notifiable): array
    {
        return ['mail', 'database', FcmChannel::class];
    }

    /**
     * Email representation.
     */
    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Projet temporaire validé')
            ->line("Le projet temporaire « {$this->tempProject->title} » a été validé.")
            ->line("ID officiel : {$this->officialId}");
    }

    /**
     * Array representation (database).
     */
    public function toArray($notifiable): array
    {
        return [
            'title' => 'Projet temporaire validé',
            'message' => "Le projet « {$this->tempProject->title} » a été validé.",
            'temporary_project_id' => $this->tempProject->id,
            'official_id' => $this->officialId,
        ];
    }

    /**
     * FCM push notification payload.
     */
    public function toFcm($notifiable): array
    {
        return [
            'title' => 'Projet temporaire validé',
            'body'  => "Le projet « {$this->tempProject->title} » a été validé.",
            'data'  => [
                'type' => 'temp_project_verified',
                'temporary_project_id' => $this->tempProject->id,
                'official_id' => $this->officialId,
            ],
        ];
    }
}
