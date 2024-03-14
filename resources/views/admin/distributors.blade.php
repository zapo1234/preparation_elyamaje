
	@extends("layouts.app")

		@section("style")
			<link href="{{asset('assets/plugins/datatable/css/dataTables.bootstrap5.min.css')}}" rel="stylesheet" />
			<link href="{{('assets/plugins/select2/css/select2.min.css')}}" rel="stylesheet" />
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
									<li class="breadcrumb-item active" aria-current="page">Distributeurs</li>
								</ol>
							</nav>
						</div>
						<div class="ms-auto ms-auto-responsive">
							<form action="{{ route('sync.distributors') }}" method="GET">
								@csrf
								<button type="submit" class="btn btn-dark px-5">Synchroniser les distributeurs</button>
							</form>
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

					
						<div class="card card_table_mobile_responsive">
							<div class="card-body">
								<table id="example" class="table_mobile_responsive w-100 table table-striped table-bordered">
									<thead>
										<tr>
											<th>Distributeur</th>
											<th>Rôle</th>
											<th class="col-md-3">Identifiant</th>
										</tr>
									</thead>

									<tbody>
										@foreach($distributors as $distributor)
											<tr>
												<td data-label="Distributeur">{{ $distributor['first_name']  }} {{ $distributor['last_name'] ?? ''  }}</td>
												<td data-label="Rôle">{{ $distributor['role']  }}</td>
												<td data-label="Identifiant">{{ $distributor['customer_id']  }}</td>
											</tr>
										@endforeach
									</tbody>
								</table>
							</div>
						</div>
					
				</div>
			</div>

		@endsection

	
	@section("script")

		<script src="{{asset('assets/plugins/datatable/js/jquery.dataTables.min.js')}}"></script>
		<script src="{{asset('assets/plugins/datatable/js/dataTables.bootstrap5.min.js')}}"></script>
		<script src="{{asset('assets/plugins/select2/js/select2.min.js')}}"></script>
		<script>


		$(document).ready(function() {

			$('#example').DataTable({
				
			})
			
		})

		
		</script>
	@endsection


