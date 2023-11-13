@extends("layouts.app")

@section("style")
<link href="{{asset('assets/plugins/datatable/css/dataTables.bootstrap5.min.css')}}" rel="stylesheet" />
@endsection

@section("wrapper")
<div class="page-wrapper page-preparateur-order">
	<div class="page-content">
		<div class="page-breadcrumb d-sm-flex align-items-center mb-2">
			<div class="breadcrumb-title pe-3">Préparation</div>
			<div class="ps-3">Internet</div>
			<input id="userinfo" type="hidden" value="{{ $user }}">
			<input id="barcode" type="hidden" value="">
			<input id="barcode_verif" type="hidden" value="">
			<input id="order_in_progress" type="hidden" value="">
			@csrf
		</div>


		<div class="switcher-wrapper">
			<div class="switcher-btn"> <i class="bx bx-help-circle"></i></div>
			<div class="switcher-body">
				<div class="d-flex align-items-center">
					<h5 class="mb-0 text-uppercase">Informations</h5>
					<button type="button" class="btn-close ms-auto close-switcher" aria-label="Close"></button>
				</div>
				<hr>
				<div class="d-flex align-items-center justify-content-between">
					Ici apparrait les commandes qui vous sont attribués, cliquez sur le bouton "Préparer" afin de commencer la préparation.
				</div>
				<hr>
				<h6 class="font-bold mb-0">Commandes à préparer</h6>
				<hr>
				<div class="d-flex align-items-center justify-content-between">
					Ce sont les commandes en cours à préparer.
				</div>
				<hr>
				<h6 class="font-bold mb-0">Commandes en attente de validation</h6>
				<hr>
				<div class="d-flex align-items-center justify-content-between">
					Ce sont les commandes que vous avez validées partiellement par causes de produits manquants ou autres et qui nécéssitent une intervention du chef d'équipe.
					(Il sera prévenu automatiquement).
				</div>
				<hr>
				<h6 class="font-bold mb-0">Commands en attente validée</h6>
				<hr>
				<div class="d-flex align-items-center justify-content-between">
					Ce sont les commandes que vous avez validées partiellement et qui ont été validées par le chef d'équipe et dont vous pouvez reprendre la préparation.
				</div>
			</div>
		</div>



		<div class="card">
			<div class="card-body">
				<ul class="nav nav-tabs nav_mobile_responsive nav-primary" role="tablist">
					<li class="nav-item" role="presentation">
						<a class="nav-link active" data-bs-toggle="tab" href="#primaryhome" role="tab" aria-selected="true">
							<div class="d-flex align-items-center">
								<div class="tab-icon"><i class="bx bx-sync font-20 me-1"></i>
								</div>
								<div class="nav_div_mobile_responsive align-items-center d-flex tab-title">
									<span>A préparer</span>
									<div class="pe-3 number_order_pending">{{ $number_orders }}</div>
								</div>
							</div>
						</a>
					</li>
					<li class="nav-item" role="presentation">
						<a class="nav-link" data-bs-toggle="tab" href="#primaryprofile" role="tab" aria-selected="false">
							<div class="d-flex align-items-center">
								<div class="tab-icon"><i class="bx bx-hourglass font-18 me-1"></i>
								</div>
								<div class="nav_div_mobile_responsive align-items-center d-flex tab-title">
									<span>En attente de validation</span>
									<div class="pe-3 waiting number_order_pending">{{ $number_orders_waiting_to_validate }}</div>
								</div>
							</div>
						</a>
					</li>
					<li class="nav-item" role="presentation">
						<a class="nav-link" data-bs-toggle="tab" href="#primarycontact" role="tab" aria-selected="false">
							<div class="d-flex align-items-center">
								<div class="tab-icon"><i class="bx bx-hourglass font-18 me-1"></i>
								</div>
								<div class="nav_div_mobile_responsive align-items-center d-flex tab-title">
									<span>En attente validée</span>
									<div class="pe-3 waiting number_order_pending">{{ $number_orders_validate }}</div>
								</div>
							</div>
						</a>
					</li>
				</ul>
				<div class="tab-content py-3">
					<div class="tab-pane fade active show" id="primaryhome" role="tabpanel">
						@if(count($orders) > 0)
						<div class="courses-container mb-4">
							<div class="course">
								<div class="course-preview">
									<h6>Commande</h6>
									<h2 class="text-center">#{{ $orders['details']['id'] }}</h2>
								</div>
								<div class="w-100 d-flex flex-column justify-content-between">
									<div class="course-info d-flex justify-content-between align-items-center">
										<div class="{{ $orders['details']['customer_note'] ? 'customer_note_mobile' : '' }} ">
											<h6>{{ \Carbon\Carbon::parse($orders['details']['date'])->isoFormat(' DD/MM/YY à HH:mm') }}</h6>
											<h2 class="customer_name_{{ $orders['details']['id'] }}">{{ $orders['details']['first_name']  }} {{ $orders['details']['last_name']  }}</h2>

											@if($orders['details']['customer_note'])
												<span class="customer_note_preparateur"><span>Note :</span> {{ $orders['details']['customer_note'] }} </h2>
											@endif

										</div>
										@if(str_contains($orders['details']['shipping_method'], 'chrono'))
											<div class="chronopost_shipping_method_preparateur"></div>
										@endif
										<button id="{{ $orders['details']['id'] }}" class="show_order btn">Préparer</button>
									</div>
									<div class="progress" id="progress_{{ $orders['details']['id'] }}">
										<div class="progress-bar" role="progressbar" style="width: 0%" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
									</div>
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
												<span class="column3 name_column">Pick / Qté</span>
												<span class="column4 name_column">Allée</span>
												<!-- <span class="column4 name_column">Code Barre</span> -->
											</div>

											<div class="body_detail_product_order">
												@foreach($orders['items'] as $item)
												<div class="barcode_{{ $item['barcode'] ?? 0 }} {{ $item['pick'] == $item['quantity'] ? 'pick' : '' }} product_order p-2 d-flex w-100 align-items-center justify-content-between detail_product_order_line">
													<div class="column11 d-flex align-items-center detail_product_name_order flex-column">
														@if($item['cost'] == 0)
														<span><span class="text-success">(Cadeau) </span>{{ $item['name'] }}</span>
														@else
															@if($item['name'])
																<span>{{ $item['name'] }}</span>
															@else
																<span class="text-danger">Produit manquant ({{ $item['productID'] }})</span>
															@endif
														@endif
														<div class="mt-1 d-flex flex-column align-items-start">
															<span style="font-size:13px">{{ $item['barcode'] ?? '' }}</span>
															<div class="d-flex">
																<span onclick="enter_manually_barcode({{ $item['product_woocommerce_id']}} , '{{ $orders['details']['id'] }}')" class="manually_barcode"><i class="lni lni-keyboard"></i></span>
																<span class="remove_{{ $item['barcode'] }}_{{ $orders['details']['id'] }} remove_product" onclick="remove_product({{ $item['barcode']}} , '{{ $orders['details']['id'] }}')"><i class="lni lni-spinner-arrow"></i></span>
															</div>
														</div>
													</div>
													<span class="column22">{{ round(floatval($item['cost']),2) }}</span>
													<span class="quantity column33"><span class="quantity_pick_in">{{ $item['pick'] }}</span> / <span class="quantity_to_pick_in">{{ $item['quantity'] }}</span> </span>
													<span class="column44">{{ $item['location'] }}</span>
												</div>
												@endforeach
											</div>

											<div class="align-items-end flex-column mt-2 d-flex justify-content-end">
												<div class="w-100 d-flex align-items-end justify-content-between flex-wrap">
													<span class="mt-1 mb-2 montant_total_order">
														@if($orders['details']['coupons'])
														@foreach(explode(',', $orders['details']['coupons']) as $key => $coupon)
														<div><span class="font-18 badge bg-success">Code : {{ $coupon }}</span></div>
														@endforeach
														@endif
														#{{ $orders['details']['id'] }}
													</span>

													<div class="mt-1 mb-2 montant_total_order detail_amount">
														<div>
															</span>Sous-total des articles : <strong> {{ $orders['details']['total'] + $orders['details']['discount'] + $orders['details']['gift_card_amount'] - $orders['details']['total_tax'] - $orders['details']['shipping_amount'] }} {{ config('app.currency_symbol') }}</strong>
														</div>
														<div class="text-success">
															@if($orders['details']['discount'] > 0)
															</span>Code promo: <strong class="discount"> {{ $orders['details']['coupons'] ?? '' }} (-{{ $orders['details']['discount'] }} {{ config('app.currency_symbol') }})</strong>
															@endif
														</div>
														<div>
															<span class="detail_footer_order">Expédition: </span><strong>{{ $orders['details']['shipping_amount'] }} {{ config('app.currency_symbol') }}</strong>
														</div>
														<div>
															<span class="detail_footer_order">TVA: </span><strong>{{ $orders['details']['total_tax'] }} {{ config('app.currency_symbol') }}</strong>
														</div>
														<div class="text-success">
															@if($orders['details']['gift_card_amount'] > 0)
															</span>Gift Card : <strong class="discount"> ({{-$orders['details']['gift_card_amount'] }}{{ config('app.currency_symbol') }})</strong>
															@endif
														</div>
														<div>
															<span class="detail_footer_order">Payé: </span><strong>{{ $orders['details']['total'] }} {{ config('app.currency_symbol') }}</strong>
														</div>
													</div>
												</div>
												<div class="w-100 d-flex justify-content-between">
													<button type="button" class="btn btn-dark px-5" data-bs-dismiss="modal"><i class="d-none responsive-icon lni lni-arrow-left"></i><span class="responsive-text">Retour</button>
													<button type="button" class="reset_order btn btn-dark px-5"><i class="d-none responsive-icon lni lni-reload"></i><span class="responsive-text">Recommencer la commande</span></button>
													<button from_dolibarr="{{ isset($orders['details']['from_dolibarr']) ? true : false }}" type="button" class="validate_pick_in btn btn-dark px-5"><i class="d-none responsive-icon lni lni-checkmark"></i><span class="responsive-text">Valider</button>
												</div>

											</div>
										</div>

									</div>
								</div>
							</div>
						</div>
						@endif

					</div>
					<div class="tab-pane fade" id="primaryprofile" role="tabpanel">
						@if(count($orders_waiting_to_validate) > 0)
						@foreach($orders_waiting_to_validate as $order)


						<div class="courses-container order_waiting mb-4">
							<div class="course">
								<div class="course-preview">
									<h6>Commande</h6>
									<h2 class="text-center">#{{ $order['details']['id'] }}</h2>
								</div>
								<div class="w-100 d-flex flex-column justify-content-between">
									<div class="course-info d-flex justify-content-between align-items-center">
										<div class="{{ $order['details']['customer_note'] ? 'customer_note_mobile' : '' }} ">
											<h6>{{ \Carbon\Carbon::parse($order['details']['date'])->isoFormat(' DD/MM/YY à HH:mm') }}</h6>
											<h2 class="customer_name_{{ $order['details']['id'] }}">{{ $order['details']['first_name']  }} {{ $order['details']['last_name']  }}</h2>

											@if($order['details']['customer_note'])
												<span class="customer_note_preparateur"><span>Note :</span> {{ $order['details']['customer_note'] }} </h2>
											@endif
										</div>

										@if(str_contains($order['details']['shipping_method'], 'chrono'))
											<div class="chronopost_shipping_method_preparateur"></div>
										@endif
										<button id="{{ $order['details']['id'] }}" class="show_order btn">Reprendre</button>
									</div>
									<div class="progress" id="progress_{{ $order['details']['id'] }}">
										<div class="progress-bar" role="progressbar" style="width: 0%" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
									</div>
								</div>
								
							</div>
						</div>

						<!-- MODAL -->
						<div class="modal_order modal fade" data-order="{{ $order['details']['id'] }}" id="order_{{ $order['details']['id'] }}" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
							<div class="modal-dialog modal-dialog-centered" role="document">
								<div class="modal-content">
									<div class="modal-body detail_product_order">
										<div class="detail_product_order_head d-flex flex-column">
											<div class="p-1 mb-2 head_detail_product_order d-flex w-100 justify-content-between">
												<span class="column1 name_column">Article</span>
												<span class="column2 name_column">Coût</span>
												<span class="column3 name_column">Pick / Qté</span>
												<span class="column4 name_column">Allée</span>
												<!-- <span class="column4 name_column">Code Barre</span> -->
											</div>

											<div class="body_detail_product_order">
												@foreach($order['items'] as $item)
												<div class="barcode_{{ $item['barcode']  ?? 0 }} {{ $item['pick'] == $item['quantity'] ? 'pick' : '' }} product_order p-2 d-flex w-100 align-items-center justify-content-between detail_product_order_line">
													<div class="column11 d-flex align-items-center detail_product_name_order flex-column">
														@if($item['cost'] == 0)
														<span><span class="text-success">(Cadeau) </span>{{ $item['name'] }}</span>
														@else
															@if($item['name'])
																<span>{{ $item['name'] }}</span>
															@else
																<span class="text-danger">Produit manquant</span>
															@endif
														@endif
														<div class="mt-1 d-flex flex-column align-items-start">
															<span style="font-size:13px">{{ $item['barcode'] ?? '' }}</span>
															<div class="d-flex">
																<span onclick="enter_manually_barcode({{ $item['product_woocommerce_id']}} , '{{ $order['details']['id'] }}')" class="manually_barcode"><i class="lni lni-keyboard"></i></span>
																<span class="remove_{{ $item['barcode'] }}_{{ $order['details']['id'] }} remove_product" onclick="remove_product({{ $item['barcode']}} , '{{ $order['details']['id'] }}')"><i class="lni lni-spinner-arrow"></i></span>
															</div>
														</div>
													</div>
													<span class="column22">{{ round(floatval($item['cost']),2) }}</span>
													<span class="quantity column33"><span class="quantity_pick_in">{{ $item['pick'] }}</span> / <span class="quantity_to_pick_in">{{ $item['quantity'] }}</span> </span>
													<span class="column44">{{ $item['location'] }}</span>
												</div>
												@endforeach
											</div>

											<div class="align-items-end flex-column mt-2 d-flex justify-content-end">
												<div class="w-100 d-flex align-items-end justify-content-between flex-wrap">
													<span class="mt-1 mb-2 montant_total_order">
														@if($order['details']['coupons'])
														@foreach(explode(',', $order['details']['coupons']) as $key => $coupon)
														<div><span class="font-18 badge bg-success">Code : {{ $coupon }}</span></div>
														@endforeach
														@endif

														#{{ $order['details']['id'] }}
													</span>

													<div class="mt-1 mb-2 montant_total_order detail_amount">
														<div>
															</span>Sous-total des articles : <strong> {{ $order['details']['total'] + $order['details']['discount'] + $order['details']['gift_card_amount'] - $order['details']['total_tax'] - $order['details']['shipping_amount'] }} {{ config('app.currency_symbol') }}</strong>
														</div>
														<div class="text-success">
															@if($order['details']['discount'] > 0)
															</span>Code promo: <strong class="discount"> {{ $order['details']['coupons'] ?? '' }} (-{{ $order['details']['discount'] }} {{ config('app.currency_symbol') }})</strong>
															@endif
														</div>
														<div>
															<span class="detail_footer_order">Expédition: </span><strong>{{ $order['details']['shipping_amount'] }} {{ config('app.currency_symbol') }}</strong>
														</div>
														<div>
															<span class="detail_footer_order">TVA: </span><strong>{{ $order['details']['total_tax'] }} {{ config('app.currency_symbol') }}</strong>
														</div>
														<div class="text-success">
															@if($order['details']['gift_card_amount'] > 0)
															</span>Gift Card : <strong class="discount"> ({{-$order['details']['gift_card_amount'] }}{{ config('app.currency_symbol') }})</strong>
															@endif
														</div>
														<div>
															<span class="detail_footer_order">Payé: </span><strong>{{ $order['details']['total'] }} {{ config('app.currency_symbol') }}</strong>
														</div>
													</div>
												</div>
												<div class="w-100 d-flex justify-content-between">
													<button type="button" class="btn btn-dark px-5" data-bs-dismiss="modal"><i class="d-none responsive-icon lni lni-arrow-left"></i><span class="responsive-text">Retour</button>
													<button type="button" class="reset_order btn btn-dark px-5"><i class="d-none responsive-icon lni lni-reload"></i><span class="responsive-text">Recommencer la commande</span></button>
													<button from_dolibarr="{{ isset($order['details']['from_dolibarr']) ? true : false }}" type="button" class="validate_pick_in btn btn-dark px-5"><i class="d-none responsive-icon lni lni-checkmark"></i><span class="responsive-text">Valider</button>
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
					<div class="tab-pane fade" id="primarycontact" role="tabpanel">
						@if(count($orders_validate) > 0)
						@foreach($orders_validate as $order)
						<div class="courses-container order_waiting mb-4">
							<div class="course">
								<div class="course-preview">
									<h6>Commande</h6>
									<h2 class="text-center">#{{ $order['details']['id'] }}</h2>
								</div>
								<div class="w-100 d-flex flex-column justify-content-between">
									<div class="{{ str_contains($order['details']['shipping_method'], 'chrono') ? 'chronopost_shipping_method' : '' }} course-info d-flex justify-content-between align-items-center">
										<div class="{{ $order['details']['customer_note'] ? 'customer_note_mobile' : '' }} ">
											<h6>{{ \Carbon\Carbon::parse($order['details']['date'])->isoFormat(' DD/MM/YY à HH:mm') }}</h6>
											<h2 class="customer_name_{{ $order['details']['id'] }}">{{ $order['details']['first_name']  }} {{ $order['details']['last_name']  }}</h2>
										
											@if($order['details']['customer_note'])
												<span class="customer_note_preparateur"><span>Note :</span> {{ $order['details']['customer_note'] }} </h2>
											@endif
										</div>

										@if(str_contains($order['details']['shipping_method'], 'chrono'))
											<div class="chronopost_shipping_method_preparateur"></div>
										@endif
										<button id="{{ $order['details']['id'] }}" class="show_order btn">Reprendre</button>
									</div>
									<div class="progress" id="progress_{{ $order['details']['id'] }}">
										<div class="progress-bar" role="progressbar" style="width: 0%" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
									</div>
								</div>
							</div>
						</div>

						<!-- MODAL -->
						<div class="modal_order modal fade" data-order="{{ $order['details']['id'] }}" id="order_{{ $order['details']['id'] }}" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
							<div class="modal-dialog modal-dialog-centered" role="document">
								<div class="modal-content">
									<div class="modal-body detail_product_order">
										<div class="detail_product_order_head d-flex flex-column">
											<div class="p-1 mb-2 head_detail_product_order d-flex w-100 justify-content-between">
												<span class="column1 name_column">Article</span>
												<span class="column2 name_column">Coût</span>
												<span class="column3 name_column">Pick / Qté</span>
												<span class="column4 name_column">Allée</span>
												<!-- <span class="column4 name_column">Code Barre</span> -->
											</div>

											<div class="body_detail_product_order">
												@foreach($order['items'] as $item)
												<div class="barcode_{{ $item['barcode']  ?? 0 }} {{ $item['pick'] == $item['quantity'] ? 'pick' : '' }} product_order p-2 d-flex w-100 align-items-center justify-content-between detail_product_order_line">
													<div class="column11 d-flex align-items-center detail_product_name_order flex-column">
														@if($item['cost'] == 0)
														<span><span class="text-success">(Cadeau) </span>{{ $item['name'] }}</span>
														<br>
														<span>{{ $item['barcode'] }}</span>
														@else
															@if($item['name'])
																<span>{{ $item['name'] }}</span>
															@else
																<span class="text-danger">Produit manquant</span>
															@endif
														@endif
														<div class="mt-1 d-flex flex-column align-items-start">
															<span style="font-size:13px">{{ $item['barcode'] ?? '' }}</span>
															<div class="d-flex">
																<span onclick="enter_manually_barcode({{ $item['product_woocommerce_id']}} , '{{ $order['details']['id'] }}')" class="manually_barcode"><i class="lni lni-keyboard"></i></span>
																<span class="remove_{{ $item['barcode'] }}_{{ $order['details']['id'] }} remove_product" onclick="remove_product({{ $item['barcode']}} , '{{ $order['details']['id'] }}')"><i class="lni lni-spinner-arrow"></i></span>
															</div>
														</div>
													</div>
													<span class="column22">{{ round(floatval($item['cost']),2) }}</span>
													<span class="quantity column33"><span class="quantity_pick_in">{{ $item['pick'] }}</span> / <span class="quantity_to_pick_in">{{ $item['quantity'] }}</span> </span>
													<span class="column44">{{ $item['location'] }}</span>
												</div>
												@endforeach
											</div>

											<div class="align-items-end flex-column mt-2 d-flex justify-content-end">
												<div class="w-100 d-flex align-items-end justify-content-between flex-wrap">
													<span class="mt-1 mb-2 montant_total_order">
														@if($order['details']['coupons'])
														@foreach(explode(',', $order['details']['coupons']) as $key => $coupon)
														<div><span class="font-18 badge bg-success">Code : {{ $coupon }}</span></div>
														@endforeach
														@endif
														#{{ $order['details']['id'] }}
													</span>

													<div class="mt-1 mb-2 montant_total_order detail_amount">
														<div>
															</span>Sous-total des articles : <strong> {{ $order['details']['total'] + $order['details']['discount'] + $order['details']['gift_card_amount'] - $order['details']['total_tax'] - $order['details']['shipping_amount'] }} {{ config('app.currency_symbol') }}</strong>
														</div>
														<div class="text-success">
															@if($order['details']['discount'] > 0)
															</span>Code promo: <strong class="discount"> {{ $order['details']['coupons'] ?? '' }} (-{{ $order['details']['discount'] }} {{ config('app.currency_symbol') }})</strong>
															@endif
														</div>
														<div>
															<span class="detail_footer_order">Expédition: </span><strong>{{ $order['details']['shipping_amount'] }} {{ config('app.currency_symbol') }}</strong>
														</div>
														<div>
															<span class="detail_footer_order">TVA: </span><strong>{{ $order['details']['total_tax'] }} {{ config('app.currency_symbol') }}</strong>
														</div>
														<div class="text-success">
															@if($order['details']['gift_card_amount'] > 0)
															</span>Gift Card : <strong class="discount"> ({{-$order['details']['gift_card_amount'] }}{{ config('app.currency_symbol') }})</strong>
															@endif
														</div>
														<div>
															<span class="detail_footer_order">Payé: </span><strong>{{ $order['details']['total'] }} {{ config('app.currency_symbol') }}</strong>
														</div>
													</div>
												</div>
												<div class="w-100 d-flex justify-content-between">
													<button type="button" class="btn btn-dark px-5" data-bs-dismiss="modal"><i class="d-none responsive-icon lni lni-arrow-left"></i><span class="responsive-text">Retour</button>
													<button type="button" class="reset_order btn btn-dark px-5"><i class="d-none responsive-icon lni lni-reload"></i><span class="responsive-text">Recommencer la commande</span></button>
													<button from_dolibarr="{{ isset($order['details']['from_dolibarr']) ? true : false }}" type="button" class="validate_pick_in btn btn-dark px-5"><i class="d-none responsive-icon lni lni-checkmark"></i><span class="responsive-text">Valider</button>
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
			</div>
		</div>



		<!-- Modal commande préparée avec succès -->
		<div class="modal_success modal fade" id="modalSuccess" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
			<div class="modal-dialog modal-dialog-centered" role="document">
				<div class="modal-content">

					<div class="modal_body_success modal-body d-flex flex-column">
						<div class="no-print d-none loading_prepared_command d-flex flex-column align-items-center">
							<h2 class="mb-5">Validation de la préparation...</h2>
							<div class="spinner-border" role="status">
								<span class="visually-hidden">Loading...</span>
							</div>
						</div>

						<div class="d-none success_prepared_command d-flex flex-column align-items-center">
							<h2 class="no-print mb-5 d-flex ">Commande préparée avec succès !</h2>
							<div class="d-flex" id="qrcode"></div>

							<span class="d-flex  info_order"></span>
							<div class="info_order_product d-flex flex-column align-items-center mt-3"></div>

							<div class="d-flex no-print col">
								<!-- Détails imprimante -->
								<input type="hidden" class="printer_ip"  value="{{ $printer->address_ip ?? '' }}">
								<input type="hidden" class="printer_port" value="{{ $printer->port ?? ''}}">

								<button type="button" class="impression_code mt-5 btn btn-dark px-5 radius-20">
									<i class="bx bx-printer"></i>
									<span>Imprimer</span>
									<div class="d-none spinner-border spinner-border-sm" role="status"> <span class="visually-hidden">Loading...</span></div>
								</button>
							</div>
						</div>

						<div class="no-print d-none error_prepared_command d-flex flex-column align-items-center">
							<h2 class="mb-5">Oops, la comande n'a pas pu être validée</h2>
							<div class="danger">
								<i class="text-danger bx bx-x-circle mr-1 mr-1 font-50"></i>
							</div>
						</div>


						<div class="mt-5 no-print justify-content-center close_modal_validation mt-3 w-100 d-flex">
							<button type="button" class="close_modal_order btn btn-dark px-5">Fermer</button>
						</div>

					</div>
				</div>
			</div>
		</div>


		<!-- Modal reset commande -->
		<div class="modal_reset_order modal fade" id="modalReset" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
			<div class="modal-dialog modal-dialog-centered" role="document">
				<div class="modal-content">
					<div class="modal_body_reset modal-body d-flex flex-column justify-content-center">
						<h2 class="text-center">Recommencer la commande ?</h2>
						<div class="mt-3 w-100 d-flex justify-content-center">
							<button type="button" class="btn btn-dark px-5" data-bs-dismiss="modal">Non</button>
							<button style="margin-left:15px" type="button" class="btn btn-dark px-5 confirmation_reset_order ">Oui</button>
						</div>
					</div>
				</div>
			</div>
		</div>

		<!-- Modal vérification quantité -->
		<div class="modal_reset_order modal_verif_order modal fade" data-order="" id="modalverification" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
			<div class="modal-dialog modal-dialog-centered" role="document">
				<div class="modal-content">
					<div class="modal_body_reset modal-body d-flex flex-column justify-content-center">
						<h2 class="text-center">Attention, cette commande contient <span class="quantity_product"></span> <span class="name_quantity_product"></span></h2>
						<span style="font-size:25px" class="mb-3 text-center">Produit(s) restant(s) à bipper : <span class="text-danger" style="font-size:30px" id="quantity_product_to_verif"></span></span>
						<input type="hidden" value="" id="product_to_verif">
					</div>
				</div>
			</div>
		</div>

		
		<!-- Modal vérification quantité -->
		<div class="modal_reset_order modal_verif_order modal fade" data-order="" id="modalverification2" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
			<div class="modal-dialog modal-dialog-centered" role="document">
				<div class="modal-content">
					<div class="modal_body_reset modal-body d-flex flex-column justify-content-center">
						<h2 class="text-center">Attention, cette commande contient <span class="quantity_product"></span> <span class="name_quantity_product"></span></h2>
					</div>
					<div class="w-100 d-flex justify-content-center p-2">
						<button type="button" class="btn btn-dark px-5" data-bs-dismiss="modal">Valider</button>
					</div>
				</div>
			</div>
		</div>


		<!-- Modal de validation partielle -->
		<div class="modal fade modal_reset_order" id="modalPartial" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
			<div class="modal-dialog modal-dialog-centered" role="document">
				<div class="modal-content">
					<div class="modal_body_reset modal-body d-flex flex-column justify-content-center">
						<h2 class="text-center text-danger">Attention, cette commande est incomplète</h2>
						<h4 class="mb-2 text-center">Souhaitez-vous la mettre de côté ? Le chef d'équipe en sera informé</h4>
						<textarea style="resize:none" class="mb-3 form-control" id="note_partial_order" placeholder="Note..." rows="3"></textarea>
						<div class="w-100 d-flex justify-content-center">
							<button type="button" class="btn btn-dark px-5" data-bs-dismiss="modal">Non</button>
							<button style="margin-left:10px;" type="button" class="valid_partial_order btn btn-dark px-5">Oui</button>
						</div>
					</div>
				</div>
			</div>
		</div>


		<!-- Modal info produit déjà bippé ou inexistant-->
		<div class="modal fade modal_reset_order" id="infoMessageModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
			<div class="modal-dialog modal-dialog-centered" role="document">
				<div class="modal-content">
					<div class="modal-body">
						<h3 class="text-center info_message"></h3>
					</div>
					<div class="modal-footer">
						<button type="button" class="btn btn-primary" data-bs-dismiss="modal">Fermer</button>
					</div>
				</div>
			</div>
		</div>


		<!-- Modal entrée manuelle de code barre -->
		<div class="modal fade modal_reset_order" id="modalManuallyBarcode" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
			<div class="modal-dialog modal-dialog-centered" role="document">
				<div class="modal-content">
					<div class="modal_body_reset modal-body d-flex flex-column justify-content-center">
						<h2 class="text-center">Code barre</h2>
						<input class="mt-2 mb-2 custom_input" type="text" id="barcode_manually" name="barcode_manually" value="">
						<input type="hidden" id="product_id_barcode" value="">
						<input type="hidden" id="product_id_barcode_order_id" value="">
						<div class="mt-3 w-100 d-flex justify-content-center">
							<button type="button" class="btn btn-dark px-5" data-bs-dismiss="modal">Annuler</button>
							<button style="margin-left:10px;" type="button" class="valid_manually_barcode btn btn-dark px-5">Valider</button>
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
<script src="{{asset('assets/js/epos-2.24.0.js')}}"></script>
<script src="{{asset('assets/js/preparateur.js')}}"></script>

<script>
	var count_orders = '<?php echo json_encode($count_orders); ?>'
	var count_rea = '<?php echo $count_rea; ?>'
	var count_orders = JSON.parse(count_orders)

	$(".orders_customer").append('<span class="badge_order_count translate-middle badge rounded-pill bg-danger">+'+count_orders.order+'</span>')
	$(".orders_distributor").append('<span class="badge_order_count translate-middle badge rounded-pill bg-danger">+'+count_orders.distrib+'</span>')
	$(".transfers_orders").append('<span class="badge_order_count translate-middle badge rounded-pill bg-danger">+'+count_rea+'</span>')

</script>
@endsection