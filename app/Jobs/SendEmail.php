<?php

namespace App\Jobs;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

class SendEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $users;
    protected $notification;

    public function __construct($users, $notification)
    {
        $this->users = $users;
        $this->notification = $notification;
    }

    public function handle()
    {
        if($this->users instanceof User) {
            $this->users->notify($this->notification);
        } else {
            foreach($this->users as $user)
            {
                $user->notify($this->notification);
            }
        }
    }
}
