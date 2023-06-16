
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
						<div class="breadcrumb-title pe-3">Colissimo</div>
						<div class="ps-3">
							<nav aria-label="breadcrumb">
								<ol class="breadcrumb mb-0 p-0">
									<li class="breadcrumb-item active" aria-current="page">Étiquettes</li>
								</ol>
							</nav>
						</div>
						<div class="ms-auto ms-auto-responsive">
							<button id="show_modal_bordereau" type="button" class="btn btn-dark px-5">Générer bordereau</button>
						</div>
					</div>



					<!-- Modal Génération Bordereau par date -->
					<div class="modal fade" id="modalBordereau" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
						<div class="modal-dialog modal-dialog-centered" role="document">
							<div class="modal-content">
								<form method="POST" action="{{ route('label.generate') }}">
									@csrf
									<div class="modal-body">
										<h2 class="text-center">Choisir la date</h2>
										<div class="d-flex justify-content-center w-100">
											<input class="date_bordereau_input" type="date" name="date" value="{{ date('Y-m-d') }}">
										</div>
									</div>
									<div class="modal-footer">
										<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
										<button type="submit" class="btn btn-primary">Générer</button>
									</div>
								</form>
							</div>
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

					<div class="row">
						<div class="card card_table_mobile_responsive">
							<div class="card-body">
								<table id="example" class="table_mobile_responsive w-100 table table-striped table-bordered">
									<thead>
										<tr>
											<th>Commande</th>
											<th>Status</th>
											<th>N° de suivi</th>
											<th>Date</th>
											<th class="col-md-2">Étiquette</th>
										</tr>
									</thead>
									<tbody>
										@foreach($labels as $key => $label)
											<tr>
												<td>
													<div class="d-flex justify-content-between">
														<span>{{ $label->order_id }}</span>
														@if($key == 0)
															<span class="rounded-pill badge bg-danger">New</span>
														@endif
													</div>
												</td>
												<td>
													<span class="badge bg-{{ $label->status }} text-light">{{ $label->status }}</span>
												</td>
												<td>{{ $label->tracking_number }}</td>
												<td>{{ $label->created_at->format('d/m/Y') }}</td>
												<td data-label="PDF">
													<form method="POST" action="{{ route('label.download') }}">
														@csrf
														<input name="label_id" type="hidden" value="{{ $label->id }}">
														<button type="submit" class="btn btn-outline-danger px-5"><i class="bx bx-file"></i>Voir</button>
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
				order: [[3, 'desc']],
			})
		})

		$("#show_modal_bordereau").on('click', function(){
			$("#modalBordereau").modal('show')
		})


		</script>
	@endsection


