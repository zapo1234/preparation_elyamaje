@extends("layouts.app")

@section("style")
<link href="{{asset('assets/plugins/datatable/css/dataTables.bootstrap5.min.css')}}" rel="stylesheet" />
@endsection

@section("wrapper")
<div class="page-wrapper page-preparateur-order">
	<div class="page-content">
		<div class="page-breadcrumb d-sm-flex align-items-center mb-2">
			<div class="breadcrumb-title pe-3">Préparation</div>
			<div class="ps-3">Transfert</div>
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
					<span>Ici apparrait les <span class="font-bold">transferts</span> qui vous sont attribués, cliquez sur le bouton "Préparer" afin de commencer la préparation.</span>
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
									<div class="pe-3 number_order_pending">{{ $number_transfers }}</div>
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
									<div class="pe-3 waiting number_order_pending">{{ $number_transfers_waiting_to_validate }}</div>
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
									<div class="pe-3 waiting number_order_pending">{{ $number_transfers_validate }}</div>
								</div>
							</div>
						</a>
					</li>
				</ul>
				<div class="tab-content py-3">
					<div class="tab-pane fade active show" id="primaryhome" role="tabpanel">
						@if(count($transfers) > 0)
						<div class="courses-container mb-4">
							<div class="course">
								<div class="course-preview">
									<h6>Transfert</h6>
									<h2 style="font-size:1.2rem !important">{{ $transfers['details']['id'] }}</h2>
								</div>
								<div class="w-100 d-flex flex-column justify-content-between">
									<div class="course-info d-flex justify-content-between align-items-center">
										<div>
											<h6>Le {{ \Carbon\Carbon::parse($transfers['details']['date'])->isoFormat(' DD/MM/YY') }}</h6>
										</div>

										<button data-transfers=true id="{{ $transfers['details']['id'] }}" class="show_order btn">Préparer</button>
									</div>
									<div class="progress" id="progress_{{ $transfers['details']['id'] }}">
										<div class="progress-bar" role="progressbar" style="width: 0%" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
									</div>
								</div>
							</div>
						</div>

						<!-- MODAL -->
						<div class="modal_order modal fade" data-order="{{ $transfers['details']['id'] }}" id="order_{{ $transfers['details']['id'] }}" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
							<div class="modal-dialog modal-dialog-centered" role="document">
								<div class="modal-content">
									<div class="modal-body detail_product_order">
										<div class="detail_product_order_head d-flex flex-column">
											<div class="p-1 mb-2 head_detail_product_order d-flex w-100 justify-content-between">
												<span class="column1 name_column">Article</span>
												<span class="column2 name_column">Coût</span>
												<span class="column3 name_column">Pick / Qté</span>
												<span class="column4 name_column">Allée</span>
											</div>

											<div class="body_detail_product_order">
												@foreach($transfers['items'] as $item)
												<div class="barcode_{{ $item['barcode']  ?? 0 }} product_order p-2 d-flex w-100 align-items-center justify-content-between detail_product_order_line">
													<div class="column11 d-flex align-items-center detail_product_name_order flex-column">
														
														@if($item['label'])
															<span>{{ $item['label'] }}</span>
														@else
															<span class="text-danger">Produit manquant ({{ $item['product_id'] }})</span>
														@endif
														<div class="mt-1 d-flex flex-column align-items-start">
															<span style="font-size:13px">{{ $item['barcode'] ?? '' }}</span>

															@if($item['barcode'] && $item['barcode'] != "no_barcode")
																<div class="d-flex">
																	<span onclick="enter_manually_barcode({{ $item['product_id']}} , {{ $transfers['details']['id'] }})" class="manually_barcode"><i class="lni lni-keyboard"></i></span>
																	<span class="remove_{{ $item['barcode'] }}_{{ $transfers['details']['id'] }} remove_product" onclick="remove_product({{ $item['barcode']}} , {{ $transfers['details']['id'] }})"><i class="lni lni-spinner-arrow"></i></span>
																</div>
															@endif
														</div>
													</div>
													<span class="column22">{{ round(floatval($item['price_ttc']),2) }}</span>
													<span class="quantity column33"><span class="quantity_pick_in">0</span> / <span class="quantity_to_pick_in">{{ $item['qty'] }}</span> </span>
													<span class="column44">{{ $item['location'] }}</span>
												</div>
												@endforeach
											</div>

											<div class="align-items-end flex-column mt-2 d-flex justify-content-end">
												<div class="w-100 d-flex align-items-end justify-content-between flex-wrap">
													<span class="mt-1 mb-2 montant_total_order">
														#Transfert {{ $transfers['details']['id'] }}
													</span>
											
												</div>
												<div class="w-100 d-flex justify-content-between">
													<button type="button" class="btn btn-dark px-5" data-bs-dismiss="modal"><i class="d-none responsive-icon lni lni-arrow-left"></i><span class="responsive-text">Retour</button>
													<button type="button" class="reset_order btn btn-dark px-5"><i class="d-none responsive-icon lni lni-reload"></i><span class="responsive-text">Recommencer la préparation</span></button>
													<button from_transfers=true type="button" class="validate_pick_in  btn btn-dark px-5"><i class="d-none responsive-icon lni lni-checkmark"></i><span class="responsive-text">Valider</button>
												</div>

											</div>
										</div>

									</div>
								</div>
							</div>
						</div>
						@endif
					</div>


					<!-- Transferts en en attente de validation -->
					<div class="tab-pane fade" id="primaryprofile" role="tabpanel">
						@if(count($transfers_waiting_to_validate) > 0)
							@foreach($transfers_waiting_to_validate as $transfer)
								<div class="courses-container mb-4">
									<div class="course">
										<div class="course-preview">
											<h6>Transfert</h6>
											<h2 style="font-size:1.2rem !important">{{ $transfer['details']['id'] }}</h2>
										</div>
										<div class="w-100 d-flex flex-column justify-content-between">
											<div class="course-info d-flex justify-content-between align-items-center">
												<div>
													<h6>Le {{ \Carbon\Carbon::parse($transfer['details']['date'])->isoFormat(' DD/MM/YY') }}</h6>
												</div>

												<button data-tarnsfers=true id="{{ $transfer['details']['id'] }}" class="show_order btn">Préparer</button>
											</div>
											<div class="progress" id="progress_{{ $transfer['details']['id'] }}">
												<div class="progress-bar" role="progressbar" style="width: 0%" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
											</div>
										</div>
									</div>
								</div>

								<!-- MODAL -->
								<div class="modal_order modal fade" data-order="{{ $transfer['details']['id'] }}" id="order_{{ $transfer['details']['id'] }}" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
									<div class="modal-dialog modal-dialog-centered" role="document">
										<div class="modal-content">
											<div class="modal-body detail_product_order">
												<div class="detail_product_order_head d-flex flex-column">
													<div class="p-1 mb-2 head_detail_product_order d-flex w-100 justify-content-between">
														<span class="column1 name_column">Article</span>
														<span class="column2 name_column">Coût</span>
														<span class="column3 name_column">Pick / Qté</span>
														<span class="column4 name_column">Allée</span>
													</div>

													<div class="body_detail_product_order">
														@foreach($transfer['items'] as $item)
														<div class="barcode_{{ $item['barcode']  ?? 0 }} {{ $item['pick'] == $item['qty'] ? 'pick' : '' }} product_order p-2 d-flex w-100 align-items-center justify-content-between detail_product_order_line">
															<div class="column11 d-flex align-items-center detail_product_name_order flex-column">
																
																@if($item['label'])
																	<span>{{ $item['label'] }}</span>
																@else
																	<span class="text-danger">Produit manquant ({{ $item['product_id'] }})</span>
																@endif
															
																<div class="mt-1 d-flex flex-column align-items-start">
																	<span style="font-size:13px">{{ $item['barcode'] ?? '' }}</span>

																	@if($item['barcode'] && $item['barcode'] != "no_barcode")
																		<div class="d-flex">
																			<span onclick="enter_manually_barcode({{ $item['product_id']}} , {{ $transfer['details']['id'] }})" class="manually_barcode"><i class="lni lni-keyboard"></i></span>
																			<span class="remove_{{ $item['barcode'] }}_{{ $transfer['details']['id'] }} remove_product" onclick="remove_product({{ $item['barcode']}} , {{ $transfer['details']['id'] }})"><i class="lni lni-spinner-arrow"></i></span>
																		</div>
																	@endif
																</div>
															</div>
															<span class="column22">{{ round(floatval($item['price_ttc']),2) }}</span>
															<span class="quantity column33"><span class="quantity_pick_in">{{ $item['pick'] }}</span> / <span class="quantity_to_pick_in">{{ $item['qty'] }}</span> </span>
															<span class="column44">{{ $item['location'] }}</span>
														</div>
														@endforeach
													</div>

													<div class="align-items-end flex-column mt-2 d-flex justify-content-end">
														<div class="w-100 d-flex align-items-end justify-content-between flex-wrap">
															<span class="mt-1 mb-2 montant_total_order">
																#Transfert {{ $transfer['details']['id'] }}
															</span>
													
														</div>
														<div class="w-100 d-flex justify-content-between">
															<button type="button" class="btn btn-dark px-5" data-bs-dismiss="modal"><i class="d-none responsive-icon lni lni-arrow-left"></i><span class="responsive-text">Retour</button>
															<button type="button" class="reset_order btn btn-dark px-5"><i class="d-none responsive-icon lni lni-reload"></i><span class="responsive-text">Recommencer la préparation</span></button>
															<button from_transfers=true type="button" class="validate_pick_in btn btn-dark px-5"><i class="d-none responsive-icon lni lni-checkmark"></i><span class="responsive-text">Valider</button>
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



					<!-- Transferts en en attente validé -->
					<div class="tab-pane fade" id="primarycontact" role="tabpanel">
						@if(count($transfers_validate) > 0)
							@foreach($transfers_validate as $transfer)
								<div class="courses-container mb-4">
									<div class="course">
										<div class="course-preview">
											<h6>Transfert</h6>
											<h2 style="font-size:1.2rem !important">{{ $transfer['details']['id'] }}</h2>
										</div>
										<div class="w-100 d-flex flex-column justify-content-between">
											<div class="course-info d-flex justify-content-between align-items-center">
												<div>
													<h6>Le {{ \Carbon\Carbon::parse($transfer['details']['date'])->isoFormat(' DD/MM/YY') }}</h6>
												</div>
												<button data-tarnsfers=true id="{{ $transfer['details']['id'] }}" class="show_order btn">Préparer</button>
											</div>
											<div class="progress" id="progress_{{ $transfer['details']['id'] }}">
												<div class="progress-bar" role="progressbar" style="width: 0%" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
											</div>
										</div>
									</div>
								</div>

								<!-- MODAL -->
								<div class="modal_order modal fade" data-order="{{ $transfer['details']['id'] }}" id="order_{{ $transfer['details']['id'] }}" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
									<div class="modal-dialog modal-dialog-centered" role="document">
										<div class="modal-content">
											<div class="modal-body detail_product_order">
												<div class="detail_product_order_head d-flex flex-column">
													<div class="p-1 mb-2 head_detail_product_order d-flex w-100 justify-content-between">
														<span class="column1 name_column">Article</span>
														<span class="column2 name_column">Coût</span>
														<span class="column3 name_column">Pick / Qté</span>
														<span class="column4 name_column">Allée</span>
													</div>

													<div class="body_detail_product_order">
														@foreach($transfer['items'] as $item)
														<div class="barcode_{{ $item['barcode']  ?? 0 }} product_order p-2 d-flex w-100 align-items-center justify-content-between detail_product_order_line">
															<div class="column11 d-flex align-items-center detail_product_name_order flex-column">
																
																@if($item['label'])
																	<span>{{ $item['label'] }}</span>
																@else
																	<span class="text-danger">Produit manquant ({{ $item['product_id'] }})</span>
																@endif
															
																<div class="mt-1 d-flex flex-column align-items-start">
																	<span style="font-size:13px">{{ $item['barcode'] ?? '' }}</span>

																	@if($item['barcode'] && $item['barcode'] != "no_barcode")
																		<div class="d-flex">
																			<span onclick="enter_manually_barcode({{ $item['product_id']}} , {{ $transfer['details']['id'] }})" class="manually_barcode"><i class="lni lni-keyboard"></i></span>
																			<span class="remove_{{ $item['barcode'] }}_{{ $transfer['details']['id'] }} remove_product" onclick="remove_product({{ $item['barcode']}} , {{ $transfer['details']['id'] }})"><i class="lni lni-spinner-arrow"></i></span>
																		</div>
																	@endif
																</div>
															</div>
															<span class="column22">{{ round(floatval($item['price_ttc']),2) }}</span>
															<span class="quantity column33"><span class="quantity_pick_in">0</span> / <span class="quantity_to_pick_in">{{ $item['qty'] }}</span> </span>
															<span class="column44">{{ $item['location'] }}</span>
														</div>
														@endforeach
													</div>

													<div class="align-items-end flex-column mt-2 d-flex justify-content-end">
														<div class="w-100 d-flex align-items-end justify-content-between flex-wrap">
															<span class="mt-1 mb-2 montant_total_order">
																#Transfert {{ $transfer['details']['id'] }}
															</span>
													
														</div>
														<div class="w-100 d-flex justify-content-between">
															<button type="button" class="btn btn-dark px-5" data-bs-dismiss="modal"><i class="d-none responsive-icon lni lni-arrow-left"></i><span class="responsive-text">Retour</button>
															<button type="button" class="reset_order btn btn-dark px-5"><i class="d-none responsive-icon lni lni-reload"></i><span class="responsive-text">Recommencer la préparation</span></button>
															<button from_transfers=true type="button" class="validate_pick_in  btn btn-dark px-5"><i class="d-none responsive-icon lni lni-checkmark"></i><span class="responsive-text">Valider</button>
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
							<h2 class="no-print mb-5 d-flex ">Transfert préparé avec succès !</h2>
							<div class="d-flex" id="qrcode"></div>

							<span class="d-flex info_order"></span>
							<div class="info_order_product d-flex flex-column align-items-center mt-3"></div>

							<div class="d-flex  no-print col">
								<!-- Détails imprimante -->
								<input type="hidden" class="printer_ip"  value="{{ $printer->address_ip ?? ''}}">
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
						<h2 class="text-center text-danger">Attention, ce transfert est incomplet</h2>
						<span class="mb-3 text-center partial_transfert">Souhaitez-vous le valider quand même ? <b>(un bon avec les produits manquant sera généré)</b></span>
						<div class="w-100 d-flex justify-content-center">
							<button type="button" class="btn btn-dark px-5" data-bs-dismiss="modal">Non</button>
							<button from_transfers="true" style="margin-left:10px;" type="button" class="valid_partial_order btn btn-dark px-5">Oui</button>
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
							<button style="margin-left:10px;" type="button" class="valid_manually_barcode_transfert btn btn-dark px-5">Valider</button>
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