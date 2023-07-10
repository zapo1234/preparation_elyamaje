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
							<div class="breadcrumb-title pe-3">Commandes</div>
						</div>
						<div class="ms-auto ms-auto-responsive">
							<button id="history_by_date" type="button" class="btn btn-dark px-5">Générer historique</button>
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
												<th scope="col-md-1">Commande</th>
												<th scope="col">Préparée</th>
												<th scope="col">Emballée</th>
											</tr>
										</thead>
										<tbody>
											@foreach($histories as $histo)
												<tr>
													<td data-label="Commande">#{{ $histo['order_id'] }}</td>
													<td data-label="Préparée">
														<div class="d-flex flex-column">
															<div>
																<span class="badge bg-dark">{{ $histo['prepared'] }}</span>  
															</div>
															<span>{{ $histo['prepared_date'] }}</span>
														</div>
													</td>
													<td data-label="Emballée">
														<div class="d-flex flex-column">
															<div>
																<span class="badge bg-dark">{{ $histo['finished'] }}</span>
															</div>
															<span>{{ $histo['finished_date'] }}</span>
														</div>
													</td>
												</tr>

											@endforeach
										</tbody>
									</table>
								</div>

							</div>
						</div>


					<!-- Modal -->
					<div class="modal fade" id="modalGenerateHistory" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
						<div class="modal-dialog modal-dialog-centered" role="document">
							<div class="modal-content">
								<div class="modal-body">
									<form method="POST" action="{{ route('history.generate') }}">
										@csrf
										<h2 class="text-center">Choisir la date</h2>
										<div class="d-flex justify-content-center w-100">
											<input class="date_historique" type="date" name="date_historique" value="{{ date('Y-m-d') }}">
										</div>
										<div class="d-flex justify-content-center mt-3 w-100">
											<button type="button" class="btn btn-dark px-5" data-bs-dismiss="modal">Fermer</button>
											<button style="margin-left:15px" type="submit" class="btn btn-dark px-5">Générer</button>
										</div>
									</form>
								</div>
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

			$("#history_by_date").on('click', function(){
				$('#modalGenerateHistory').modal('show')
			})
			
			$(document).ready(function() {
				$('#example').DataTable({
					"ordering": false
				})
			})
        </script>
	@endsection

