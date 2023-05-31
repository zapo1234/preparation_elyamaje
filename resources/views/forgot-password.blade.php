<!DOCTYPE html>
<html lang="en">

<head>
	<!-- Required meta tags -->
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<!--favicon-->
	<link rel="icon" href="{{ asset('assets/images/Logo_elyamaje.png')}}" type="image/png" />

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
		<div class="authentication-forgot d-flex align-items-center justify-content-center">
			<div class="card forgot-box">
				<div class="card-body">
					<div class="p-4 rounded">
						<div class="text-center">
							<img src="assets{{ ('/images/icons/lock_black.png') }}" width="120" alt="" />
						</div>
						<h4 class="text-center mt-5 font-weight-bold">Mot de passe oublié ?</h4>
						<p class="text-muted">Entrez votre email pour réinitialiser votre mot de passe</p>
						<form method="post" action="{{ route('password.reset') }}">
							@csrf
							<div class="my-4">
								<label class="form-label">Email</label>
								<input type="text" name="email" class="form-control form-control-lg" placeholder="example@user.com" />
							</div>

							@if(session('error'))
								<div class="d-flex w-100">
									<div class="w-100 text-center alert alert-danger">
										{{ session('error') }}
									</div>
								</div>
							@endif
							@if(session('success'))
								<div class="d-flex w-100">
									<div class="w-100 text-center alert alert-success">
										{{ session('success') }}
									</div>
								</div>
							@endif


							<div class="d-grid gap-2">
								<button type="submit" class="btn btn-primary btn-lg">Envoyer</button>
								<a href="{{ url('login') }}" class="btn btn-white btn-lg">
									<i class='bx bx-arrow-back me-1'></i>
									Connexion
								</a>
							</div>
						</form>
					</div>
				</div>
			</div>
		</div>
	</div>
	<!-- end wrapper -->
</body>

</html>