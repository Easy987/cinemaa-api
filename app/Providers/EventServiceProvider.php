<?php

namespace App\Providers;

use App\Listeners\MessageLoggedListener;
use App\Models\Movie\Movie;
use App\Models\Movie\MovieTitle;
use App\Observers\MovieObserver;
use App\Observers\MovieTitleObserver;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Log\Events\MessageLogged;
use Illuminate\Support\Facades\Event;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        MovieTitle::observe(MovieTitleObserver::class);
    }
}
