<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use App\Models\Experience;
use App\Notifications\Channels\FcmChannel;

class NewProjectForStructure extends Notification
{
    public Experience $experience;
    protected $userLocale; // Add locale support

    public function __construct(Experience $experience, $userLocale = null)
    {
        $this->experience = $experience;
        $this->userLocale = $userLocale;
    }

    public function via($notifiable)
    {
        return ['database', 'mail', FcmChannel::class];
    }

    public function toArray($notifiable)
    {
        return [
            'title_en' => 'New Project Added',
            'title_fr' => 'Nouveau projet ajouté',
            'message_en' => 'A new project concerning your structure has been added. Please verify it.',
            'message_fr' => 'Un nouveau projet concernant votre structure a été ajouté. Veuillez le vérifier.',
            'temp_project_id' => $this->experience->temp_project_id,
            'structure_name' => $this->experience->structure->name ?? 'Your structure', // Added for context
            'type' => 'new_project',
        ];
    }

    public function toMail($notifiable)
    {
        // Determine which language to use
        $locale = $this->userLocale ?? $notifiable->locale ?? 'fr';

        if ($locale === 'fr') {
            return (new MailMessage)
                ->subject('Nouveau projet pour votre structure')
                ->line('Un nouveau projet concernant votre structure a été ajouté.')
                ->action('Vérifier le projet', url('/temp-projects/' . $this->experience->temp_project_id))
                ->line('Veuillez le vérifier dès que possible.');
        } else {
            return (new MailMessage)
                ->subject('New project for your structure')
                ->line('A new project concerning your structure has been added.')
                ->action('Verify project', url('/temp-projects/' . $this->experience->temp_project_id))
                ->line('Please verify it as soon as possible.');
        }
    }

    public function toFcm($notifiable)
    {
        // Determine which language to use based on user's locale
        $locale = $this->userLocale ?? $notifiable->locale ?? 'fr';

        // Log for debugging (optional)
        \Log::info('Sending NewProjectForStructure notification', [
            'user_id' => $notifiable->id,
            'structure_id' => $this->experience->structure_id ?? null,
            'temp_project_id' => $this->experience->temp_project_id,
            'user_locale' => $locale,
        ]);

        // Translations
        $translations = [
            'en' => [
                'title' => 'New Project Added',
                'body' => 'A new project concerning your structure has been added. Please verify it.',
            ],
            'fr' => [
                'title' => 'Nouveau projet ajouté',
                'body' => 'Un nouveau projet concernant votre structure a été ajouté. Veuillez le vérifier.',
            ]
        ];

        $translation = $translations[$locale] ?? $translations['fr'];

        return [
            'title' => $translation['title'], // User's preferred language
            'body' => $translation['body'],   // User's preferred language
            'data' => [
                'type' => 'new_project',
                'temp_project_id' => $this->experience->temp_project_id,
                'experience_id' => $this->experience->id,
                'structure_id' => $this->experience->structure_id,
                'title_en' => 'New Project Added',
                'title_fr' => 'Nouveau projet ajouté',
                'message_en' => 'A new project concerning your structure has been added. Please verify it.',
                'message_fr' => 'Un nouveau projet concernant votre structure a été ajouté. Veuillez le vérifier.',
                'locale' => $locale, // Send the locale used
                // Add structure name if available
                'structure_name' => $this->experience->structure->name ?? null,
            ],
        ];
    }
}
