<?php

namespace App\Notifications\Channels;

use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Http;

class FcmChannel
{
    public function send($notifiable, Notification $notification)
    {
        if (!$token = $notifiable->fcm_token) {
            return;
        }

        $message = $notification->toFcm($notifiable);

        Http::withHeaders([
            'Authorization' => 'key=' . env('FCM_SERVER_KEY'),
            'Content-Type' => 'application/json',
        ])->post('https://fcm.googleapis.com/fcm/send', [
            'to' => $token,
            'notification' => [
                'title' => $message['title'],
                'body' => $message['body'],
                'sound' => 'default',
            ],
            'data' => $message['data'] ?? [],
        ]);
    }
}
