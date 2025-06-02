<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\VerifyEmail as BaseVerifyEmail;
use Illuminate\Notifications\Messages\MailMessage;

class VerifyEmail extends BaseVerifyEmail
{
    public function toMail($notifiable)
    {
        $verificationUrl = $this->verificationUrl($notifiable);

        return (new MailMessage)
            ->subject("Verify Email / Vérifier l'adresse e-mail")
            ->greeting("Welcome to Kiungo ! / Bienvenue sur Kiungo !")
            ->line("Please click the button below to verify your email address. / Cliquez sur le bouton ci-dessous pour vérifier votre adresse e-mail.")
            ->action("Verify Email / Vérifier l'email", $verificationUrl)
            ->line("If you did not create an account, no further action is required. / Si vous n'avez pas créé de compte, aucune autre action n'est requise.");
    }
}
