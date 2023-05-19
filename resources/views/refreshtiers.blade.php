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
		@endsection

	
	@section("script")

		<script src="{{asset('assets/plugins/datatable/js/jquery.dataTables.min.js')}}"></script>
		<script src="{{asset('assets/plugins/datatable/js/dataTables.bootstrap5.min.js')}}"></script>

		<script>
			
		
		</script>

	@endsection


