@extends("layouts.app")

		@section("style")
			<link href="{{asset('assets/plugins/datatable/css/dataTables.bootstrap5.min.css')}}" rel="stylesheet" />
		@endsection 

		@section("wrapper")
			<div class="page-wrapper">
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
													<h2>#{{ $orders['details']['id'] }}</h2>
												</div>
												<div class="course-info d-flex justify-content-between align-items-center">
													<div>
														<h6>{{ \Carbon\Carbon::parse($orders['details']['date'])->isoFormat(' DD/MM/YY à HH') }}h</h6>
														<h2 class="customer_name_{{ $orders['details']['id'] }}">{{ $orders['details']['first_name']  }} {{ $orders['details']['last_name']  }}</h2>
													</div>
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
																<span class="column3 name_column">Pick / Qté</span>
																<!-- <span class="column4 name_column">Code Barre</span> -->
															</div>	

															<div class="body_detail_product_order">
																@foreach($orders['items'] as $item)
																	<div class="barcode_{{ $item['barcode'] ?? 0 }} {{ $item['pick'] == $item['quantity'] ? 'pick' : '' }} product_order p-2 d-flex w-100 align-items-center justify-content-between detail_product_order_line">
																		<div class="column11 d-flex align-items-center detail_product_name_order">
																			@if($item['cost'] == 0)
																			<span><span class="text-danger">(Cadeau) </span>{{ $item['name'] }}</span>
																			@else 
																				<span>{{ $item['name'] }}</span>
																			@endif
																		</div>
																		<span class="column22">{{ round(floatval($item['cost']),2) }}</span>
																		<span class="quantity column33"><span class="quantity_pick_in">{{ $item['pick'] }}</span> / <span class="quantity_to_pick_in">{{ $item['quantity'] }}</span> </span>
																	</div>
																@endforeach
															</div>

															<div class="align-items-end flex-column mt-2 d-flex justify-content-end"> 
																<div class="w-100 d-flex align-items-end justify-content-between flex-wrap">
																	<span class="mt-1 mb-2 montant_toltal_order">
																	@if($orders['details']['coupons'])
																		<div><span  class="font-18 badge bg-success">Code : {{ $orders['details']['coupons'] }} (-{{$orders['details']['discount_amount']}}%)</span></div>
																	@endif
																	#{{ $orders['details']['id'] }} </span>
																	
																	<div class="mt-1 mb-2 montant_toltal_order">
																		<div>
																			<span class="detail_footer_order">Sous-total des articles : </span><strong>{{ floatval($orders['details']['total']) - floatval($orders['details']['total_tax']) }} {{ config('app.currency_symbol') }}</strong>
																		</div>
																		<div>
																			<span class="detail_footer_order">TVA : </span><strong>{{ $orders['details']['total_tax'] }} {{ config('app.currency_symbol') }}</strong>
																		</div>
																		<div>
																			<span class="detail_footer_order">Total de la commande:   </span><strong>{{ $orders['details']['total'] }} {{ config('app.currency_symbol') }}</strong>
																		</div>
																	</div>
																</div>
																<div class="w-100 d-flex justify-content-between">
																	<button type="button" class="btn btn-dark px-5" data-bs-dismiss="modal"><i class="d-none responsive-icon lni lni-arrow-left"></i><span class="responsive-text">Retour</button>
																	<button type="button" class="reset_order btn btn-dark px-5" ><i class="d-none responsive-icon lni lni-reload"></i><span class="responsive-text">Recommencer la commande</span></button>
																	<button type="button" class="validate_pick_in btn btn-dark px-5"><i class="d-none responsive-icon lni lni-checkmark"></i><span class="responsive-text">Valider</button>
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
														<h2>#{{ $order['details']['id'] }}</h2>
													</div>
													<div class="course-info d-flex justify-content-between align-items-center">
														<div>
															<h6>{{ \Carbon\Carbon::parse($order['details']['date'])->isoFormat(' DD/MM/YY à HH') }}h</h6>
															<h2 class="customer_name_{{ $order['details']['id'] }}">{{ $order['details']['first_name']  }} {{ $order['details']['last_name']  }}</h2>
														</div>
														<button id="{{ $order['details']['id'] }}" class="show_order btn">Reprendre</button>
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
																	<!-- <span class="column4 name_column">Code Barre</span> -->
																</div>	

																<div class="body_detail_product_order">
																	@foreach($order['items'] as $item)
																		<div class="barcode_{{ $item['barcode']  ?? 0 }} {{ $item['pick'] == $item['quantity'] ? 'pick' : '' }} product_order p-2 d-flex w-100 align-items-center justify-content-between detail_product_order_line">
																			<div class="column11 d-flex align-items-center detail_product_name_order">
																				@if($item['cost'] == 0)
																				<span><span class="text-danger">(Cadeau) </span>{{ $item['name'] }}</span>
																				@else 
																					<span>{{ $item['name'] }}</span>
																				@endif
																			</div>
																			<span class="column22">{{ round(floatval($item['cost']),2) }}</span>
																			<span class="quantity column33"><span class="quantity_pick_in">{{ $item['pick'] }}</span> / <span class="quantity_to_pick_in">{{ $item['quantity'] }}</span> </span>
																		</div>
																	@endforeach
																</div>

																<div class="align-items-end flex-column mt-2 d-flex justify-content-end"> 
																	<div class="w-100 d-flex align-items-end justify-content-between flex-wrap">
																		<span class="mt-1 mb-2 montant_toltal_order">
																		@if($order['details']['coupons'])
																			<div><span  class="font-18 badge bg-success">Code : {{ $order['details']['coupons'] }} (-{{$order['details']['discount_amount']}}%)</span></div>
																		@endif
																		#{{ $order['details']['id'] }} </span>
																		
																		<div class="mt-1 mb-2 montant_toltal_order">
																			<div>
																				<span class="detail_footer_order">Sous-total des articles : </span><strong>{{ floatval($order['details']['total']) - floatval($order['details']['total_tax']) }} {{ config('app.currency_symbol') }}</strong>
																			</div>
																			<div>
																				<span class="detail_footer_order">TVA : </span><strong>{{ $order['details']['total_tax'] }} {{ config('app.currency_symbol') }}</strong>
																			</div>
																			<div>
																				<span class="detail_footer_order">Total de la commande:   </span><strong>{{ $order['details']['total'] }} {{ config('app.currency_symbol') }}</strong>
																			</div>
																		</div>
																	</div>
																	<div class="w-100 d-flex justify-content-between">
																		<button type="button" class="btn btn-dark px-5" data-bs-dismiss="modal"><i class="d-none responsive-icon lni lni-arrow-left"></i><span class="responsive-text">Retour</button>
																		<button type="button" class="reset_order btn btn-dark px-5" ><i class="d-none responsive-icon lni lni-reload"></i><span class="responsive-text">Recommencer la commande</span></button>
																		<button type="button" class="validate_pick_in btn btn-dark px-5"><i class="d-none responsive-icon lni lni-checkmark"></i><span class="responsive-text">Valider</button>
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
														<h2>#{{ $order['details']['id'] }}</h2>
													</div>
													<div class="course-info d-flex justify-content-between align-items-center">
														<div>
															<h6>{{ \Carbon\Carbon::parse($order['details']['date'])->isoFormat(' DD/MM/YY à HH') }}h</h6>
															<h2 class="customer_name_{{ $order['details']['id'] }}">{{ $order['details']['first_name']  }} {{ $order['details']['last_name']  }}</h2>
														</div>
														<button id="{{ $order['details']['id'] }}" class="show_order btn">Reprendre</button>
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
																	<!-- <span class="column4 name_column">Code Barre</span> -->
																</div>	

																<div class="body_detail_product_order">
																	@foreach($order['items'] as $item)
																		<div class="barcode_{{ $item['barcode']  ?? 0 }} {{ $item['pick'] == $item['quantity'] ? 'pick' : '' }} product_order p-2 d-flex w-100 align-items-center justify-content-between detail_product_order_line">
																			<div class="column11 d-flex align-items-center detail_product_name_order">
																				@if($item['cost'] == 0)
																				<span><span class="text-danger">(Cadeau) </span>{{ $item['name'] }}</span>
																				@else 
																					<span>{{ $item['name'] }}</span>
																				@endif
																			</div>
																			<span class="column22">{{ round(floatval($item['cost']),2) }}</span>
																			<span class="quantity column33"><span class="quantity_pick_in">{{ $item['pick'] }}</span> / <span class="quantity_to_pick_in">{{ $item['quantity'] }}</span> </span>
																		</div>
																	@endforeach
																</div>

																<div class="align-items-end flex-column mt-2 d-flex justify-content-end"> 
																	<div class="w-100 d-flex align-items-end justify-content-between flex-wrap">
																		<span class="mt-1 mb-2 montant_toltal_order">
																		@if($order['details']['coupons'])
																			<div><span  class="font-18 badge bg-success">Code : {{ $order['details']['coupons'] }} (-{{$order['details']['discount_amount']}}%)</span></div>
																		@endif
																		#{{ $order['details']['id'] }} </span>
																		
																		<div class="mt-1 mb-2 montant_toltal_order">
																			<div>
																				<span class="detail_footer_order">Sous-total des articles : </span><strong>{{ floatval($order['details']['total']) - floatval($order['details']['total_tax']) }} {{ config('app.currency_symbol') }}</strong>
																			</div>
																			<div>
																				<span class="detail_footer_order">TVA : </span><strong>{{ $order['details']['total_tax'] }} {{ config('app.currency_symbol') }}</strong>
																			</div>
																			<div>
																				<span class="detail_footer_order">Total de la commande:   </span><strong>{{ $order['details']['total'] }} {{ config('app.currency_symbol') }}</strong>
																			</div>
																		</div>
																	</div>
																	<div class="w-100 d-flex justify-content-between">
																		<button type="button" class="btn btn-dark px-5" data-bs-dismiss="modal"><i class="d-none responsive-icon lni lni-arrow-left"></i><span class="responsive-text">Retour</button>
																		<button type="button" class="reset_order btn btn-dark px-5" ><i class="d-none responsive-icon lni lni-reload"></i><span class="responsive-text">Recommencer la commande</span></button>
																		<button type="button" class="validate_pick_in btn btn-dark px-5"><i class="d-none responsive-icon lni lni-checkmark"></i><span class="responsive-text">Valider</button>
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

										<div class="d-flex  no-print col">
                                            <button type="button" class="impression_code mt-5 btn btn-dark px-5 radius-30">Imprimer</button>
                                        </div>
									</div>

									<div class="no-print d-none error_prepared_command d-flex flex-column align-items-center">
										<h2 class="mb-5">Oops, la comande n'a pas pu être validée</h2>
										<div class="danger">
											<i class="text-danger bx bx-x-circle mr-1 mr-1 font-50"></i>
										</div>
									</div>

									
									<div class="mt-5 no-print d-none justify-content-center close_modal_validation mt-3 w-100 d-flex">
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
									<div class="w-100 d-flex justify-content-center">
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

				</div>
			</div>
		@endsection

	@section("script")
		<script src="{{asset('assets/plugins/datatable/js/jquery.dataTables.min.js')}}"></script>
		<script src="{{asset('assets/plugins/datatable/js/dataTables.bootstrap5.min.js')}}"></script>
		<script src="{{asset('assets/js/qrcode.js')}}"></script>
		<script src="{{asset('assets/js/preparateur.js')}}"></script>
	@endsection
