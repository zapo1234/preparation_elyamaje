@extends("layouts.app")

		@section("style")
			<link href="{{asset('assets/plugins/datatable/css/dataTables.bootstrap5.min.css')}}" rel="stylesheet" />
		@endsection

		@section("wrapper")
			<div class="page-wrapper">
				<div class="page-content">
					<div class="page-breadcrumb d-sm-flex align-items-center mb-2">
						<div class="breadcrumb-title pe-3">Commandes en préparation</div>
						<div class="pe-3 number_order_pending"></div>

					</div>
					<div class="row">
						<div class="card card_table_mobile_responsive">
							<div class="card-body">
								<div class="table-responsive">

									<table id="example" class="w-100 table_mobile_responsive table table-striped table-bordered">
										<thead>
											<tr>
												<th scope="col">Commande</th>
												<th scope="col">Date</th>
												<th scope="col">État</th>
												<th scope="col">Total</th>
												<th class="col-md-1" scope="col">Action</th>
											</tr>
										</thead>
									</table>
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

		<script>
			$(document).ready(function() {

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
					order: [ 1, 'asc' ],
					ajax: {
						url: '{{ route("getAllOrders") }}',
						dataSrc: '',
					},
				
					columns: [
						{ 
						data: null,
							render: function(data, type, row) {
								return "#"+row.id+' '+row.billing.first_name + ' ' + row.billing.last_name;
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
								return `
									<div class="badge rounded-pill text-success bg-light-success p-2 text-uppercase px-3">
										<i class="bx bxs-circle align-middle me-1"></i>En cours
									</div>`;
							}
            			},
						{ data: 'total' },
						{data: null,
							render: function(data, type, row) {
							
								return `
									<div class="modal_order modal fade" id="order_`+row.id+`" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
										<div class="modal-dialog modal-dialog-centered" role="document">
											<div class="modal-content">
												<div class="modal-body detail_product_order">
													<div class="detail_product_order_head d-flex flex-column">
														<div class="p-1 mb-2 head_detail_product_order d-flex w-100 justify-content-between">
															<span class="name_column">Article</span>
															<span class="name_column">Coût</span>
															<span class="name_column">Qté</span>
															<span class="name_column">Total</span>
															<span class="name_column">TVA</span>
														</div>	

														<div class="body_detail_product_order">
															${row.line_items.map((element) => `
																<div class="p-2 d-flex w-100 align-items-center justify-content-between detail_product_order_line">
																	<div class="d-flex align-items-center detail_product_name_order">
																		${element.price == 0 ? `<span><span class="text-success">(Cadeau)</span> `+element.name+`</span>` : `<span>`+element.name+`</span>`}
																	</div>
																	<span>	`+parseFloat(element.price).toFixed(2)+ `</span>
																	<span> `+element.quantity+` </span>
																	<span>`+parseFloat(element.price * element.quantity).toFixed(2)+`</span>
																	<span>` +parseFloat(element.total_tax).toFixed(2)+` </span>
																</div>`
														).join('')}
														</div>
														<div class="align-items-end flex-column mt-2 d-flex justify-content-end"> 
															<span class="mt-1 mb-2 montant_toltal_order">Total: `+row.total+`€</span>
															<button type="button" class="btn btn-dark px-5" data-bs-dismiss="modal">Fermer</button>
														</div>
													</div>

												</div>
											</div>
										</div>
									</div>
									<button type="button" onclick="show(`+row.id+`)" class="show_detail btn btn-dark px-2">Voir détail</button>
									`;
							}
            			},

					],

					"initComplete": function(settings, json) {
						var info = $('#example').DataTable().page.info();
						var total = 0

						// Calcul total valeur des commandes
						$('#example').DataTable().rows().eq(0).each( function ( index ) {
							var row = $('#example').DataTable().row( index );
							var data = row.data();
							total = parseFloat(total) + parseFloat(data.total)
						} );
						
						$(".number_order_pending").append('<span>'+info.recordsTotal+' ('+parseFloat(total).toFixed(2)+'€)</span>')

					},

					"fnRowCallback": function(nRow, aData, iDisplayIndex, iDisplayIndexFull) {
						$('td:nth-child(1)', nRow).attr('data-label', 'Commande');
						$('td:nth-child(2)', nRow).attr('data-label', 'Date');
						$('td:nth-child(3)', nRow).attr('data-label', 'État');
						$('td:nth-child(4)', nRow).attr('data-label', 'Total');
						$('td:nth-child(5)', nRow).attr('data-label', 'Action');
					}
					

				})
			})

			function show(id){
			
				$('#order_'+id).modal({
					backdrop: 'static',
					keyboard: false
				})

				$("#order_"+id).modal('show')

			}
		
		</script>

	@endsection


