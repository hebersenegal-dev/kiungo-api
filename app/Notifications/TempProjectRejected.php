<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use App\Notifications\Channels\FcmChannel;

class TempProjectRejected extends Notification
{
    protected $project;
    protected $reason;

    public function __construct($project, $reason = null)
    {
        $this->project = $project;
        $this->reason = $reason;
    }

    public function via($notifiable)
    {
        return ['database', 'mail', FcmChannel::class];
    }

    public function toArray($notifiable)
    {
        return [
            'title_en' => 'Project Rejected',
            'title_fr' => 'Projet rejeté',
            'message_en' => 'Your temporary project "' . $this->project->title . '" has been rejected.' . ($this->reason ? ' Reason: ' . $this->reason : ''),
            'message_fr' => 'Votre projet temporaire "' . $this->project->title . '" a été rejeté.' . ($this->reason ? ' Motif : ' . $this->reason : ''),
            'reason' => $this->reason,
            'project_id' => $this->project->id,
            'type' => 'temp_project_rejected',
        ];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Your temporary project has been rejected')
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line('We inform you that your temporary project titled "' . $this->project->title . '" has been rejected.')
            ->line('Rejection reason: ' . ($this->reason ?? 'Not specified.'))
            ->line('You can resubmit your project after correction.')
            ->action('View your projects', url('/projects'))
            ->line('Thank you for your understanding.');
    }

    public function toFcm($notifiable)
    {
        return [
            'title' => 'Projet rejeté',
            'body'  => 'Votre projet "' . $this->project->title . '" a été rejeté.',
            'data'  => [
                'type'      => 'temp_project_rejected',
                'project_id'=> $this->project->id,
                'reason'    => $this->reason,
                'title_en' => 'Project Rejected',
                'title_fr' => 'Projet rejeté',
                'message_en' => 'Your temporary project "' . $this->project->title . '" has been rejected.',
                'message_fr' => 'Votre projet temporaire "' . $this->project->title . '" a été rejeté.',
                'project_title' => $this->project->title,
            ],
        ];
    }
}
