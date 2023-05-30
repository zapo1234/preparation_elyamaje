
	@extends("layouts.app")

		@section("style")
			<link href="{{asset('assets/plugins/datatable/css/dataTables.bootstrap5.min.css')}}" rel="stylesheet" />
			<link href="assets/plugins/select2/css/select2.min.css" rel="stylesheet" />
			<link href="assets/plugins/select2/css/select2-bootstrap4.css" rel="stylesheet" />
		@endsection

		@section("wrapper")
			<div class="page-wrapper">
				<div class="page-content">
					<div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
						<div class="breadcrumb-title pe-3">Configuration</div>
						<div class="ps-3">
							<nav aria-label="breadcrumb">
								<ol class="breadcrumb mb-0 p-0">
									<li class="breadcrumb-item active" aria-current="page">Comptes</li>
								</ol>
							</nav>
						</div>
						<div class="ms-auto">
							<div class="col">
								<button id="show_modal_account" type="button" class="btn btn-dark px-5">Ajouter un compte</button>
							</div>
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
													<label for="role" class="form-label">Status</label>
													<select required name="role[]" id="role" class="form-select">
														@foreach($roles as $role)
															@if( $role->id != 1)
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
						<div class="card">
							<div class="card-body">
								<div class="table-responsive">
									<table id="example" class="table_mobile_responsive w-100 table table-striped table-bordered">
										<thead>
											<tr>
												<th>Nom</th>
												<th>Status</th>
											</tr>
										</thead>

										<tbody>
											@foreach ($users as $user)
												<tr>
													<td>{{ $user['name'] }}</td>
													<td>	
														@foreach($user['role'] as $key => $r)
															@if(count($user['role']) > 1)
																@if($key == count($user['role']) - 1)
																	/ {{ $r }}
																@else 
																	{{ $r }}
																@endif
															@else
																{{ $r }} 
															@endif
															
														@endforeach
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
			</div>
		@endsection

	
	@section("script")

		<script src="{{asset('assets/plugins/datatable/js/jquery.dataTables.min.js')}}"></script>
		<script src="{{asset('assets/plugins/datatable/js/dataTables.bootstrap5.min.js')}}"></script>
		<script src="assets/plugins/select2/js/select2.min.js"></script>
		<script>

		$(document).ready(function() {

			$("select").select2({multiple: true, maximumSelectionLength: 2})
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


		</script>
	@endsection


