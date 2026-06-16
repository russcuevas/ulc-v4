<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class NewClientNotification extends Notification
{
    use Queueable;

    protected $client;

    public function __construct($client)
    {
        $this->client = $client;
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toArray($notifiable)
    {
        return [
            'type' => 'new_client',
            'client_id' => $this->client->id ?? null,
            'message' => 'New client added: ' . ($this->client->fullname ?? 'Client'),
        ];
    }
}
