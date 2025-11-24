<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use App\Notifications\Channels\FcmChannel;

class TempProjectRejected extends Notification
{
  

    protected $project;
    protected $reason;

    /**
     * Create a new notification instance.
     */
    public function __construct($project, $reason = null)
    {
        $this->project = $project;
        $this->reason = $reason;
    }

    /**
     * Notification channels.
     */
    public function via($notifiable)
    {
        return ['database', 'mail', FcmChannel::class];
    }

    /**
     * Database notification.
     */
    public function toArray($notifiable)
    {
        return [
            'title' => 'Projet rejeté',
            'message' => 'Votre projet temporaire "' . $this->project->title . '" a été rejeté.',
            'reason' => $this->reason,
        ];
    }

    /**
     * Email notification.
     */
    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Votre projet temporaire a été rejeté')
            ->greeting('Bonjour ' . $notifiable->name . ',')
            ->line('Nous vous informons que votre projet temporaire intitulé "' . $this->project->title . '" a été rejeté.')
            ->line('Motif du rejet : ' . ($this->reason ?? 'Non spécifié.'))
            ->line('Vous pouvez soumettre à nouveau votre projet après correction.')
            ->action('Voir vos projets', url('/projects'))
            ->line('Merci de votre compréhension.');
    }

    /**
     * FCM mobile push notification.
     */
    public function toFcm($notifiable)
    {
        return [
            'title' => 'Projet rejeté',
            'body'  => 'Votre projet "' . $this->project->title . '" a été rejeté.',
            'data'  => [
                'type'      => 'temp_project_rejected',
                'project_id'=> $this->project->id,
                'reason'    => $this->reason,
            ],
        ];
    }
}
