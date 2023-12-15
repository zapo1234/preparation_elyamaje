
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
									<li class="breadcrumb-item active" aria-current="page">Comptes</li>
								</ol>
							</nav>
						</div>
						<div class="ms-auto ms-auto-responsive">
							<button id="show_modal_account" type="button" class="btn btn-dark px-5">Ajouter un compte</button>
						</div>
					</div>



					<!-- Modal création de compte -->
					<div class="modal fade" id="createAccountModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
						<div class="modal-dialog modal-dialog-centered" role="document">
							<div class="modal-content">
								<form data-bitwarden-watching="1" method="POST" action="{{ route('account.create') }}">
									@csrf
									<div class="modal-body">
										<div class="card-body p-3">
											<div class="card-title d-flex align-items-center">
												<div><i class="bx bxs-user me-1 font-22 text-primary"></i>
												</div>
												<h5 class="mb-0 text-primary">Ajouter un compte</h5>
											</div>
											<hr>
											<div class="row g-3">
												<div class="col-md-12">
													<label for="name_last_name" class="form-label">Nom / Prénom*</label>
													<input required name="name_last_name" type="text" class="form-control" id="name_last_name">
												</div>
											
												<div class="col-md-12">
													<label for="email" class="form-label">Email*</label>
													<input required name="email" type="email" class="form-control" id="email">
												</div>
												<div class="col-md-12">
													<label for="role" class="form-label">Rôle*</label>
													<select required name="role[]" id="role" class="form-select select">
														@foreach($roles as $role)
															@if( in_array($role->id, [2,3,4,5]) || $isAdmin)
																<option value="{{ $role->id }}">{{  $role->role }}</option>
															@endif
														@endforeach
													</select>
												</div>
												<div class="col-md-12">
													<label for="type" class="form-label">Type*</label>
													<select required name="type" id="type" class="form-select">
														<option value="warehouse">Entrepôt</option>
														<option value="shop">Boutique</option>
													</select>
												</div>
												<div class="poste_input d-none col-md-12">
													<label for="poste" class="form-label">N° du poste</label>
													<input name="poste" type="number" class="poste_field form-control" id="poste">
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

					<div class="card card_table_mobile_responsive">
						<div class="card-body">
							<div class="d-flex justify-content-center">
								<div class="loading spinner-border text-dark" role="status"> 
									<span class="visually-hidden">Loading...</span>
								</div>
							</div>

							<!-- Type -->
							<select class="d-none select2_custom type_dropdown input_form_type">
								<option value="">Type</option>
								<option selected value="Entrepôt">Entrepôt</option>
								<option value="Boutique">Boutique</option>
							</select>

							<!-- Compte actif -->
							<select class="d-none select2_custom actif_dropdown input_form_type">
								<option selected value="1">Actif</option>
								<option value="0">Inactif</option>
							</select>

							<table id="example" class="d-none table_mobile_responsive w-100 table table-striped table-bordered">
								<thead>
									<tr>
										<th>Nom</th>
										<th>Rôles</th>
										<th>Type</th>
										<th class="col-md-1">Action</th>
										<th class="col-md-1">Actif</th>
									</tr>
								</thead>

								<tbody>
									@foreach ($users as $user)
										<tr>
											<td data-label="Nom">{{ $user['name'] }}</td>
											<td data-label="Rôles">	
												@if($user['active'] == 1)
													@foreach($roles as $role)
														@if(in_array($role['id'], $user['role_id']))
															<span class="role_user_badge badge" style="background-color:{{ $role['color'] }}">{{ $role['role'] }}</span>
														@endif
													@endforeach
												@else
													<span class="role_user_badge badge" style="background-color:#E1DDDD">Compte inactif</span>
												@endif
												
											</td>
											<td data-label="Type">{{ $user['type'] == "warehouse" ? "Entrepôt" : ($user['type'] == "shop" ? "Boutique" : "Non défini")}}</td>
											<td class="d-flex justify-content-between" data-label="Action" >
												@if(!$isAdmin && count(array_intersect([2,3,4,5], $user['role_id'])) == 0 && $user['active'] == 1 )
													<div class="d-flex">
														<div class="action_table font-22 text-secondary">	
															<i class="text-secondary fadeIn animated bx bx-edit"></i>
														</div>
													</div>
												@elseif($user['active'] == 1)
													<div class="d-flex">
														<div data-id="{{ $user['user_id'] }}" class="update_action action_table font-22 text-primary">	
															<i class="fadeIn animated bx bx-edit"></i>
														</div>
														@if($user['user_id'] != 1 && $user['active'] == 1)
															<div data-user="{{ $user['name'] }}" data-id="{{ $user['user_id'] }}" style="margin-left:10px;" class="delete_action action_table font-22 text-primary">	
																<i class="text-danger fadeIn animated bx bx-trash-alt"></i>
															</div>
														@endif
													</div>
												@elseif($user['active'] == 0)
													<div title="Activer le compte" data-user="{{ $user['name'] }}" data-id="{{ $user['user_id'] }}" style="margin-left:10px;" class="active_action action_table font-22 text-primary">	
														<i class="text-success fadeIn animated bx bx-check"></i>
													</div>
												@endif
											</td>
											<td data-label="Actif">{{ $user['active'] }}</td>
										</tr>


										<div class="modal fade" id="updateAccount_user_{{ $user['user_id'] }}" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
											<div class="modal-dialog modal-dialog-centered" role="document">
												<div class="modal-content">
													<form data-bitwarden-watching="1" method="POST" action="{{ route('account.update') }}">
														@csrf
														<input name="account_user_update" type="hidden" id="account_user_update" value="{{ $user['user_id'] }}">
														<div class="modal-body">
															<div class="card-body p-3">
																<div class="card-title d-flex align-items-center">
																	<div><i class="bx bxs-user me-1 font-22 text-primary"></i>
																	</div>
																	<h5 class="mb-0 text-primary">Modifier le compte</h5>
																</div>
																<hr>
																<div class="row g-3">
																	<div class="col-md-12">
																		<label for="update_name_last_name" class="form-label">Nom / Prénom*</label>
																		<input value="{{ $user['name'] }}" required name="update_name_last_name" type="text" class="form-control" id="update_name_last_name">
																	</div>
																
																	<div class="col-md-12">
																		<label for="update_email" class="form-label">Email*</label>
																		<input value="{{ $user['email'] }}" required name="update_email" type="update_email" class="form-control" id="update_email">
																	</div>
																	<div class="col-md-12">
																		<label for="update_role" class="form-label">Rôle*</label>
																		<input type="hidden" value="{{ implode(',', $user['role_id']) }}" id="role_user">
																		<select data-id="{{ $user['user_id'] }}" multiple required name="update_role[]" id="update_role" class="select form-select">
																			@foreach($roles as $role)
																				@if( in_array($role->id, [2,3,4,5]) || $isAdmin)
																					<option value="{{ $role->id }}">{{  $role->role }}</option>
																				@else 
																					<option selected disabled value="{{ $role->id }}">{{  $role->role }}</option>
																				@endif
																			@endforeach
																		</select>
																	</div>
																	<div class="col-md-12">
																		<label for="update_type" class="form-label">Type*</label>
																		<select required name="update_type" id="update_type" class="form-select">
																			@if($user['type'] === "warehouse")
																				<option selected value="warehouse">Entrepôt</option>
																				<option value="shop">Boutique</option>
																			@elseif($user['type'] === "shop")
																				<option selected value="shop">Boutique</option>
																				<option selected value="warehouse">Entrepôt</option>
																			@else 
																				<option value="warehouse">Entrepôt</option>
																				<option value="shop">Boutique</option>
																			@endif
																		</select>
																	</div>
																	<div class="poste_input d-none col-md-12">
																		<label for="update_poste" class="form-label">N° du poste</label>
																		<input value="{{ $user['poste'] }}" name="update_poste" type="number" class="poste_field form-control" id="update_poste">
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
									@endforeach
								</tbody>
							</table>
						</div>
					</div>
				</div>
			</div>


			<!-- Modal Suppression -->
			<div class="modal fade" id="deleteAccount" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
				<div class="modal-dialog modal-dialog-centered" role="document">
					<div class="modal-content">
						<form method="POST" action="{{ route('account.delete') }}">
							@csrf
							<div class="modal-body">
								<h2 class="text-center">Supprimer le compte <br><span class="font-bold account_to_delete"></span> ?</h2>
								<input name="account_user" type="hidden" id="account_user" value="">
							</div>
							<div class="modal-footer">
								<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
								<button type="submit" class="btn btn-primary">Oui</button>
							</div>
						</form>
					</div>
				</div>
			</div>

			<!-- Modal d'activation -->
			<div class="modal fade" id="activeAccount" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
				<div class="modal-dialog modal-dialog-centered" role="document">
					<div class="modal-content">
						<form method="POST" action="{{ route('account.active') }}">
							@csrf
							<div class="modal-body">
								<h2 class="text-center">Activer le compte <br><span class="font-bold account_to_active"></span> ?</h2>
								<input name="account_to_active" type="hidden" id="account_to_active" value="">
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
		<script src="assets/plugins/select2/js/select2.min.js"></script>
		<script>

			
		function showHidePoste(array){
			if(array.includes("3")){
				$(".poste_input").removeClass('d-none')
				$(".poste_field").attr('required', true)
			} else {
				$(".poste_input").addClass('d-none')
				$(".poste_field").attr('required', false)
			}
		}

		$(document).ready(function() {

			$(".select").select2({multiple: true, maximumSelectionLength: 3})
			$('.select').val(null);
			$('.select2-selection__rendered').html('');

			$('#example').DataTable({
				"columnDefs": [
					{ "visible": false, "targets": 2 },
					{ "visible": false, "targets": 4 },
				],
				"initComplete": function(settings, json) {
					$("#example").removeClass('d-none')
					$(".dataTables_length").css('display', 'flex')
            		$(".dataTables_length").addClass('select2_custom')
					$(".type_dropdown").appendTo('.dataTables_length')
					$(".type_dropdown").removeClass('d-none')
					$(".type_dropdown").select2({
						width: '100px',
					});

					$(".actif_dropdown select").css('margin-left', '10px')
					$(".actif_dropdown").appendTo('.dataTables_length')
					$(".actif_dropdown").removeClass('d-none')
					$(".actif_dropdown").select2({
						width: '100px',
					});
					
					$(".loading").hide()
				}
			})

			$(".select2-container").css('margin-left', '10px')
			$('.type_dropdown').trigger('change')
			$('.actif_dropdown').trigger('change')			
		})

		$('.type_dropdown').on('change', function(e){
			var type_dropdown = $(this).val();
			$('#example').DataTable()
			.column(2).search(type_dropdown, true, false)
			.draw();
		})

		$('.actif_dropdown').on('change', function(e){
			var actif_dropdown = $(this).val();
			$('#example').DataTable()
			.column(4).search(actif_dropdown, true, false)
			.draw();
		})

		$(".select").on("change", function(){
			showHidePoste($(this).val())
		})

		$("#show_modal_account").on('click', function(){
			$('#createAccountModal').modal({
				backdrop: 'static',
				keyboard: false
			})
			showHidePoste($("#role").val())
			$("#createAccountModal").modal('show')
		})


		// Supprimer compte
		$(".delete_action").on('click', function(){
			var id_account = $(this).attr('data-id')
			var account_name = $(this).attr('data-user')
			$("#account_user").val(id_account)
			$(".account_to_delete").text(account_name)
			$("#deleteAccount").modal('show')
		})

		// Modifier compte
		$(".update_action").on('click', function(){
			var id_account = $(this).attr('data-id')
			var roles = $("#updateAccount_user_"+id_account).find('#role_user').val()
			$("#updateAccount_user_"+id_account).find('#update_role').val(roles.split(',')).trigger('change').select2();
			$("#updateAccount_user_"+id_account).modal({
				backdrop: 'static',
				keyboard: false
			})
			$("#updateAccount_user_"+id_account).modal('show')
		});

		// Activer le compte
		$(".active_action").on('click', function(){
			var id_account = $(this).attr('data-id')
			var account_name = $(this).attr('data-user')
			$("#account_to_active").val(id_account)
			$(".account_to_active").text(account_name)
			$("#activeAccount").modal('show')
		})


		</script>
	@endsection


