<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use App\Notifications\Channels\FcmChannel;

class TempProjectRejected extends Notification
{
    protected $project;
    protected $reason;
    protected $userLocale; // Add locale support

    /**
     * Create a new notification instance.
     */
    public function __construct($project, $reason = null, $userLocale = null)
    {
        $this->project = $project;
        $this->reason = $reason;
        $this->userLocale = $userLocale;
    }

    /**
     * Notification channels.
     */
    public function via($notifiable)
    {
        return ['database', 'mail', FcmChannel::class];
    }

    /**
     * Database notification.
     */
    public function toArray($notifiable)
    {
        return [
            'title_en' => 'Project Rejected',
            'title_fr' => 'Projet rejeté',
            'message_en' => 'Your temporary project "' . $this->project->title . '" has been rejected.' . ($this->reason ? ' Reason: ' . $this->reason : ''),
            'message_fr' => 'Votre projet temporaire "' . $this->project->title . '" a été rejeté.' . ($this->reason ? ' Motif : ' . $this->reason : ''),
            'reason' => $this->reason,
            'project_id' => $this->project->id,
            'project_title' => $this->project->title,
            'type' => 'temp_project_rejected',
        ];
    }

    /**
     * Email notification.
     */
    public function toMail($notifiable)
    {
        // Determine which language to use
        $locale = $this->userLocale ?? $notifiable->locale ?? 'fr';

        if ($locale === 'fr') {
            return (new MailMessage)
                ->subject('Votre projet temporaire a été rejeté')
                ->greeting('Bonjour ' . $notifiable->name . ',')
                ->line('Nous vous informons que votre projet temporaire intitulé "' . $this->project->title . '" a été rejeté.')
                ->line('Motif du rejet : ' . ($this->reason ?? 'Non spécifié.'))
                ->line('Vous pouvez soumettre à nouveau votre projet après correction.')
                ->action('Voir vos projets', url('/projects'))
                ->line('Merci de votre compréhension.');
        } else {
            return (new MailMessage)
                ->subject('Your temporary project has been rejected')
                ->greeting('Hello ' . $notifiable->name . ',')
                ->line('We inform you that your temporary project titled "' . $this->project->title . '" has been rejected.')
                ->line('Rejection reason: ' . ($this->reason ?? 'Not specified.'))
                ->line('You can resubmit your project after correction.')
                ->action('View your projects', url('/projects'))
                ->line('Thank you for your understanding.');
        }
    }

    /**
     * FCM mobile push notification.
     */
    public function toFcm($notifiable)
    {
        // Determine which language to use based on user's locale
        $locale = $this->userLocale ?? $notifiable->locale ?? 'fr';

        // Log for debugging (optional)
        \Log::info('Sending TempProjectRejected notification', [
            'user_id' => $notifiable->id,
            'project_id' => $this->project->id,
            'project_title' => $this->project->title,
            'user_locale' => $locale,
            'has_reason' => !empty($this->reason),
        ]);

        // Translations with dynamic project title and optional reason
        $baseTranslations = [
            'en' => [
                'title' => 'Project Rejected',
                'body_without_reason' => 'Your project "' . $this->project->title . '" has been rejected.',
                'body_with_reason' => 'Your project "' . $this->project->title . '" has been rejected. Reason: ' . $this->reason,
            ],
            'fr' => [
                'title' => 'Projet rejeté',
                'body_without_reason' => 'Votre projet "' . $this->project->title . '" a été rejeté.',
                'body_with_reason' => 'Votre projet "' . $this->project->title . '" a été rejeté. Motif : ' . $this->reason,
            ]
        ];

        $translation = $baseTranslations[$locale] ?? $baseTranslations['fr'];

        // Choose body based on whether there's a reason
        $body = $this->reason ? $translation['body_with_reason'] : $translation['body_without_reason'];

        return [
            'title' => $translation['title'], // User's preferred language
            'body' => $body, // User's preferred language
            'data' => [
                'type' => 'temp_project_rejected',
                'project_id' => $this->project->id,
                'reason' => $this->reason,
                'title_en' => 'Project Rejected',
                'title_fr' => 'Projet rejeté',
                'message_en' => 'Your temporary project "' . $this->project->title . '" has been rejected.' . ($this->reason ? ' Reason: ' . $this->reason : ''),
                'message_fr' => 'Votre projet temporaire "' . $this->project->title . '" a été rejeté.' . ($this->reason ? ' Motif : ' . $this->reason : ''),
                'project_title' => $this->project->title,
                'locale' => $locale, // Send the locale used
            ],
        ];
    }
}
