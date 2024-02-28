
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
						<div class="breadcrumb-title pe-3">Journaux d'erreurs</div>
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
							<div class="d-flex justify-content-center">
								<div class="loading spinner-border text-dark" role="status"> 
									<span class="visually-hidden">Loading...</span>
								</div>
							</div>

						

							<table id="example" class="d-none table_mobile_responsive w-100 table table-striped table-bordered">
								<thead>
									<tr>
										<th class="col-md-1">Commande</th>
										<th>Message</th>
										<th class="col-md-2">Date</th>
										<th class="col-md-2">ID</th>
									</tr>
								</thead>

								<tbody>
									@foreach ($logs as $log)
										<tr>
											<td class="col-md-1 font-bold" style="color: black !important">{{ $log['order_id'] }}</td>
											<td><span style="word-break:break-word" class="text-danger">{{ $log['message'] }}</span></td>
											<td class="col-md-2 font-bold" style="color: black !important">{{ $log['created_at'] }}</td>
											<td class="col-md-2">{{ $log['id'] }}</td>
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
		<script src="assets/plugins/select2/js/select2.min.js"></script>
		<script>



		$(document).ready(function() {


			$('#example').DataTable({
				"order": [[3, 'DESC']],
				"columnDefs": [
					{ "visible": false, "targets": 3 },
				],
				"initComplete": function(settings, json) {
					$("#example").removeClass('d-none')
					$(".loading").hide()
				}
			})		
		})

		
		</script>
	@endsection


