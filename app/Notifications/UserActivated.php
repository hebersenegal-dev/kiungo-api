<?php

namespace App\Notifications;

use App\Notifications\Channels\FcmChannel;
use Illuminate\Notifications\Notification;

class UserActivated extends Notification
{
    protected $userLocale; // Add this

    public function __construct($userLocale = 'fr')
    {
        $this->userLocale = $userLocale;
    }

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
        // Keep French for now (fastest) - update later if needed
        return (new \Illuminate\Notifications\Messages\MailMessage)
            ->subject('Votre compte est vérifié')
            ->line('Félicitations ! Votre compte a été vérifié avec succès.');
    }

    public function toFcm($notifiable)
    {
        // Determine which language to use based on user's locale
        $locale = $this->userLocale ?? 'fr';

        $translations = [
            'en' => [
                'title' => 'Account Verified',
                'body' => 'Your account has been successfully verified.',
            ],
            'fr' => [
                'title' => 'Vérification du compte',
                'body' => 'Votre compte a été vérifié avec succès.',
            ]
        ];

        $translation = $translations[$locale] ?? $translations['fr'];

        return [
            'title' => $translation['title'],
            'body' => $translation['body'],
            'data' => [
                'type' => 'user_activated',
                'user_id' => $notifiable->id,
                'title_en' => 'Account Verified',
                'title_fr' => 'Vérification du compte',
                'message_en' => 'Your account has been successfully verified.',
                'message_fr' => 'Votre compte a été vérifié avec succès.',
                'locale' => $locale, // Send the locale used
            ],
        ];
    }
}
