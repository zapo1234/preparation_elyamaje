<!DOCTYPE html>
<html lang="en">

<head>
	<!-- Required meta tags -->
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<!--favicon-->
	<link rel="icon" href="assets{{ ('/images/Logo_elyamaje.png') }}" type="image/png" />
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

<body>
	<!-- wrapper -->
	<div class="wrapper">
		<div class="authentication-header"></div>
		 <div class="authentication-reset-password d-flex align-items-center justify-content-center">
			<div class="row">
				<div class="col-12 col-lg-10 mx-auto">
					<div class="card">
						<div class="row g-0">
							<div class="col-lg-5 border-end">
								<div class="card-body">
									<div class="p-5">
										<div class="text-start">
											<img src="assets{{ ('/images/Logo_elyamaje.png') }}" width="100" alt="">
										</div>
										<h4 class="mt-5 font-weight-bold">Réinitialisation</h4>
										<p class="text-muted">Veuillez entrer un nouveau mot de passe</p>
										<form action="{{ route('auth.passwords.reset') }}" method="POST"> 
											@csrf
											<input  name="token"  type="hidden" value="{{ $token }}"/>
											<div class="mb-3 mt-5">
												<label class="form-label">Nouveau mot de passe</label>
												<input id="pass1" name="pass1" type="text" class="form-control" placeholder="Nouveau mot de passe" />
											</div>
											<div class="mb-3">
												<label class="form-label">Confirmation du mot de passe</label>
												<input id="pass2" name="pass2"  type="text" class="form-control" placeholder="Confirmation du mot de passe" />
												<span class="same_password text-danger"></span>
											</div>

											@if(session('error'))
												<div class="d-flex w-100">
													<div class="w-100 text-center alert alert-danger">
														{{ session('error') }}
													</div>
												</div>
											@endif

											<div class="d-grid gap-2">
												<button type="submit" class="btn btn-primary">Modifier mot de passe</button> <a href="{{ url('login') }}" class="btn btn-light"><i class='bx bx-arrow-back mr-1'></i>Connexion</a>
											</div>
										</form>
									</div>
								</div>
							</div>
							<div class="col-lg-7 d-flex align-items-center">
								<img src="assets/images/login-images/forgot-password-frent-img.jpg" class="card-img login-img" alt="...">
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
	<!-- end wrapper -->
</body>

</html>


<script src="assets/js/jquery.min.js"></script>
<script>

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