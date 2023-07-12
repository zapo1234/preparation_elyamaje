
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
					<div class="breadcrumb-title pe-3">Analytics</div>
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

				<div class="card card_table_mobile_responsive">
					<div class="card-body">

						<div style="background:rgb(0 0 0 / 12%)" class="d-none number_order p-2 radius-10 text-center mt-1 mb-3">
							<h1 class="mb-0 font-weight-bold text-primary d-flex justify-content-center"><span style="margin-right:5px" class="order_prepared"></span> / <span style="margin-left:5px" class="order_finished"></span></h1>
							<p class="mb-0">Commandes préparées / emballées</p>
						</div>

						<div class="loading_div d-flex justify-content-center">
							<div class="loading spinner-border text-dark" role="status"> 
								<span class="visually-hidden">Loading...</span>
							</div>
						</div>

						<div class="table-responsive">
							<input format="dd/mm/yyyy" type="date" class="d-none custom_dropdown date_dropdown">
				
							<table id="example" class="d-none table_mobile_responsive w-100 table_list_order table table-striped table-bordered">
								<thead>
									<tr>
										<th>Nom</th>
										<th>Commandes Préparées</th>
										<th>Commandes Emballées</th>
										<th>Produits bippés</th>
										<th>Date</th>
									</tr>
								</thead>
								<tbody>
									@foreach ($histories as $histo)
										@foreach ($histo as $his)
											<tr>
												<td data-label="Nom">{{ $his['name'] }}</td>
												<td class="prepare_column" data-label="Commandes Préparées">{{ $his['prepared_count'] }}</td>
												<td class="finished_column" data-label="Commandes Emballées">{{ $his['finished_count'] }}</td>
												<td data-label="Produits bippés">{{ $his['items_picked'] }}</td>
												<td data-label="Date">{{ $his['date'] }}</td>
											</tr>
										@endforeach
									@endforeach
								</tbody>
							</table>
						</div>
					</div>
				</div>

				<div class="col">
					<div class="card">
						<div class="card-body">
							<div class="chart_average" id="chart6"></div>
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

			var average = '<?php echo json_encode($average_by_name) ?>';
			var list_name = []
			var order_prepared = []
			var order_finished = []
			var items_picked = []

			Object.entries(JSON.parse(average)).forEach(([key, value]) => {
				list_name.push(key)
				order_prepared.push(value.avg_prepared)
				order_finished.push(value.avg_finished)
				items_picked.push(value.avg_items_picked)
			});

			// chart 6
			Highcharts.chart('chart6', {
				chart: {
					type: 'bar',
					styledMode: true
				},
				title: {
					text: 'Moyenne préparation'
				},
				xAxis: {
					categories: list_name
				},
				yAxis: {
					min: 0,
					title: {
						text: '',
						style: {
							display: 'none',
						}
					}
				},
				legend: {
					reversed: false
				},
				
				colors: ['#4eda58', '#ff7300', '#212529'],
				series: [{
					name: 'Commandes préparées',
					data: order_prepared
				},{
					name: 'Commandes emballées',
					data: order_finished
				},{
					name: 'Produits bippés',
					data: items_picked,
				}]
			});

		</script>
	@endsection


