
	@extends("layouts.app")

		@section("style")
			<link href="{{asset('assets/plugins/datatable/css/dataTables.bootstrap5.min.css')}}" rel="stylesheet" />
			<link href="{{('assets/plugins/select2/css/select2.min.css')}}" rel="stylesheet" />
			<link href="assets/plugins/select2/css/select2-bootstrap4.css" rel="stylesheet" />
		@endsection

		@section("wrapper")
			<div class="page-wrapper">
				<div class="page-content">
					<div class="page-breadcrumb d-sm-flex align-items-center mb-3">
						<div class="breadcrumb-title pe-3">Expéditions</div>
						<div class="ps-3">
							<nav aria-label="breadcrumb">
								<ol class="breadcrumb mb-0 p-0">
									<li class="breadcrumb-item active" aria-current="page">Étiquettes</li>
									<input id="order_id" type="hidden" value="">
									<input type="hidden" value="" id="detail_order_label">
								</ol>
							</nav>
						</div>
						<div  class="ms-auto ms-auto-responsive d-flex">
							<button id="show_modal_bordereau" type="button" class="btn btn-dark px-5">Générer bordereau</button>
						</div>
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
								Ici, vous pouvez retrouver la liste des commandes avec leurs étiquettes colissimo. Vous pouvez également 
								générer une étiquette depuis cette interface et l'imprimer
							</div>
						</div>
					</div>

					<!-- Modal Génération Bordereau par date -->
					<div class="modal fade modal_radius" id="modalBordereau" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
						<div class="modal-dialog modal-dialog-centered" role="document">
							<div class="modal-content">
								<div class="modal-body">
									<div class="d-flex w-100 justify-content-end">
										<i style="z-index:10; cursor:pointer;position:absolute" data-bs-dismiss="modal" class="font-20 bx bx-x"></i>
									</div>
									<div class="container_multy_step">
										<form method="POST" action="{{ route('bordereau.generate') }}">
											@csrf
											<div class="step_form" id="form1">
												<h3 class="text-dark">Bordereau</h3>
											
												<div class="w-100 d-flex justify-content-center align-items-center mb-3 mt-3">
												<span class="d-flex align-items-center" style="gap: 10px">
													<label class="text-dark font-20">Chronopost</label>
													<input name="origin[]" style="width:1.5em; height: 1.5em; cursor: pointer" class="form-check-input check_all" type="checkbox" value="chronopost">
												</span>
												<span class="d-flex align-items-center" style="gap: 10px; margin-left: 25px">
													<label class="text-dark font-20">Colissimo</label>
													<input name="origin[]" style="width:1.5em; height: 1.5em; cursor: pointer" class="form-check-input check_all" type="checkbox" value="colissimo">
												</span>
												</div>

												<div class="btn_box">
													<button class="btn btn-dark px-5" id="next1" type="button">Suivant</button>
												</div>
											</div>
											<div class="step_form" id="form2">
												<h3 class="text-dark">Date</h3>
												<div class="d-flex justify-content-center w-100">
													<input style="border: 1px solid black" class="date_bordereau_input" type="date" name="date" value="{{ date('Y-m-d') }}">
												</div>
												<div class="btn_box">
												<button class="btn btn-dark px-5" id="back1" type="button">Retour</button>
												<button class="btn btn-dark px-5" id="next2" type="submit">Valider</button>
												</div>
											</div>
										</form>
										<div class="progress_container">
											<div class="progress" id="progress"></div>
											<div class="circle active_progress">1</div>
											<div class="circle">2</div>
										</div>
									</div>
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
					<div class="card card_table_mobile_responsive label_table">
						<div class="card-body">
							<div class="d-flex justify-content-center">
								<div class="loading spinner-border text-dark" role="status"> 
									<span class="visually-hidden">Loading...</span>
								</div>
							</div>

							<form method="GET" action="{{ route('labels.filter') }}" class="d-flex d-none order_research">								
								<select name="origin" class="select2_custom type_dropdown input_form_type">
									<option value="">Expéditeur</option>
									@if(isset($parameter['origin']))
										@if($parameter['origin'] == "colissimo")
											<option selected value="colissimo">Colissimo</option>
											<option value="chronopost">Chronopost</option>
										@else if($parameter['origin'] == "chronopost")
											<option selected value="chronopost">Chronopost</option>
											<option value="colissimo">Colissimo</option>
										@endif
									@else 
										<option value="colissimo">Colissimo</option>
										<option value="chronopost">Chronopost</option>
									@endif
								</select>
								<select name="status" class="select2_custom status_dropdown input_form_type h-100">
									<option value="">Status</option>
										@foreach($status_list as $keyStatus => $status)
											@if(isset($parameter['status']))
												@if($parameter['status'] == $keyStatus)
													<option selected value="{{$keyStatus}}">{{$status}}</option>
												@else 
													<option value="{{$keyStatus}}">{{$status}}</option>
												@endif
											@else 
												<option value="{{$keyStatus}}">{{$status}}</option>
											@endif
										@endforeach
								</select>
								<input value="{{ $parameter['created_at'] ?? '' }}" name="created_at" class="custom_input" style="padding: 4px;" type="date">
								<input value="{{ $parameter['order_woocommerce_id'] ?? '' }}" placeholder="Numéro de commande" name="order_woocommerce_id" class="custom_input" style="padding: 4px;" type="text">
								<button style="margin-left:10px" class="research_label_order d-flex align-items-center btn btn-primary" type="submit">Rechercher</button>
							</form>
							
					
							<table id="example" class="dashboard_label d-none table_mobile_responsive w-100 table table-striped table-bordered">
								<thead>
									<tr>
										<th class="col-md-1">Commande</th>
										<th>Status</th>
										<th>Générée le</th>
										<th class="col-md-2">Visualiser</th>
										<th class="col-md-2">Imprimer</th>
										<th class="col-md-2">Douane</th>
										<th class="col-md-1">Expédition</th>
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
											<td data-label="Générée le">{{ $order[0]['label_created_at'] ? date("d/m/Y", strtotime($order[0]['label_created_at'])) : '' }}</td>
											<td data-label="Visualiser">
												@if(isset($order['labels']))
													@foreach($order['labels'] as $label)
														<div class="mb-3 flex-wrap d-flex w-100 align-items-center justify-content-between">
															<div>
																<form class="d-flex" method="POST" action="{{ route('label.show') }}">
																	@csrf
																	<input name="label_id" type="hidden" value="{{ $label['label_id'] }}">  
																	<button type="submit" class="download_label_button"><i class="bx bx-show-alt"></i><span class="label_tracking_responsive">{{ $label['tracking_number'] }}</span></button>
																</form>
															</div>
															<div>
																<button data-tracking="{{ $label['tracking_number'] }}" data-label="{{ $label['label_id'] }}" type="submit" class="delete_label download_label_button"><i class="bx bx-trash"></i></button>
															</div>
														</div>
													@endforeach
													<div class="w-100 d-flex justify-center">
														<button data-order="{{ $order[0]['order_woocommerce_id'] }}" from_dolibarr="{{ isset($order[0]['fk_commande']) ? true : false }}" type="button" class="generate_label_button download_label_button"><i class="bx bx-plus"></i>Générer</button>
													</div>
												@else 
													<div class="w-100 d-flex justify-center">
														<button data-order="{{ $order[0]['order_woocommerce_id'] }}" from_dolibarr="{{ isset($order[0]['fk_commande']) ? 1 : 0 }}" type="button" class="generate_label_button download_label_button"><i class="bx bx-plus"></i>Générer</button>
													</div>
												@endif
											</td>
											<td style="vertical-align:baseline" data-label="Imprimer">
												@if(isset($order['labels']))
													@foreach($order['labels'] as $label)
														<div class="mb-3 flex-wrap d-flex w-100 align-items-center justify-content-between">
															<div>
																@if( $label['label_format'] == "PDF")
																	<form class="d-flex" method="POST" action="{{ route('label.download') }}">
																		@csrf
																		<input name="label_id" type="hidden" value="{{ $label['label_id'] }}">
																		<input name="order_id" type="hidden" value="{{ $order[0]['order_woocommerce_id'] }}">
																		<input name="label_format" type="hidden" value="{{ $label['label_format'] }}">

																		<button type="submit" class="d-flex download_label_button print_pdf_file"><i class="bx bx-download"></i><span class="label_tracking_responsive">{{ $label['tracking_number'] }}</span></button>
																	</form>
																@elseif($label['label_format'] == "ZPL")
																<div class="d-flex">
																	<button data-label="{{ $label['label_id'] }}" type="submit" class="download_label_button print_zpl_file"><i class="bx bx-printer"></i><span class="label_tracking_responsive">{{ $label['tracking_number'] }}</span></button>
																</div>
																@endif
															</div>
															<div>
																<button data-tracking="{{ $label['tracking_number'] }}" data-label="{{ $label['label_id'] }}" type="submit" class="delete_label download_label_button"><i class="bx bx-trash"></i></button>
															</div>
														</div>
													@endforeach
												@endif 
											</td>
											<td style="position:relative" data-label="Déclaration douanière">
												@if(isset($order['labels']))
													@foreach($order['labels'] as $label)
														<div class="mb-2 d-flex w-100 align-items-center justify-content-between">
															<div class="w-100">	
																@if($result == 1 && $label['cn23'])
																	<input class="cn23_label" type="hidden" value="{{ $label['download_cn23'] }}">
																@endif
																@if($label['cn23'])
																	
																	<div class="d-flex align-items-center w-100 justify-content-between flex-wrap">
																		<form class="d-flex" method="POST" action="{{ route('label.download_cn23') }}">
																			@csrf
																			<input name="label_id" type="hidden" value="{{ $label['label_id'] }}">
																			<input name="order_id" type="hidden" value="{{ $order[0]['order_woocommerce_id'] }}">
																			<button type="submit" class="download_cn23 d-flex download_label_button"><i class="bx bx-download"></i>Télécharger ({{$label['tracking_number']}})</button>
																		</form>

																		<div class="download_cn23_status icon-badge position-relative bg-{{ $label['download_cn23'] ? 'success' : 'danger' }} me-lg-2"> 
																			@if($label['download_cn23'])
																				<i title="Déjà téléchargé" class="text-white bx bx-check"></i>
																			@else 
																				<i title="Pas encore téléchargé" class="text-white bx bx-x"></i>
																			@endif
																		</div>
																	</div>
																@else 
																	<span class="no_necessary badge rounded-pill bg-secondary">Non nécéssaire</span>
																@endif
															</div>
														</div>
													@endforeach
												@endif
											</td>
											<td data-label="Expédition">

												
													<i onclick="showCustomerOrderDetail('{{ $order[0]['order_woocommerce_id'] }}')" class="show_detail_customer lni lni-delivery"></i>
													<div class="modal fade modal_radius" id="order_detail_customer_{{ $order[0]['order_woocommerce_id'] }}" tabindex="-1" role="dialog" aria-labelledby="exampleModalLongTitle" aria-hidden="true">
														<div class="modal-dialog modal-dialog-centered" role="document">
															<div class="modal-content">
																<div class="modal-body">
																	<div class="mt-2 d-flex flex-column w-100 customer_billing">
																		<div class="d-flex w-100 justify-content-between">
																			<span class="customer_detail_title badge bg-dark">Facturation</span>
																			@if(str_contains('chrono', $order[0]['shipping_method']))
																				<div class="shipping_chrono_logo"></div>
																			@endif
																		</div>

																		@if($order[0]['billing_customer_first_name'])
																			<div class="d-flex w-100 justify-content-between">
																				<span class="billing_customer_first_name">{{ $order[0]['billing_customer_first_name'] }}</span>
																				<i data-edit="billing_customer_first_name" class="edit_detail_order bx bx-pencil"></i>
																			</div>
																		@endif

																		@if($order[0]['billing_customer_last_name'])
																			<div class="d-flex w-100 justify-content-between">
																				<span class="billing_customer_last_name">{{ $order[0]['billing_customer_last_name'] }}</span>
																				<i data-edit="billing_customer_last_name" class="edit_detail_order bx bx-pencil"></i>
																			</div>
																		@endif

																		@if($order[0]['billing_customer_email'])
																			<div class="d-flex w-100 justify-content-between">
																				<div class="d-flex w-100">
																					<i class="bx bx-envelope"></i>
																					<span class="billing_customer_email">{{ $order[0]['billing_customer_email'] }}</span>
																				</div>
																				<i data-edit="billing_customer_email" class="edit_detail_order bx bx-pencil"></i>
																			</div>
																		@endif

																		@if($order[0]['billing_customer_phone'])
																			<div class="d-flex w-100 justify-content-between">
																				<div class="d-flex w-100">
																					<i class="bx bx-phone"></i>
																					<span class="billing_customer_phone">{{ $order[0]['billing_customer_phone'] }}</span>
																				</div>
																				<i data-edit="billing_customer_phone" class="edit_detail_order bx bx-pencil"></i>
																			</div>
																		@endif

																		@if($order[0]['billing_customer_company'])
																			<div class="d-flex w-100 justify-content-between">
																				<span class="billing_customer_company">{{ $order[0]['billing_customer_company'] }}</span>
																				<i data-edit="billing_customer_company" class="edit_detail_order bx bx-pencil"></i>
																			</div>
																		@endif

																		@if($order[0]['billing_customer_address_1'])
																			<div class="d-flex w-100 justify-content-between">
																				<span class="billing_customer_address_1">{{ $order[0]['billing_customer_address_1'] }}</span>
																				<i data-edit="billing_customer_address_1" class="edit_detail_order bx bx-pencil"></i>
																			</div>
																		@endif

																		@if($order[0]['billing_customer_address_2'])
																			<div class="d-flex w-100 justify-content-between">
																				<span class="billing_customer_address_2">{{ $order[0]['billing_customer_address_2'] }}</span>
																				<i data-edit="billing_customer_address_2" class="edit_detail_order bx bx-pencil"></i>
																			</div>
																		@endif

																		@if($order[0]['billing_customer_state'])
																			<div class="d-flex w-100 justify-content-between">
																				<span class="billing_customer_state">{{ $order[0]['billing_customer_state'] }}</span>
																				<i data-edit="billing_customer_state" class="edit_detail_order bx bx-pencil"></i>
																			</div>
																		@endif

																		@if($order[0]['billing_customer_postcode'])
																			<div class="d-flex w-100 justify-content-between">
																				<span class="billing_customer_postcode">{{ $order[0]['billing_customer_postcode'] }}</span>
																				<i data-edit="billing_customer_postcode" class="edit_detail_order bx bx-pencil"></i>
																			</div>
																		@endif

																		@if($order[0]['billing_customer_city'])
																			<div class="d-flex w-100 justify-content-between">
																				<span class="billing_customer_city">{{ $order[0]['billing_customer_city'] }}</span>
																				<i data-edit="billing_customer_city" class="edit_detail_order bx bx-pencil"></i>
																			</div>
																		@endif

																		@if($order[0]['billing_customer_country'])
																			<div class="d-flex w-100 justify-content-between">
																				<span class="billing_customer_country">{{ $order[0]['billing_customer_country'] }}</span>
																			</div>
																		@endif
																	</div>

																	<div class="mt-3 d-flex flex-column w-100 customer_shipping">
																		<span class="customer_detail_title badge bg-dark">Expédition</span>

																		@if($order[0]['shipping_customer_first_name'])
																			<div class="d-flex w-100 justify-content-between">
																				<span class="shipping_customer_first_name">{{ $order[0]['shipping_customer_first_name'] }}</span>
																				<i data-edit="shipping_customer_first_name" class="edit_detail_order bx bx-pencil"></i>
																			</div>
																		@endif

																		@if($order[0]['shipping_customer_last_name'])
																			<div class="d-flex w-100 justify-content-between">
																				<span class="shipping_customer_last_name">{{ $order[0]['shipping_customer_last_name'] }}</span>
																				<i data-edit="shipping_customer_last_name" class="edit_detail_order bx bx-pencil"></i>
																			</div>
																		@endif

																		@if($order[0]['shipping_customer_company'])
																			<div class="d-flex w-100 justify-content-between">
																				<span class="shipping_customer_company">{{ $order[0]['shipping_customer_company'] }}</span>
																				<i data-edit="shipping_customer_company" class="edit_detail_order bx bx-pencil"></i>
																			</div>
																		@endif

																		@if($order[0]['shipping_customer_address_1'])
																			<div class="d-flex w-100 justify-content-between">
																				<span class="shipping_customer_address_1">{{ $order[0]['shipping_customer_address_1'] }}</span>
																				<i data-edit="shipping_customer_address_1" class="edit_detail_order bx bx-pencil"></i>
																			</div>
																		@endif

																		@if($order[0]['shipping_customer_address_2'])
																			<div class="d-flex w-100 justify-content-between">
																				<span class="shipping_customer_address_2">{{ $order[0]['shipping_customer_address_2'] }}</span>
																				<i data-edit="shipping_customer_address_2" class="edit_detail_order bx bx-pencil"></i>
																			</div>
																		@endif

																		@if($order[0]['shipping_customer_state'])
																			<div class="d-flex w-100 justify-content-between">
																				<span class="shipping_customer_state">{{ $order[0]['shipping_customer_state'] }}</span>
																				<i data-edit="shipping_customer_state" class="edit_detail_order bx bx-pencil"></i>
																			</div>
																		@endif

																		@if($order[0]['shipping_customer_postcode'])
																			<div class="d-flex w-100 justify-content-between">
																				<span class="shipping_customer_postcode">{{ $order[0]['shipping_customer_postcode'] }}</span>
																				<i data-edit="shipping_customer_postcode" class="edit_detail_order bx bx-pencil"></i>
																			</div>
																		@endif

																		@if($order[0]['shipping_customer_city'])
																			<div class="d-flex w-100 justify-content-between">
																				<span class="shipping_customer_city">{{ $order[0]['shipping_customer_city'] }}</span>
																				<i data-edit="shipping_customer_city" class="edit_detail_order bx bx-pencil"></i>
																			</div>
																		@endif

																		@if($order[0]['shipping_customer_country'])
																			<div class="d-flex w-100 justify-content-between">
																				<span class="shipping_customer_country">{{ $order[0]['shipping_customer_country'] }}</span>
																			</div>
																		@endif
																	</div>
																</div>

																<input type="hidden" value="{{ $order[0]['order_woocommerce_id'] }}" id="order_detail_id">
																<input type="hidden" value="{{ $order[0]['user_id'] }}" id="order_attributed">

																<div class="modal-footer d-flex w-100 justify-content-between">
																	<span>Commande #{{ $order[0]['order_woocommerce_id'] }}</span>
																	<button type="button" class="btn btn-primary" data-bs-dismiss="modal">Fermer</button>
																</div>
															</div>
														</div>
													</div>
												
											</td>
										</tr>
									@endforeach
								</tbody>
							</table>
						</div>
					</div>
				</div>
			</div>


			<!-- Modal avertissement documents à télécharger -->
			<div class="modal fade" id="warningCn23Download" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
				<div class="modal-dialog modal-dialog-centered" role="document">
					<div class="modal-content">
						<div class="modal-body">
							<h2 class="text-center">
								<svg style="margin-bottom:5px;" xmlns="http://www.w3.org/2000/svg" width="30" height="30" fill="red" class="bi bi-exclamation-triangle" viewBox="0 0 16 16">
									<path d="M7.938 2.016A.13.13 0 0 1 8.002 2a.13.13 0 0 1 .063.016.146.146 0 0 1 .054.057l6.857 11.667c.036.06.035.124.002.183a.163.163 0 0 1-.054.06.116.116 0 0 1-.066.017H1.146a.115.115 0 0 1-.066-.017.163.163 0 0 1-.054-.06.176.176 0 0 1 .002-.183L7.884 2.073a.147.147 0 0 1 .054-.057zm1.044-.45a1.13 1.13 0 0 0-1.96 0L.165 13.233c-.457.778.091 1.767.98 1.767h13.713c.889 0 1.438-.99.98-1.767L8.982 1.566z"/>
									<path d="M7.002 12a1 1 0 1 1 2 0 1 1 0 0 1-2 0zM7.1 5.995a.905.905 0 1 1 1.8 0l-.35 3.507a.552.552 0 0 1-1.1 0L7.1 5.995z"/>
								</svg>	
								Des documents douaniers sont nécéssaires pour cette commande
							</h2>
							<div class="d-flex justify-content-between mt-3 w-100">
								<img src="assets{{ ('/images/icons/switzerland.png') }}" width="45" alt="" />
								<button type="button" class="btn btn-dark px-5" data-bs-dismiss="modal">Fermer</button>
								<img src="assets{{ ('/images/icons/switzerland.png') }}" width="45" alt="" />
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
			  <div data-bs-keyboard="false" data-bs-backdrop="static" class="modal_radius generate_label_modal modal fade" id="generateLabelModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
					<div class="modal-dialog modal-dialog-centered" role="document">
						<div class="modal-content">
							<div class="modal-body">	
								<form class="h-100" method="POST" action="{{ route('label.generate') }}">
									@csrf
									<input id="order_id_label" type="hidden" name="order_id" value="">
									<input id="from_dolibarr" type="hidden" name="from_dolibarr" value="">
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
											<button  onclick="this.disabled=true;this.form.submit();" style="margin-left:15px" type="submit" class="valid_generate_label btn btn-dark px-5">Générer</button>
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
		<script src="{{asset('assets/plugins/select2/js/select2.min.js')}}"></script>
		<script src="{{asset('assets/js/label.js')}}"></script>
	@endsection


