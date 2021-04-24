<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;

class RequestReceived implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    protected $roomUser;
    public function __construct($roomUser)
    {
        $this->roomUser = $roomUser;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new PrivateChannel('Cinema.Notification.' . $this->roomUser->id);
    }

    public function broadcastAs()
    {
        return 'RequestReceived';
    }

    public function broadcastWith()
    {
        return ['new' => 1];
    }
}
