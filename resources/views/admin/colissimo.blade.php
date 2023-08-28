
	@extends("layouts.app")

		@section("style")
			<link href="{{asset('assets/plugins/datatable/css/dataTables.bootstrap5.min.css')}}" rel="stylesheet" />
			<link href="assets/plugins/select2/css/select2.min.css" rel="stylesheet" />
			<link href="assets/plugins/select2/css/select2-bootstrap4.css" rel="stylesheet" />
		@endsection

		@section("wrapper")
			<div class="page-wrapper">
				<div class="page-content">
					<div class="page-breadcrumb d-sm-flex align-items-center mb-3">
						<div class="breadcrumb-title pe-3">Configuration</div>
						<div class="ps-3">
							<nav aria-label="breadcrumb">
								<ol class="breadcrumb mb-0 p-0">
									<li class="breadcrumb-item active" aria-current="page">Colissimo</li>
								</ol>
							</nav>
						</div>
					</div>

					@if(session()->has('success'))
						<div class="alert alert-success border-0 bg-success alert-dismissible fade show">
							<div class="text-white">{{ session()->get('success') }}</div>
							<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
						</div>
					@endif
					@if(session()->has('error'))
						<div class="alert alert-danger border-0 bg-danger alert-dismissible fade show">
							<div class="text-white">{{ session()->get('error') }}</div>
							<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
						</div>
					@endif

					<div class="w-100 d-flex justify-content-center">
						<form class="w-75 colissimo_form" method="POST" action="{{ route('colissimo.update') }}">
							@csrf
							<div class="card border-top border-0 border-4 border-dark">
								<div class="card-body p-5">
									<div class="card-title d-flex align-items-center">
										<div>
											<i class="bx bx-label me-1 font-22 text-primary"></i>
										</div>
										<h5 class="mb-0 text-primary">Étiquette</h5>
									</div>
									<hr>
									<form class="row g-3" data-bitwarden-watching="1">
										<div class="col-md-12 mt-2">
											<label for="format" class="form-label">Format d'impression des étiquettes</label>
											<select name="format" id="format" class="form-select">
												@foreach($list_format as $key => $format)
													@if($colissimo)	
														@if($colissimo->format == $key)
															<option selected value="{{ $key }}">{{ $format }}</option>
														@else 
															<option value="{{ $key }}">{{ $format }}</option>
														@endif
													@else
														<option value="{{ $key }}">{{ $format }}</option>
													@endif
												@endforeach
											</select>
										</div>

										<div style="margin-top:50px" class="card-title d-flex align-items-center">
											<div>
												<i class="bx bx-printer me-1 font-22 text-primary"></i>
											</div>
											<h5 class="mb-0 text-primary">Impression des étiquettes ZPL</h5>
										</div>
										<hr>
										
										<div class="col-md-12 mt-3">
											<label for="address_ip" class="form-label">Adresse IP</label>
											<input value="{{ $colissimo ? $colissimo->address_ip : '' }}" name="address_ip" type="text" class="form-control" id="address_ip">
										</div>
										<div class="col-md-12 mt-3">
											<label for="port" class="form-label">Port</label>
											<input value="{{ $colissimo ? $colissimo->port : '' }}" name="port" type="text" class="form-control" id="port">
										</div>
										
										<div class="col-12 mt-3">
											<button type="submit" class="btn btn-primary px-5">Enregistrer</button>
										</div>
									</form>
								</div>
							</div>
						</form>
					</div>
				</div>
			</div>
		@endsection

	
	@section("script")

		<script src="{{asset('assets/plugins/datatable/js/jquery.dataTables.min.js')}}"></script>
		<script src="{{asset('assets/plugins/datatable/js/dataTables.bootstrap5.min.js')}}"></script>
		<script src="assets/plugins/select2/js/select2.min.js"></script>
		<script>

		$(document).ready(function() {
			$('#example').DataTable({
				
			})
		})

		

		
		</script>
	@endsection


