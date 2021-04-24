<?php

namespace App\Jobs;

use App\Models\Movie\Movie;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class DownloadMovie implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    protected $imdbID;
    protected $porthuID;
    protected $userID;

    public function __construct($imdbID, $porthuID, $userID)
    {
        $this->imdbID = $imdbID;
        $this->porthuID = $porthuID;
        $this->userID = $userID;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        Log::info('Downloading movie: ' . $this->imdbID);
        Movie::download($this->imdbID, $this->porthuID, $this->userID);
        Log::info('Movie download successful');
    }
}
