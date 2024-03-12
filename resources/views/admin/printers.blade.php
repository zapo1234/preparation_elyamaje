
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
									<li class="breadcrumb-item active" aria-current="page">Imprimantes</li>
								</ol>
							</nav>
						</div>
						<div class="ms-auto ms-auto-responsive">
							<button id="show_modal_add_printer" type="button" class="btn btn-dark px-5">Ajouter une imprimante</button>
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
											<th>Nom</th>
											<th>Adresse IP</th>
											<th>Port</th>
											<th>Préparateur</th>
											<th>Action</th>
										</tr>
									</thead>
									<tbody>
										@foreach($printers as $printer)
											<tr>
												<td data-label="Nom">{{  $printer->name }}</td>
												<td data-label="Adresse IP">{{  $printer->address_ip }}</td>
												<td data-label="Port">{{  $printer->port }}</td>
												<td data-label="Préparateur">
													@if($printer->userName)
														{{ $printer->userName }}
													@else 
														<span class="text-warning">Non attribuée</span>
													@endif
												</td>
												<td class="d-flex justify-content-between" data-label="Action" >
													<div class="d-flex">
														<div data-id="{{ $printer->id }}" class="update_action action_table font-22 text-primary">	
															<i class="text-primary fadeIn animated bx bx-edit"></i>
														</div>
														<div data-id="{{ $printer->id }}" style="margin-left:10px;" class="delete_action action_table font-22">	
															<i class="text-danger fadeIn animated bx bx-trash-alt"></i>
														</div>
													</div>
												</td>
											</tr>


											<!-- Modal update imprimante -->
											<div class="modal modal_radius fade" id="updatePrinterModal_{{ $printer->id }}" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
												<div class="modal-dialog modal-dialog-centered" role="document">
													<div class="modal-content">
														<form data-bitwarden-watching="1" method="POST" action="{{ route('printer.update') }}">
															@csrf
															<div class="modal-body">
																<div class="card-body p-3">
																	<div class="card-title d-flex align-items-center">
																		<div><i class="bx bx-printer me-1 font-22 text-primary"></i>
																		</div>
																		<h5 class="mb-0 text-primary">Modifier une imprimante</h5>
																		<input type="hidden" value="{{ $printer->id }}" required name="printer_id" id="printer_id">
																	</div>
																	<hr>
																	<div class="row g-3">
																		<div class="col-md-12">
																			<label for="update_name" class="form-label">Nom*</label>
																			<input value="{{ $printer->name }}" required name="update_name" type="text" class="form-control" id="update_name">
																		</div>
																		<div class="col-md-12">
																			<label for="update_address_ip" class="form-label">Adresse IP*</label>
																			<input value="{{ $printer->address_ip }}" required name="update_address_ip" type="text" class="form-control" id="update_address_ip">
																		</div>
																		<div class="col-md-12">
																			<label for="update_port" class="form-label">Port (9100 par défaut)</label>
																			<input value="{{ $printer->port }}" name="update_port" type="text" class="form-control" id="update_port">
																		</div>
																		<div class="col-md-12">
																			<label for="update_user_id" class="form-label">Préparateur*</label>
																			<select required name="update_user_id" id="update_user_id" class="form-select">
																				@foreach($preparateurs as $preparateur)
																					@if($preparateur['user_id'] == $printer->user_id)
																						<option selected value="{{ $preparateur['user_id'] }}">{{  $preparateur['name'] }}</option>
																					@else
																						<option value="{{ $preparateur['user_id'] }}">{{  $preparateur['name'] }}</option>
																					@endif
																				@endforeach
																			</select>
																		</div>
																	</div>
																</div>
															</div>
															<div class="modal-footer">
																<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
																<button type="submit" class="btn btn-primary px-5">Modifier</button>
															</div>
														</form>
													</div>
												</div>
											</div>
											<!-- Modal update imprimante -->

										@endforeach
									</tbody>
								</table>
							</div>
						</div>
				</div>
			</div>

			<!-- Modal ajout imprimante -->
			<div class="modal modal_radius fade" id="addPrinterModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
				<div class="modal-dialog modal-dialog-centered" role="document">
					<div class="modal-content">
						<form data-bitwarden-watching="1" method="POST" action="{{ route('printer.add') }}">
							@csrf
							<div class="modal-body">
								<div class="card-body p-3">
									<div class="card-title d-flex align-items-center">
										<div><i class="bx bx-printer me-1 font-22 text-primary"></i>
										</div>
										<h5 class="mb-0 text-primary">Ajouter une imprimante</h5>
									</div>
									<hr>
									<div class="row g-3">
										<div class="col-md-12">
											<label for="name" class="form-label">Nom*</label>
											<input required name="name" type="text" class="form-control" id="name">
										</div>
										<div class="col-md-12">
											<label for="address_ip" class="form-label">Adresse IP*</label>
											<input placeholder="192.168.0.0" required name="address_ip" type="text" class="form-control" id="address_ip">
										</div>
										<div class="col-md-12">
											<label for="port" class="form-label">Port (9100 par défaut)</label>
											<input value="9100" placeholder="9100" name="port" type="text" class="form-control" id="port">
										</div>
										<div class="col-md-12">
											<label for="user_id" class="form-label">Préparateur*</label>
											<select required name="user_id" id="user_id" class="form-select">
												@foreach($preparateurs as $preparateur)
													<option value="{{ $preparateur['user_id'] }}">{{  $preparateur['name'] }}</option>
												@endforeach
											</select>
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

			<!-- Modal Suppression -->
			<div class="modal modal_radius fade" id="deletePrinter" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
				<div class="modal-dialog modal-dialog-centered" role="document">
					<div class="modal-content">
						<form method="POST" action="{{ route('printer.delete') }}">
							@csrf
							<div class="modal-body">
								<h2 class="text-center">Supprimer l'imprimante ?</h2>
								<input name="printer_id" type="hidden" id="printer_id_to_delete" value="">
							</div>
							<div class="modal-footer">
								<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
								<button type="submit" class="btn btn-primary">Oui</button>
							</div>
						</form>
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

		$("#show_modal_add_printer").on('click', function(){
			$('#addPrinterModal').modal({
				backdrop: 'static',
				keyboard: false
			})
			$("#addPrinterModal").modal('show')
		})

		// Supprimer imprimante
		$(".delete_action").on('click', function(){
			var printer_id = $(this).attr('data-id')
			$("#printer_id_to_delete").val(printer_id)
			$("#deletePrinter").modal('show')
		})

		// Modifier imprimante
		$(".update_action").on('click', function(){
			var printer_id = $(this).attr('data-id')
			$("#updatePrinterModal_"+printer_id).modal('show')
		});

		
		</script>
	@endsection


