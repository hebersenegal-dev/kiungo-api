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
        return (new MailMessage)
            ->subject('Your structure is now verified')
            ->line('Congratulations! The temporary structure associated with your account has been verified.')
            ->action('View details', url('/structures'))
            ->line('Thank you for being part of our platform.');
    }

    public function toFcm($notifiable)
    {
        return [
            'title' => 'Structure vérifiée',
            'body' => 'Une structure que vous aviez mentionnée est officiellement sur la plateforme.',
            'data' => [
                'type' => 'temp_structure_verified',
                'user_id' => $notifiable->id,
                'title_en' => 'Structure Verified',
                'title_fr' => 'Structure vérifiée',
                'message_en' => 'A structure you mentioned is now officially on the platform.',
                'message_fr' => 'Une structure que vous aviez mentionnée est officiellement sur la plateforme.',
            ],
        ];
    }
}
