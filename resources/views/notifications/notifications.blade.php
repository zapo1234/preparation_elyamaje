

@extends("layouts.app")

		@section("style")
			<link href="{{asset('assets/plugins/datatable/css/dataTables.bootstrap5.min.css')}}" rel="stylesheet" />
			<link href="assets/plugins/select2/css/select2.min.css" rel="stylesheet" />
			<link href="assets/plugins/select2/css/select2-bootstrap4.css" rel="stylesheet" />

		@endsection 

		@section("wrapper")
			<div class="page-wrapper">
				<div class="page-content">
					<div class="page-breadcrumb d-sm-flex align-items-center mb-2 justify-content-between">
						<div class="d-flex flex-wrap justify-content-center">
							<div class="breadcrumb-title pe-3">Notifications</div>
							<div class="pe-3 number_order_pending"></div>
						</div>
					</div>
					<div class="col d-flex col-lg-12">
						<div class="card card_table_mobile_responsive  w-100">
							<div class="card-body">
								<div class="w-100 d-flex justify-content-center loading_notifications">
									<div class="spinner-border" role="status"> 
										<span class="visually-hidden">Loading...</span>
									</div>
								</div>
								<table id="example" class="notifications_table d-none w-100 table_mobile_responsive table table-striped table-bordered">
									<thead>
										<tr>
											<th scope="col">Auteur</th>
											<th scope="col">Message</th>
											<th scope="col">Date</th>
											<th scope="col">ID</th> <!-- Pour le tri -->
										</tr>
									</thead>
									<tbody>
										@foreach($notifications as $notification)
											<tr>
												<td data-label="Auteur">{{ $notification->name }}</td>
												<td data-label="Message"><span>{{ $notification->detail }}</span></td>
												<td data-label="Date">{{ $notification->date }}</td>
												<td data-label="ID">{{ $notification->id }}</td> <!-- Pour le tri -->
											</tr>
										@endforeach
									</tbody>
								</table>
							</div>
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
				"order": [[3, 'desc']],
				"aoColumnDefs": [ 
					{ "bSearchable": false, "bVisible": false, "aTargets": [ 3 ] },
				],
				"initComplete": function(settings, json) {
					$(".loading_notifications").addClass('d-none')
					$("#example").removeClass('d-none')
				}
			})
		})
			
		</script>
	@endsection

