<?php

namespace App\Observers;

use App\Models\Movie\MovieTitle;
use Illuminate\Support\Str;

class MovieTitleObserver
{
    public function creating(MovieTitle $movieTitle)
    {
        $movieTitle->slug = Str::slug($movieTitle->title);
    }
}
