
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
										<div class="card-body p-5">
											<div class="card-title d-flex align-items-center">
												<div><i class="bx bxs-user me-1 font-22 text-primary"></i>
												</div>
												<h5 class="mb-0 text-primary">Ajouter un compte</h5>
											</div>
											<hr>
											<div class="row g-3">
												<div class="col-md-12">
													<label for="name_last_name" class="form-label">Nom / Prénom</label>
													<input required name="name_last_name" type="text" class="form-control" id="name_last_name">
												</div>
											
												<div class="col-md-12">
													<label for="email" class="form-label">Email</label>
													<input required name="email" type="email" class="form-control" id="email">
												</div>
												<div class="col-md-12">
													<label for="role" class="form-label">Rôle</label>
													<select required name="role[]" id="role" class="form-select">
														@foreach($roles as $role)
															@if( $role->id != 1 || $isAdmin)
																<option value="{{ $role->id }}">{{  $role->role }}</option>
															@endif
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
											<th>Nom</th>
											<th>Rôles</th>
											<th class="col-md-1">Action</th>
										</tr>
									</thead>

									<tbody>
										@foreach ($users as $user)
											<tr>
												<td data-label="Nom">{{ $user['name'] }}</td>
												<td data-label="Status">	
													@foreach($roles as $role)
														@if(in_array($role['id'], $user['role_id']))
															<span class="role_user_badge badge" style="background-color:{{ $role['color'] }}">{{ $role['role'] }}</span>
														@endif
													@endforeach
												</td>
												<td class="d-flex justify-content-between" data-label="Action" >
													@if(in_array('Admin', $user['role']) && !$isAdmin)
														<div class="d-flex">
															<div class="action_table font-22 text-secondary">	
																<i class="text-secondary fadeIn animated bx bx-edit"></i>
															</div>
														</div>
													@else 
														<div class="d-flex">
															<div data-id="{{ $user['user_id'] }}" class="update_action action_table font-22 text-primary">	
																<i class="fadeIn animated bx bx-edit"></i>
															</div>
															@if($user['user_id'] != 1)
																<div data-id="{{ $user['user_id'] }}" style="margin-left:10px;" class="delete_action action_table font-22 text-primary">	
																	<i class="text-danger fadeIn animated bx bx-trash-alt"></i>
																</div>
															@endif
														</div>
													@endif
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



			<!-- Modal Suppression -->
			<div class="modal fade" id="deleteAccount" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
				<div class="modal-dialog modal-dialog-centered" role="document">
					<div class="modal-content">
						<form method="POST" action="{{ route('account.delete') }}">
							@csrf
							<div class="modal-body">
								<h2 class="text-center">Supprimer le compte ?</h2>
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



			<!-- Modal modification de compte -->
			<div class="modal fade" id="updateAccount" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
				<div class="modal-dialog modal-dialog-centered" role="document">
					<div class="modal-content">
						<form data-bitwarden-watching="1" method="POST" action="{{ route('account.update') }}">
							@csrf
							<input name="account_user_update" type="hidden" id="account_user_update" value="">
							<div class="modal-body">
								<div class="card-body p-5">
									<div class="card-title d-flex align-items-center">
										<div><i class="bx bxs-user me-1 font-22 text-primary"></i>
										</div>
										<h5 class="mb-0 text-primary">Modifier le compte</h5>
									</div>
									<hr>
									<div class="row g-3">
										<div class="col-md-12">
											<label for="update_name_last_name" class="form-label">Nom / Prénom</label>
											<input required name="update_name_last_name" type="text" class="form-control" id="update_name_last_name">
										</div>
									
										<div class="col-md-12">
											<label for="update_email" class="form-label">Email</label>
											<input required name="update_email" type="update_email" class="form-control" id="update_email">
										</div>
										<div class="col-md-12">
											<label for="update_role" class="form-label">Rôle</label>
											<select required name="update_role[]" id="update_role" class="form-select">
												@foreach($roles as $role)
													@if( $role->id != 1 || $isAdmin)
														<option value="{{ $role->id }}">{{  $role->role }}</option>
													@else 
														<option disabled value="{{ $role->id }}">{{  $role->role }}</option>
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

		@endsection

	
	@section("script")

		<script src="{{asset('assets/plugins/datatable/js/jquery.dataTables.min.js')}}"></script>
		<script src="{{asset('assets/plugins/datatable/js/dataTables.bootstrap5.min.js')}}"></script>
		<script src="assets/plugins/select2/js/select2.min.js"></script>
		<script>

		$(document).ready(function() {

			$("select").select2({multiple: true, maximumSelectionLength: 3})

			$('select').val(null);
			$('.select2-selection__rendered').html('');

			$('#example').DataTable({

			})

		})

		$("#show_modal_account").on('click', function(){
			$('#createAccountModal').modal({
				backdrop: 'static',
				keyboard: false
			})
			$("#createAccountModal").modal('show')
		})


		// Supprimer compte
		$(".delete_action").on('click', function(){
			var id_account = $(this).attr('data-id')
			$("#account_user").val(id_account)
			$("#deleteAccount").modal('show')
		})

		// Modifier compte
		$(".update_action").on('click', function(){
			var id_account = $(this).attr('data-id')
			
			$.ajax({
				url:"{{ route('account.user') }}",
				method: 'GET',
				data: {user_id: id_account}
			}).done(function(data) {
				if(JSON.parse(data).success){
					var user = JSON.parse(data).user
					$("#update_name_last_name").val(user.name)
					$("#update_email").val(user.email)
					$('#update_role').val(user.roles).trigger('change').select2();
					$("#account_user_update").val(id_account)
					$("#updateAccount").modal('show')

				} else {
					alert(data)
				}
			});


		})
			
		


		</script>
	@endsection

