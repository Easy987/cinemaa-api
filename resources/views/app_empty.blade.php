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

    <!-- ADSENSE -->
    <script data-ad-client="ca-pub-3890640160453569" async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"></script>


    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css"
          integrity="sha512-iBBXm8fW90+nuLcSKlbmrPcLa0OT92xO1BIsZ+ywDWZCvqsWgccV3gFoRBv0z+8dLJgyAHIhR35VZc2oM/gI1w=="
          crossorigin="anonymous"/>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta3/dist/css/bootstrap.min.css" rel="stylesheet"
          integrity="sha384-eOJMYsd53ii+scO/bJGFsiCZc+5NDVN2yr8+0RDqr0Ql0h+rP48ckxlpbzKgwra6" crossorigin="anonymous">

    <link rel='stylesheet prefetch' href='https://cdnjs.cloudflare.com/ajax/libs/normalize/5.0.0/normalize.min.css'><link rel='stylesheet prefetch' href='https://fonts.googleapis.com/icon?family=Material+Icons'>
    <style class="cp-pen-styles">@import url("https://fonts.googleapis.com/css?family=Arimo:400,700");
        body {
            height: 100%;
            width: 100%;
            background: #e9e9e9;
            font-family: 'Arimo', Arial, sans-serif;
            font-weight: 400;
            font-size: 14px;
            color: black;
        }

        * {
            -webkit-transition: 300ms;
            transition: 300ms;
        }

        .intro {
            text-align: center;
        }

        ul {
            list-style-type: none;
        }

        h1, h2, h3, h4, h5, p {
            font-weight: 400;
        }

        a {
            text-decoration: none;
            color: black;
        }

        a:hover {
            color: #6ABCEA;
        }

        .container {
            display: -webkit-box;
            display: -ms-flexbox;
            display: flex;
            -ms-flex-wrap: wrap;
            flex-wrap: wrap;
            max-width: 100%;
            margin-left: auto;
            margin-right: auto;
            -webkit-box-pack: center;
            -ms-flex-pack: center;
            justify-content: center;
        }

        .movie-card {
            background: #ffffff;
            box-shadow: 0px 6px 18px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 315px;
            margin: 2em;
            border-radius: 10px;
            display: inline-block;
        }

        .movie-header {
            padding: 0;
            margin: 0;
            height: 367px;
            width: 100%;
            display: block;
            border-top-left-radius: 10px;
            border-top-right-radius: 10px;
        }

        .manOfSteel {
            background: url("https://cinemaa.cc/api/photos/93384bd6-cb16-4888-8fd9-4fc4d5eedfc5");
            background-size: cover;
        }

        .babyDriver {
            background: url("https://cinemaa.cc/api/photos/93384aef-c293-4dd3-85d5-a2e4fc3a2996");
            background-size: cover;
        }

        .theDarkTower {
            background: url("https://cinemaa.cc/api/photos/93384a7a-0896-4b9e-a473-22a17cff476d");
            background-size: cover;
            background-position: 100% 100%;
        }

        .bladeRunner2049 {
            background: url("https://cinemaa.cc/api/photos/93384a77-fbbe-4bde-8b66-6a7676b76a4e");
            background-size: cover;
            background-position: 100% 80%;
        }

        .header-icon-container {
            position: relative;
        }

        .header-icon {
            width: 100%;
            height: 367px;
            line-height: 367px;
            text-align: center;
            vertical-align: middle;
            margin: 0 auto;
            color: #ffffff;
            font-size: 54px;
            text-shadow: 0px 0px 20px #6abcea, 0px 5px 20px #6ABCEA;
            opacity: .85;
        }

        .header-icon:hover {
            background: rgba(0, 0, 0, 0.15);
            font-size: 74px;
            text-shadow: 0px 0px 20px #6abcea, 0px 5px 30px #6ABCEA;
            border-top-left-radius: 10px;
            border-top-right-radius: 10px;
            opacity: 1;
        }

        .movie-card:hover {
            -webkit-transform: scale(1.03);
            transform: scale(1.03);
            box-shadow: 0px 10px 25px rgba(0, 0, 0, 0.08);
        }

        .movie-content {
            padding: 18px 18px 24px 18px;
            margin: 0;
        }

        .movie-content-header, .movie-info {
            display: table;
            width: 100%;
        }

        .movie-title {
            font-size: 24px;
            margin: 0;
            display: table-cell;
        }

        .imax-logo {
            width: 50px;
            height: 15px;
            background: url("https://6a25bbd04bd33b8a843e-9626a8b6c7858057941524bfdad5f5b0.ssl.cf5.rackcdn.com/media_kit/3e27ede823afbf139c57f1c78a03c870.jpg") no-repeat;
            background-size: contain;
            display: table-cell;
            float: right;
            position: relative;
            margin-top: 5px;
        }

        .movie-info {
            margin-top: 1em;
        }

        .info-section {
            display: table-cell;
            text-transform: uppercase;
            text-align: center;
        }

        .info-section:first-of-type {
            text-align: left;
        }

        .info-section:last-of-type {
            text-align: right;
        }

        .info-section label {
            display: block;
            color: rgba(0, 0, 0, 0.5);
            margin-bottom: .5em;
            font-size: 9px;
        }

        .info-section span {
            font-weight: 700;
            font-size: 11px;
            color: black;
        }

        @media screen and (max-width: 500px) {
            .movie-card {
                width: 95%;
                max-width: 95%;
                margin: 1em;
                display: block;
            }

            .container {
                padding: 0;
                margin: 0;
            }
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
                <div class="container">


                    <div class="movie-card">
                        <div class="movie-header manOfSteel">
                            <div class="header-icon-container">
                                <a href="#">
                                    <i class="material-icons header-icon"></i>
                                </a>
                            </div>
                        </div><!--movie-header-->
                        <div class="movie-content">
                            <div class="movie-content-header">
                                <a href="#">
                                    <h3 class="movie-title">Man of Steel</h3>
                                </a>
                                <div class="imax-logo"></div>
                            </div>
                            <div class="movie-info">
                                <div class="info-section">
                                    <label>Date & Time</label>
                                    <span>Sun 8 Sept - 10:00PM</span>
                                </div><!--date,time-->
                                <div class="info-section">
                                    <label>Screen</label>
                                    <span>03</span>
                                </div><!--screen-->
                                <div class="info-section">
                                    <label>Row</label>
                                    <span>F</span>
                                </div><!--row-->
                                <div class="info-section">
                                    <label>Seat</label>
                                    <span>21,22</span>
                                </div><!--seat-->
                            </div>
                        </div><!--movie-content-->
                    </div><!--movie-card-->

                    <div class="movie-card">
                        <div class="movie-header babyDriver">
                            <div class="header-icon-container">
                                <a href="#">
                                    <i class="material-icons header-icon"></i>
                                </a>
                            </div>
                        </div><!--movie-header-->
                        <div class="movie-content">
                            <div class="movie-content-header">
                                <a href="#">
                                    <h3 class="movie-title">Baby Driver</h3>
                                </a>
                                <div class="imax-logo"></div>
                            </div>
                            <div class="movie-info">
                                <div class="info-section">
                                    <label>Date & Time</label>
                                    <span>Tue 4 July - 05:00PM</span>
                                </div><!--date,time-->
                                <div class="info-section">
                                    <label>Screen</label>
                                    <span>01</span>
                                </div><!--screen-->
                                <div class="info-section">
                                    <label>Row</label>
                                    <span>H</span>
                                </div><!--row-->
                                <div class="info-section">
                                    <label>Seat</label>
                                    <span>15</span>
                                </div><!--seat-->
                            </div>
                        </div><!--movie-content-->
                    </div><!--movie-card-->

                    <div class="movie-card">
                        <div class="movie-header theDarkTower">
                            <div class="header-icon-container">
                                <a href="#">
                                    <i class="material-icons header-icon"></i>
                                </a>
                            </div>
                        </div><!--movie-header-->
                        <div class="movie-content">
                            <div class="movie-content-header">
                                <a href="#">
                                    <h3 class="movie-title">The Dark Tower</h3>
                                </a>
                                <div class="imax-logo"></div>
                            </div>
                            <div class="movie-info">
                                <div class="info-section">
                                    <label>Date & Time</label>
                                    <span>Wed 16 Aug - 07:00PM</span>
                                </div><!--date,time-->
                                <div class="info-section">
                                    <label>Screen</label>
                                    <span>06</span>
                                </div><!--screen-->
                                <div class="info-section">
                                    <label>Row</label>
                                    <span>C</span>
                                </div><!--row-->
                                <div class="info-section">
                                    <label>Seat</label>
                                    <span>18</span>
                                </div><!--seat-->
                            </div>
                        </div><!--movie-content-->
                    </div><!--movie-card-->

                    <div class="movie-card">
                        <div class="movie-header bladeRunner2049">
                            <div class="header-icon-container">
                                <a href="#">
                                    <i class="material-icons header-icon"></i>
                                </a>
                            </div>
                        </div><!--movie-header-->
                        <div class="movie-content">
                            <div class="movie-content-header">
                                <a href="#">
                                    <h3 class="movie-title">Blade Runner 2049</h3>
                                </a>
                                <div class="imax-logo"></div>
                            </div>
                            <div class="movie-info">
                                <div class="info-section">
                                    <label>Date & Time</label>
                                    <span>Mon 16 Oct - 10:00PM</span>
                                </div><!--date,time-->
                                <div class="info-section">
                                    <label>Screen</label>
                                    <span>06</span>
                                </div><!--screen-->
                                <div class="info-section">
                                    <label>Row</label>
                                    <span>D</span>
                                </div><!--row-->
                                <div class="info-section">
                                    <label>Seat</label>
                                    <span>05,06</span>
                                </div><!--seat-->
                            </div>
                        </div><!--movie-content-->
                    </div><!--movie-card-->

                </div><!--container-->
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

</html>
