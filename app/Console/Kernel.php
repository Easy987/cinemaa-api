<?php

namespace App\Console;

use App\Jobs\CheckLink;
use App\Jobs\GenerateSitemap;
use App\Models\Movie\MovieLink;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Facades\Log;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        //
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->call(function () {
            $links = MovieLink::where('status', '!=', '3')->whereHas('site', function(\Illuminate\Database\Eloquent\Builder $subQuery) {
                $subQuery->whereNotIn('name', ['STREAMZZ', 'STREAMCRYPT', 'WOLFSTREAM']);
            })->get();

            foreach($links as $link) {
                dispatch(new CheckLink($link->id, $link->link))->onQueue('low');
            }
        })->daily();

        $schedule->job(new GenerateSitemap)->monthly();
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
