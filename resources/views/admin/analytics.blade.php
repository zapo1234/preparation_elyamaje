
@extends("layouts.app")

	@section("style")
		<link href="{{asset('assets/plugins/datatable/css/dataTables.bootstrap5.min.css')}}" rel="stylesheet" />
		<link href="{{asset('assets/plugins/highcharts/css/highcharts.css')}}" rel="stylesheet" />
	@endsection

	@section("wrapper")
		<div class="page-wrapper">
			<div class="page-content">
				<div class="d-flex w-100 justify-content-between page-breadcrumb d-sm-flex align-items-center mb-3">
					<div class="breadcrumb-title pe-3">
						Analytics
					</div>
					<div class="d-flex pe-3 analytics_data_title align-items-center">
						Préparées : 
						<div class="align-items-center d-flex breadcrumb-title pe-3" style="margin-right: 15px">
							<span class="ml-1 data_number order_prepared">	</span>
							<div class="ml-1 text-success load_spinner  spinner-border spinner-border-sm" role="status"> <span class="visually-hidden">Loading...</span></div>
						</div>

						Emballées : 
						<div class="align-items-center d-flex">
							<span class="ml-1 data_number order_finished">	</span>
							<div class="ml-1 text-success load_spinner spinner-border spinner-border-sm" role="status"> <span class="visually-hidden">Loading...</span></div>
						</div>
					</div>
					<div style="width:105px"></div>
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

				<div class="card card_table_mobile_responsive radius-10">
					<div class="header_title hide_mobile d-flex align-items-center">
						<div class="w-100 d-flex justify-content-between">
							<h5>Nom</h5>
							<h5>Commandes Préparées</h5>
							<h5>Commandes Emballées</h5>
							<h5>Produits Bippés</h5>
							<h5>Date</h5>
						</div>
					</div>

					<div class="mobile_padding  card-body analytics_preparation p-0 mt-2">
						<div>
							<input format="dd/mm/yyyy" type="date" class="d-none custom_dropdown date_dropdown">
				
							<table id="example" class="table_mobile_responsive w-100 table_list_order table table-striped table-bordered">
								<!-- <tbody></tbody> -->
								<tbody>
									@for($i = 0; $i < 5; $i++)
										<tr class="loading_table_analytics">
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

				<div class="mobile_padding  col">
					<div class="card radius-10">
						<div class="header_title d-flex align-items-center">
							<div class="w-100 d-flex justify-content-between">
								<h5>Moyenne préparation / Jour</h5>
							</div>
						</div>
						<div class="card-body">
							<div class="chart_average" id="chart6">

								<div class="loading_chart">
									<div class="loading_chart1"></div>
									<div class="loading_chart2"></div>
									<div class="loading_chart3"></div>
									<div class="loading_chart4"></div>
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
		<script src="assets/plugins/highcharts/js/highcharts.js"></script>
		<script src="assets/js/analytics.js"></script>
	@endsection


