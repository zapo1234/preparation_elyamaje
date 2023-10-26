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
									<li class="breadcrumb-item active" aria-current="page">Commandes</li>
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
																<span class="badge bg-dark">{{ $histo['prepared'] }}</span>
																@if($histo['prepared'])
																	<span class="date_prepared">le {{ $histo['prepared_date'] }}</span>  
																@endif
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
														@if($histo['order_status'])
															<select data-order="{{ $histo['order_id'] }}" class="{{ $histo['order_status'] }} select_status select_user">
																@foreach($list_status as $key => $list)
																	@if($key == $histo['order_status'])
																		<option selected value="{{ $histo['order_status'] }}">{{ __('status.'.$histo['order_status']) }}</option>
																	@else 
																		<option value="{{ $key }}">{{ __('status.'.$key) }}</option>
																	@endif
																@endforeach
															</select>
														@endif
													</td>
													<td data-label="Détails">
														<button class="show_detail_button show_detail" onclick="show({{ $histo['order_id'] }})">
															<i class="font-primary font-20 bx bx-cube"></i>
														</button>	
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
					"ordering": false,
					"initComplete": function( settings, json ) {
						$(".loading").addClass('d-none')
						$('#example').removeClass('d-none');
					}
				})

				$('body').on('change', '.select_status', function () {
					var order_id = $(this).attr('data-order')
					var status = $(this).val()

					$(this).removeClass()
					$(this).addClass($(this).val())
					$(this).addClass("select_status")
					$(this).addClass("select_user")

					// Change status order
					$.ajax({
						url: "updateOrderStatus",
						method: 'GET',
						method: 'POST',
        				data: {_token: $('input[name=_token]').val(), order_id: order_id, status: status, from_dolibarr: false}
					}).done(function(data) {
						if(JSON.parse(data).success){
							// Remove order from commandeId and update dolibarr id command
							if(status == "processing"){
								$.ajax({
									url: "orderReInvoicing",
									method: 'GET',
									method: 'POST',
									data: {_token: $('input[name=_token]').val(), order_id: order_id}
								}).done(function(data) {
									if(JSON.parse(data).success){
									} else {
										alert(JSON.parse(data).message)
									}
								});
							}
						} else {
							alert('Erreur !')
						}
					});

				})
			})

			// Show detail product order
			function show(id){

				$("#example").css('opacity', '0.3')
				$(".loading_show_detail_order ").removeClass('d-none')

				$(".show_detail").attr('disabled', true)
				$.ajax({
					url: "getProductsOrder",
					method: 'GET',
					data: {order_id: id}
				}).done(function(data) {
					$("#example").css('opacity', '1')
					$(".loading_show_detail_order ").addClass('d-none')

					if(JSON.parse(data).success){
					
						var order = JSON.parse(data).order
						console.log(order)
						if(order.length > 0){
							// Dolibarr et Woocommerce
							if(!order[0].transfers){
								var total = parseFloat(order[0].total_order)
								var discount_total = !order[0].from_dolibarr ? parseFloat(order[0].discount) : 0
								var gift_card = !order[0].from_dolibarr ? (order[0].gift_card_amount > 0 ? parseFloat(order[0].gift_card_amount): 0) : 0
								var total_tax = !order[0].from_dolibarr ? parseFloat(order[0].total_tax_order) : parseFloat(order[0].total_tax)
								var sub_total = parseFloat(total) + parseFloat(discount_total) + parseFloat(gift_card) - parseFloat(total_tax) - (!order[0].from_dolibarr ? parseFloat(order[0].shipping_amount) : 0)
							}
							

							$(".modal_order_admin").remove()
							$('body').append(`<div class="modal_order_admin modal_order modal fade" id="order_`+order[0].order_woocommerce_id+`" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
								<div class="modal-dialog modal-dialog-centered" role="document">
									<div class="modal-content">
										<div class="modal-body detail_product_order">
											<div class="detail_product_order_head d-flex flex-column">
												<div class="p-1 mb-2 head_detail_product_order d-flex w-100 justify-content-between">
													<span class="column1 name_column">Article</span>
													<span class="column2 name_column">Coût</span>
													<span class="column3 name_column">Pick / Qté</span>
													<span class="column4 name_column">Total</span>
												</div>	

												<div class="body_detail_product_order">
													${order.map((element) => `
														<div class="${element.product_id}  ${element.variation_id} ${order[0].id}_${element.id} ${id[element.product_id] ? (id[element.product_id] == element.quantity ? 'pick' : '') : ''} ${id[element.variation_id] ? (id[element.variation_id] == element.quantity ? 'pick' : '') : ''} d-flex w-100 align-items-center justify-content-between detail_product_order_line">
															<div class="column11 d-flex align-items-center detail_product_name_order">
																${element.price == 0 ? `<span><span class="text-success">(Cadeau)</span> `+element.name+`</span>` : `<span>`+element.name+`</span>`}
															</div>
															${!order[0].transfers ? '<span class="column22">'+parseFloat(element.cost).toFixed(2)+'</span>' : '<span class="column22">'+parseFloat(element.price_ttc).toFixed(2)+'</span>'}
															<span class="column33 quantity">${id[element.product_id] ? id[element.product_id] : (id[element.variation_id] ? id[element.variation_id] : 0)} / ${element.quantity}</span>
															${!order[0].transfers ? '<span class="column44">'+parseFloat(element.price * element.quantity).toFixed(2)+'</span>' : '<span class="column44">'+parseFloat(element.price_ttc * element.quantity).toFixed(2)+'</span>'}
														</div>`
												).join('')}
												</div>
												<div class="align-items-end mt-2 d-flex justify-content-between footer_detail_order"> 
													<div class="d-flex flex-column justify-content-between">
														<div class="d-flex flex-column align-items-center justify-content-end">
															${order[0].coupons ? `<span class="order_customer_coupon mb-2 badge bg-success">`+order[0].coupons+`</span>` : ``}
														</div>
													</div>

													${!order[0].transfers ?
														`<div class="d-flex flex-column list_amount">
															<span class="montant_total_order">Sous-total des articles:<strong class="total_ht_order">`+parseFloat(sub_total).toFixed(2)+`€</strong></span> 
															${order[0].coupons && order[0].coupons_amount > 0 ? `<span class="text-success">Code(s) promo: <strong>`+order[0].coupons+` (-`+order[0].coupons_amount+`€)</strong></span>` : ``}
															${!order[0].from_dolibarr ? '<span class="montant_total_order">Expédition:<strong>'+order[0].shipping_amount+'€</strong></span>' : ''}
															<span class="montant_total_order">TVA: <strong class="total_tax_order">`+total_tax+`€</strong></span>
															${gift_card > 0 ? `<span class="text-success">PW Gift Card: <strong>`+gift_card+`€)</strong></span>` : ``}
															<span class="mt-1 mb-2 montant_total_order">Payé: <strong class="total_paid_order">`+total+`€</strong></span>
															<div class="d-flex justify-content-end">
																<button style="width:-min-content" type="button" class="close_show_order btn btn-dark px-5" data-bs-dismiss="modal">Fermer</button>
															</div>
														</div>` : 
														`<div class="d-flex flex-column list_amount">
															<div class="d-flex justify-content-end">
																<button style="width:-min-content" type="button" class="close_show_order btn btn-dark px-5" data-bs-dismiss="modal">Fermer</button>
															</div>
														</div>`
													}
												</div>
											</div>

										</div>
									</div>
								</div>
							</div>`)
							$('#order_'+id).modal({
								backdrop: 'static',
								keyboard: false
							})

							$("#order_"+id).appendTo("body").modal('show')
						} else {
							$(".show_detail").attr('disabled', false)
							alert('Aucune information pour cette commande !')
						}
					} else {
						$(".show_detail").attr('disabled', false)
						alert('Aucune information pour cette commande !')
					}
				})	
			}
			
			$('body').on('click', '.close_show_order', function () {
				$(".show_detail").attr('disabled', false)
			})

        </script>
	@endsection

