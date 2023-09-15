
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
						<div class="breadcrumb-title pe-3">Facturation</div>
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
						<div class="billing_form w-75 card border-top border-0 border-4 border-dark">
							<div class="card-body p-5">
								<div class="card-title d-flex align-items-center">
									<div>
										<i class="bx bx-box me-1 font-22 text-primary"></i>
									</div>
									<h5 class="mb-0 text-primary">Commande</h5>
								</div>
								<hr>
								<form method="post" action="{{ route('admin.billingOrder') }}">
									@csrf
									<div class="col-md-12 mt-3">
										<label for="order_id" class="form-label">Numéro de commande</label>
										<input value="" name="order_id" type="text" class="form-control" id="order_id">
									</div>
								
									<div class="col-12 mt-3">
										<button type="sumbit" class="btn btn-primary px-5">Facturer</button>
									</div>
								</form>
							</div>
						</div>
					</div>
				</div>
			</div>
		@endsection

	
@section("script")

<script src="{{asset('assets/plugins/datatable/js/jquery.dataTables.min.js')}}"></script>
<script src="{{asset('assets/plugins/datatable/js/dataTables.bootstrap5.min.js')}}"></script>

@endsection


