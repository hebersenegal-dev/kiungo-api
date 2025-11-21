<?php

namespace App\Notifications\Channels;

use Illuminate\Notifications\Notification;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification as FcmNotification;

class FcmChannel
{
    protected $messaging;

    public function __construct()
    {
        $factory = (new Factory)
            ->withServiceAccount(config('firebase.credentials'));

        $this->messaging = $factory->createMessaging();
    }

    public function send($notifiable, Notification $notification)
    {
        if (!$token = $notifiable->fcm_token) {
            return;
        }

        $messageData = $notification->toFcm($notifiable);

        $message = CloudMessage::withTarget('token', $token)
            ->withNotification(FcmNotification::create(
                $messageData['title'],
                $messageData['body']
            ))
            ->withData($messageData['data'] ?? []);

        try {
            $this->messaging->send($message);
        } catch (\Throwable $e) {
            \Log::error('FCM send failed', ['error' => $e->getMessage()]);
        }
    }
}
