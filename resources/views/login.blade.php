<!doctype html>
<html class="html_login" lang="en">

<head>
	<!-- Required meta tags -->
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<!--favicon-->
	<link rel="icon" href="assets{{ ('/images/icons/elyamaje_logo_mini.jpg') }}" type="image/jpg" />
	<!--plugins-->
	<link href="{{asset('assets/plugins/simplebar/css/simplebar.css')}}" rel="stylesheet" />
	<link href="assets/plugins/perfect-scrollbar/css/perfect-scrollbar.css" rel="stylesheet" />
	<link href="assets/plugins/metismenu/css/metisMenu.min.css" rel="stylesheet" />
	<!-- loader-->
	<link href="assets/css/pace.min.css" rel="stylesheet" />
	<script src="assets/js/pace.min.js"></script>
	<!-- Bootstrap CSS -->
	<link href="assets/css/bootstrap.min.css" rel="stylesheet">
	<link href="assets/css/bootstrap-extended.css" rel="stylesheet">
	<link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500&display=swap" rel="stylesheet">
	<link href="assets/css/app.css" rel="stylesheet">
	<link href="assets/css/icons.css" rel="stylesheet">
	<title>Elyamaje – Préparation des commandes</title>
</head>

<body class="bg-lock-screen  pace-done"><div class="pace  pace-inactive"><div class="pace-progress" data-progress-text="100%" data-progress="99" style="transform: translate3d(100%, 0px, 0px);">
  <div class="pace-progress-inner"></div>
</div>
<div class="pace-activity"></div></div>
	<!-- wrapper -->
	<div class="wrapper">
		<div class="flex-column authentication-lock-screen d-flex align-items-center justify-content-center">

            @if(session('error'))
                <div class="card shadow-none bg-transparent">
                    <div class="card-body p-md-5 text-center">
                        <h2 class="text-white">{{ date('H:i') }}</h2>
                        <h5 class="text-capitalize mb-5 text-white">{{ $date }}</h5>
                        <div class="w-100 d-flex flex-column align-items-center justify-content-center">
                            <img src="assets/images/elyamaje_logo_blanc.png" width="120" alt="">
                        </div>
                        <form method="post" action="{{ route('login') }}" class="form_login_error">
                            @csrf 
                            <div class="mb-3 mt-3">
                                <input name="email" type="email" class="form-control" placeholder="Email">
                            </div>
                            <div class="mb-3 mt-3">
                                <div class="input-group" id="show_hide_password">
                                    <input  name="password" type="password" class="form-control" placeholder="Mot de passe">
                                    <a href="javascript:;" class="input-group-text bg-white"><i class='bx bx-hide'></i></a>
                                </div>
                            </div>

                     
                            <div class="alert border-0 border-start border-5 border-info-custom alert-dismissible fade show">
                                <div>{{ session('error') }}</div>
                                <!-- <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button> -->
                            </div>
                           


                            <div class="d-grid">
                                <button type="submit" class="btn btn-dark">Connexion</button>
                            </div>
                            <div class="forgot_password mt-3 d-flex justify-content-center">
                                <a class="text-light" href="{{ url('authentication-forgot-password') }}">Mot de passe oublié ?</a>
                            </div>
                        </form>
                    </div>
                </div>
            @else 
                <div class="card shadow-none bg-transparent">
                    <div class="card-body p-md-5 text-center">
                        <h2 class="time text-white">{{ date('H:i') }}</h2>
                        <h5 class="text-capitalize mb-5 text-white">{{ $date }}</h5>
                        <div class="w-100 d-flex flex-column align-items-center justify-content-center">
                            <img src="assets/images/elyamaje_logo_blanc.png" width="120" alt="">
                        </div>
                        <form method="post" action="{{ route('login') }}" class="">
                            @csrf 
                            <div class="mb-3 mt-3">
                                <input required name="email" type="email" class="form-control" placeholder="Email">
                            </div>
                            <div class="mb-3 mt-3">
                                <div class="input-group" id="show_hide_password">
                                    <input autocomplete="on" required name="password" type="password" class="form-control" placeholder="Mot de passe">
                                    <a href="javascript:;" class="input-group-text bg-white"><i class='bx bx-hide'></i></a>
                                </div>
                               
                            </div>
                            <div class="d-grid">
                                <button type="submit" class="btn btn-dark">Connexion</button>	
                            </div>
                            <div class="forgot_password mt-3 d-flex justify-content-center">
                                <a class="text-light" href="{{ url('authentication-forgot-password') }}">Mot de passe oublié ?</a>
                            </div>
                        </form>
                    </div>
                </div>
            @endif
           

		
		</div>
	</div>

	<!--end wrapper-->
	<!-- Bootstrap JS -->
	<script src="assets/js/bootstrap.bundle.min.js"></script>
	<!--plugins-->
	<script src="assets/js/jquery.min.js"></script>
	<script src="assets/plugins/simplebar/js/simplebar.min.js"></script>
	<script src="assets/plugins/metismenu/js/metisMenu.min.js"></script>
	<script src="assets/plugins/perfect-scrollbar/js/perfect-scrollbar.js"></script>
	<!--Password show & hide js -->
	<script>
		$(document).ready(function () {
			$("#show_hide_password a").on('click', function (event) {
				event.preventDefault();
				if ($('#show_hide_password input').attr("type") == "text") {
					$('#show_hide_password input').attr('type', 'password');
					$('#show_hide_password i').addClass("bx-hide");
					$('#show_hide_password i').removeClass("bx-show");
				} else if ($('#show_hide_password input').attr("type") == "password") {
					$('#show_hide_password input').attr('type', 'text');
					$('#show_hide_password i').removeClass("bx-hide");
					$('#show_hide_password i').addClass("bx-show");
				}
			});
		});

        function time(){
            var d = new Date();
            var s = d.getSeconds();
            var m = d.getMinutes() < 10 ? "0"+d.getMinutes() : d.getMinutes();
            var h = d.getHours();

            if($('.time').text() != h + ":" + m){
                $('.time').text(h + ":" + m)
            }
        }

        setInterval(time,1000);
	</script>
	<!--app JS-->
	<script src="assets/js/app.js"></script>
</body>


</html>