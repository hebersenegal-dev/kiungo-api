<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class PasswordResetOtp extends Notification
{
    use Queueable;

    public $otp;

    public function __construct($otp)
    {
        $this->otp = $otp;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
  {
      return (new MailMessage)
          ->subject(__('Password Reset OTP'))
          ->line(__('You have requested to reset your password. Use the OTP below to proceed:'))
          ->line('**' . $this->otp . '**')
          ->line(__('This OTP will expire in 10 minutes.'))
          ->line(__('If you didn\'t request this reset, please ignore this email.'));
  }

    public function toArray($notifiable)
    {
        return [
            'otp' => $this->otp,
        ];
    }
}
