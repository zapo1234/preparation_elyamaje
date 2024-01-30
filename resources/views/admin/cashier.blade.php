
@extends("layouts.app")

@section("style")
    <link href="{{asset('assets/plugins/datatable/css/dataTables.bootstrap5.min.css')}}" rel="stylesheet" />
    <link href="assets/plugins/select2/css/select2.min.css" rel="stylesheet" />
    <link href="assets/plugins/select2/css/select2-bootstrap4.css" rel="stylesheet" />
@endsection

@section("wrapper")
    <div class="page-wrapper">
        <div class="page-content">
            <div class="page-breadcrumb d-sm-flex align-items-center mb-2">
                <div class="breadcrumb-title pe-3">Beauty Prof's</div>
                <div class="ps-3">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0 p-0">
                            <li class="breadcrumb-item active" aria-current="page">Caisse</li>
                        </ol>
                    </nav>
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


            
            <div class="card card_table_mobile_responsive radius-10 w-100">
                <div class="card-body">
                    <div class="d-flex justify-content-center">
                        <div class="loading spinner-border text-dark" role="status"> 
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                    <div class="table-responsive">
                    
                        <form method="GET" action="{{ route('admin.cashier') }}" class="d-flex d-none order_research">
                            <input value="{{ $parameter['created_at'] ?? '' }}" name="created_at" class="custom_input" style="padding: 4px;" type="date">
                            <input value="{{ $parameter['ref_order'] ?? '' }}" placeholder="Numéro de commande" name="ref_order" class="custom_input" style="padding: 4px;" type="text">
                            <button style="margin-left:10px" class="research_history_order d-flex align-items-center btn btn-primary" type="submit">Rechercher</button>
                        </form>

                        <table id="example" class="d-none w-100 table_list_order table_mobile_responsive table table-striped table-bordered">

                            <div class="d-none loading_show_detail_order w-100 d-flex justify-content-center">
                                <div class="spinner-grow text-dark" role="status"> <span class="visually-hidden">Loading...</span></div>
                            </div>
                            
                            <thead>
                                <tr>
                                    <th class="col-md-1" scope="col">Commande</th>
                                    <th class="col-md-4"scope="col">Cliente</th>
                                    <th class="col-md-4" scope="col">Vendeuse</th>
                                    <th class="col-md-1" scope="col">Date</th>
                                    <th class="col-md-1" scope="col">Status</th>
                                    <th class="col-md-1" scope="col">Détails</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($orders as $order)
                                    <tr class="{{ $order['need_action'] ? 'need_action' : ''}}">
                                        <td><span>{{ $order['ref_order'] }}</span></td>
                                        <td><span class="p-2 badge bg-dark">{{ $order['name'] }} {{ $order['pname'] != $order['name'] ? $order['pname']: '' }}</span></td>
                                        <td><span class="p-2 badge bg-dark">{{ $order['seller'] }}</span></td>
                                        <td><span>{{ $order['created_at'] }}</span></td>
                                        <td>
                                            @if($order['status'])
                                                <select style="width: 180px; font-weight: bold;	font-size: 0.9em; " data-from_dolibarr="true" data-order="{{ $order['order_id'] }}" class="{{ $order['status'] }} select_status select_user">
                                                    @foreach($list_status as $key => $list)
                                                        @if($key == $order['status'])
                                                            <option selected value="{{ $order['status'] }}">
                                                                {{ __('status.'.$order['status']) }}
                                                            </option>
                                                        @else 
                                                            <option value="{{ $key }}">{{ __('status.'.$key) }}</option>
                                                        @endif
                                                    @endforeach
                                                </select>
                                            @else 
                                                <span class="p-2 badge" style="background-color:#d16c6c">Aucune information</span>
                                            @endif
                                        </td>
                                        <td><span></span></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
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

    <script>
        $(document).ready(function() {
            $('#example').DataTable({
                "ordering": false,
                "initComplete": function( settings, json ) {
                    $(".order_research").appendTo('.dataTables_length')
                    $(".dataTables_length").css('display', 'flex')
                    $(".dataTables_length").addClass('select2_custom')
                    $(".order_research").removeClass('d-none')
                    $(".order_research input").css('margin-left', '10px')

                    $(".loading").addClass('d-none')
                    $('#example').removeClass('d-none');
                }
            })

            
            $('body').on('change', '.select_status', function () {
                var order_id = $(this).attr('data-order')
                var status = $(this).val()
                var from_dolibarr = $(this).attr('data-from_dolibarr') == "true" ? 1 : 0

                // Change status order
                $.ajax({
                    url: "updateOrderStatus",
                    method: 'GET',
                    method: 'POST',
                    data: {_token: $('input[name=_token]').val(), order_id: order_id, status: status, from_dolibarr: from_dolibarr}
                }).done(function(data) {
                    if(JSON.parse(data).success){
                        // Remove order from commandeId and update dolibarr id command
                        if(status == "processing"){
                            $.ajax({
                                url: "orderReInvoicing",
                                method: 'GET',
                                method: 'POST',
                                data: {_token: $('input[name=_token]').val(), order_id: order_id}
                            }).done(function(data) {
                                if(JSON.parse(data).success){
                                } else {
                                    alert(JSON.parse(data).message)
                                }
                            });
                        }
                    } else {
                        alert('Erreur !')
                    }
                });
            })
        })
    </script>
@endsection


