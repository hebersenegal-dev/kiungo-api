<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Notifications\Messages\MailMessage;

class CustomResetPassword extends ResetPassword
{
    public function toMail($notifiable)
    {
        $frontendUrl = config('app.frontend_url');
        $resetUrl = "{$frontendUrl}/reset-password?token={$this->token}&email=" . urlencode($notifiable->getEmailForPasswordReset());

        return (new MailMessage)
            ->subject('Réinitialisation de mot de passe')
            ->line('Vous avez demandé une réinitialisation de mot de passe.')
            ->action('Réinitialiser le mot de passe', $resetUrl)
            ->line('Si ce n\'était pas vous, vous pouvez ignorer ce message.');
    }
}
