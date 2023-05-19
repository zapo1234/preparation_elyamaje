@extends("layouts.app")

		@section("style")
			<link href="{{asset('assets/plugins/datatable/css/dataTables.bootstrap5.min.css')}}" rel="stylesheet" />
		@endsection

		@section("wrapper")
			<div class="page-wrapper">
				<div class="page-content">
					<div class="page-breadcrumb d-sm-flex align-items-center mb-2">
						<div class="breadcrumb-title pe-3">Mise à jours clients dolibarr</div>
						<div class="pe-3 number_order_pending"></div>

					</div>
					<div class="row">
						<div class="card card_table_mobile_responsive">
							<div class="card-body">
								<div class="table-responsive">
                                <table id="example" class="w-100 table_mobile_responsive table table-striped table-bordered">
										<thead>
											<tr>
												<th scope="col">Mettre à jours les clients</th>
												<th scope="col">Action</th>
											
											</tr>
                                            <tr>
                                                <td>Tiers dolibarr</td>
                                             <td><button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#exampleModal">Import tiers</button> </td>
                                            </tr>
										</thead>
									</table>
									
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>

            <div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true" style="position:absolute;width:550px;height:250px;top:100px;left:28%">
                                                <div class="modal-dialog">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title" id="exampleModalLabel">Importer des clients depuis dolibar</h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                        </div>
                                                        <div class="modal-body">
                                                            
                                                            <form method="post" id="transfers_code_promo" action="">
                                                              @csrf
                                                            <h2></h2>
                                                                 <div id="error_code"></div>
        
                                                           </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annnuler</button>
                                                            <button type="submit" id="transfers_codepromo" class="btn btn-primary">imports</button>
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

		<script>
			
		
		</script>

	@endsection


