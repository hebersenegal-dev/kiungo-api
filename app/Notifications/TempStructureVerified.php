<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class TempStructureVerified extends Notification
{
    use Queueable;

    public function via($notifiable)
    {
        return ['database', 'mail'];
    }

    public function toArray($notifiable)
    {
        return [
            'title' => 'Structure vérifiée',
            'message' => 'Une structure que vous aviez mentionne est officellement sur la plateforme.',
        ];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Votre structure est maintenant vérifiée')
            ->line('Félicitations ! La structure temporaire associée à votre compte a été vérifiée.')
            ->action('Voir les détails', url('/structures')) // ajustez l'URL selon vos routes
            ->line('Merci de faire partie de notre plateforme.');
    }
}
