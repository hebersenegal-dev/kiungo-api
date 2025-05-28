<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use App\Models\TemporaryProject;

class TempProjectVerified extends Notification
{
    use Queueable;

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
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
     public function toMail($notifiable): MailMessage
   {
       return (new MailMessage)
           ->subject('Nouveau projet temporaire à valider')
           ->line("Un nouveau projet temporaire intitulé \"{$this->tempProject->title}\" a été ajouté.");
   }


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
}
