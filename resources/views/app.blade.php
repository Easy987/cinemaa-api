<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Filmforrás</title>

    <link rel="apple-touch-icon" sizes="180x180" href="{{ env('FRONTEND_URL') }}/img/icons/filmforras/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ env('FRONTEND_URL') }}/img/icons/filmforras/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ env('FRONTEND_URL') }}/img/icons/filmforras/favicon-16x16.png">
    <link rel="manifest" href="{{ env('FRONTEND_URL') }}/img/icons/filmforras/site.webmanifest">
    <link rel="shortcut icon" href="{{ env('FRONTEND_URL') }}/img/icons/filmforras/favicon.ico">
    <link rel="mask-icon" href="{{ env('FRONTEND_URL') }}/img/icons/filmforras/safari-pinned-tab.svg" color="#5bbad5">
    <meta name="msapplication-TileColor" content="#da532c">
    <meta name="theme-color" content="#ffffff">

    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css"
          integrity="sha512-iBBXm8fW90+nuLcSKlbmrPcLa0OT92xO1BIsZ+ywDWZCvqsWgccV3gFoRBv0z+8dLJgyAHIhR35VZc2oM/gI1w=="
          crossorigin="anonymous"/>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta3/dist/css/bootstrap.min.css" rel="stylesheet"
          integrity="sha384-eOJMYsd53ii+scO/bJGFsiCZc+5NDVN2yr8+0RDqr0Ql0h+rP48ckxlpbzKgwra6" crossorigin="anonymous">
    <link rel="stylesheet" href="/jquery.mCustomScrollbar.min.css" />

    <script data-ad-client="ca-pub-4562643697086060" async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"></script>
    <style>
        body {
            font-family: 'Nunito', sans-serif;
        }

        .card {
            height: 370px;
            margin-top: auto;
            margin-bottom: auto;
            width: 400px;
            background-color: rgba(0, 0, 0, 0.5) !important;
        }

        .social_icon span {
            font-size: 60px;
            margin-left: 10px;
            color: #f77f00;
        }

        .social_icon span:hover {
            color: white;
            cursor: pointer;
        }

        .card-header h3 {
            color: white;
        }

        .social_icon {
            position: absolute;
            right: 20px;
            top: -45px;
        }

        .input-group-prepend span {
            width: 50px;
            background-color: #f77f00;
            color: black;
            border: 0 !important;
        }

        input:focus {
            outline: 0 0 0 0 !important;
            box-shadow: 0 0 0 0 !important;

        }

        .remember {
            color: white;
        }

        .remember input {
            width: 20px;
            height: 20px;
            margin-left: 15px;
            margin-right: 5px;
        }

        .login_btn {
            color: black;
            background-color: #f77f00;
        }

        .login_btn:hover {
            color: black;
            background-color: white;
        }

        .links {
            color: white;
        }

        .links a {
            margin-left: 4px;
        }

        .accordion-item {
            background-color: black;
        }

        .accordion-button, .accordion-button:not(.collapsed) {
            color: #f77f00;
            background: #222831;
        }

        .accordion-button:focus {
            border-color: #f77f00;
            box-shadow: none;
        }

        .video-container {
            overflow: hidden;
            position: relative;
            width:100%;
        }

        .video-container::after {
            padding-top: 56.25%;
            display: block;
            content: '';
        }

        .video-container iframe {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
        }

        .smaller-font {
            font-size: 12px;
        }

        .mCS-my-theme.mCSB_scrollTools .mCSB_dragger .mCSB_dragger_bar{ background-color: orange; }
        .mCS-my-theme.mCSB_scrollTools .mCSB_draggerRail{ background-color: white; }
    </style>
