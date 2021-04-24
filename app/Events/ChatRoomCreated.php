<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;

class ChatRoomCreated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    protected $room;
    protected $roomUser;
    public function __construct($roomUser, $room)
    {
        $this->roomUser = $roomUser;
        $this->room = $room;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new PrivateChannel('Cinema.Chat.' . $this->roomUser['id']);
    }

    public function broadcastAs()
    {
        return 'ChatRoomCreated';
    }

    public function broadcastWith()
    {
        return ['room' => $this->room];
    }
}
