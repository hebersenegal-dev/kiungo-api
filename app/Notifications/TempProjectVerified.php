<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use App\Models\TemporaryProject;

class TempProjectVerified extends Notification
{
    use Queueable;

    public TemporaryProject $tempProject;
    public int $officialProjectId;

    /**
     * Create a new notification instance.
     *
     * @param TemporaryProject $tempProject
     * @param int $officialProjectId
     */
    public function __construct(TemporaryProject $tempProject, int $officialProjectId)
    {
        $this->tempProject = $tempProject;
        $this->officialProjectId = $officialProjectId;
    }

    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Projet vérifié')
            ->line("Le projet \"{$this->tempProject->title}\" a été vérifié et publié avec succès.")
            ->action('Voir le projet', url('/projects/' . $this->officialProjectId));
    }

    public function toArray($notifiable)
    {
        return [
            'title' => 'Projet vérifié',
            'message' => "Le projet \"{$this->tempProject->title}\" a été vérifié et publié avec succès.",
            'project_id' => $this->officialProjectId,
        ];
    }
}
