<!doctype html>
<html class="html_login" lang="en">

<head>
	<!-- Required meta tags -->
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<!--favicon-->
	<link rel="icon" href="assets{{ ('/images/icons/elyamaje_logo_mini.jpg') }}" type="image/jpg" />
	<!--plugins-->
	<link href="assets/plugins/simplebar/css/simplebar.css" rel="stylesheet" />
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

        
                <div class="card shadow-none bg-transparent">
                    <div class="card-body p-md-5 text-center">
                        <div class="mt-3 w-100 d-flex flex-column align-items-center justify-content-center">
                            <img src="assets/images/elyamaje_logo_blanc.png" width="120" alt="">
                        </div>
                        <form method="post" action="{{ route('password.reset') }}">
                            @csrf 
                            <div class="mb-3 mt-3">
                                <input name="email" type="email" class="form-control" placeholder="Email">
                            </div>
                            <div class="d-grid">

								@if(session('error'))
									<div class="d-flex w-100">
										<div style="width:250px" class="text-center alert alert-danger">
											{{ session('error') }}
										</div>
									</div>
								@endif
								@if(session('success'))
									<div class="d-flex w-100">
										<div style="width:250px" class="text-center alert alert-success">
											{{ session('success') }}
										</div>
									</div>
								@endif

								<div class="d-grid gap-2">
									<button type="submit" class="btn btn-dark">Envoyer</button>

									<a href="{{ url('login') }}">
										<div class="d-grid">
											<button type="button" class="btn btn-white">
												<i class='bx bx-arrow-back me-1'></i>	
												Connexion
											</button>
										</div>
									</a>

								</div>
                            </div>
                        </form>
                    </div>
                </div>
           
           

		
		</div>
	</div>
</body>


</html>