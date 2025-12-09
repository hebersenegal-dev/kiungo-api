<?php

namespace App\Notifications;

use App\Notifications\Channels\FcmChannel;
use Illuminate\Notifications\Notification;

class UserActivated extends Notification
{
    public function via($notifiable)
    {
        return ['database', 'mail', FcmChannel::class];
    }

    public function toArray($notifiable)
    {
        return [
            'title_en' => 'Account Verified',
            'title_fr' => 'Vérification du compte',
            'message_en' => 'Your account has been successfully verified.',
            'message_fr' => 'Votre compte a été vérifié avec succès.',
            'type' => 'user_activated',
            'user_id' => $notifiable->id,
        ];
    }

    public function toMail($notifiable)
    {
        // Keep French for now (fastest) - update later
        return (new \Illuminate\Notifications\Messages\MailMessage)
            ->subject('Votre compte est vérifié')
            ->line('Félicitations ! Votre compte a été vérifié avec succès.');
    }

    public function toFcm($notifiable)
    {
        // Send BOTH languages in data, Flutter will pick the right one
        return [
            'title' => 'Vérification du compte', // French title for FCM display
            'body' => 'Votre compte a été vérifié avec succès.', // French body for FCM
            'data' => [
                'type' => 'user_activated',
                'user_id' => $notifiable->id,
                'title_en' => 'Account Verified',
                'title_fr' => 'Vérification du compte',
                'message_en' => 'Your account has been successfully verified.',
                'message_fr' => 'Votre compte a été vérifié avec succès.',
            ],
        ];
    }
}
