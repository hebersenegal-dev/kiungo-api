<?php

namespace App\Notifications;

use App\Notifications\Channels\FcmChannel;
use Illuminate\Notifications\Notification;

class UserActivated extends Notification implements
{

    public function via($notifiable)
    {
        return ['database', 'mail', FcmChannel::class];
    }

    public function toArray($notifiable)
    {
        return [
            'title' => 'Vérification du compte',
            'message' => 'Votre compte a été vérifié avec succès.',
        ];
    }

    public function toMail($notifiable)
    {
        return (new \Illuminate\Notifications\Messages\MailMessage)
            ->subject('Votre compte est vérifié')
            ->line('Félicitations ! Votre compte a été vérifié avec succès.');
    }

    public function toFcm($notifiable)
    {
        return [
            'title' => 'Vérification du compte',
            'body' => 'Votre compte a été vérifié avec succès.',
            'data' => [
                'type' => 'user_activated',
                'user_id' => $notifiable->id,
            ],
        ];
    }
}
