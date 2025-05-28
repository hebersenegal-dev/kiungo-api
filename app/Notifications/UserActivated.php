<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class UserActivated extends Notification
{
    use Queueable;

    public function via($notifiable)
    {
        return ['database', 'mail'];
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
            ->line('Félicitations ! Votre compte a été vérifié avec succès.')
    }
}
