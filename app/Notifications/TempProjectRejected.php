<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class TempProjectRejected extends Notification
{
    use Queueable;

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
     * Define the channels.
     */
    public function via($notifiable)
    {
        return ['database', 'mail'];
    }

    /**
     * Store notification data in database.
     */
    public function toArray($notifiable)
    {
        return [
            'title' => 'Projet rejeté',
            'message' => 'Votre projet temporaire "' . $this->project->title . '" a été rejeté par l’équipe de validation.',
            'reason' => $this->reason,
        ];
    }

    /**
     * Send mail notification.
     */
    public function toMail($notifiable)
    {
        $mail = (new MailMessage)
            ->subject('Votre projet temporaire a été rejeté')
            ->greeting('Bonjour ' . $notifiable->name . ',')
            ->line('Nous vous informons que votre projet temporaire intitulé "' . $this->project->title . '" a été rejeté.')
            ->line('Motif du rejet : ' . ($this->reason ?? 'Non spécifié.'))
            ->line('Vous pouvez soumettre à nouveau votre projet après correction.')
            ->action('Voir vos projets', url('/projects'))
            ->line('Merci de votre compréhension.');

        return $mail;
    }
}
