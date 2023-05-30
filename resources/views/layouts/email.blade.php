<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="ISO-8859-15">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Elyamaje') }}</title>

    <!-- Scripts -->
    <script src="{{ asset('js/app.js') }}" defer></script>

    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css?family=Nunito" rel="stylesheet">

    <!-- Styles -->
    <link href="{{ asset('email/css/template_resetpassword.css') }}" rel="stylesheet">
    <style type="text/css">
    .resetpassword{color:white;background-color:black;border:2px solid black;margin-left:10%;font-size:15px;text-decoration:none;border-radius:15px;padding:1%;}
    .resetpassword a{color:white;text-decoration:none;}
    h1{font-size:20px;color:black,font-weight:400;}
    
    @media (max-width: 575.98px) {
    .resetpassword{color:white;background-color:black;border:2px solid black;margin-left:10%;font-size:15px;text-decoration:none;border-radius:15px;padding:1%;}
    .resetpassword a{color:white;text-decoration:none;}
    h1{font-size:20px;color:black,font-weight:400;}
        
    }
        
    </style>
    
</head>
<body>
    

        <main class="py-4">
            @yield('content')
        </main>
    </div>
</body>
</html>