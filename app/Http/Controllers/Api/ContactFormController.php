<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use App\Notifications\ContactFormNotification;

class ContactFormController extends Controller
{
    public function sendContactEmail(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'message' => 'required|string',
            'address' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:50',
        ]);

        // Get the recipient email from .env
        $recipientEmail = env('CONTACT_RECIPIENT_EMAIL', 'contact@kiungo.com');

        Notification::route('mail', $recipientEmail)
            ->notify(new ContactFormNotification(
                $request->name,
                $request->email,
                $request->message,
                $request->address,
                $request->phone
            ));

        return response()->json(['message' => 'Message sent successfully.']);
    }
}
