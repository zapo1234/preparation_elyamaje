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
						<div class="breadcrumb-title pe-3">Historique</div>
						<div class="ps-3">
							<nav aria-label="breadcrumb">
								<ol class="breadcrumb mb-0 p-0">
									<li class="breadcrumb-item active" aria-current="page">Commandes & Transferts</li>
								</ol>
							</nav>
						</div>
						<div class="ms-auto ms-auto-responsive">
							<button id="history_by_date" type="button" class="btn btn-dark px-5">Générer historique</button>
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

						<div class="card card_table_mobile_responsive radius-10 w-100">
							<div class="card-body">
								<div class="d-flex justify-content-center">
									<div class="loading spinner-border text-dark" role="status"> 
										<span class="visually-hidden">Loading...</span>
									</div>
								</div>
								<div class="table-responsive">
								
									<form method="GET" action="{{ route('leader.history') }}" class="d-flex d-none order_research">
										<input value="{{ $parameter['created_at'] ?? '' }}" name="created_at" class="custom_input" style="padding: 4px;" type="date">
										<input value="{{ $parameter['order_woocommerce_id'] ?? '' }}" placeholder="Numéro de commande" name="order_woocommerce_id" class="custom_input" style="padding: 4px;" type="text">
										<button style="margin-left:10px" class="research_history_order d-flex align-items-center btn btn-primary" type="submit">Rechercher</button>
									</form>

									<table id="example" class="d-none w-100 table_list_order table_mobile_responsive table table-striped table-bordered">

										<div class="d-none loading_show_detail_order w-100 d-flex justify-content-center">
											<div class="spinner-grow text-dark" role="status"> <span class="visually-hidden">Loading...</span></div>
										</div>
										
										<thead>
											<tr>
												<th class="col-md-1" scope="col">Commande</th>
												<th class="col-md-4"scope="col">Préparée</th>
												<th class="col-md-4" scope="col">Emballée</th>
												<th class="col-md-1" scope="col">Status</th>
												<th class="col-md-1" scope="col">Détails</th>
											</tr>
										</thead>
										<tbody>
											@foreach($histories as $histo)
												<tr>
													<td data-label="Commande">#{{ $histo['order_id'] }}</td>
													<td data-label="Préparée">
														<div class="d-flex flex-column">
															<div class="d-flex flex-wrap histo_order align-items-center">
																<span class="badge bg-dark">{{ $histo['prepared'] ?? ($histo['finished'] ?'Un autre jour' : '') }}</span>
																<!-- @if($histo['prepared']) -->
																	<span class="date_prepared">le {{ $histo['prepared_date'] }}</span>  
																<!-- @endif -->
															</div>
															
														</div>
													</td>
													<td data-label="Emballée">
														<div class="d-flex flex-column">
															<div class="d-flex flex-wrap histo_order align-items-center">
																<span class="badge bg-dark">{{ $histo['finished'] }}</span>
																@if($histo['finished'])
																	<span class="date_finished">le {{ $histo['finished_date'] }}</span>  
																@endif
															</div>
														</div>
													</td>
													<td data-label="Status">
														@if($histo['order_status'] || $histo['order_dolibarr_status'])
															<select style="width: 180px; font-weight: bold;	font-size: 0.9em; " data-from_dolibarr="{{ str_contains($histo['order_id'], 'BP') || str_contains($histo['order_id'], 'CO') ? 'true' : 'false' }}" data-order="{{ $histo['order_id'] }}" class="{{ $histo['order_status'] ?? $histo['order_dolibarr_status'] }} select_status select_user">
																@foreach($list_status as $key => $list)
																	@if($key == $histo['order_status'] || $key == $histo['order_dolibarr_status'])
																		<option selected value="{{ $histo['order_status'] ?? $histo['order_dolibarr_status'] }}">
																			{{ $histo['order_status'] ? __('status.'.$histo['order_status']) : __('status.'.$histo['order_dolibarr_status']) }}
																		</option>
																	@else 
																		<option value="{{ $key }}">{{ __('status.'.$key) }}</option>
																	@endif
																@endforeach
															</select>

														@elseif($histo['order_transfer_status'])
															<span class="p-2 badge bg-secondary">{{ __('status_transfers.'.$histo['order_transfer_status']) }}</span>
														@else 
															<span class="p-2 badge" style="background-color:#d16c6c">Aucune information</span>
														@endif
													</td>
													<td data-label="Détails">
														<button class="show_detail_button show_detail" onclick="show('{{ $histo['order_id'] }}')">
															<i class="font-primary font-20 bx bx-cube"></i>
														</button>	
														@if(strlen($histo['order_id']) != 10 && !str_contains($histo['order_id'], 'CO'))
															<button class="show_detail_button show_detail" onclick="show_detail_customer('{{ $histo['order_id'] }}')">
																<i class="font-primary font-20 bx bx-user"></i>
															</button>	
														@endif
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
	<script src="{{asset('assets/js/leaderHistory.js')}}"></script>

	@endsection

