<?php

namespace App\Jobs;

use App\Models\Movie\MovieLink;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\BadResponseException;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\ServerException;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;

class CheckLink implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    private $id;
    private $link;
    private $errorTexts = [
        "deleted by",
        "removed by",
        "deletion",
        "error-code",
        "doesn't exist",
        "doesnt exist",
        "not found",
        "not be found",
        "can't find",
        "cant find",
        "has been removed",
        "0.0 Mb",
        "https://vidlox.me/player_clappr1/small.mp4", // Vidlox
        "images/default/video_box/no.jpg", // Indavideo
        "We’re sorry",
        "Video not found",
        'content="BLOCKED"'
    ];
    private $maxAttempts = 3;

    public function __construct($id, $link)
    {
        $this->id = $id;
        $this->link = $link;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $httpClient = new Client(['http_errors' => false]);

        $attempts = 0;
        do {
            $response = null;
            $statusCode = null;
            try {
                $response = $httpClient->get($this->link, ['allow_redirects' => true]);

                $statusCode = $response->getStatusCode();

                $attempts++;

                sleep(1);
            } catch(\Exception $exception) {
                MovieLink::where('id', $this->id)->update(['status' => '3']);
                $attempts = $this->maxAttempts;
            }
        } while($statusCode !== 200 && $attempts < $this->maxAttempts);

        if($response && $statusCode === 200) {
            $linkBody = Str::lower($response->getBody()->getContents());

            if(!Str::contains($this->link, 'dood') && !Str::contains($this->link, 'streamzz') && Str::contains($linkBody, $this->errorTexts)) {
                MovieLink::where('id', $this->id)->update(['status' => '3']);
            } else {
                MovieLink::where('id', $this->id)->update(['status' => '2']);
            }
        }
    }
}
