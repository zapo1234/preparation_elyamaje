@extends("layouts.app")

	@section("style")
		<link href="{{asset('assets/plugins/datatable/css/dataTables.bootstrap5.min.css')}}" rel="stylesheet" />
		<link href="assets/plugins/select2/css/select2.min.css" rel="stylesheet" />
		<link href="assets/plugins/select2/css/select2-bootstrap4.css" rel="stylesheet" />
		<link href="{{asset('assets/plugins/highcharts/css/highcharts.css')}}" rel="stylesheet" />
	@endsection

	@section("wrapper")
		<div class="page-wrapper">
			<div class="page-content">
				<div class="page-breadcrumb d-sm-flex align-items-center mb-3">
					<div class="breadcrumb-title pe-3">Commande facturées dolibar</div>
				</div>

				<div class="card card_table_mobile_responsive">
					<div class="card-body">

					
					
						<div class="table-responsive">
							
				
							<table id="example" class="d-none table_mobile_responsive w-100 table_list_order table table-striped table-bordered">
								<thead>
									<tr>
										<th>Date</th>
										<th>Nombre commande facturées</th>
										<th>Détails(journée de préparation)</th>
										<th>Controle sur les commandes facturés</th>
										<th></th>
									</tr>
								</thead>
								<tbody>
						                    @foreach($list_result as $val) 
						                     	<tr>
												<td data-label="Nom">{{ $val['date'] }}</td>
												<td class="prepare_column" data-label="Commandes Préparées">{{ $val['nombre'] }}</td>
												<td class="finished_column" data-label="Commandes Emballées"> <button type="button" class="p-2 px-3 verificode" data-id1="{{ $val['dat'] }}" style="background-color:black;color:white;width:auto;border-radius:5px;border:2px solid black">Voir détails</button></td>
												<td data-label="Produits bippés"><button type="button" class="p-2 px-3 verificodes" data-id2="{{ $val['dat'] }}" style="background-color:black;color:white;width:auto;border-radius:5px;border:2px solid black">voir</button></td>
												<td data-label="Date"></td>
											</tr>
										
								         @endforeach
								</tbody>
							</table>
						</div>
					</div>
				</div>

				
                  <!-- Modal -->
		<div class="modal fade" id="details_facture" style="margin-top:30px;height:400px;overflow-y:scroll" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
		<div class="modal-dialog modal-dialog-centered" role="document">
			<div class="modal-content p-3">
			 <h3 style="font-size:17px;text-align:center;text-transform:uppercase">Point Commande facturées<span id="journee_date"></span> </h3>
               <div>
                 <div id="zapo"></div>
				
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
		<script src="assets/plugins/highcharts/js/highcharts.js"></script>
		<script src="assets/js/analytics.js"></script>

		<script>
          
          $('.verificode').click(function(){
              
               var id = $(this).data('id1');
               
               $.ajax({
	         	url: "{{ route('tiers.getidscommande') }}",
	        	method: 'GET',
	      	  data: {id:id},
	    	}).done(function(data) {
              
                 $('#zapo').html(data);
		    });
              
          });

		  $(".verificode").on('click', function(){
			    $("#details_facture").modal('show')
		       });

          
          
         </script>
	@endsection

