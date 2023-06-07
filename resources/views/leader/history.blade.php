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
							<div class="breadcrumb-title pe-3">Historiques</div>
							<div class="pe-3 number_order_pending"></div>
						</div>
					</div>

				
						<div class="card card_table_mobile_responsive radius-10 w-100">
							<div class="card-body">
								<!-- <div class="d-flex align-items-center">
									<div>
										<h5 class="mb-4">Commandes <span class="text-success total_amount"></span></h5>
									</div>
								</div> -->
								<div class="table-responsive">
									<table id="example" class="w-100 table_list_order table_mobile_responsive table table-striped table-bordered">
										<thead>
											<tr>
												<th scope="col">Date</th>
												<th class="col-md-2" scope="col">PDF</th>
											</tr>
										</thead>
										<tbody>
											@foreach($histories_by_date as $key => $histories)
												<tr>
													<td data-label="Date">{{ date("d/m/Y", strtotime($key)) }}</td>
													<td data-label="PDF">
														<form method="POST" action="{{ route('leader.downloadPDF') }}">
															@csrf
															<input name="date_historique" type="hidden" value="{{ $key }}">
															<button  type="submit" class="btn btn-outline-danger px-5"><i class="bx bx-file"></i>Télécharger</button>
														</form>
													</td>
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
					"ordering": false
				})
			})
        </script>
	@endsection

