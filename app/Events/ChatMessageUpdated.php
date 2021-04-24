<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ChatMessageUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    protected $userID;
    protected $message;

    public $tries = 1;

    public function __construct($userID, $message)
    {
        $this->userID = $userID;
        $this->message = $message;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new PrivateChannel('Cinema.Chat.' . $this->userID);
    }

    public function broadcastAs()
    {
        return 'ChatMessageUpdated';
    }

    public function broadcastWith()
    {
        return ['message' => $this->message];
    }
}
