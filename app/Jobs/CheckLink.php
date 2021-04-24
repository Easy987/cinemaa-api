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
    private $httpClient;
    private $errorTexts = [
        "not found",
        "error-code",
        "doesn't exist",
        "deleted by",
        "removed by",
        "can't find the file",
        "has been removed",
        "images/default/video_box/no.jpg" // Indavideo
    ];
    private $maxAttempts = 10;

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
        $this->httpClient = new Client(['http_errors' => false]);

        $attempts = 0;
        do {
            try {
                $response = $this->httpClient->get($this->link);

                $statusCode = $response->getStatusCode();

                $attempts++;

                sleep(1);
            } catch (ClientException $exception) {
                MovieLink::where('id', $this->id)->update(['status' => '3']);
            } catch (ServerException $exception) {
                MovieLink::where('id', $this->id)->update(['status' => '3']);
            }
        } while($statusCode !== 200 && $attempts < $this->maxAttempts);

        if($statusCode === 200) {
            $linkBody = Str::lower($response->getBody()->getContents());

            if(!Str::contains($this->link, 'dood') && Str::contains($linkBody, $this->errorTexts)) {
                MovieLink::where('id', $this->id)->update(['status' => '3']);
            }
        }
    }
}
