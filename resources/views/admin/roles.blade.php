
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
									<li class="breadcrumb-item active" aria-current="page">Roles</li>
								</ol>
							</nav>
						</div>
						<div class="ms-auto ms-auto-responsive">
							<button id="show_modal_role" type="button" class="btn btn-dark px-5">Ajouter un role</button>
						</div>
					</div>


					
					<!-- Modal création de rôle -->
					<div class="modal fade" id="createRoleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
						<div class="modal-dialog modal-dialog-centered" role="document">
							<div class="modal-content">
								<form data-bitwarden-watching="1" method="POST" action="{{ route('role.create') }}">
									@csrf
									<div class="modal-body">
										<div class="card-body p-3">
											<div class="card-title d-flex align-items-center">
												<div><i class="bx bx-key me-1 font-22 text-primary"></i>
												</div>
												<h5 class="mb-0 text-primary">Ajouter un rôle</h5>
											</div>
											<hr>
											<div class="row g-3">
												<div class="col-md-12">
													<label for="role" class="form-label">Rôle</label>
													<input required name="role" type="text" class="form-control" id="role">
												</div>
											</div>
											<div class="row g-3 mt-2">
												<div class="col-md-12">
													<label for="color" class="form-label">Couleur</label>
													<input required name="color" type="color" class="form-control" id="color">
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
							<table id="example" class="d-none table_mobile_responsive w-100 table table-striped table-bordered">
								<thead>
									<tr>
										<th>Role</th>
										<th>Couleur</th>
										<th class="col-md-1">Action</th>
										<th class="col-md-1">id</th>
									</tr>
								</thead>

								<tbody>
									@foreach ($roles as $role)
										<tr>
											<td data-label="Nom">{{ $role['role'] }}</td>
											<td data-label="Couleur">
												<div class="w-100 d-flex justify-content-between">
													<span class="role_color" style="width:75px; height:25px;background-color:{{ $role['color'] }}"></span>
												</div>
												
											</td>
											<td class="d-flex justify-content-between" data-label="Action" >
												@if(!in_array($role['id'], $role_can_not_delete))
													<div class="d-flex">
														<div data-id="{{ $role['id'] }}" class="update_action action_table font-22 text-primary">	
															<i class="fadeIn animated bx bx-edit"></i>
														</div>
														<div data-id="{{ $role['id'] }}" style="margin-left:10px;" class="delete_action action_table font-22 text-primary">	
															<i class="text-danger fadeIn animated bx bx-trash-alt"></i>
														</div>
													</div>
												@else 
													<div class="d-flex">
														<div data-id="{{ $role['id'] }}" class="update_action action_table font-22 text-primary">	
															<i class="fadeIn animated bx bx-edit"></i>
														</div>
														<div class="action_table font-22 text-secondary" style="margin-left:10px;">	
															<i class="text-secondary fadeIn animated bx bx-trash-alt"></i>
														</div>
													</div>
												@endif
											</td>
											<td data-label="ID">{{ $role['id'] }}</td>
										</tr>

										<!-- Modal modification de rôle -->
										<div class="modal fade" id="updateRoleModal_{{ $role['id'] }}" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
											<div class="modal-dialog modal-dialog-centered" role="document">
												<div class="modal-content">
													<form method="POST" action="{{ route('role.update') }}">
														@csrf
														{{ method_field('PUT') }}
														<div class="modal-body">
															<div class="card-body p-3">
																<div class="card-title d-flex align-items-center">
																	<div><i class="bx bx-key me-1 font-22 text-primary"></i>
																	</div>
																	<h5 class="mb-0 text-primary">Modifier un rôle</h5>
																</div>
																<hr>
																<div class="row g-3">
																	<div class="col-md-12">
																		<label for="update_role" class="form-label">Rôle</label>
																		<input value="{{ $role['role'] }}" required name="update_role" type="text" class="form-control" id="update_role">
																	</div>
																</div>
																<div class="row g-3 mt-2">
																	<div class="col-md-12">
																		<label for="update_color" class="form-label">Couleur</label>
																		<input value="{{ $role['color'] }}" required name="update_color" type="color" class="form-control" id="update_color">
																	</div>
																</div>
															</div>
														</div>
														<div class="modal-footer">
															<input type="hidden" name="role_id" value="{{ $role['id'] }}">
															<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
															<button type="submit" class="btn btn-primary px-5">Modifier</button>
														</div>
													</form>
												</div>
											</div>
										</div>

										<!-- Modal Suppression -->
										<div class="modal fade" id="deleteRole_{{ $role['id'] }}" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
											<div class="modal-dialog modal-dialog-centered" role="document">
												<div class="modal-content">
													<form method="POST" action="{{ route('role.delete') }}">
														@csrf
														{{ method_field('delete') }}
														<div class="modal-body">
															<h2 class="text-center">Supprimer le rôle ?</h2>
															<input name="account_user" type="hidden" id="account_user" value="">
														</div>
														<div class="modal-footer">
															<input type="hidden" name="role_id" value="{{ $role['id'] }}">
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

		@endsection

	
	@section("script")

		<script src="{{asset('assets/plugins/datatable/js/jquery.dataTables.min.js')}}"></script>
		<script src="{{asset('assets/plugins/datatable/js/dataTables.bootstrap5.min.js')}}"></script>
		<script src="assets/plugins/select2/js/select2.min.js"></script>
		<script>



		$(document).ready(function() {

			$('#example').DataTable({
				"order": [[ 3, 'asc' ]],
				"columnDefs": [
					{ "visible": false, "targets": 3 }
				],
				"initComplete": function(settings, json) {
					$(".loading").hide()
					$("#example").removeClass('d-none')
				}
			})
			
		})

		$("#show_modal_role").on('click', function(){
			$('#createRoleModal').modal({
				backdrop: 'static',
				keyboard: false
			})
			$("#createRoleModal").modal('show')
		})

		$(".update_action").on('click', function(){
			$('#updateRoleModal_'+$(this).attr('data-id')).modal({
				backdrop: 'static',
				keyboard: false
			})
			$("#updateRoleModal_"+$(this).attr('data-id')).modal('show')
		})

		$(".delete_action").on('click', function(){
			$('#deleteRole_'+$(this).attr('data-id')).modal({
				backdrop: 'static',
				keyboard: false
			})
			$("#deleteRole_"+$(this).attr('data-id')).modal('show')
		})

		</script>
	@endsection


