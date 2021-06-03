<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Notifications\PasswordNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class SendPasswordEmails extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cinema:send-password-emails';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Resets everyone password and sends them in email';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('Gathering users...');

        $users = User::where('email', 'LIKE', '%@%')->get();
        //$users = new Collection();
        //$users->push(User::where('username', 'Easy987')->first());

        foreach($users as $user) {
            $password = Str::random(16);
            $this->info($password);
            $user->update(['password' => $password]);

            $user->notify(new PasswordNotification($user->username, $password));

            sleep(1);
        }
    }
}
