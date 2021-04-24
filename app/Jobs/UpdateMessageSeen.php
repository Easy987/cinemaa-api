<?php

namespace App\Jobs;

use App\Models\ChatRoom\ChatRoomMessageSeen;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class UpdateMessageSeen implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    protected $messageID;
    protected $userID;
    protected $messageUserID;

    public function __construct($messageID, $messageUserID, $userID)
    {
        $this->messageID = $messageID;
        $this->messageUserID = $messageUserID;
        $this->userID = $userID;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        if($this->messageUserID !== $this->userID) {
            ChatRoomMessageSeen::firstOrCreate(['user_id' => $this->userID, 'message_id' => $this->messageID]);
        }
    }
}