</head>
<body style="background-color: #1a191f; color: white;">
<div class="container">
    <div class="row">
        <div class="col-1"></div>
        <div class="col-10">
            <div class="row pt-5 pb-3">
                <div class="col-md-4 col-12">
                    <img class="img-fluid" src="{{ env('FRONTEND_URL') }}/img/forras.png">
                </div>
                <div class="col-md-3 col-12 pt-md-0 pt-4" style="margin-left: auto;">
                    <form>
                        <div class="input-group form-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="fas fa-user"></i></span>
                            </div>
                            <input class="form-control" placeholder="{{ __('base.username') }}" style="height: 29px;"
                                   type="text">

                        </div>
                        <div class="input-group form-group pt-2 pb-2">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="fas fa-key"></i></span>
                            </div>
                            <input type="password" style="height: 29px;" class="form-control" placeholder="{{ __('base.password') }}">
                        </div>
                        <div class="form-group">
                            <input type="submit" value="{{ __('base.login') }}" class="btn float-right login_btn">
                        </div>
                    </form>
                </div>
            </div>
            <div class="row p-md-5 p-2" style="background: rgba(0, 0, 0, 0.18)">
                @if($movie === null)
                    <h1>{{ $lang === 'hu' ? 'A megadott film nem található!' : 'The given movie doesnt exists!' }}</h1>
                @else
                    <h1 class="text-center">{{ $movie['titles'][$lang]  }}</h1>
                    <div class="row">
                        <div class="col-lg-3 col-12 text-md-left text-center">
                            <img class="img-fluid" style="max-width: 256px;" src="{{ $movie['poster'] }}" alt="poster">
                        </div>
                        <div class="col-lg-8 col-12 pt-md-0 pt-3" style="margin-left: auto;">
                            <!-- 21:9 aspect ratio -->
                            <div class="video-container">
                                <iframe class="embed-responsive-item"
                                        src="https://www.youtube.com/embed/{{ count($movie['videos']) > 0 ? $movie['videos'][0]['youtube_id'] : '' }}"
                                        width="640" height="360" allowfullscreen></iframe>
                            </div>
                        </div>
                    </div>
                    <div class="row p-4">
                        {{ isset($movie['descriptions'][$lang]) ? $movie['descriptions'][$lang] : '' }}
                    </div>
                    <div class="row pb-3">
                        <h4 style="color: #f77f00">{{ $lang === 'hu' ? 'Online nézhető linkek' : 'Online links' }}</h4>
                    </div>
                    @if($movie['type'] === 0)
                        @if(count($movie['links']) === 0)
                            <h3>{{ $lang === 'hu' ? 'Jelenleg egy darab link sincs feltöltve!' : 'There arent any uploaded links for this movie.' }}</h3>
                        @else
                            <div class="scroll table-responsive smaller-font">
                                <table class="table" style="color: white;">
                                    <thead>
                                    <tr class="text-center">
                                        <th scope="col">{{ __('base.play') }}</th>
                                        <th scope="col">{{ __('base.site') }}</th>
                                        <th scope="col">{{ __('base.quality') }}</th>
                                        <th scope="col">{{ __('base.language') }}</th>
                                        <th scope="col">{{ __('base.date') }}</th>
                                        <th scope="col">{{ __('base.uploader') }}</th>
                                        <th scope="col">{{ __('base.report') }}</th>
                                        <th scope="col">{{ __('base.views') }}</th>
                                    </tr>
                                    </thead>
                                    <tbody class="text-center">
                                    @foreach($links as $link)
                                        <tr>
                                            <td class="text-center"><a data-toggle="tooltip" data-placement="top" title="{{ __('base.play') }}" target="_blank" href="{{ route('link', ['link_id' => $link['id']]) }}"><i class="fas fa-play"></i></a></td>
                                            <td>{{ $link['site'] ? $link['site']['name'] : __('base.unknown') }}</td>
                                            <td>{{ __('base.qualities.' . $link['linkType']['name']) }}</td>
                                            <td><img style="width: 32px;" data-toggle="tooltip" data-placement="top" title="{{ __('base.languageTypes.' . $link['languageType']['name']) }}" src="{{ env('FRONTEND_URL') }}/img/flags/{{ $link['flagName'] }}.png"></td>
                                            <td>{{ \Carbon\Carbon::parse($link['created_at'])->format('Y-m-d H:i:s')  }}</td>
                                            <td>{{ $link['user'] ? ($link['user']['public_name'] === 1 ? $link['user']['username'] : __('base.unknown')) : __('base.unknown')  }}</td>
                                            <td class="text-center"><button data-id="{{ $link['id'] }}" class="reportButton"><i data-toggle="tooltip" data-placement="top" title="{{ __('base.report') }}" class="fas fa-bug"></i></button></td>
                                            <td class="text-center">{{ $link['views'] }}</td>
                                        </tr>
                                    @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    @else
                        @if($parts)
                            @foreach($links as $partIndex => $part)
                                <div class="accordion" id="accordionPart{{ $partIndex }}">
                                    <div class="accordion-item">
                                        <h2 class="accordion-header" id="headingPart{{ $partIndex }}">
                                            <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapsePart{{ $partIndex }}" aria-expanded="true" aria-controls="collapsePart{{ $partIndex }}">
                                                {{ $partIndex }}. {{ __('base.part') }}
                                            </button>
                                        </h2>
                                        <div id="collapsePart{{ $partIndex }}" class="accordion-collapse collapse" aria-labelledby="headingPart{{ $partIndex }}" data-bs-parent="#accordionPart{{ $partIndex }}">
                                            <div class="accordion-body">

                                                <div class="scroll table-responsive smaller-font">
                                                    <table class="table" style="color: white;">
                                                        <thead>
                                                        <tr class="text-center">
                                                            <th scope="col">{{ __('base.play') }}</th>
                                                            <th scope="col">{{ __('base.site') }}</th>
                                                            <th scope="col">{{ __('base.quality') }}</th>
                                                            <th scope="col">{{ __('base.language') }}</th>
                                                            <th scope="col">{{ __('base.date') }}</th>
                                                            <th scope="col">{{ __('base.uploader') }}</th>
                                                            <th scope="col">{{ __('base.report') }}</th>
                                                            <th scope="col">{{ __('base.views') }}</th>
                                                        </tr>
                                                        </thead>
                                                        <tbody class="text-center">
                                                        @foreach($part as $link)
                                                            <tr>
                                                                <td class="text-center"><a data-toggle="tooltip" data-placement="top" title="{{ __('base.play') }}" target="_blank" href="{{ route('link', ['link_id' => $link['id']]) }}"><i class="fas fa-play"></i></a></td>
                                                                <td>{{ $link['site'] ? $link['site']['name'] : __('base.unknown') }}</td>
                                                                <td>{{ __('base.qualities.' . $link['linkType']['name']) }}</td>
                                                                <td><img style="width: 32px;" data-toggle="tooltip" data-placement="top" title="{{ __('base.languageTypes.' . $link['languageType']['name']) }}" src="{{ env('FRONTEND_URL') }}/img/flags/{{ $link['flagName'] }}.png"></td>
                                                                <td>{{ \Carbon\Carbon::parse($link['created_at'])->format('Y-m-d H:i:s')  }}</td>
                                                                <td>{{ $link['user'] ? ($link['user']['public_name'] === 1 ? $link['user']['username'] : __('base.unknown')) : __('base.unknown')  }}</td>
                                                                <td class="text-center"><button data-id="{{ $link['id'] }}" class="reportButton"><i data-toggle="tooltip" data-placement="top" title="{{ __('base.report') }}" class="fas fa-bug"></i></button></td>
                                                                <td class="text-center">{{ $link['views'] }}</td>
                                                            </tr>
                                                        @endforeach
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        @else
                            @foreach($links as $seasonIndex => $season)
                                <div class="accordion" id="accordionSeason{{ $seasonIndex }}">
                                    <div class="accordion-item">
                                        <h2 class="accordion-header" id="headingSeason{{ $seasonIndex }}">
                                            <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseSeason{{ $seasonIndex }}" aria-expanded="true" aria-controls="collapseSeason{{ $seasonIndex }}">
                                                {{ $seasonIndex }}. {{ __('base.season') }}
                                            </button>
                                        </h2>
                                        <div id="collapseSeason{{ $seasonIndex }}" class="accordion-collapse collapse" aria-labelledby="headingSeason{{ $seasonIndex }}" data-bs-parent="#accordionSeason{{ $seasonIndex }}">
                                            <div class="accordion-body">
                                                @foreach($season as $episodeIndex => $episode)
                                                    <div class="accordion" id="accordionSeason{{ $seasonIndex }}Episode{{ $episodeIndex }}">
                                                        <div class="accordion-item">
                                                            <h2 class="accordion-header" id="headingSeason{{ $seasonIndex }}Episode{{ $episodeIndex }}">
                                                                <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseSeason{{ $seasonIndex }}Episode{{ $episodeIndex }}" aria-expanded="true" aria-controls="collapseSeason{{ $seasonIndex }}Episode{{ $episodeIndex }}">
                                                                    {{ $episodeIndex }}. {{ __('base.episode') }}
                                                                </button>
                                                            </h2>
                                                            <div id="collapseSeason{{ $seasonIndex }}Episode{{ $episodeIndex }}" class="accordion-collapse collapse" aria-labelledby="headingSeason{{ $seasonIndex }}Episode{{ $episodeIndex }}" data-bs-parent="#accordionSeason{{ $seasonIndex }}Episode{{ $episodeIndex }}">
                                                                <div class="accordion-body">

                                                                    <div class="scroll table-responsive smaller-font">
                                                                        <table class="table" style="color: white;">
                                                                            <thead>
                                                                            <tr class="text-center">
                                                                                <th scope="col">{{ __('base.play') }}</th>
                                                                                <th scope="col">{{ __('base.site') }}</th>
                                                                                <th scope="col">{{ __('base.quality') }}</th>
                                                                                <th scope="col">{{ __('base.language') }}</th>
                                                                                <th scope="col">{{ __('base.date') }}</th>
                                                                                <th scope="col">{{ __('base.uploader') }}</th>
                                                                                <th scope="col">{{ __('base.report') }}</th>
                                                                                <th scope="col">{{ __('base.views') }}</th>
                                                                            </tr>
                                                                            </thead>
                                                                            <tbody class="text-center">
                                                                            @foreach($episode as $link)
                                                                                <tr>
                                                                                    <td class="text-center"><a data-toggle="tooltip" data-placement="top" title="{{ __('base.play') }}" target="_blank" href="{{ route('link', ['link_id' => $link['id']]) }}"><i class="fas fa-play"></i></a></td>
                                                                                    <td>{{ $link['site'] ? $link['site']['name'] : __('base.unknown') }}</td>
                                                                                    <td>{{ __('base.qualities.' . $link['linkType']['name']) }}</td>
                                                                                    <td><img style="width: 32px;" data-toggle="tooltip" data-placement="top" title="{{ __('base.languageTypes.' . $link['languageType']['name']) }}" src="{{ env('FRONTEND_URL') }}/img/flags/{{ $link['flagName'] }}.png"></td>
                                                                                    <td>{{ \Carbon\Carbon::parse($link['created_at'])->format('Y-m-d H:i:s')  }}</td>
                                                                                    <td>{{ $link['user'] ? ($link['user']['public_name'] === 1 ? $link['user']['username'] : __('base.unknown')) : __('base.unknown')  }}</td>
                                                                                    <td class="text-center"><button data-id="{{ $link['id'] }}" class="reportButton"><i data-toggle="tooltip" data-placement="top" title="{{ __('base.report') }}" class="fas fa-bug"></i></button></td>
                                                                                    <td class="text-center">{{ $link['views'] }}</td>
                                                                                </tr>
                                                                            @endforeach
                                                                            </tbody>
                                                                        </table>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        @endif
                    @endif
                @endif
            </div>
        </div>
        <div class="col-1"></div>
    </div>
