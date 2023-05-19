@extends("layouts.app")

		@section("style")
			<link href="{{asset('assets/plugins/datatable/css/dataTables.bootstrap5.min.css')}}" rel="stylesheet" />
		@endsecrion 

		@section("wrapper")
			<div class="page-wrapper">
				<div class="page-content">
					<div class="page-breadcrumb d-sm-flex align-items-center mb-2">
						<div class="breadcrumb-title pe-3">Commandes à préparer</div>
						<div class="pe-3 number_order_pending">{{ count($orders) }}</div>

					</div>

					@foreach($orders as $order)

						<div class="courses-container">
							<div class="course">
								<div class="course-preview">
									<h6>Commande</h6>
									<h2>#{{ $order['details']['id'] }}</h2>
								</div>
								<div class="course-info">
									<h6>{{ \Carbon\Carbon::parse($order['details']['date'])->isoFormat(' DD/MM/YY à HH') }}h</h6>
									<h2>{{ $order['details']['first_name']  }} {{ $order['details']['last_name']  }}</h2>
									<button id="{{ $order['details']['id'] }}" class="show_order btn">Préparer</button>
								</div>
							</div>
						</div>

						<!-- MODAL -->
						<div class="modal_order modal fade" id="order_{{ $order['details']['id'] }}" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
							<div class="modal-dialog modal-dialog-centered" role="document">
								<div class="modal-content">
									<div class="modal-body detail_product_order">
										<div class="detail_product_order_head d-flex flex-column">
											<div class="p-1 mb-2 head_detail_product_order d-flex w-100 justify-content-between">
												<span class="name_column">Article</span>
												<span class="name_column">Coût</span>
												<span class="name_column">Qté</span>
												<span class="name_column">Total</span>
											</div>	

											<div class="body_detail_product_order">
												@foreach($order['items'] as $item)
													<div id="barcode_{{ $item['barcode']  ?? 0 }}" class="{{ $item['pick'] == 1 ? 'pick' : '' }} p-2 d-flex w-100 align-items-center justify-content-between detail_product_order_line">
														<div class="d-flex align-items-center detail_product_name_order">
															<span>{{ $item['name'] }}</span>
														</div>
														<span>{{ round(floatval($item['cost']),2) }}</span>
														<span> {{ $item['quantity'] }} </span>
														<span>{{ round(floatval($item['quantity'] * $item['cost']),2) }} </span>
													</div>
												@endforeach
											</div>
											
											<div class="align-items-end flex-column mt-2 d-flex justify-content-end"> 
												<span class="mt-1 mb-2 montant_toltal_order">Total: {{ $order['details']['total'] }}€</span>
												
												<div class="w-100 d-flex justify-content-between">
													<button type="button" class="btn btn-dark px-5" data-bs-dismiss="modal">Retour</button>
													<button type="button" class="btn btn-dark px-5">Valider</button>
												</div>
												
											</div>
										</div>

									</div>
								</div>
							</div>
						</div>
		
					@endforeach
					
				</div>
			</div>
		@endsection

	
	@section("script")

		<script src="{{asset('assets/plugins/datatable/js/jquery.dataTables.min.js')}}"></script>
		<script src="{{asset('assets/plugins/datatable/js/dataTables.bootstrap5.min.js')}}"></script>

		<script>

			$('body').on('click', '.show_order', function() {
				var id = $(this).attr('id')

				$('#order_'+id).modal({
					backdrop: 'static',
					keyboard: false
				})

				$("#order_"+id).modal('show')
			})
		




			$(document).on("scanButtonDown", "document", function(e) {
				alert("1")
				// // get scanned content
				// var scannedProductId = this.getScannedContent();
				
				// // get product 
				// var product = getProductById(scannedProductId);

				// // add productname to list
				// $("#product_list").append("<li>" + product.name + "</li>");
			});

			document.addEventListener("keydown", function(e) {
				const textInput = e.key || String.fromCharCode(e.keyCode);
				const targetName = e.target.localName;
				let newUPC = '';
				if (textInput && textInput.length === 1 && targetName !== 'input'){
					newUPC = UPC+textInput;

					if (newUPC.length >= 6) {
						alert('barcode scanned:  ', newUPC);
					} 
				}
			});
        
        </script>
	@endsection


