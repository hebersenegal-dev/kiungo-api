<?php

namespace App\Notifications;

use App\Notifications\Channels\FcmChannel;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TempStructureVerified extends Notification
{

    public function via($notifiable)
    {
        return ['database', 'mail', FcmChannel::class];
    }

    public function toArray($notifiable)
    {
        return [
            'title' => 'Structure vérifiée',
            'message' => "Une structure que vous aviez mentionnée est officiellement sur la plateforme.",
        ];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Votre structure est maintenant vérifiée')
            ->line('Félicitations ! La structure temporaire associée à votre compte a été vérifiée.')
            ->action('Voir les détails', url('/structures'))
            ->line('Merci de faire partie de notre plateforme.');
    }

    public function toFcm($notifiable)
    {
        return [
            'title' => 'Structure vérifiée',
            'body' => "Une structure que vous aviez mentionnée est officiellement sur la plateforme.",
            'data' => [
                'type' => 'temp_structure_verified',
                'user_id' => $notifiable->id,
            ],
        ];
    }
}
