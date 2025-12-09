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

    public function __construct(TemporaryProject $tempProject, int $officialId)
    {
        $this->tempProject = $tempProject;
        $this->officialId = $officialId;
    }

    public function via($notifiable): array
    {
        return ['mail', 'database', FcmChannel::class];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Temporary project validated')
            ->line("The temporary project « {$this->tempProject->title} » has been validated.")
            ->line("Official ID: {$this->officialId}");
    }

    public function toArray($notifiable): array
    {
        return [
            'title_en' => 'Temporary Project Validated',
            'title_fr' => 'Projet temporaire validé',
            'message_en' => "The project « {$this->tempProject->title} » has been validated. ID: {$this->officialId}",
            'message_fr' => "Le projet « {$this->tempProject->title} » a été validé. ID : {$this->officialId}",
            'temporary_project_id' => $this->tempProject->id,
            'official_id' => $this->officialId,
            'type' => 'temp_project_verified',
        ];
    }

    public function toFcm($notifiable): array
    {
        return [
            'title' => 'Projet temporaire validé',
            'body'  => "Le projet « {$this->tempProject->title} » a été validé.",
            'data'  => [
                'type' => 'temp_project_verified',
                'temporary_project_id' => $this->tempProject->id,
                'official_id' => $this->officialId,
                'title_en' => 'Temporary Project Validated',
                'title_fr' => 'Projet temporaire validé',
                'message_en' => "The project « {$this->tempProject->title} » has been validated.",
                'message_fr' => "Le projet « {$this->tempProject->title} » a été validé.",
                'project_title' => $this->tempProject->title, // Keep original for reference
            ],
        ];
    }
}
