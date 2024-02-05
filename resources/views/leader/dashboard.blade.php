@extends("layouts.app")

		@section("style")
			<link href="{{asset('assets/plugins/datatable/css/dataTables.bootstrap5.min.css')}}" rel="stylesheet" />
			<link href="assets/plugins/select2/css/select2.min.css" rel="stylesheet" />
			<link href="assets/plugins/select2/css/select2-bootstrap4.css" rel="stylesheet" />
			<link href="assets/css/pace.min.css" rel="stylesheet" />
		@endsection 

		@section("wrapper")
			<div class="page-wrapper">
				<div class="page-content">
					<div class="page-breadcrumb d-sm-flex align-items-center mb-2 justify-content-between">
						<div class="d-flex flex-wrap justify-content-center">
							<div class="breadcrumb-title pe-3">Commandes</div>
							<div class="pe-3 number_order_pending"></div>
						</div>
						@csrf
						<button style="height:35px" disabled type="button" class="allocation_of_orders btn btn-dark px-5 p-0">Gérer les commandes</button>
					</div>

					<div class="dashboard_leader row row-cols-1 row-cols-lg-2">
				   		<div class="team_board col flex-column d-flex col-lg-4">
							<div class="card radius-10 w-100 h-100">
								<div class="header_title d-flex align-items-center">
									<div>
										<h5 class="mb-0">Équipes</h5>
									</div>
								</div>
								<div class="card-body">
									<!-- Liste des utilisateurs et leur rôle -->
									<div class="p-3 mb-3 ps ps--active-y role_list">
										@foreach($teams as $key => $team)
											<div class="flex-wrap customers-list-item d-flex align-items-center border-top p-2 cursor-pointer">
												<div class="">
													<img src="{{ $team['picture'] ? 'storage/app/images/'.$team['picture'] : 'assets/images/avatars/default_avatar.png' }}" class="rounded-circle" width="46" height="46" alt="">
												</div>
												<div class="ms-2">
													<h6 class="mb-1 font-14">
														@if(Auth()->user()->id == $team['user_id'])
															{{ $team['name'] }}<strong> (Moi)</strong>
														@else 
															{{ $team['name'] }}
														@endif
													</h6>
													<p class="mb-0 font-13 text-secondary">{{ $team['email'] }}</p>
												</div>
												<div class="list-inline d-flex flex-wrap align-items-center list_role_user customers-contacts ms-auto">	
													@foreach($roles as $role)
														@if($role['id'] != 1 && $role['id'] != 4)
															@if(in_array($role['id'], $team['role_id']))
																<span class="badge" style="background-color:{{ $role['color'] }}">{{ $role['role'] }}</span>
															@endif
														@endif
													@endforeach
												</div>
											</div>
										@endforeach
									</div>
								</div>
							</div>
							<div class="card radius-10 w-100 h-100">
									<div class="header_title d-flex align-items-center">
										<div>
											<h5 class="mb-0">Réatribuer des commandes</h5>
										</div>
									</div>
									<div class="card-body">
										
										<!-- Réatribution des commandes d'un user vers un autre -->
										<div class="p-3 mb-3 ps ps--active-y role_list">
										@if($number_preparateur > 1)
											@foreach($teams_have_order as $key => $team)
												<div class="justify-content-between flex-wrap customers-list-item d-flex align-items-center border-top {{ $key == count($teams) - 1 ? 'border-bottom' : '' }} p-2 cursor-pointer">
													<div class="d-flex align-items-center">
														<img src="assets/images/avatars/default_avatar.png" class="rounded-circle" width="46" height="46" alt="">
														<div class="ms-2">
															<h6 id="team_user_{{ $team['id'] }}" class="mb-1 font-14">
																@if(Auth()->user()->id == $team['id'])
																	{{ $team['name'] }}<strong> (Moi)</strong>
																@else 
																	{{ $team['name'] }}
																@endif
															</h6>
														</div>
													</div>
													
													<div class="font-22">	
														<i class="lni lni-arrow-right"></i>
													</div>
													<div class="list-inline d-flex align-items-center customers-contacts">	
														<select id="attribution_{{ $team['id'] }}" class="select_user change_attribution_order">
															<option value="">Réatribution</option>
															@foreach($teams as $key => $team2)
																@if($team['id'] != $team2['user_id'] && in_array(2, $team2['role_id']))
																	<option id="user_name_{{ $team2['user_id'] }}" value="{{ $team2['user_id'] }}">{{  $team2['name']  }}</option>
																@endif
															@endforeach
														</select>
													</div>
												</div>
											@endforeach
										@endif
									</div>
								</div>

								<!-- Modal de confirmation de changement de rôle -->
								<!-- <div class="modal fade" id="valid_change_user_role" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
									<div class="modal-dialog modal-dialog-centered" role="document">
										<div class="modal-content">
										<div class="modal-body d-flex flex-column justify-content-center">
											<h2 class="text-center">Changer le rôle de cet utilisateur ?</h2>
											<div class="w-100 d-flex justify-content-center">
												<input type="hidden" class="user_role_id" value="">
												<button type="button" class="change_user_role_button btn btn-dark px-5 ">Oui</button>
												<button style="margin-left:15px" type="button" class="cancel_user_role_button btn btn-dark px-5" data-bs-dismiss="modal">Non</button>
											</div>
										</div>
										</div>
									</div>
								</div> -->


								<!-- Modal de confirmation de réatribution de commandes à un user -->
								<div class="modal fade modal_radius" id="reallocationOrders" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
									<div class="modal-dialog modal-dialog-centered" role="document">
										<div class="modal-content">
											<div class="modal-body">
												<h2 class="text-center reallocationOrdersTitle">Voulez-vous réatribuer les commandes de 
													<strong id="from_user"></strong> à <strong id="to_user"></strong>
												</h2>
												<input type="hidden" class="from_to_user" value="">
												<div class="w-100 d-flex justify-content-center">
													<div class="d-none spinner-border loading_realocation" role="status"> 
														<span class="visually-hidden">Loading...</span>
													</div>
													<button type="button" class="btn btn-dark px-5" data-bs-dismiss="modal">Non</button>
													<button style="margin-left:15px" type="button" class="reallocationOrdersConfirm btn btn-dark px-5 ">Oui</button>
												</div>
											</div>
										</div>
									</div>
								</div>

							</div>
						</div>
						<div class="col d-flex col-lg-8">
								<div class="card card_table_mobile_responsive radius-10 w-100">
									<div class="header_title hide_mobile d-flex align-items-center">
										<div class="w-100 d-flex justify-content-between">
											<h5 style="width:180px">Commande</h5>
											<h5>Attribution</h5>
											<h5>Date</h5>
											<h5>État</h5>
											<h5>Total</h5>
											<h5>Détail</h5>
										</div>
									</div>
									<div class="mobile_padding card-body mt-2 p-0">
										<div class="table-responsive">
											<!-- chronopost -->
											<select class="d-none select2_custom shipping_dropdown input_form_type">
												<option value="">Chronopost</option>
												<option value="chrono">Oui</option>
												<option value="classic">Non</option>
											</select>

											<!-- users -->
											<select class="d-none select2_custom preparateur_dropdown input_form_type">
												<option value="">Préparateur</option>
												@foreach($teams_have_order as $prepa)
													<option value="{{ $prepa['id'] }}">{{  $prepa['name']  }}</option>
												@endforeach
											</select>

											<!-- country -->
											<select class="d-none select2_custom country_dropdown input_form_type">
												<option value="">Expédition</option>
												<option value="CH">Suisse</option>
												<option value="BE">Belgique</option>
												<option value="FR">France</option>
												<option value="LU">Luxembourg</option>
											</select>

											<select class="d-none select2_custom status_dropdown input_form_type">
												<option value="">Status</option>
												<option value="processing">En cours</option>
												<option value="prepared-order">Commande préparée</option>
												<option value="waiting_to_validate">En attente de validation</option>
												<option value="waiting_validate">En attente validée</option>
												<option value="order-new-distrib">Commande futur distributeur</option>
												<option value="en-attente-de-pai">En attente de paiement distributeur</option>
											</select>

											<table id="example" class="loading_table_content w-100 table_list_order table_mobile_responsive table table-striped table-bordered">
												<thead>
													<tr>
														<th scope="col">Commande</th>
														<th scope="col">Attribution</th>
														<th scope="col">Date</th>
														<th scope="col">État</th>
														<th scope="col">Total</th>
														<th class="col-md-1" scope="col">Détail</th>
														<th class="d-none col-md-1" scope="col">Shipping</th>
														<th class="d-none col-md-1" scope="col">Status</th>
														<th class="d-none col-md-1" scope="col">Préparateur</th>
													</tr>
												</thead>
												<tbody></tbody>
												<tbody>
													@for($i = 0; $i < 9; $i++)
														<tr class="loading_table">
															<td class="td-3"><span></span></td>
															<td class="td-3"><span></span></td>
															<td class="td-3"><span></span></td>
															<td class="td-3"><span></span></td>
															<td class="td-3"><span></span></td>
															<td class="td-3"><span></span></td>
														</tr>
													@endfor
												</tbody>
											</table>
										</div>

									</div>
								</div>

								<!-- Modal pour lancer l'attribution des commandes -->
								<div class="modal fade modal_radius" id="allocationOrders" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
									<div class="modal-dialog modal-dialog-centered" role="document">
										<div class="modal-content">
											<div style="padding: 7px; position: absolute;" class="d-flex w-100 justify-content-end">
												<i style="z-index:10;cursor:pointer;font-size:24px;" data-bs-dismiss="modal" class="lni lni-close"></i>
											</div>	
											<div class="modal-body">
												<h2 class="text-center allocationOrdersTitle">Que souhaitez-vous faire ?</h2>
												<div class="w-100 d-flex justify-content-center">
													<div class="d-none spinner-border loading_allocation" role="status"> 
														<span class="visually-hidden">Loading...</span>
													</div>
													<!-- <button type="button" class="btn btn-dark px-5" data-bs-dismiss="modal">Annuler</button> -->
													<button style="margin-left:15px" type="button" class="mt-3 allocationOrdersConfirm btn btn-dark px-5 ">Attribuer</button>
													<button style="margin-left:15px" type="button" class="mt-3 unassignOrdersConfirm btn btn-dark px-5 ">Désattribuer</button>

													<i style="font-size:50px" class="d-none text-success lni lni-checkmark-circle"></i>
												</div>
											</div>
										</div>
									</div>
								</div>


								<!-- Modal confirmation supression produit commande Woocommerce -->
								<div class="modal fade modal_backfrop_fixe" id="deleteProductOrderModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
									<div class="modal-dialog modal-dialog-centered" role="document">
										<div class="modal-content">
											<div class="modal-body">
												<h2 class="text-center">Supprimer ce produit de la commande ?</h2>
												<span class="d-flex justify-content-center text-danger w-100 mb-2 product_name_to_delete"></span>
												<input type="hidden" id="order_id" value="">
												<input type="hidden" id="line_item_id"value="">
												<input type="hidden" id="product_order_id"value="">
												<input type="hidden" id="quantity_order"value="">
													<div class="d-none loading_delete d-flex w-100 justify-content-center">
														<div class="spinner-border" role="status"> 
															<span class="visually-hidden">Loading...</span>
														</div>
													</div>
												<div class="delete_modal w-100 d-flex justify-content-center flex-column">
													<button onclick="deleteProductOrderConfirm(1)" type="button" class="bg-danger border-danger mb-2 btn btn-dark px-5 ">Oui et remettre en stock</button>
													<button onclick="deleteProductOrderConfirm(0)" type="button" class="bg-danger border-danger mb-2 btn btn-dark px-5 ">Oui sans remettre en stock</button>
													<button type="button" class="btn btn-dark px-5" data-bs-dismiss="modal">Annuler</button>
												</div>
											</div>
										</div>
									</div>
								</div>

								<!-- Modal confirmation supression produit commande Dolibarr-->
								<div class="modal fade modal_backfrop_fixe" id="deleteProductOrderDolibarrModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
									<div class="modal-dialog modal-dialog-centered" role="document">
										<div class="modal-content">
											<div class="modal-body">
												<h2 class="text-center">Supprimer ce produit de la commande ?</h2>
												<span class="text-center d-flex justify-content-center text-danger w-100 mb-2 product_dolibarr_name_to_delete"></span>
												<input type="hidden" id="order_id_dolibarr" value="">
												<input type="hidden" id="product_dolibarr_id" value="">
												<input type="hidden" id="product_order_id_dolibarr"value="">
												<div class="w-100">
													<label>Quantité :</label>
													<input class="custom_input w-100 mb-3" type="number" id="quantity_order_dolibarr"value="1">
												</div>
													<div class="d-none loading_delete d-flex w-100 justify-content-center">
														<div class="spinner-border" role="status"> 
															<span class="visually-hidden">Loading...</span>
														</div>
													</div>
												<div class="delete_modal w-100 d-flex justify-content-center flex-column">
													<button onclick="deleteProductOrderDolibarrConfirm()" type="button" class="bg-danger border-danger mb-2 btn btn-dark px-5 ">Valider</button>
													<button type="button" class="btn btn-dark px-5" data-bs-dismiss="modal">Annuler</button>
												</div>
											</div>
										</div>
									</div>
								</div>

								<!-- Modal ajout de produits -->
								<div class="modal fade modal_backfrop_fixe" id="addProductOrderModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
									<div class="modal-dialog modal-dialog-centered" role="document">
										<div class="modal-content">
											<div class="modal-body">
												<h2 class="mb-3 text-center">Choisissez le produit à ajouter</h2>
													<input type="hidden" value="" id="order_id_add_product">
													<div class="d-flex justify-content-between flex-wrap">
														<select name="products" class="list_product_to_add mb-3">
															@foreach($products as $product)
																<option value="{{ $product['product_woocommerce_id'] }}">{{ $product['name'] }}</option>
															@endforeach
															<input id="quantity_product" style="width:50px" type="number" value="1">
														</select>
													</div>
													<div class="d-none loading_add d-flex w-100 justify-content-center mt-3">
														<div class="spinner-border" role="status"> 
															<span class="visually-hidden">Loading...</span>
														</div>
													</div>
												<div class="w-100 add_modal d-flex justify-content-center mt-3">
													<button type="button" class="btn btn-dark px-5" data-bs-dismiss="modal">Annuler</button>
													<button onclick="addProductOrderConfirm()" style="margin-left:15px" type="button" class="btn btn-dark px-5 ">Ajouter</button>
												</div>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
		@endsection

	
		@section("script")
			<script src="assets/js/pace.min.js"></script>
			<script src="{{asset('assets/plugins/datatable/js/jquery.dataTables.min.js')}}"></script>
			<script src="{{asset('assets/plugins/datatable/js/dataTables.bootstrap5.min.js')}}"></script>
			<script src="assets/plugins/select2/js/select2.min.js"></script>
			<script src="assets/js/orders.js"></script>
		
		@endsection

