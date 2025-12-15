<?php

namespace App\Notifications;

use App\Models\TemporaryProject;
use App\Notifications\Channels\FcmChannel;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class TempProjectVerified extends Notification
{
    protected TemporaryProject $tempProject;
    protected int $officialId;
    protected $userLocale; // Add locale support

    /**
     * Create a new notification instance.
     */
    public function __construct(TemporaryProject $tempProject, int $officialId, $userLocale = null)
    {
        $this->tempProject = $tempProject;
        $this->officialId = $officialId;
        $this->userLocale = $userLocale;
    }

    /**
     * Notification delivery channels.
     */
    public function via($notifiable): array
    {
        return ['mail', 'database', FcmChannel::class];
    }

    /**
     * Email representation.
     */
    public function toMail($notifiable): MailMessage
    {
        // Determine which language to use
        $locale = $this->userLocale ?? $notifiable->locale ?? 'fr';

        if ($locale === 'fr') {
            return (new MailMessage)
                ->subject('Projet temporaire validé')
                ->line("Le projet temporaire « {$this->tempProject->title} » a été validé.")
                ->line("ID officiel : {$this->officialId}");
        } else {
            return (new MailMessage)
                ->subject('Temporary project validated')
                ->line("The temporary project « {$this->tempProject->title} » has been validated.")
                ->line("Official ID: {$this->officialId}");
        }
    }

    /**
     * Array representation (database).
     */
    public function toArray($notifiable): array
    {
        return [
            'title_en' => 'Temporary Project Validated',
            'title_fr' => 'Projet temporaire validé',
            'message_en' => "The project « {$this->tempProject->title} » has been validated.",
            'message_fr' => "Le projet « {$this->tempProject->title} » a été validé. ID.",
            'temporary_project_id' => $this->tempProject->id,
            'official_id' => $this->officialId,
            'project_title' => $this->tempProject->title,
            'type' => 'temp_project_verified',
        ];
    }

    /**
     * FCM push notification payload.
     */
    public function toFcm($notifiable): array
    {
        // Determine which language to use based on user's locale
        $locale = $this->userLocale ?? $notifiable->locale ?? 'fr';

        // Log for debugging (optional)
        \Log::info('Sending TempProjectVerified notification', [
            'user_id' => $notifiable->id,
            'project_id' => $this->tempProject->id,
            'official_id' => $this->officialId,
            'user_locale' => $locale,
            'project_title' => $this->tempProject->title,
        ]);

        // Translations with dynamic project title
        $translations = [
            'en' => [
                'title' => 'Temporary Project Validated',
                'body' => "The project « {$this->tempProject->title} » has been validated.",
            ],
            'fr' => [
                'title' => 'Projet temporaire validé',
                'body' => "Le projet « {$this->tempProject->title} » a été validé.",
            ]
        ];

        $translation = $translations[$locale] ?? $translations['fr'];

        return [
            'title' => $translation['title'], // User's preferred language
            'body' => $translation['body'],   // User's preferred language
            'data' => [
                'type' => 'temp_project_verified',
                'temporary_project_id' => $this->tempProject->id,
                'official_id' => $this->officialId,
                'title_en' => 'Temporary Project Validated',
                'title_fr' => 'Projet temporaire validé',
                'message_en' => "The project « {$this->tempProject->title} » has been validated.",
                'message_fr' => "Le projet « {$this->tempProject->title} » a été validé.",
                'project_title' => $this->tempProject->title,
                'locale' => $locale, // Send the locale used
            ],
        ];
    }
}
