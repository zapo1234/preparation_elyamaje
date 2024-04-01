
@extends("layouts.app")

@section("style")
    <link href="{{asset('assets/plugins/datatable/css/dataTables.bootstrap5.min.css')}}" rel="stylesheet" />
    <link href="{{('assets/plugins/select2/css/select2.min.css')}}" rel="stylesheet" />
    <link href="assets/plugins/select2/css/select2-bootstrap4.css" rel="stylesheet" />
    <link href="assets/css/style_reassort.css" rel="stylesheet" />

    <link href="{{asset('assets/plugins/datetimepicker/css/classic.css')}}" rel="stylesheet" />
    <link href="{{asset('assets/plugins/datetimepicker/css/classic.time.css')}}" rel="stylesheet" />
    <link href="{{asset('assets/plugins/datetimepicker/css/classic.date.css')}}" rel="stylesheet" />
    <link href="{{asset('assets/plugins/bootstrap-material-datetimepicker/css/bootstrap-material-datetimepicker.min.css')}}" rel="stylesheet" />

   
@endsection

@section("wrapper")
    <div class="page-wrapper">
        <div class="page-content">

            {{-- Alert d erreur --}}
            @include('layouts.transfert.alertSuccesError')
            {{-- alert succes --}}
            <div class="alert alert-success border-0 bg-success alert-dismissible fade show alert-succes-calcul" style="display: none">
                <div class="text-white text_alert">Transfère envoyer pour préparation</div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>

            {{-- alert erreur --}}
            <div class="alert alert-danger border-0 bg-danger alert-dismissible fade show alert-danger-calcul" style="display: none">
                <div class="text-white text_alert">Erreur de transfère</div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>



            <h6 class="mb-0 text-uppercase">ACTUALISATION DES TABLES</h6>
            <div class="card-body p-0 mt-2" id="list_reassort_id">
                
                <div id="id_reassor1" class="card card_product_commande">
                    <div class="table-responsive p-3">
                        <table id="example4" class="table mb-0 dataTable table_mobile_responsive w-100 table_list_order table-striped table-bordered">
                            <thead>
                                <tr>
                                    <th title="L'entrepôt qui va être décrémenté">Table</th>
                                    <th title="L'entrepôt qui va être décrémenté">Dernière actualisation</th>
                                    <th title="L'entrepôt qui va être décrémenté">Action</th>
                                </tr>
                            </thead>
                            <tbody id="">
                            

                                @foreach ($datas as $key => $data)
                                    <tr id="">
                                        <td id="" style="text-align: left !important;">{{$data["name_table"]}}</td>
                                        <td id="" style="text-align: left !important;">{{$data["last_update"]}}</td>
                                        <td id="" style="text-align: left !important;">
                                            <a class="btn btn-sm btn-dark" id="id_btn_validate_48668" style="color: #fff !important;" href="{{$data["route"]}}">
                                                Mettre à jour
                                            </a>
                                            
                                        </td>
                                    </tr>
                                @endforeach

                            </tbody>
                        </table>
                </div>   
            </div>

            





       
        </div>
    </div>

@endsection


@section("script")

<script src="{{asset('assets/plugins/datatable/js/jquery.dataTables.min.js')}}"></script>
<script src="{{asset('assets/plugins/datatable/js/dataTables.bootstrap5.min.js')}}"></script>
<script src="{{asset('assets/plugins/select2/js/select2.min.js')}}"></script>

<script src="{{asset('assets/plugins/datetimepicker/js/legacy.js')}}"></script>
<script src="{{asset('assets/plugins/datetimepicker/js/picker.js')}}"></script>

<script src="{{asset('assets/plugins/datetimepicker/js/picker.time.js')}}"></script>
<script src="{{asset('assets/plugins/datetimepicker/js/picker.date.js')}}"></script>

<script src="{{asset('assets/plugins/bootstrap-material-datetimepicker/js/moment.min.js')}}"></script>
<script src="{{asset('assets/plugins/bootstrap-material-datetimepicker/js/bootstrap-material-datetimepicker.min.js')}}"></script>







@endsection


