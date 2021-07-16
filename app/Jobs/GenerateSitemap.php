<?php

namespace App\Jobs;

use App\Enums\MovieTypeEnum;
use App\Models\Movie\Movie;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\File;
use Spatie\Sitemap\Sitemap;
use Spatie\Sitemap\Tags\Url;

class GenerateSitemap implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */

    private $staticUrls = [
        '/',
        '/filmek',
        '/sorozatok',
        '/ajanlo',
        '/keresek',
        '/forum',
        '/uzenofal',
        '/felhasznalok',
        '/kereses',
        '/hirdetes',
        '/aszf',
        '/kapcsolat',
        '/bejelentkezes',
        '/regisztracio',
    ];

    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $siteMap = Sitemap::create();
        $movies = Movie::active()->get();

        foreach($this->staticUrls as $staticUrl) {
            $siteMap = $siteMap->add(Url::create(config('app.frontend_url') . $staticUrl)
                ->setChangeFrequency(Url::CHANGE_FREQUENCY_YEARLY)
                ->setPriority(0.1));
        }

        foreach($movies as $movie) {

            $movieTitle = $movie->getTitle();
            $movieUrl = ($movie->type === (string)MovieTypeEnum::Movie ? '/film' : '/sorozat') . '/' . $movieTitle->slug . '/' . $movie->year . '/' . $movie->length;

            $siteMap = $siteMap->add(Url::create(config('app.frontend_url') . $movieUrl)
                ->setChangeFrequency(Url::CHANGE_FREQUENCY_DAILY)
                ->setPriority(1));
        }

        File::delete(public_path('sitemap.xml'));

        $siteMap->writeToFile(public_path('sitemap.xml'));
    }
}
