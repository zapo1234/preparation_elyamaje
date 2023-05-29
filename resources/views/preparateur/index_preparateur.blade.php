@extends("layouts.app")

		@section("style")
			<link href="{{asset('assets/plugins/datatable/css/dataTables.bootstrap5.min.css')}}" rel="stylesheet" />
		@endsecrion 

		@section("wrapper")
			<div class="page-wrapper">
				<div class="page-content">
					<div class="page-breadcrumb d-sm-flex align-items-center mb-2">
						<div class="breadcrumb-title pe-3">Commandes à préparer</div>
						<div class="pe-3 number_order_pending">{{$number_orders }}</div>
						<input id="barcode" type="hidden" value="">
						@csrf
					</div>


					@if(count($orders) > 0)
						<div class="courses-container">
							<div class="course">
								<div class="course-preview">
									<h6>Commande</h6>
									<h2>#{{ $orders['details']['id'] }}</h2>
								</div>
								<div class="course-info">
									<h6>{{ \Carbon\Carbon::parse($orders['details']['date'])->isoFormat(' DD/MM/YY à HH') }}h</h6>
									<h2 class="customer_name">{{ $orders['details']['first_name']  }} {{ $orders['details']['last_name']  }}</h2>
									<button id="{{ $orders['details']['id'] }}" class="show_order btn">Préparer</button>
								</div>
							</div>
						</div>

						<!-- MODAL -->
						<div class="modal_order modal fade" data-order="{{ $orders['details']['id'] }}" id="order_{{ $orders['details']['id'] }}" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
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
												@foreach($orders['items'] as $item)
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
													<span class="mt-1 mb-2 montant_toltal_order">#{{ $orders['details']['id'] }} </span>
													<span class="mt-1 mb-2 montant_toltal_order">Total: {{ $orders['details']['total'] }}€</span>
												</div>
												<div class="w-100 d-flex justify-content-between">
													<button type="button" class="btn btn-dark px-5" data-bs-dismiss="modal"><i class="d-none responsive-icon lni lni-arrow-left"></i><span class="responsive-text">Retour</button>
													<button type="button" class="reset_order btn btn-dark px-5" ><i class="d-none responsive-icon lni lni-reload"></i><span class="responsive-text">Recommencer la commande</span></button>
													<button type="button" disabled class="validate_pick_in btn btn-dark px-5"><i class="d-none responsive-icon lni lni-checkmark"></i><span class="responsive-text">Valider</button>
												</div>
												
											</div>
										</div>

									</div>
								</div>
							</div>
						</div>
					@endif



					<!-- Modal command epréparée avec succès -->
					<div class="modal_success modal fade" id="modalSuccess" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
						<div class="modal-dialog modal-dialog-centered" role="document">
							<div class="modal-content">
						
								<div class="modal_body_success modal-body d-flex flex-column justify-content-center">
									<div class="no-print d-none loading_prepared_command d-flex flex-column justify-content-center align-items-center">
										<h2 class="mb-5">Validation de la préparation...</h2>
										<div class="spinner-border" role="status"> 
											<span class="visually-hidden">Loading...</span>
										</div>
									</div>

									<div class="d-none success_prepared_command d-flex flex-column justify-content-center align-items-center">
										<h2 class="no-print mb-5">Commande préparée avec succès !</h2>
										<div id="qrcode"></div>
										<span class="info_order"></span>
										<div class="no-print col">
                                            <button type="button" class="impression_code mt-5 btn btn-dark px-5 radius-30">Imprimer</button>
                                        </div>
									</div>

									<div class="no-print d-none error_prepared_command d-flex flex-column justify-content-center align-items-center">
										<h2 class="mb-5">Oops, la comande n'a pas pu être validée</h2>
										<div class="danger">
											<i class="text-danger bx bx-x-circle mr-1 mr-1 font-50"></i>
										</div>
									</div>

									
									<div class="mt-5 no-print d-none close_modal_validation mt-3 w-100 d-flex justify-content-center">
										<button type="button" class="close_modal_order btn btn-dark px-5">Fermer</button>
									</div>

								</div>
							</div>
						</div>
					</div>



					<div class="modal_reset_order modal fade" id="modalReset" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
						<div class="modal-dialog modal-dialog-centered" role="document">
							<div class="modal-content">
						
								<div class="modal_body_reset modal-body d-flex flex-column justify-content-center">
									<h2 class="text-center">Recommencer la commande ?</h2>
									<div class="w-100 d-flex justify-content-center">
										<button type="button" class="btn btn-dark px-5 confirmation_reset_order ">Oui</button>
										<button style="margin-left:15px" type="button" class="btn btn-dark px-5" data-bs-dismiss="modal">Non</button>
									</div>
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
		<script src="{{asset('assets/js/qrcode.js')}}"></script>


		<script>

			$('body').on('click', '.show_order', function() {
				var id = $(this).attr('id')
				$('#order_'+id).modal({
					backdrop: 'static',
					keyboard: false
				})
				$("#order_"+id).modal('show')
			})

			$(document).ready(function() {

				if(localStorage.getItem('barcode')){
					var list_barcode = JSON.parse(localStorage.getItem('barcode')).split(',')
					if(!Array.isArray(list_barcode)){
						$("#barcode_"+list_barcode).addClass('pick')
					} else {
						list_barcode.forEach(function(item){
							$("#barcode_"+item).addClass('pick')
						});
					}
				} 

				if($(".pick").length == $(".product_order").length){
					$(".validate_pick_in").attr('disabled', false)
					$(".validate_pick_in").css('background-color', '#16e15e')
					$(".validate_pick_in").css('border', 'none')
				}
			})

			document.addEventListener("keydown", function(e) {
				if($(".modal_order ").hasClass('show')){
					if (!isNaN(parseInt(e.key))) {
						$("#barcode").val($("#barcode").val()+e.key)
						if($("#barcode").val().length == 13){
							if($("#barcode_"+$("#barcode").val()).length > 0){
							
								$("#barcode_"+$("#barcode").val()).addClass('pick')
								if($(".pick").length == $(".product_order").length){
									$(".validate_pick_in").attr('disabled', false)
									$(".validate_pick_in").css('background-color', '#16e15e')
									$(".validate_pick_in").css('border', 'none')
								}

								if(localStorage.getItem('barcode')){
									var list_barcode = localStorage.getItem('barcode')
									var new_list_list_barcode = JSON.stringify(list_barcode+','+$("#barcode").val())
									localStorage.setItem('barcode', new_list_list_barcode);
								} else {
									localStorage.setItem('barcode', JSON.stringify($("#barcode").val()));
								}
								$("#barcode").val("")
							} else {
								$("#barcode").val("")
							}
						}
					}
				}
			});

			$(".validate_pick_in").on('click', function(){
				if($(".pick").length == $(".product_order").length && localStorage.getItem('barcode')){
					
					// Ouvre la modal de loading
					$(".loading_prepared_command").removeClass('d-none')
					$("#modalSuccess").modal('show')
					
					var order_id = $(".show").attr("data-order")
					var pick_items = JSON.parse(localStorage.getItem('barcode')).split(',')

					$.ajax({
						url: "{{ route('orders.prepared') }}",
						method: 'POST',
						data: {_token: $('input[name=_token]').val(), order_id: order_id, pick_items: pick_items}
					}).done(function(data) {
						$(".loading_prepared_command").addClass('d-none')

						if(JSON.parse(data).success){
							$(".success_prepared_command").removeClass('d-none')
							const href =order_id+","+pick_items.length+","+$(".customer_name").text();
							const size = 300;
							$(".info_order").text("#Commande "+order_id+" - "+pick_items.length+" Produit(s)")

							new QRCode(document.querySelector("#qrcode"), {
								text: href,
								width: size,
								height: size,

								colorDark: "#000000",
								colorLight: "#ffffff"
							});
							localStorage.removeItem("barcode");

						} else {
							$(".error_prepared_command").removeClass('d-none')
						}
					});
				}
			})

			$(".reset_order").on('click', function(){
				$("#modalReset").modal("show")
			})

			$(".confirmation_reset_order").on('click', function(){
				$(".product_order").removeClass("pick")
				$("#barcode").val("")
				localStorage.removeItem("barcode");
				$(".validate_pick_in").attr('disabled', true)

				$.ajax({
						url: "{{ route('orders.reset') }}",
						method: 'POST',
						data: {_token: $('input[name=_token]').val(), order_id: $(".show").attr("data-order")}
				}).done(function(data) {
					if(JSON.parse(data).success){
						location.reload()
					} else {
						alert("Erreur !")
					}
				});
			})

			$(".close_modal_order").on('click', function(){
				if(!$(".error_prepared_command").hasClass("d-none")){
					$("#modalSuccess").modal('hide')
					$(".success_prepared_command").addClass("d-none")
					$(".error_prepared_command").addClass("d-none")
					$(".loading_prepared_command").addClass("d-none")
				} else {
					location.reload()
				}
			})

			$(".impression_code").on('click', function(){
				window.print()
				$(".close_modal_validation").removeClass("d-none")
			})
        
        </script>
	@endsection
