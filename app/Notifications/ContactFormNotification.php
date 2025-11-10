<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class ContactFormNotification extends Notification
{
    use Queueable;

    public $name;
    public $email;
    public $message;
    public $address;
    public $phone;

    public function __construct($name, $email, $message, $address = null, $phone = null)
    {
        $this->name = $name;
        $this->email = $email;
        $this->message = $message;
        $this->address = $address;
        $this->phone = $phone;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject("New Contact Form Submission - Nouveau message de contact")
            ->greeting("New Contact Form Message / Nouveau message de formulaire de contact")
            ->line("You have received a new contact form submission. / Vous avez reçu un nouveau message via le formulaire de contact.")
            ->line("**Name / Nom:** {$this->name}")
            ->line("**Email:** {$this->email}")
            ->lineIf($this->phone, "**Phone / Téléphone:** {$this->phone}")
            ->lineIf($this->address, "**Address / Adresse:** {$this->address}")
            ->line("**Message:**")
            ->line($this->message)
            ->action("Reply to {$this->name} / Répondre à {$this->name}", "mailto:{$this->email}")
            ->line("This message was sent from your website contact form. / Ce message a été envoyé via le formulaire de contact de votre site web.");
    }

    public function toArray($notifiable)
    {
        return [
            'name' => $this->name,
            'email' => $this->email,
            'message' => $this->message,
            'address' => $this->address,
            'phone' => $this->phone,
        ];
    }
}
