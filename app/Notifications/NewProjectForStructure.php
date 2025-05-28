<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use App\Models\Experience;

class NewProjectForStructure extends Notification
{
    use Queueable;

    public Experience $experience;

    public function __construct(Experience $experience)
    {
        $this->experience = $experience;
    }

    public function via($notifiable)
    {
        return ['database', 'mail'];
    }

    public function toArray($notifiable)
    {
        return [
            'title' => 'Nouveau projet ajouté',
            'message' => 'Un nouveau projet concernant votre structure a été ajouté. Veuillez le vérifier.',
            'temp_project_id' => $this->experience->temp_project_id,
        ];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Nouveau projet pour votre structure')
            ->line('Un nouveau projet concernant votre structure a été ajouté.');
    }
}
