<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\BroadcastMessage;

class AcademicUpdateNotification extends Notification
{
    use Queueable;

    public function __construct(
        private string $title,
        private string $message,
        private ?string $url = null,
        private string $category = 'academic',
        private array $context = [],
    ) {}

    public function via(object $notifiable): array
    {
        return ['database', 'broadcast'];
    }

    public function toArray(object $notifiable): array
    {
        return ['title' => $this->title, 'message' => $this->message, 'url' => $this->url,
            'category' => $this->category, 'context' => $this->context];
    }

    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage($this->toArray($notifiable) + [
            'read' => false,
            'created_label' => 'Just now',
            'read_url' => route('account.notifications.read', $this->id, false),
        ]);
    }

    public function broadcastType(): string
    {
        return 'academic.update';
    }
}
