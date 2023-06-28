
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
								<div class="modal-body">
									<form method="POST" action="{{ route('bordereau.generate') }}">
										@csrf
										<h2 class="text-center">Choisir la date</h2>
										<div class="d-flex justify-content-center w-100">
											<input class="date_bordereau_input" type="date" name="date" value="{{ date('Y-m-d') }}">
										</div>
										<div class="d-flex justify-content-center mt-3 w-100">
											<button type="button" class="btn btn-dark px-5" data-bs-dismiss="modal">Annuler</button>
											<button style="margin-left:15px" type="submit" class="btn btn-dark px-5">Générer</button>
										</div>
									</form>
								</div>
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

					<div class="show_messages"></div>

					<div class="row">
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
											<th>Commande</th>
											<th>Status</th>
											<th>Date</th>
											<th class="col-md-2">Étiquette</th>
										</tr>
									</thead>
									<tbody>
										@foreach($orders as $order)
											<tr>
												<td data-label="Commande">
													<span>{{ $order[0]['order_woocommerce_id'] }}</span>
												</td>
												<td data-label="Status">
													<span class="badge bg-default bg-light-{{ $order[0]['status'] }} text-light">{{ __('status.'.$order[0]['status'] ) }}</span>
												</td>
												<td data-label="Date">{{ date("d/m/Y", strtotime($order[0]['date'])) }}</td>
												<td data-label="Étiquette">
													@if(isset($order['labels']))
														@foreach($order['labels'] as $label)
															<div class="mb-2 d-flex w-100 align-items-center justify-content-between">
																<div>
																	<form class="d-flex" method="POST" action="{{ route('label.show') }}">
																		@csrf
																		<input name="label_id" type="hidden" value="{{ $label['label_id'] }}">  
																		<button type="submit" class="download_label_button"><i class="bx bx-show-alt"></i>{{ $label['tracking_number'] }} <span class="label_created_at text-secondary">({{ date("d/m/Y", strtotime($label['label_created_at'])) }})</span></button>
																	</form>
																	<form class="d-flex" method="POST" action="{{ route('label.download') }}">
																		@csrf
																		<input name="label_id" type="hidden" value="{{ $label['label_id'] }}">
																		<input name="order_id" type="hidden" value="{{ $order[0]['order_woocommerce_id'] }}">
																		<button type="submit" class="download_label_button"><i class="bx bx-download"></i>{{ $label['tracking_number'] }}</button>
																	</form>
																</div>
																<div>
																	<button data-tracking="{{ $label['tracking_number'] }}" data-label="{{ $label['label_id'] }}" type="submit" class="delete_label download_label_button"><i class="bx bx-trash"></i></button>
																</div>
															</div>
														@endforeach
														<div>
															<button data-order="{{ $order[0]['order_woocommerce_id'] }}" type="button" class="generate_label_button download_label_button"><i class="bx bx-plus"></i>Générer</button>
														</div>
													@else 
														<div>
															<button data-order="{{ $order[0]['order_woocommerce_id'] }}" type="button" class="generate_label_button download_label_button"><i class="bx bx-plus"></i>Générer</button>
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


			<!-- Modal supression -->
			<div class="modal fade" id="deleteLabelModalCenter" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
				<div class="modal-dialog modal-dialog-centered" role="document">
					<div class="modal-content">
						<div class="modal-body">
							<h2 class="text-center">Voulez-vous supprimer cette étiquette ?</h2>
							<form method="POST" action="{{ route('label.delete') }}">
								@csrf
								<input id="tracking_number" name="tracking_number" type="hidden" value="">
								<input id="label_id" name="label_id" type="hidden" value="">
								<div class="d-flex justify-content-center mt-3 w-100">
									<button type="button" class="btn btn-dark px-5" data-bs-dismiss="modal">Annuler</button>
									<button style="margin-left:15px" type="submit" class="btn btn-dark px-5">Oui</button>
								</div>
							</form>
						</div>
					</div>
				</div>
			</div>


			  <!-- Modal generate label -->
			  <div data-bs-keyboard="false" data-bs-backdrop="static" class="generate_label_modal modal fade" id="generateLabelModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
					<div class="modal-dialog modal-dialog-centered" role="document">
						<div class="modal-content">
							<div class="modal-body">	
								<form class="h-100" method="POST" action="{{ route('label.generate') }}">
									@csrf
									<input id="order_id_label" type="hidden" name="order_id" value="">
									<div class="h-100 d-flex flex-column justify-content-between">
										<div class="d-flex flex-column">
											<div class="mb-2 d-flex w-100 justify-content-between">
												<span style="width: 50px"><input data-id="" class="form-check-input check_all" type="checkbox" value="" aria-label="Checkbox for product order"></span>
												<span class="head_1 w-50">Article</span>
												<span class="head_2 w-25">P.U (€)</span>
												<span class="head_3 w-25">Quantité</span>
												<span class="head_4 w-25">Poids (kg)</span>
											</div>
											<div class="body_line_items_label">
											
											</div>
										</div>
										<div class="button_validate_modal_label d-flex justify-content-center mt-3 w-100">
											<button type="button" class="btn btn-dark px-5" data-bs-dismiss="modal">Annuler</button>
											<button style="margin-left:15px" type="submit" class="btn btn-dark px-5">Générer</button>
										</div>
									</div>
								</form>
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
					"order": [[3, 'asc']],
					"initComplete": function(settings, json) {
						$(".loading").hide()
						$("#example").removeClass('d-none')
					}
				})
			})

			$("#show_modal_bordereau").on('click', function(){
				$("#modalBordereau").modal('show')
			})

			$(".delete_label").on('click', function(){
				$("#tracking_number").val($(this).attr('data-tracking'))
				$("#label_id").val($(this).attr('data-label'))
				$("#deleteLabelModalCenter").modal('show')
			})


			$(".generate_label_button").on('click', function(){
					var order_id = $(this).attr('data-order')
					$.ajax({
						url: "{{ route('label.product_order_label') }}",
						method: 'POST',
						data : {_token: $('input[name=_token]').val(), order_id: order_id}
					}).done(function(data) {
						if(JSON.parse(data).success){
							$(".line_items_label").remove()
							$("#order_id_label").val(order_id)
							$(".check_all").attr('data-id', order_id)
							$(".check_all").prop('checked', true)
							$(".body_line_items_label").attr('id', 'order_'+order_id)

							var product_order = JSON.parse(data).products_order
							var innerHtml = ''
							var product = 0

							Object.entries(product_order).forEach(([key, value]) => {
								product = value.quantity - value.total_quantity == 0 ? product + 0 : product + 1
								innerHtml += 
								`<div class="${value.quantity - value.total_quantity == 0 ? 'disabled_text' : '' } line_items_label d-flex w-100 align-items-center justify-content-between">
									<span style="width: 50px">
										<input name="label_product[]" ${value.quantity - value.total_quantity == 0 ? 'disabled' : 'checked' } class="checkbox_label form-check-input" type="checkbox" value="${value.product_woocommerce_id}" aria-label="Checkbox for product order">	
									</span>
									<span class="w-50">${value.name}</span>
									<span class="w-25">${value.cost}</span>
									<span class="w-25" ><input ${value.quantity - value.total_quantity == 0 ? 'disabled' : '' } min="1" max="${value.quantity - (value.total_quantity ?? 0) }" value="${value.quantity -  (value.total_quantity ?? 0) }" name="quantity[${value.product_woocommerce_id}]" type="number"> / ${value.quantity}</span>
									<span class="w-25">${value.weight}</span>
								</div>`
								
							});
							
							// Si tous les produits sont déjà dans des étiquettes alors désactiver le button de génération
							if(product == 0){
								$(".button_validate_modal_label").children('button').last().attr('disabled', true)
							} else {
								$(".button_validate_modal_label").children('button').last().attr('disabled', false)
							}

							$(".body_line_items_label").append(innerHtml)
							$(".generate_label_modal").modal('show')
						} else {
							$(".show_messages").prepend(`
								<div class="alert alert-danger border-0 bg-danger alert-dismissible fade show">
									<div class=" text-white">`+JSON.parse(data).message+`</div>
									<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
								</div>
							`)
						}
					})
			})

			$('body').on('click', '.check_all', function() {
				if($(this).prop('checked')){
					$("#order_"+$(this).attr('data-id')+' .checkbox_label').each(function( index ) {
						if($(this).attr('disabled') != "disabled"){
							$(this).prop('checked', true)
						}
					});
				} else {
					$("#order_"+$(this).attr('data-id')+' .checkbox_label').prop('checked', false)
				}
			})
		</script>
	@endsection


