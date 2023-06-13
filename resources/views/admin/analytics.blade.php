
@extends("layouts.app")

	@section("style")

	@endsection

		@section("wrapper")
			<div class="page-wrapper">
				<div class="page-content">
					<div class="page-breadcrumb d-sm-flex align-items-center mb-3">
						<div class="breadcrumb-title pe-3">Analytics</div>
						<!-- <div class="ps-3">
							<nav aria-label="breadcrumb">
								<ol class="breadcrumb mb-0 p-0">
									<li class="breadcrumb-item active" aria-current="page">Catégories</li>
								</ol>
							</nav>
						</div>
						<div class="ms-auto ms-auto-responsive">
							<form action="{{ route('admin.syncCategories') }}" method="get">
								@csrf
								<button type="submit" class="btn btn-dark px-5">Synchroniser les catégories</button>
							</form>
                        </div> -->
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

					<div class="card">
						<div class="card-body" style="position: relative;">
								<div class="flex-wrap d-flex align-items-center">
									<h6 class="mb-0 font-weight-bold">Commandes Préparées & Emballées (Jour)</h6>
									<div class="d-flex dropdown ms-auto select_chart">
										<input id="date_research" value="{{ date('Y-m-d') }}" type="date">
										<div class="col" style="margin-left:15px">
                                            <button disabled id="valid_search" type="button" class="btn btn-dark px-3 radius-10">Valider</button>
                                        </div>
									</div>
								</div>
								<div class="d-none chart_6 p-3 radius-10 text-center mt-3">
									<h1 class="mb-0 font-weight-bold text-primary"><span class="prepared_count"></span> / <span  class="finished_count"></span></h1>
									<p class="mb-0">Nombre de commandes Préparées / Emballées</p>
								</div>
								<div class="d-flex justify-content-center">
									<div class="loading spinner-border text-dark" role="status"> 
										<span class="visually-hidden">Loading...</span>
									</div>
								</div>
								<div id="chart6"> 
							<div class="resize-triggers"><div class="expand-trigger"><div style="width: 459px; height: 461px;"></div></div><div class="contract-trigger"></div></div></div>
						</div>
					</div>
				</div>
			</div>
		@endsection

	
	@section("script")
	<script src="assets/plugins/apexcharts-bundle/js/apexcharts.min.js"></script>

	<script>
		
		$(document).ready(function() {
			chart()
		})

		$("#date_research").on("change", function(){
			if($(this).val() != ""){
				$('#valid_search').attr('disabled', false)
			}
		})

		$('#valid_search').on('click', function(){
			var date = $("#date_research").val()
			if(date != ""){
				document.querySelector("#chart6").innerHTML= ""
				$('#valid_search').attr('disabled', true)
				chart(date)
			}
		})

		function chart(date = false){
			
			$.ajax({
				url: "{{ route('admin.getAnalytics') }}",
				async: true,
				data:{date: date},
				dataType: 'json',
				type: "get",
			}).done(function (data) {
				$(".loading").hide()
				$(".chart_6").removeClass('d-none')

				if(data != 0){

					let prepared_count = data.prepared_count.reduce(add, 0)
					let finished_count = data.finished_count.reduce(add, 0)
					
					$(".prepared_count").text(prepared_count)
					$(".finished_count").text(finished_count)

					// chart 6
					var options = {
						series: [{
						name: 'Préparée',
						data: data.prepared_count
					}, {
						name: 'Emballée',
						data: data.finished_count
					}],
					chart: {
						foreColor: '#9a9797',
						type: 'bar',
						height: 260,
						
					},
					grid: {
					show: true,
					borderColor: '#ededed',
					strokeDashArray: 4,
					},
					plotOptions: {
						bar: {
							horizontal: false,
							columnWidth: '15%',
						},
					},
					markers: {
						size: 4,
						strokeColors: "#fff",
						strokeWidth: 2,
						hover: {
							size: 7,
						}
					},
					dataLabels: {
						enabled: false
					},
					stroke: {
						show: true,
						width: 0,
						curve: 'smooth'
					},
					colors: ["#F8C328", "#45CB3F"],
					xaxis: {
						categories: data.name
					},
					fill: {
						opacity: 1
					},
				};

					var chart = new ApexCharts(document.querySelector("#chart6"), options);
					chart.render();
				} else {
					$(".prepared_count").text(0)
					$(".finished_count").text(0)
				}

			});
		}

		function add(accumulator, a) {
			return accumulator + a;
		}

		


	</script>

	@endsection


