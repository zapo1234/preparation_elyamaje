
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
									<li class="breadcrumb-item active" aria-current="page">Distributeurs</li>
								</ol>
							</nav>
						</div>
						<div class="ms-auto ms-auto-responsive">
							<button id="show_modal_distributors" type="button" class="btn btn-dark px-5">Ajouter un Distributeur</button>
						</div>
					</div>


						<!-- Modal création de distributeurs -->
						<div class="modal fade" id="createDistributorsModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
						<div class="modal-dialog modal-dialog-centered" role="document">
							<div class="modal-content">
								<form data-bitwarden-watching="1" method="POST" action="{{ route('distributors.create') }}">
									@csrf
									<div class="modal-body">
										<div class="card-body p-5">
											<div class="card-title d-flex align-items-center">
												<div><i class="bx bx-user me-1 font-22 text-primary"></i>
												</div>
												<h5 class="mb-0 text-primary">Ajouter un distributeur</h5>
											</div>
											<hr>
											<div class="row g-3">
												<div class="col-md-12">
													<label for="name" class="form-label">Nom</label>
													<input required name="name" type="text" class="form-control" id="name">
												</div>
											</div>
											<div class="row g-3 mt-2">
												<div class="col-md-12">
													<label for="identifiant" class="form-label">Identifiant (customer_id)</label>
													<input required name="identifiant" type="text" class="form-control" id="identifiant">
												</div>
											</div>
										</div>
									</div>
									<div class="modal-footer">
										<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
										<button type="submit" class="btn btn-primary px-5">Ajouter</button>
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
								<!-- <div class="d-flex justify-content-center">
									<div class="loading spinner-border text-dark" role="status"> 
										<span class="visually-hidden">Loading...</span>
									</div>
								</div> -->
								<table id="example" class="table_mobile_responsive w-100 table table-striped table-bordered">
									<thead>
										<tr>
											<th>Distributeur</th>
											<th class="col-md-3">Identifiant</th>
											<th class="col-md-1">Action</th>
										</tr>
									</thead>

									<tbody>
										@foreach($distributors as $distributor)
											<tr>
												<td>{{ $distributor['name']  }}</td>
												<td>{{ $distributor['customer_id']  }}</td>
												<td>
													<div class="d-flex">
														<div data-id="{{ $distributor['id'] }}" class="update_action action_table font-22 text-primary">	
															<i class="fadeIn animated bx bx-edit"></i>
														</div>
														<div data-id="{{ $distributor['id'] }}" style="margin-left:10px;" class="delete_action action_table font-22 text-primary">	
															<i class="text-danger fadeIn animated bx bx-trash-alt"></i>
														</div>
													</div>
												</td>
											</tr>

											<!-- Modal modification de rôle -->
											<div class="modal fade" id="updateDistributorModal_{{ $distributor['id'] }}" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
												<div class="modal-dialog modal-dialog-centered" role="document">
													<div class="modal-content">
														<form method="POST" action="{{ route('distributors.update') }}">
															@csrf
															{{ method_field('PUT') }}
															<div class="modal-body">
																<div class="card-body p-5">
																	<div class="card-title d-flex align-items-center">
																		<div><i class="bx bx-key me-1 font-22 text-primary"></i>
																		</div>
																		<h5 class="mb-0 text-primary">Modifier un distributeur</h5>
																	</div>
																	<hr>
																	<div class="row g-3">
																		<div class="col-md-12">
																			<label for="update_name" class="form-label">Nom</label>
																			<input value="{{ $distributor['name'] }}" required name="update_name" type="text" class="form-control" id="update_name">
																		</div>
																	</div>
																	<div class="row g-3 mt-2">
																		<div class="col-md-12">
																			<label for="update_identifiant" class="form-label">Identifiant (customer_id)</label>
																			<input value="{{ $distributor['customer_id'] }}" required name="update_identifiant" type="text" class="form-control" id="update_identifiant">
																		</div>
																	</div>
																</div>
															</div>
															<div class="modal-footer">
																<input type="hidden" name="distributor_id" value="{{ $distributor['id'] }}">
																<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
																<button type="submit" class="btn btn-primary px-5">Modifier</button>
															</div>
														</form>
													</div>
												</div>
											</div>

											<!-- Modal Suppression -->
											<div class="modal fade" id="deleteDistributor_{{ $distributor['id'] }}" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
												<div class="modal-dialog modal-dialog-centered" role="document">
													<div class="modal-content">
														<form method="POST" action="{{ route('distributors.delete') }}">
															@csrf
															{{ method_field('delete') }}
															<div class="modal-body">
																<h2 class="text-center">Supprimer le distributeur ?</h2>
															</div>
															<div class="modal-footer">
																<input type="hidden" name="distributor_id" value="{{ $distributor['id'] }}">
																<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
																<button type="submit" class="btn btn-primary">Oui</button>
															</div>
														</form>
													</div>
												</div>
											</div>

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
				
			})
			
		})


		$("#show_modal_distributors").on('click', function(){
			$('#createDistributorsModal').modal({
				backdrop: 'static',
				keyboard: false
			})
			$("#createDistributorsModal").modal('show')
		})

		$(".update_action").on('click', function(){
			$('#updateDistributorModal_'+$(this).attr('data-id')).modal({
				backdrop: 'static',
				keyboard: false
			})

			$("#updateDistributorModal_"+$(this).attr('data-id')).modal('show')
		})

		$(".delete_action").on('click', function(){
			$('#deleteDistributor_'+$(this).attr('data-id')).modal({
				backdrop: 'static',
				keyboard: false
			})
			$("#deleteDistributor_"+$(this).attr('data-id')).modal('show')
		})

		
		</script>
	@endsection