</div>
</body>
<script
    src="https://code.jquery.com/jquery-3.6.0.min.js"
    integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4="
    crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-JEW9xMcG8R+pH31jmWH6WWP0WintQrMb4s7ZOdauHnUtxwoG2vI5DkLtS3qm9Ekf"
        crossorigin="anonymous"></script>
<script src="/notify.min.js"></script>
<script src="/jquery.mCustomScrollbar.concat.min.js"></script>
<script>
    $(function () {
        $('[data-toggle="tooltip"]').tooltip();

        $(".reportButton").click(function(){
            $.post( "report/" + $(this)[0].dataset.id, { _token: "{{ csrf_token() }}" }, function( data ) {
                if(data === 'Created') {
                    $.notify("{{ __('base.thanks_for_report') }}", { position:"bottom right", className:"success"});
                } else {
                    $.notify("{{ __('base.report_already') }}", { position:"bottom right", className:"error"});
                }
            });
        });

        $(window).on("load",function(){
            $(".scroll").mCustomScrollbar({
                axis:"x",
                theme:"my-theme",
                mouseWheel:{ enable: true }
            });
        });
    });

</script>
<!-- Banner-First -->
<ins class="adsbygoogle"
     style="display:block"
     data-ad-client="ca-pub-3890640160453569"
     data-ad-slot="2375609994"
     data-ad-format="auto"
     data-full-width-responsive="true"></ins>
<script>
    (adsbygoogle = window.adsbygoogle || []).push({});
</script>

</html>
