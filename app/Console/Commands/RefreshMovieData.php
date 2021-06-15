<?php

namespace App\Console\Commands;

use App\Enums\MovieTypeEnum;
use App\Models\Movie\Movie;
use App\Models\Movie\MovieDescription;
use App\Models\Movie\MovieTitle;
use GuzzleHttp\Client;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;
use Imdb\Config;
use Imdb\Exception;
use Imdb\Title;
use Imdb\TitleSearch;
use PHPHtmlParser\Dom;

class RefreshMovieData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cinema:refresh-movie-data';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Refresh movie datas from IMDB, Porthu, Mafab';

    /**
     * Create a new command instance.
     *
     * @return void
     */

    private $actionTypes = ['Everything', 'Titles', 'Descriptions', 'Poster', 'Photos', 'Genres', 'Writers', 'Directors', 'Actors'];
    private $actionTypesCount = ['Titles' => 2, 'Descriptions' => 2, 'Poster' => 1, 'Photos' => 999, 'Writers' => 999, 'Directors' => 999, 'Actors' => 999];
    private $movieTypes = ['All', 'Needed'];

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     * @throws \Exception
     */
    public function handle()
    {
        #region Gathering action types
        $actionTypes = $this->choice(
            'What do you want to refresh?',
             $this->actionTypes,
            0,
            1,
            true
        );
        $moviesType = $this->choice(
            'Execute it on all movies, or the needed ones?',
             $this->movieTypes,
            0,
            1,
            false
        );

        #endregion
        #region Calculating needed actions
        $movies = new Collection();
        $httpClient = new Client();
        $imdbConfig = new Config();
        $imdbConfig->language = 'hu-HU,hu,en';

        if(in_array('Everything', $actionTypes, true)) {
            $countRelations = collect(array_slice($this->actionTypes, 1))->map(function($type) {
                return mb_strtolower($type);
            });
        } else {
            $countRelations = [];

            foreach($actionTypes as $actionType) {
                $countRelations[] = mb_strtolower($actionType);
            }
        }

        switch($moviesType) {
            case 'All':
                $movies = Movie::withCount(array_splice($this->actionTypes, 1))->get();
                break;
            case 'Needed':
                $movies = Movie::withCount(array_splice($this->actionTypes, 1));

                foreach($countRelations as $relation) {
                    $movies = $movies->has($relation, '<', $this->actionTypesCount[Str::ucfirst($relation)]);
                }

                //$movies = $movies->where('id', '93384a69-5abb-41ca-b717-82b5b0a2642f');

                $movies = $movies->get();
                break;
        }
        #endregion
        #region Processing movies
        $this->info('Found movies: ' . $movies->count());

        $this->output->progressStart($movies->count());

        foreach($movies as $movie) {
            $needsImdbIDRefresh = false;

            if(( ($movie->imdb_id === null || $movie->imdb_id === '') && $movie->titles_count > 0)) {
                $needsImdbIDRefresh = true;
            }

            try {
                $imdb = new Title($movie->imdb_id, $imdbConfig);

                $realID = $imdb->real_id();

                $englishTitle = $movie->titles()->where('lang', 'en')->first();
                $hungarianTitle = $movie->titles()->where('lang', 'hu')->first();

                $imdbEnglishTitle = $imdb->orig_title() === '' ? $imdb->title() : $imdb->orig_title();
                $imdbHungarianTitle = $imdb->title();

                if($englishTitle && $hungarianTitle && $englishTitle->title === $hungarianTitle->title && $englishTitle->title !== $imdb->title()) {
                    $needsImdbIDRefresh = true;
                } else {
                    if ($englishTitle && $imdbEnglishTitle !== $englishTitle->title) {
                        $needsImdbIDRefresh = true;
                    } else if ($hungarianTitle && $imdbHungarianTitle !== $hungarianTitle->title) {
                        $needsImdbIDRefresh = true;
                    }
                }
            } catch (Exception $e) {
                $needsImdbIDRefresh = true;
            }

            if($needsImdbIDRefresh) {
                $englishTitle = $movie->titles()->where('lang', 'en')->first();
                $foundIMDB = false;

                if($englishTitle) {
                    $this->info('');
                    $this->alert('Wrong IMDB for movie: ' . $englishTitle->title);

                    $search = new TitleSearch();
                    $results = $search->search($englishTitle->title);

                    foreach($results as $result) {
                        $title = $result->orig_title() !== '' ? $result->orig_title() : $result->title();

                        if($title === $englishTitle->title && (integer)$result->year() === (integer)$movie->year && (string)(integer)$result->is_serial() === (string)$movie->type ) {
                            $this->info('IMDB acquired for movie: ' . $englishTitle->title);

                            $movie->update(
                                [
                                    'type' => $result->is_serial() ? (string)MovieTypeEnum::Series : (string)MovieTypeEnum::Movie,
                                    'year' => $result->year() ?? 0,
                                    'season' => $result->seasons() ?? 0,
                                    'length' => $result->runtime() ?? 0,
                                    'imdb_id' => 'tt'.$result->imdbid(),
                                    'imdb_rating' => $result->rating() ?? 0,
                                    'imdb_votes' => $result->votes() ?? 0,
                                ]);
                            $foundIMDB = true;

                            $imdb = new Title($movie->imdb_id, $imdbConfig);

                            break;
                        }
                    }
                }
                if(!$englishTitle || !$foundIMDB) {
                    $this->alert('Couldnt acquire IMDB for movie: ' . $movie->imdb_id);
                }

            }

            if($movie) {
                if(in_array('Titles', $actionTypes, true)) {
                    MovieTitle::updateOrCreate(['movie_id' => $movie->id, 'lang' => 'hu'],
                        [
                            'title' => $imdb->title()
                        ]);

                    MovieTitle::updateOrCreate(['movie_id' => $movie->id, 'lang' => 'en'],
                        [
                            'title' => Str::length($imdb->orig_title()) === 0 ? $imdb->title() : $imdb->orig_title()
                        ]);
                }

                if(in_array('Descriptions', $actionTypes, true)) {
                    $plots = collect($imdb->plot_split())->filter(static function($plot) {
                        return Str::length($plot['plot']) >= config('cinema.plot_minimum_length');
                    })->take(1)->map(static function($plot) {
                        return ['lang' => 'en', 'description' => $plot['plot']];
                    });

                    $movieTitle = $imdb->orig_title() !== '' ? $imdb->orig_title() : $imdb->title();

                    $origTitle = str_replace('/', ' ', $movieTitle);

                    $usableURL = ('https://www.mafab.hu/search/&search=' . urlencode($origTitle));

                    $found = false;

                    $dom = new Dom();
                    $dom->loadStr(file_get_contents($usableURL));
                    $foundMovies = $dom->find('.col-xs-13 .movie_title_link');
                    foreach($foundMovies as $index => $mafabMovie) {
                        $href = $dom->find('.col-xs-13 .movie_title_link')[$index]->getAttribute('href');
                        $title = $dom->find('.col-xs-13 small[style="font-size:11px;"]')[$index]->firstChild()->text;
                        $year = $dom->find('.col-xs-13 .pull-left')[$index*2]->firstChild()->text;

                        $movieLink = 'https://www.mafab.hu' . $href;

                        if(Str::contains($movieLink, Str::slug($movieTitle)) || (Str::contains($title, $movieTitle) && Str::contains($year, $imdb->year()))) {
                            $dom = new Dom();
                            $dom->loadStr(file_get_contents($movieLink));

                            $content = $dom->find('.bio-content');
                            if(!$found && isset($content[0])) {
                                $hungarianPlot = html_entity_decode($content[0]->innertext);

                                $plots->add(['lang' => 'hu', 'description' => trim($hungarianPlot)]);

                                $found = true;
                            }

                            if(!$found) {
                                preg_match('/(\d+).html/', $href, $matches);

                                $mafabMovieID = $matches[1];

                                $plotContent = $httpClient->request('POST', 'https://www.mafab.hu/includes/jquery/movies_ajax.php',
                                    [
                                        'form_params' => [
                                            'request' => 'official_bio',
                                            'movie_id' => $mafabMovieID,
                                        ]
                                    ]);

                                $plotContent = $plotContent->getBody()->getContents();

                                $dom = new Dom();
                                $dom->loadStr($plotContent);
                                $plot = $dom->find('.biobox_full_official p');

                                if(isset($plot[0])) {
                                    $hungarianPlot = html_entity_decode($plot[0]->firstChild()->text);

                                    $plots->add(['lang' => 'hu', 'description' => trim($hungarianPlot)]);

                                    $found = true;
                                }
                            }
                            break;
                        }
                    }

                    if(!$found) {
                        $usableURL = ('https://www.themoviedb.org/search?query=' . urlencode($origTitle));

                        $dom = new Dom();
                        $dom->loadStr(file_get_contents($usableURL));
                        $foundMovies = $dom->find('.results .card');
                        foreach($foundMovies as $index => $theMovieDBMovie) {
                            $a = $dom->find('.results .card');
                            if(isset($a[$index])) {
                                $href = $dom->find('.results .card a')[$index]->getAttribute('href');

                                if(isset($dom->find('.results .card .result h2')[$index]) && isset($dom->find('.results .card .release_date')[$index])) {
                                    $title = $dom->find('.results .card .result h2')[$index]->firstChild()->text;
                                    $year = $dom->find('.results .card .release_date')[$index]->firstChild()->text;

                                    $movieLink = 'https://www.themoviedb.org/' . $href . '?language=hu-HU';

                                    if(
                                        Str::contains($movieLink, Str::slug($movieTitle)) ||
                                        ((Str::contains(Str::lower($title), Str::lower($movieTitle)) || Str::contains(Str::lower($title), Str::lower($imdb->title())) || Str::contains(Str::lower($title), Str::lower($movie->getTitle()->title))) && Str::contains(Str::lower($year), Str::lower($imdb->year()))) ||
                                        $foundMovies->count() === 1) {
                                        $dom = new Dom();
                                        $dom->loadStr(file_get_contents($movieLink));

                                        $content = $dom->find('meta[property="og:description"]');

                                        if(!$found && isset($content[0])) {
                                            $hungarianPlot = html_entity_decode($content[0]->getAttribute('content'));

                                            $plots->add(['lang' => 'hu', 'description' => trim($hungarianPlot)]);

                                            $found = true;
                                        }
                                    }
                                }
                            }
                        }
                    }

                    if($plots->count() === 2) {
                        $this->info('Adding');
                    } else if($plots->where('lang', 'hu')->count() === 0) {
                        $this->info('English found');
                    } else {
                        $this->info('Hungarian found');
                    }

                    foreach($plots as $plot) {
                        MovieDescription::updateOrCreate(['movie_id' => $movie->id, 'lang' => $plot['lang']],
                            [
                                'movie_id' => $movie->id,
                                'lang' => $plot['lang'],
                                'description' => $plot['description']
                            ]);
                    }
                }
            }

            $this->output->progressAdvance();
        }

        $this->output->progressFinish();
        #endregion
        return 0;
    }
}
