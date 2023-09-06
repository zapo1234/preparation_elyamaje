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
										<th></th>
										<th></th>
									</tr>
								</thead>
								<tbody>
						                    @foreach($list_result as $val) 
						                     	<tr>
												<td data-label="Nom">{{ $val['date'] }}</td>
												<td class="prepare_column" data-label="Commandes Préparées">{{ $val['nombre'] }}</td>
												<td class="finished_column" data-label="Commandes Emballées"><button type="button"  data-id1 ="{{ $val['dat'] }}" class="btn btn-info">Voir détails</button></td>
												<td data-label="Produits bippés"></td>
												<td data-label="Date"></td>
											</tr>
										
								         @endforeach
								</tbody>
							</table>
						</div>
					</div>
				</div>

				<div id="zapo"></div>
                  <!-- Modal -->
		<div class="modal fade" id="details_facture" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
		<div class="modal-dialog modal-dialog-centered" role="document">
			<div class="modal-content p-3">
			<form method="post" id="form_verifcodes" action="/utilisateur/prgramme/fidelite">
				<input type="hidden" name="_token" value="G6bCRwa4c4v3pZVsQQvEgnCzphw78u68UGU6fqGn">
				<h3 style="font-size:17px;text-align:center;text-transform:uppercase">Vérifier le code fidélité <span id="nommer"></span> </h3>

				<div id="error_codelive"></div>

				<div>

				<input type="text" size="45" class="form-control" placeholder="code(NB tapez le code en miniscule)" name="codefemverify" required  required aria-describedby="basic-addon1">

				</div>

				<div class="w-100 mt-2 d-flex justify-content-center">

					<button type="button" data-bs-dismiss="modal" class="annuler" style="background-color:#eee;color:black;border:2px solid #eee;border-radius:15px;">Annuler</button>  
					<button type="submit" class="validateadds" style="background-color:#00FF00;color:black;border:2px solid #00FF00;margin-left:15px;border-radius:15px;font-weight:bold">Vérifier</button> <br/> 

				</div>
			</form>
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
          
          $('.btn-info').click(function(){
              
               var id = $(this).data('id1');
               
               $.ajax({
	         	url: "{{ route('tiers.getidscommande') }}",
	        	method: 'GET',
	      	  data: {id:id},
	    	}).done(function(data) {
              
               $('#zapo').html(data);
		    });
              
          });
          
          
         </script>
	@endsection

