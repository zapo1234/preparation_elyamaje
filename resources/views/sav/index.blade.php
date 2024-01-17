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
							<div class="breadcrumb-title pe-3">Sav</div>
							<div class="pe-3 number_order_pending"></div>
						</div>

						

					</div>

					<div class="row row-cols-1 row-cols-md-1 row-cols-lg-1 row-cols-xl-1 d-flex justify-content-center">
						<h4 class="text-center">En développement...</h4>
						<img src="assets/images/comming_soon.svg" style="width: 80vh">
					</div>

				</div>
			</div>
		@endsection

	
	@section("script")
		<script src="assets/js/pace.min.js"></script>
		<script src="{{asset('assets/plugins/datatable/js/jquery.dataTables.min.js')}}"></script>
		<script src="{{asset('assets/plugins/datatable/js/dataTables.bootstrap5.min.js')}}"></script>
		<script src="assets/plugins/select2/js/select2.min.js"></script>
	@endsection

