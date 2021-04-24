<?php

namespace App\Jobs;

use App\Events\ChatMessageUpdated;
use App\Http\Resources\ChatMessageResource;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class UpdateAllMessageSeen implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    protected $messages;
    protected $userID;
    protected $roomUsers;
    protected $privateRoom;

    public $timeout = 30;

    public function __construct($messages, $roomUsers, $userID, $privateRoom)
    {
        $this->messages = $messages;
        $this->userID = $userID;
        $this->roomUsers = $roomUsers;
        $this->privateRoom = $privateRoom;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        foreach($this->messages as $message) {
            if(!$message->seenByUser($this->userID) && $message->user_id !== $this->userID){
                dispatch(new UpdateMessageSeen($message->id, $message->user->id, $this->userID));

                if($this->privateRoom) {
                    foreach($this->roomUsers as $roomUser) {
                        broadcast(new ChatMessageUpdated($roomUser->id, new ChatMessageResource($message, $this->userID)));
                    }
                }
            }
        }
    }
}
