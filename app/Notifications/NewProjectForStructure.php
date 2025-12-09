<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use App\Models\Experience;
use App\Notifications\Channels\FcmChannel;

class NewProjectForStructure extends Notification
{
    public Experience $experience;

    public function __construct(Experience $experience)
    {
        $this->experience = $experience;
    }

    public function via($notifiable)
    {
        return ['database', 'mail', FcmChannel::class];
    }

    public function toArray($notifiable)
    {
        return [
            'title_en' => 'New Project Added',
            'title_fr' => 'Nouveau projet ajouté',
            'message_en' => 'A new project concerning your structure has been added. Please verify it.',
            'message_fr' => 'Un nouveau projet concernant votre structure a été ajouté. Veuillez le vérifier.',
            'temp_project_id' => $this->experience->temp_project_id,
            'type' => 'new_project',
        ];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('New project for your structure')
            ->line('A new project concerning your structure has been added.');
    }

    public function toFcm($notifiable)
    {
        return [
            'title' => 'Nouveau projet ajouté',
            'body' => 'Un nouveau projet concernant votre structure a été ajouté. Veuillez le vérifier.',
            'data' => [
                'type' => 'new_project',
                'temp_project_id' => $this->experience->temp_project_id,
                'title_en' => 'New Project Added',
                'title_fr' => 'Nouveau projet ajouté',
                'message_en' => 'A new project concerning your structure has been added. Please verify it.',
                'message_fr' => 'Un nouveau projet concernant votre structure a été ajouté. Veuillez le vérifier.',
            ],
        ];
    }
}
