<?php

namespace App\Notifications;

use App\Notifications\Channels\FcmChannel;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TempStructureVerified extends Notification
{
    protected $userLocale; // Add locale support

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
            'title_en' => 'Structure Verified',
            'title_fr' => 'Structure vérifiée',
            'message_en' => 'A structure you mentioned is now officially on the platform.',
            'message_fr' => 'Une structure que vous aviez mentionnée est officiellement sur la plateforme.',
            'type' => 'temp_structure_verified',
            'user_id' => $notifiable->id,
        ];
    }

    public function toMail($notifiable)
    {
        // Determine which language to use for email
        $locale = $this->userLocale ?? $notifiable->locale ?? 'fr';

        if ($locale === 'fr') {
            return (new MailMessage)
                ->subject('Votre structure est maintenant vérifiée')
                ->line('Félicitations ! La structure temporaire associée à votre compte a été vérifiée.')
                ->action('Voir les détails', url('/structures'))
                ->line('Merci de faire partie de notre plateforme.');
        } else {
            return (new MailMessage)
                ->subject('Your structure is now verified')
                ->line('Congratulations! The temporary structure associated with your account has been verified.')
                ->action('View details', url('/structures'))
                ->line('Thank you for being part of our platform.');
        }
    }

    public function toFcm($notifiable)
    {
        // Determine which language to use based on user's locale
        // Priority: 1. Constructor locale, 2. User's DB locale, 3. Default French
        $locale = $this->userLocale ?? $notifiable->locale ?? 'fr';

        // Log for debugging (optional)
        \Log::info('Sending TempStructureVerified notification', [
            'user_id' => $notifiable->id,
            'constructor_locale' => $this->userLocale,
            'user_db_locale' => $notifiable->locale,
            'final_locale_used' => $locale,
        ]);

        $translations = [
            'en' => [
                'title' => 'Structure Verified',
                'body' => 'A structure you mentioned is now officially on the platform.',
            ],
            'fr' => [
                'title' => 'Structure vérifiée',
                'body' => 'Une structure que vous aviez mentionnée est officiellement sur la plateforme.',
            ]
        ];

        $translation = $translations[$locale] ?? $translations['fr'];

        return [
            'title' => $translation['title'], // User's preferred language for FCM
            'body' => $translation['body'],   // User's preferred language for FCM
            'data' => [
                'type' => 'temp_structure_verified',
                'user_id' => $notifiable->id,
                'title_en' => 'Structure Verified',
                'title_fr' => 'Structure vérifiée',
                'message_en' => 'A structure you mentioned is now officially on the platform.',
                'message_fr' => 'Une structure que vous aviez mentionnée est officiellement sur la plateforme.',
                'locale' => $locale, // Send the locale used
            ],
        ];
    }
}
