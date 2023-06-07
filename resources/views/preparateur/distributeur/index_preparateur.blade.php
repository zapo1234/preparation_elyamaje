@extends("layouts.app")

		@section("style")
			<link href="{{asset('assets/plugins/datatable/css/dataTables.bootstrap5.min.css')}}" rel="stylesheet" />
		@endsection 

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
									<h2>{{ $orders['details']['first_name']  }} {{ $orders['details']['last_name']  }}</h2>
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
												<span class="name_column">Article</span>
												<span class="name_column">Coût</span>
												<span class="name_column">Qté</span>
												<span class="name_column">Total</span>
											</div>	

											<div class="body_detail_product_order">
												@foreach($orders['items'] as $item)
													<div id="barcode_{{ $item['barcode']  ?? 0 }}" class="{{ $item['pick'] == 1 ? 'pick' : '' }} product_order p-2 d-flex w-100 align-items-center justify-content-between detail_product_order_line">
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
												<span class="mt-1 mb-2 montant_toltal_order">Total: {{ $orders['details']['total'] }}€</span>
												
												<div class="w-100 d-flex justify-content-between">
													<button type="button" class="btn btn-dark px-5" data-bs-dismiss="modal">Retour</button>
													<button type="button" disabled class="validate_pick_in btn btn-dark px-5">Valider</button>
												</div>
												
											</div>
										</div>

									</div>
								</div>
							</div>
						</div>
					@endif
		
				
					
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
								}

								if(localStorage.getItem('barcode')){
									var list_barcode = localStorage.getItem('barcode')
									var new_list_list_barcode = JSON.stringify(list_barcode+','+$("#barcode").val())
									localStorage.setItem('barcode', new_list_list_barcode);
								} else {
									localStorage.setItem('barcode', JSON.stringify($("#barcode").val()));
								}
							}
						}
					}
				}
			});

			$(".validate_pick_in").on('click', function(){
				if($(".pick").length == $(".product_order").length && localStorage.getItem('barcode')){
					$.ajax({
						url: "{{ route('orders.prepared') }}",
						method: 'POST',
						data: {_token: $('input[name=_token]').val(), order_id: $(".show").attr("data-order"), pick_items: JSON.parse(localStorage.getItem('barcode')).split(',')}
					}).done(function(data) {
						if(JSON.parse(data).success){
							localStorage.removeItem("barcode");
							console.log("success !")
						} else {
							alert("Veuillez biper tous les articles de la commande !")
						}
					});
				}
			})
        
        </script>
	@endsection
