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
						<div class="d-flex flex-wrap justify-content-center">
							<div class="breadcrumb-title pe-3">Commandes</div>
							<div class="pe-3 number_order_pending"></div>
						</div>
						@csrf
						<button style="height:35px" disabled type="button" class="allocation_of_orders btn btn-dark px-5 p-0">Attribuer les commandes</button>
					</div>

					<div class="dashboard_leader row row-cols-1 row-cols-lg-2">
				   		<div class="team_board col flex-column d-flex col-lg-4">
							<div class="card radius-10 w-100 h-100">
								<div class="card-body">
									<div class="d-flex align-items-center">
										<div>
											<h5 class="mb-0">Équipes</h5>
										</div>
									</div>

									<!-- Liste des utilisateurs et leur rôle -->
									<div class="p-3 mb-3 ps ps--active-y">
										@foreach($teams as $key => $team)
											<div class="flex-wrap customers-list-item d-flex align-items-center border-top {{ $key == count($teams) - 1 ? 'border-bottom' : '' }} p-2 cursor-pointer">
												<div class="">
													<img src="assets/images/avatars/default_avatar.png" class="rounded-circle" width="46" height="46" alt="">
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
								<div class="card-body">
									<div class="d-flex align-items-center">
										<div>
											<h5 class="mb-0">Réatribuer des commandes</h5>
										</div>
									</div>

										<!-- Réatribution des commandes d'un user vers un autre -->
										<div class="p-3 mb-3 ps ps--active-y">
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
								<div class="modal fade" id="reallocationOrders" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
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
									<div class="card-body">
										<div class="d-flex align-items-center">
											<div>
												<h5 class="mb-4">Commandes <span class="text-success total_amount"></span></h5>
											</div>
										</div>
										<div class="table-responsive">

											<table id="example" class="w-100 table_list_order table_mobile_responsive table table-striped table-bordered">
												<thead>
													<tr>
														<th scope="col">Commande</th>
														<th scope="col">Attribution</th>
														<th scope="col">Date</th>
														<th scope="col">État</th>
														<th scope="col">Total</th>
														<th class="col-md-1" scope="col">Détail</th>
													</tr>
												</thead>
											</table>
										</div>

									</div>
								</div>

								<!-- Modal pour lancer l'attribution des commandes -->
								<div class="modal fade" id="allocationOrders" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
									<div class="modal-dialog modal-dialog-centered" role="document">
										<div class="modal-content">
											<div class="modal-body">
												<h2 class="text-center allocationOrdersTitle">Attribuer les commandes entre les préparateurs ?</h2>
												<div class="w-100 d-flex justify-content-center">
													<div class="d-none spinner-border loading_allocation" role="status"> 
														<span class="visually-hidden">Loading...</span>
													</div>
													<button type="button" class="btn btn-dark px-5" data-bs-dismiss="modal">Annuler</button>
													<button style="margin-left:15px" type="button" class="allocationOrdersConfirm btn btn-dark px-5 ">Lancer</button>
													<i style="font-size:50px" class="d-none text-success lni lni-checkmark-circle"></i>
												</div>
											</div>
										</div>
									</div>
								</div>


								<!-- Modal confirmation supression produit commande -->
								<div class="modal fade modal_backfrop_fixe" id="deleteProductOrderModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
									<div class="modal-dialog modal-dialog-centered" role="document">
										<div class="modal-content">
											<div class="modal-body">
												<h2 class="text-center">Supprimer ce produit de la commande ?</h2>
												<input type="hidden" id="order_id" value="">
												<input type="hidden" id="line_item_id"value="">
													<div class="d-none loading_delete d-flex w-100 justify-content-center">
														<div class="spinner-border" role="status"> 
															<span class="visually-hidden">Loading...</span>
														</div>
													</div>
												<div class="delete_modal w-100 d-flex justify-content-center">
													<button type="button" class="btn btn-dark px-5" data-bs-dismiss="modal">Annuler</button>
													<button onclick="deleteProductOrderConfirm()" style="margin-left:15px" type="button" class="btn btn-dark px-5 ">Oui</button>
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
												<h2 class="mb-3 text-center">Choisissez le produits à ajouter</h2>
													<input type="hidden" value="" id="order_id_add_product">
													<div class="d-flex justify-content-between">
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
		@endsection

	
		@section("script")

		<script src="{{asset('assets/plugins/datatable/js/jquery.dataTables.min.js')}}"></script>
		<script src="{{asset('assets/plugins/datatable/js/dataTables.bootstrap5.min.js')}}"></script>
		<script src="assets/plugins/select2/js/select2.min.js"></script>

		<script>
			
			

			$(document).ready(function() {
				$(".list_product_to_add").select2({width: "350px", dropdownParent: $("#addProductOrderModal")})
				// Sélection de la div
				const paceProgress = document.querySelector('.pace-progress');

				// Configuration de l'observer
				const observerConfig = {
					attributes: true,
					attributeFilter: ['data-progress']
				};

				// Fonction de callback de l'observer
				const observerCallback = function(mutationsList) {
					for (let mutation of mutationsList) {
						if (mutation.type === 'attributes' && mutation.attributeName === 'data-progress') {
							$(".percent").remove()

							if(mutation.target.getAttribute('data-progress') != 99){
								$(".number_order_pending").append('<span class="percent">'+mutation.target.getAttribute('data-progress')+' %</span>')
							}

						}
					}
				};

				// Création de l'observer
				const observer = new MutationObserver(observerCallback);
				// Démarrage de l'observer
				observer.observe(paceProgress, observerConfig);


				const options = {
					year: 'numeric',
					month: 'long',
					day: 'numeric',
					hour: 'numeric',
					minute: 'numeric',
					hour12: false,
					timeZone: 'Europe/Paris'
                };
				var to = 0


				$('#example').DataTable({
					scrollY: '59vh',
        			scrollCollapse: true,
					order: [ 0, 'asc' ],
					ajax: {
						url: '{{ route("leader.getAllOrders") }}',
						dataSrc: function(json) {

							// Récupérer les données des commandes (orders)
							var orders = json.orders;
							// Récupère la liste des produits déjà pick
							var products_pick = json.products_pick
							// Récupérer les données des utilisateurs (users)
							var users = json.users;
							// Combiner les données des commandes (orders) et des utilisateurs (users)
							var combinedData = orders.map(function(order) {

								return {
									id: order.id,
									first_name: order.billing.first_name,
									last_name: order.billing.last_name,
									total: order.total,
									total_tax: order.total_tax,
									name: order.name,
									status: order.status,
									status_text: order.status_text ?? 'En cours',
									date_created: order.date_created,
									line_items: order.line_items,
									user_id: order.user_id,
									users: users,
									products_pick: products_pick
								};
							});

							return combinedData;
						}
					},
				
					columns: [
						{ 
						data: null,
							render: function(data, type, row) {
								return "#"+row.id+' '+row.first_name + ' ' + row.last_name;
							}
            			},
						{data: null,
							render: function(data, type, row) {
								var selectOptions = '<option selected>Non attribuée</option>';
							
								Object.entries(row.users).forEach(([key, value]) => {
									if(value.user_id == row.user_id){
										selectOptions += `<option selected value="${value.user_id}">${value.name}</option>`;
									} else {
										selectOptions += `<option value="${value.user_id}">${value.name}</option>`;
									}

								})
								
								var selectHtml = `<select onchange="changeOneOrderAttribution(${row.id})" id="select_${row.id}" class="order_attribution select_user">${selectOptions}</select>`;

								if($("#select_"+row.id).val() == "Non attribuée"){
									$("#select_"+row.id).addClass('empty_select')
								} else {
									$("#select_"+row.id).addClass('no_empty_select')
								}
								
								return selectHtml;
							}
						},
						{data: null,
							render: function(data, type, row) {
								const date = new Date(row.date_created);
                                const dateEnFrancais = date.toLocaleString('fr-FR', options);
								return dateEnFrancais
							}
            			},
						{data: null,
							render: function(data, type, row) {

								if(row.status == "waiting_to_validate"){
									var selectOptions = '<option selected value="'+row.status+'">'+row.status_text+'</option>';
									selectOptions += `<option value="waiting_validate">En cours</option>`;
									var selectHtml = `<select onchange="changeStatusOrder(${row.id})" id="selectStatus_${row.id}" class="select_user empty_select">${selectOptions}</select>`;

									return selectHtml;
								} else {
									return `
									<div class="badge rounded-pill bg-light-`+row.status+` p-2 text-uppercase px-3">
										<i class="bx bxs-circle align-middle me-1"></i>`+row.status_text+`
									</div>`;
								}
								
							}
            			},
						{data: null,
							render: function(data, type, row) {
								return `
									<div class="w-100 d-flex flex-column">
										<span>Total (HT): <strong>` +parseFloat(row.total -row.total_tax).toFixed(2)+`</strong></span>
										<span>TVA: <strong>` +row.total_tax+`</strong></span>
										<span>Total (TTC): <strong>` +row.total+`</strong></span>
									</div>`;
							}
            			},
						{data: null,
							render: function(data, type, row) {
								var id = []
								Object.entries(row.products_pick).forEach(([key, value]) => {
									if (value.order_id == row.id){
										row.pick_items = true
										id.push(value.product_woocommerce_id) 
									} 
								}) 

								return `
									<div class="modal_order_admin modal_order modal fade" id="order_`+row.id+`" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
										<div class="modal-dialog modal-dialog-centered" role="document">
											<div class="modal-content">
												<div class="modal-body detail_product_order">
													<div class="detail_product_order_head d-flex flex-column">
														<div class="p-1 mb-2 head_detail_product_order d-flex w-100 justify-content-between">
															<span class="column1 name_column">Article</span>
															<span class="column2 name_column">Coût</span>
															<span class="column3 name_column">Qté</span>
															<span class="column4 name_column">Total</span>
															<span class="column5 name_column">Action</span>

														</div>	

														<div class="body_detail_product_order">
															${row.line_items.map((element) => 
																`
																<div class="${row.id}_${element.id} ${id.includes(element.product_id) || id.includes(element.variation_id) ? 'pick' : ''} d-flex w-100 align-items-center justify-content-between detail_product_order_line">
																	<div class="column11 d-flex align-items-center detail_product_name_order">
																		${element.price == 0 ? `<span><span class="text-success">(Cadeau)</span> `+element.name+`</span>` : `<span>`+element.name+`</span>`}
																	</div>
																	<span class="column22">	`+parseFloat(element.price).toFixed(2)+ `</span>
																	<span class="column33"> `+element.quantity+` </span>
																	<span class="column44">`+parseFloat(element.price * element.quantity).toFixed(2)+`</span>
																	<span class="column55"><i onclick="deleteProduct(`+row.id+`,`+element.id+`)" class="edit_order bx bx-trash"></i></span>

																</div>`
														).join('')}
														</div>
														<div class="close_modal align-items-end mt-2 d-flex justify-content-between"> 
															<button type="button" data-order=`+row.id+` class="add_product_order btn btn-dark px-5" >Ajouter un produit</button>
															<div class="d-flex flex-column">
																<span class="mt-1 mb-2 montant_toltal_order">Total: `+row.total+`€</span>
																<button type="button" class="btn btn-dark px-5" data-bs-dismiss="modal">Fermer</button>
															</div>
														</div>
													</div>

												</div>
											</div>
										</div>
									</div>
									<i onclick="show(`+row.id+`)" ontouchstart="show(`+row.id+`)" class="show_detail bx bx-comment-detail"></i>
									`;
							}
            			},
					],

					"initComplete": function(settings, json) {

						var info = $('#example').DataTable().page.info();
						var total = 0
						var attribution = 0
						var order_progress = 0

						// Calcul total valeur des commandes
						$('#example').DataTable().rows().eq(0).each( function ( index ) {
							var row = $('#example').DataTable().row( index );
							var data = row.data();
							total = parseFloat(total) + parseFloat(data.total)
						} );

						// Check nombre attribution
						$('#example').DataTable().rows().eq(0).each( function ( index ) {
							var row = $('#example').DataTable().row( index );
							var data = row.data();
							data.name != "Non attribuée" ? attribution = attribution + 1 : attribution = attribution
							data.status == "processing" ? order_progress = order_progress + 1 : order_progress = order_progress 
						} );
						
						$(".number_order_pending").append('<span>'+info.recordsTotal+' dont <span id="number_attribution">'+attribution+'</span> attribuée(s) - '+order_progress+' en cours</span>')
						$(".total_amount").append('('+parseFloat(total).toFixed(2)+'€ )')
						$(".allocation_of_orders").attr('disabled', false)
						$(".dataTables_paginate").parent().removeClass("col-md-7")
					},

					"fnRowCallback": function(nRow, aData, iDisplayIndex, iDisplayIndexFull) {
						var selectElements = nRow.getElementsByClassName('order_attribution');
						for (var i = 0; i < selectElements.length; i++) {
							var select = selectElements[i];

							if (select.value != 'Non attribuée') {
								select.classList.add('no_empty_select');
								// select.classList.remove('no_empty_select');
							} else {
								select.classList.add('empty_select');
								// select.classList.remove('empty_select');
							}
						}
							
						$('td:nth-child(1)', nRow).attr('data-label', 'Commande');
						$('td:nth-child(2)', nRow).attr('data-label', 'Attribution');
						$('td:nth-child(3)', nRow).attr('data-label', 'Date');
						$('td:nth-child(4)', nRow).attr('data-label', 'État');
						$('td:nth-child(5)', nRow).attr('data-label', 'Total');
						$('td:nth-child(6)', nRow).attr('data-label', 'Détail');

						return nRow;

					}
				})

				if($(window).width() < 650){
					$(".dataTables_scrollBody").css('max-height', '100%')
				}

				$(window).resize(function(){
					if($(window).width() < 650){
						$(".dataTables_scrollBody").css('max-height', '100%')
					} else {
						$(".dataTables_scrollBody").css('max-height', '59vh')
					}
				})
			})


			$(".allocation_of_orders").on("click", function(){
				$('#allocationOrders').modal({
					backdrop: 'static',
					keyboard: false
				})
				$("#allocationOrders").modal('show')
			})

			$(".allocationOrdersConfirm").on("click", function(){
				
				$("#allocationOrders button").addClass('d-none')
				$(".loading_allocation").removeClass("d-none")

				$.ajax({
					url: "{{ route('distributionOrders') }}",
					method: 'GET',
				}).done(function(data) {
					if(JSON.parse(data).success){
						$(".loading_allocation").addClass("d-none")
						$(".lni-checkmark-circle").removeClass('d-none')
						$(".allocationOrdersTitle").text("Commandes réparties avec succès !")
						setTimeout(function(){ location.reload(); }, 3000);
					} else {
						alert(JSON.parse(data).message ?? 'Erreur !')
						$("#allocationOrders button").removeClass('d-none')
						$(".loading_allocation").addClass("d-none")
						$("#allocationOrders").modal('hide')
					}
				});
			})

			$(".change_attribution_order").on("change", function(){
				if($(this).val() != ""){
					var from_user = $(this).attr('id').split('_')[1]
					var to_user = $(this).val()
					var to_user_text = $("#user_name_"+$(this).val()).text()
					
					$(".from_to_user").val(from_user+','+to_user)
					$("#from_user").text($("#team_user_"+from_user).text())
					$("#to_user").text(to_user_text)
					$("#reallocationOrders").modal('show')
				}
			})

			$(".reallocationOrdersConfirm").on("click", function(){
				var from_user = $(".from_to_user").val().split(',')[0]
				var to_user = $(".from_to_user").val().split(',')[1]


				$(".loading_realocation").removeClass('d-none')
				$("#reallocationOrders button").addClass('d-none')

				$.ajax({
					url: "{{ route('updateAttributionOrder') }}",
					method: 'POST',
					data: {_token: $('input[name=_token]').val(), from_user: from_user, to_user: to_user}
				}).done(function(data) {
					if(JSON.parse(data).success){
						$('#example').DataTable().ajax.reload();
						$(".loading_realocation").addClass('d-none')
						$("#reallocationOrders button").removeClass('d-none')
						$("#reallocationOrders").modal('hide')
					} else {
						alert('Erreur !')
					}
				});

			})


			$('body').on('click', '.add_product_order', function() {
				$("#order_id_add_product").val($(this).attr('data-order'))
				$('#addProductOrderModal').modal({
					backdrop: 'static',
					keyboard: false
				})
				$("#addProductOrderModal").modal('show')
			})

			function addProductOrderConfirm(){
				$(".loading_add").removeClass('d-none')
				$(".add_modal").addClass('d-none')

				var product = $(".list_product_to_add").val()
				var order_id = $("#order_id_add_product").val()
				var quantity = $("#quantity_product").val()

				$.ajax({
					url: "{{ route('addOrderProducts') }}",
					method: 'POST',
					data: {_token: $('input[name=_token]').val(), order_id: order_id, product: product, quantity: quantity}
				}).done(function(data) {
					if(JSON.parse(data).success){

						var order_id = JSON.parse(data).order.id
						var line_items = JSON.parse(data).order.line_items
						var last_line_items = JSON.parse(data).order.line_items[line_items.length - 1]
						
						$("#order_"+order_id+" .body_detail_product_order").append(`
							<div class="`+order_id+`_`+last_line_items.id+`  d-flex w-100 align-items-center justify-content-between detail_product_order_line">
								<div class="column11 d-flex align-items-center detail_product_name_order">
									<span>`+last_line_items.name+`</span>
								</div>
								<span class="column22">`+last_line_items.price+`</span>
								<span class="column33"> `+last_line_items.quantity+` </span>
								<span class="column44">`+last_line_items.subtotal+`</span>
								<span class="column55"><i onclick="deleteProduct(`+order_id+`,`+last_line_items.id+`)" class="edit_order bx bx-trash"></i></span>
							</div>`
						)

						$("#order_"+order_id+" .montant_toltal_order").text('Total: '+JSON.parse(data).order.total)
						$("#addProductOrderModal").modal('hide')
					} else {
						alert('Erreur !')
					}
					$(".loading_add").addClass('d-none')
					$(".add_modal").removeClass('d-none')
				});
				
			}
				

			function show(id){
				$('#order_'+id).modal({
					backdrop: false,
					keyboard: false
				})

				$("#order_"+id).modal('show')
			}

			function deleteProduct(order_id, line_item_id){
				$("#order_id").val(order_id)
				$("#line_item_id").val(line_item_id)
				$("#deleteProductOrderModal").modal('show')
			}

			function deleteProductOrderConfirm(){
				$(".loading_delete").removeClass('d-none')
				$(".delete_modal").addClass('d-none')
				var order_id = $("#order_id").val()
				var line_item_id = $("#line_item_id").val()

				$.ajax({
					url: "{{ route('deleteOrderProducts') }}",
					method: 'POST',
					data: {_token: $('input[name=_token]').val(), order_id: order_id, line_item_id: line_item_id}
				}).done(function(data) {
					if(JSON.parse(data).success){
						$("#order_"+order_id+" .montant_toltal_order").text('Total: '+JSON.parse(data).order.total)
						$('.'+order_id+'_'+line_item_id).fadeOut()
						$('.'+order_id+'_'+line_item_id).remove()
						$(".loading_delete").addClass('d-none')
					} else {
						alert('Erreur !')
					}
					$(".delete_modal").removeClass('d-none')
					$("#deleteProductOrderModal").modal('hide')
				});
			}

			function changeStatusOrder(order_id){
				var order_id = order_id
				var status = $("#selectStatus_"+order_id).val()

				$.ajax({
					url: "{{ route('updateOrderStatus') }}",
					method: 'POST',
					data: {_token: $('input[name=_token]').val(), order_id: order_id, status: status}
				}).done(function(data) {
					if(JSON.parse(data).success){
						$("#selectStatus_"+order_id).removeClass('empty_select')
						$("#selectStatus_"+order_id).addClass('no_empty_select')
					} else {
						alert('Erreur !')
					}
				});

			}

			function changeOneOrderAttribution(order_id){
				var order_id = order_id
				var user_id = $("#select_"+order_id).val()

				if(user_id == "Non attribuée"){
					$("#select_"+order_id).addClass('empty_select')
					$("#select_"+order_id).removeClass('no_empty_select')
				} else {
					$("#select_"+order_id).removeClass('empty_select')
					$("#select_"+order_id).removeClass('no_empty_select')
					$("#select_"+order_id).addClass('no_empty_select')
				}

				$.ajax({
					url: "{{ route('updateOneOrderAttribution') }}",
					method: 'POST',
					data: {_token: $('input[name=_token]').val(), order_id: order_id, user_id: user_id}
				}).done(function(data) {
					if(JSON.parse(data).success == true){
						$("#number_attribution").text(JSON.parse(data).number_order_attributed)
					} else {
						alert(JSON.parse(data).success)
					}
				});

			}
        
        </script>
	@endsection

