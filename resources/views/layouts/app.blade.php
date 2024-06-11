<!doctype html>
<html lang="en">

<head>
	<!-- Required meta tags -->
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<!--favicon-->
	<link rel="icon" href="{{asset('assets/images/icons/elyamaje_logo_mini.jpg') }}" type="image/jpg" />
	<!--plugins-->
	@yield("style")
	<link href="{{asset('assets/plugins/simplebar/css/simplebar.css')}}" rel="stylesheet" />

	<link href="{{asset('assets/plugins/perfect-scrollbar/css/perfect-scrollbar.css')}}" rel="stylesheet" />


	<link href="{{asset('assets/plugins/metismenu/css/metisMenu.min.css')}}" rel="stylesheet" />
	<!-- loader-->
	
	<!-- Bootstrap CSS -->
	<link href="{{asset('assets/css/bootstrap.min.css')}}" rel="stylesheet">
    <link href="{{asset('assets/css/bootstrap-extended.css')}}" rel="stylesheet">
	<link href="{{asset('assets/css/app.css')}}" rel="stylesheet">
	<link href="{{asset('assets/css/icons.css')}}" rel="stylesheet">




    <!-- Theme Style CSS -->
    <link rel="stylesheet" href="{{asset('assets/css/dark-theme.css')}}" />
    <link rel="stylesheet" href="{{asset('assets/css/semi-dark.css')}}" />
    <link rel="stylesheet" href="{{asset('assets/css/header-colors.css')}}" />
    <title>Elyamaje - Préparation des commandes</title>

	
</head>

<body>
	<!--wrapper-->
	<div class="wrapper">
        <!--navigation-->
        @include("layouts.nav")
        <!--end navigation-->
        
		<!--start header -->
		@include("layouts.header")
		<!--end header -->
		
		<!--start page wrapper -->
		@yield("wrapper")
		<!--end page wrapper -->
		<!--start overlay-->
		<div class="overlay toggle-icon"></div>
		<!--end overlay-->
		<!--Start Back To Top Button--> <a href="javaScript:;" class="back-to-top"><i class='bx bxs-up-arrow-alt'></i></a>
		<!--End Back To Top Button-->
		<footer class="no-print page-footer">
			<p class="mb-0">Elyamaje tous droits réservés Copyright © {{date('Y')}}</p>
		</footer>
	</div>
	<!--end wrapper-->

	
    <link rel="stylesheet" href="{{asset('assets/css/dark-theme.css')}}" />

	<!-- Bootstrap JS -->
	<script src="{{asset('assets/js/bootstrap.bundle.min.js')}}"></script>
	<!--plugins-->
	<script src="{{asset('assets/js/jquery.min.js')}}"></script>
	<script src="{{asset('assets/plugins/simplebar/js/simplebar.min.js')}}"></script>
	<script src="{{asset('assets/plugins/metismenu/js/metisMenu.min.js')}}"></script>
	<script src="{{asset('assets/plugins/perfect-scrollbar/js/perfect-scrollbar.js')}}"></script>
	<script src="{{asset('assets/js/pusher/pusher.min.js')}}"></script>
	<!--app JS-->

	{{-- @dd("2222") --}}

	<script src="{{asset('assets/js/app.js')}}"></script>
	<script> 	
		// Récupère les informations de l'utilisateur conencté
		var user_role_logged = '<?php echo json_encode(Auth()->user()->roles, JSON_HEX_APOS); ?>'
		// Notification Pusher
		notificationsListener(user_role_logged)
		var url_notification = "{{route('updateSessionByNotif')}}"
		var _token = "{{session()->all()['_token']}}"
		notificationAlertStock(url_notification,_token);
		
	</script>
	@yield("script")
</body>


</html>
