<?php

return [
    'genre_list' => explode(',', env('CINEMA_GENRE_LIST',"")),
    'allowed_langs' => explode(',', env('CINEMA_ALLOWED_LANGS',"")),
    'photo_cache_time' => env('CINEMA_PHOTO_CACHE_TIME', 3600),
    'plot_minimum_length' => env('CINEMA_PLOT_MINIMUM_LENGTH', 120),
    'link_types' => explode(',', env('CINEMA_LINK_TYPES',"")),
    'language_types' => explode(',', env('CINEMA_LANGUAGE_TYPES',"")),
];
