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
			<div class="card shadow-none bg-transparent">
				<div class="card-body p-md-5 text-center">
					<form action="{{ route('auth.passwords.reset') }}" method="POST"> 
						<div class="logo_login w-100 d-flex flex-column align-items-center justify-content-center">
							<img src="assets/images/elyamaje_logo_long_noir.png" width="175" height="29" alt="">
						</div>
						@csrf 
						<input  name="token"  type="hidden" value="{{ $token }}"/>
						<div class="mb-3 mt-3">
							<input id="pass1" name="pass1" type="password" class="form-control" placeholder="Nouveau mot de passe" />
						</div>
						<div class="mb-3 mt-3">
							<div class="input-group" id="show_hide_password">
								<input id="pass2" name="pass2"  type="password" class="form-control" placeholder="Confirmation" />
								<a href="javascript:;" class="input-group-text bg-white"><i class='bx bx-hide'></i></a>
							</div>

							<div style="height:17px" >
								<span class="same_password text-danger"></span>
							</div>
						</div>
						<div class="d-grid">
							<button type="submit" class="btn btn-dark">Valider</button>	
						</div>
					</form>
				</div>
			</div>
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

		
	const inputElement = document.getElementById('pass2');
	const inputElement2 = document.getElementById('pass1');

	const error = document.getElementsByClassName('same_password');
	inputElement.addEventListener('input', (event) => {

		error.innerText=""
		if(inputElement.value != inputElement2.value){
			$(".same_password").addClass('text-danger')
			$(".same_password").removeClass('text-success')
			$(".same_password").text("Les mots de passes sont différents")
		} else {
			$(".same_password").removeClass('text-danger')
			$(".same_password").addClass('text-success')
			$(".same_password").text("Les mots de passes sont identiques !")
		}

	})
	</script>
	<!--app JS-->
	<script src="assets/js/app.js"></script>
</body>


</html>