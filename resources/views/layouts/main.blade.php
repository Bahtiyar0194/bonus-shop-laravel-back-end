<?php 
use App\Models\Language;

if (!Session::has('lang')){
    $language = Language::where('lang_tag', '=', Config::get('app.fallback_locale'))->first();
}
else{
    $language = Language::where('lang_tag', '=', Session::get('lang'))->first();
}

$languages = Language::whereNotIn('lang_tag', ['en', $language->lang_tag])
->get();
?>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta property="og:title" content="@yield('title')"/>
    <meta property="og:description" content="@yield('description')"/>
    <meta property="og:image" content="@yield('image')"/>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" integrity="sha384-xOolHFLEh07PJGoPkLv1IbcEPTNtaed2xpHsD9ESMhqIYd0nLMwNLD69Npy4HI+N" crossorigin="anonymous">
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css">

    <title>@yield('title')</title>
</head>
<body style="position: relative;">
    <div class="header py-3">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="d-flex justify-content-between align-items-center">
                        <img style="width: 80px" src="{{url('/images/logo.png')}}"/>

                        <div class="dropdown">
                            <button class="btn p-0 dropdown-toggle d-flex align-items-center" style="color: #fff;" type="button" data-toggle="dropdown" aria-expanded="false">
                                <img style="width: 16px; margin-right: 5px" src="{{url('/images/flags/'.$language->lang_tag.'.png')}}"/> {{$language->name}}
                            </button>
                            <div class="dropdown-menu dropdown-menu-right">
                                @foreach ($languages as $lang)
                                <a class="dropdown-item align-items-center d-flex" href="/setlocale/{{$lang->lang_tag}}"> <img style="margin-right: 5px; width: 18px" src="{{url('/images/flags/'.$lang->lang_tag.'.png')}}"/>{{$lang->name}}</a>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @yield('content')
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.5.1/dist/jquery.slim.min.js" integrity="sha384-DfXdz2htPH0lsSSs5nCTpuj/zy4C+OGpamoFVy38MVBnE+IbbVYUew+OrCXaRkfj" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-Fy6S3B9q64WdZWQUiU+q4/2Lc9npb8tCaSX9FK7E8HnRr0Jz8D6OP9dO5Vg3Q9ct" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/jquery.maskedinput@1.4.1/src/jquery.maskedinput.min.js" type="text/javascript"></script>
    <script type="text/javascript">$('#phone').mask('+7 (999) 999-9999');</script>
    <script>
        function showPassword(){
            let password = $("#password");
            let password_confirmation = $("#password_confirmation");
            let eye = $(".eye");
            if (password.attr('type') === "password") {
                password.attr('type', 'text');
                password_confirmation.attr('type', 'text');
                eye.removeClass('far fa-eye-slash');
                eye.addClass('far fa-eye');
            } else {
                eye.removeClass('far fa-eye');
                password.attr('type', 'password');
                password_confirmation.attr('type', 'password');
                eye.addClass('far fa-eye-slash');
            }
        }
    </script>
</body>
</html>