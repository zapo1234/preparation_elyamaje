@extends("layouts.app")

		@section("style")
			<link href="{{asset('assets/plugins/datatable/css/dataTables.bootstrap5.min.css')}}" rel="stylesheet" />
		@endsecrion 

		@section("wrapper")
			<div class="page-wrapper">
				<div class="page-content">
					<div class="page-breadcrumb d-sm-flex align-items-center mb-2">
						<div class="breadcrumb-title pe-3">Historique des commandes</div>
					</div>


					@if(count($history) > 0)
						@foreach($history as $histo)
							<div class="courses-container">
								<div class="course">
									<div class="course-preview order_history">
										<h6>Commande</h6>
										<h2>#{{ $histo['details']['id'] }}</h2>
									</div>
									<div class="course-info">
										<h6>{{ \Carbon\Carbon::parse($histo['details']['date'])->isoFormat(' DD/MM/YY à HH') }}h</h6>
										<h2 class="customer_name">{{ $histo['details']['first_name']  }} {{ $histo['details']['last_name']  }}</h2>
										<div class="d-flex">
											<button data-order="{{ $histo['details']['id'] }}" data-product="{{ count($histo['items']) }}" data-customer="{{ $histo['details']['first_name'].' '.$histo['details']['last_name'] }}" id="{{ $histo['details']['id'] }}" class="show_order_history_code btn1"><i class="font-20 bx bx-barcode-reader"></i></button>
											<button id="{{ $histo['details']['id'] }}" class="show_order_history btn2"><i class="font-20 bx bx-detail"></i></button>

											<!-- <button id="{{ $histo['details']['id'] }}" class="show_order_history btn">Détail</button> -->
										</div>
									</div>
								</div>
							</div>
							
							<!-- MODAL -->
							<div class="modal_order modal fade" data-order="{{ $histo['details']['id'] }}" id="order_{{ $histo['details']['id'] }}" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
								<div class="modal-dialog modal-dialog-centered" role="document">
									<div class="modal-content">
										<div class="modal-body detail_product_order">
											<div class="detail_product_order_head d-flex flex-column">
												<div class="p-1 mb-2 head_detail_product_order d-flex w-100 justify-content-between">
													<span class="column1 name_column">Article</span>
													<span class="column2 name_column">Coût</span>
													<span class="column3 name_column">Qté</span>
													<!-- <span class="column4 name_column">Code Barre</span> -->
												</div>	

												<div class="body_detail_product_order">
													@foreach($histo['items'] as $item)
														<div id="barcode_{{ $item['barcode']  ?? 0 }}" class="{{ $item['pick'] == 1 ? 'pick' : '' }} product_order p-2 d-flex w-100 align-items-center justify-content-between detail_product_order_line">
															<div class="column11 d-flex align-items-center detail_product_name_order">
																@if($item['cost'] == 0)
																<span><span class="text-success">(Cadeau) </span>{{ $item['name'] }}</span>
																@else 
																	<span>{{ $item['name'] }}</span>
																@endif
															</div>
															<span class="column22">{{ round(floatval($item['cost']),2) }}</span>
															<span class="column33"> {{ $item['quantity'] }} </span>
															<!-- <span class="column44">{{  $item['barcode'] }} </span> -->
														</div>
													@endforeach
												</div>
												
												<div class="align-items-end flex-column mt-2 d-flex justify-content-end"> 
													<div class="w-100 d-flex justify-content-between">
														<span class="mt-1 mb-2 montant_toltal_order">#{{ $histo['details']['id'] }} </span>
														<span class="mt-1 mb-2 montant_toltal_order">Total: {{ $histo['details']['total'] }}€</span>
													</div>
													<div class="w-100 d-flex justify-content-between">
														<button type="button" class="btn btn-dark px-5" data-bs-dismiss="modal"><i class="d-none responsive-icon lni lni-arrow-left"></i><span class="responsive-text">Retour</button>
													</div>
													
												</div>
											</div>

										</div>
									</div>
								</div>
							</div>


							<!-- MODAL CODE QR -->
							<div class="modal_order modal fade" data-order="{{ $histo['details']['id'] }}" id="code_{{ $histo['details']['id'] }}" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
								<div class="modal-dialog modal-dialog-centered" role="document">
									<div class="modal-content">
										<div class="modal-body detail_product_order">
											<div class="detail_product_order_head d-flex flex-column justify-content-between h-100">
												<div class="mt-5 d-flex w-100 justify-content-center body_qr_code"></div>
												<div class="no-print col justify-content-center d-flex align-items-center flex-column">
													<button style="width:250px" type="button" class="impression_code mt-5 btn btn-dark px-5 radius-30">Imprimer</button>
												</div>

												
												<div class="no-print align-items-end flex-column mt-2 d-flex justify-content-end"> 
													<div class="w-100 d-flex justify-content-between">
														<span class="mt-1 mb-2 montant_toltal_order">#{{ $histo['details']['id'] }} </span>
														<span class="mt-1 mb-2 montant_toltal_order">Total: {{ $histo['details']['total'] }}€</span>
													</div>
													<div class="w-100 d-flex justify-content-between">
														<button type="button" class="btn btn-dark px-5" data-bs-dismiss="modal"><i class="d-none responsive-icon lni lni-arrow-left"></i><span class="responsive-text">Retour</button>
													</div>
													
												</div>
											</div>

										</div>
									</div>
								</div>
							</div>
							
						@endforeach

						
						
					@endif




		
				
					
				</div>
			</div>
		@endsection

	
	@section("script")

		<script src="{{asset('assets/js/qrcode.js')}}"></script>


		<script>

			$('body').on('click', '.show_order_history', function() {
				var id = $(this).attr('id')
				$('#order_'+id).modal({
					backdrop: 'static',
					keyboard: false
				})
				$("#order_"+id).modal('show')
			})


			$('body').on('click', '.show_order_history_code', function() {
				var id = $(this).attr('id')
				$('#code_'+id).modal({
					backdrop: 'static',
					keyboard: false
				})

				var order_id = $(this).attr('data-order')
				var product = $(this).attr('data-product')
				var customer_name = $(this).attr('data-customer')

				const href = order_id+","+product+","+customer_name;
				const size = 300;
				$(".info_order").text("#Commande "+order_id+" - "+product+" Produit(s)")

				$(".body_qr_code").children('canvas').remove()
				$(".body_qr_code").children('img').remove()

				new QRCode(document.querySelector(".body_qr_code"), {
					text: href,
					width: size,
					height: size,

					colorDark: "#000000",
					colorLight: "#ffffff"
				});

				$("#code_"+id).modal('show')
			})

			$(".impression_code").on('click', function(){
				window.print()
				$(".close_modal_validation").removeClass("d-none")
			})

        </script>
	@endsection
