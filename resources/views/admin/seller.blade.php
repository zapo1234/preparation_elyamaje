
@extends("layouts.app")

@section("style")
    <link href="{{asset('assets/plugins/datatable/css/dataTables.bootstrap5.min.css')}}" rel="stylesheet" />
	<link href="{{asset('assets/plugins/highcharts/css/highcharts.css')}}" rel="stylesheet" />
@endsection

@section("wrapper")
    <div class="page-wrapper">
        <div class="page-content">
            <div class="d-flex w-100 justify-content-between page-breadcrumb d-sm-flex align-items-center mb-3">
                <div class="d-flex align-items-center multiple_title">
                    <div class="breadcrumb-title pe-3">
                        Beauty Prof's
                    </div>
                    <div class="ps-3">
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb mb-0 p-0">
                                <li class="breadcrumb-item active" aria-current="page">Analytics</li>
                            </ol>
                        </nav>
                    </div>
                </div>    
               
                <div class="d-flex analytics_data_title align-items-center multiple_title">
                    En attente : 
                    <div class="align-items-center d-flex breadcrumb-title pe-3" style="margin-right: 15px">
                        <span class="ml-1 data_number order_pending text-warning">	</span>
                        <div style="border-width:0.18em" class="ml-1 text-warning load_spinner  spinner-border spinner-border-sm" role="status"> <span class="visually-hidden">Loading...</span></div>
                    </div>

                    Payées :
                    <div class="align-items-center d-flex">
                        <span class="ml-1 data_number order_paid text-success">	</span>
                        <div class="ml-1 text-success load_spinner spinner-border spinner-border-sm" role="status"> <span class="visually-hidden">Loading...</span></div>
                    </div>
                </div>
                <div class="d-flex justify-content-end align-items-center" style="width:233px">
                    <span class="font-20 font-bold">Revenu : </span>
                    <span style="margin-left: 3px" class="text-success font-20 font-bold  total_amount"></span>
                </div>
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
                        <h5>Commandes Prises</h5>
                        <h5>Panier Moyen</h5>
                        <h5>Total Vente</h5>
                    </div>
                </div>

                <div class="mobile_padding card-body analytics_bp_preparation p-0 mt-2">
                    <div>
                        <input format="dd/mm/yyyy" type="date" value="<?php echo date('Y-m-d');?>" class="d-none custom_input custom_dropdown date_dropdown">
                        <table id="example" class="table_mobile_responsive w-100 table_list_order table table-striped table-bordered">
                            <!-- <tbody></tbody> -->
                            <tbody>
                                @for($i = 0; $i < 5; $i++)
                                    <tr class="loading_table_analytics">
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
								<h5>Commandes Prises / Total vente </h5>
							</div>
						</div>
						<div class="card-body">
							<div class="chart_average_bp" id="chartBP">

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
    <script src="assets/js/analyticsBP.js"></script>
@endsection


