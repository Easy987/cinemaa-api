<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ChatMessageSent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, Queueable;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    protected $message;
    protected $lastMessage;
    protected $roomUser;
    public function __construct($roomUser, $message, $lastMessage)
    {
        $this->roomUser = $roomUser;
        $this->message = $message;
        $this->lastMessage = $lastMessage;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return [
            new PrivateChannel('Cinema.Chat.' . $this->roomUser->id),
            new PrivateChannel('Cinema.Notification.' . $this->roomUser->id)
        ];
    }

    public function broadcastAs()
    {
        return 'ChatMessageSent';
    }

    public function broadcastWith()
    {
        return ['message' => $this->message, 'last' => $this->lastMessage];
    }
}
