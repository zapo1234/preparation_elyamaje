@extends("layouts.app")

@section("style")
    <link href="{{asset('assets/plugins/datatable/css/dataTables.bootstrap5.min.css')}}" rel="stylesheet" />
    <link href="{{('assets/plugins/select2/css/select2.min.css')}}" rel="stylesheet" />
    <link href="assets/plugins/select2/css/select2-bootstrap4.css" rel="stylesheet" />
@endsection

@section("wrapper")
    <div class="page-wrapper">
        <div class="page-content">
            <div class="d-flex w-100 justify-content-between page-breadcrumb d-sm-flex align-items-center mb-3">
                <div class="d-flex align-items-center multiple_title">
                    <div class="breadcrumb-title pe-3">
                        Gala Elyamaje
                    </div>
                    <div class="ps-3">
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb mb-0 p-0">
                                <li class="breadcrumb-item active" aria-current="page">Gestion des stocks</li>
                            </ol>
                        </nav>
                    </div>
                </div>    
            </div>
            <div class="d-flex gap-3 align-items-center" style="min-height:50px;">
                <h5 class="card-title m-0">Kit à afficher</h5>
                    <div class="d-flex gap-2 justify-content-between align-items-center w-10">
                        <i class="fadeIn animated bx bx-chevron-right"></i>
                        <a href="{{ route('admin.stockscat') }}"  class="form-check-link {{ Route::currentRouteName() == 'admin.stockscat' ? 'lien-actif' : '' }}" >
                            <span class="lienStock">Voir les Limes</span>
                        </a>
                    </div>
                    <div class="d-flex gap-2 justify-content-between align-items-center w-10">
                        <i class="fadeIn animated bx bx-chevron-right"></i>
                        <a href="{{ route('admin.stocksrape') }}" class="form-check-link {{ Route::currentRouteName() == 'admin.stocksrape' ? 'lien-actif' : '' }}">
                            <span class="lienStock">Voir les Rapes</span>
                        </a>
                    </div>
                    <div class="form-check-message">
                        <label>{{ $message }}</label>
                    </div>
            </div>
            <div class="card-body p-0" style="background-color:white;">
                <div class="d-flex w-100">
                        <form method="POST" action="{{ route('admin.stockscatrap') }}" style="overflow:hidden; width:70%;" class="radius-10 d-flex flex-wrap">
                            @csrf
                            <table id="" style="width:100%" class="kitStockRape">
                                @foreach($data as $key => $val)
                                    <tr style="padding:3%">
                                        <td style="background-color:black;font-size:16px; font-weight:500; text-transform:uppercase;color:white;width:100%;border-top-left-radius: 10px;border-top-right-radius: 10px; display:block !important;">
                                            {{ $key }} 
                                        </td>
                                    </tr>
                                    @foreach($val as $kj => $vals)
                                        @foreach($vals as $lm => $vv)
                                
                                            @php

                                                $lim = explode('%',$lm);
                                                
                                                $namex = explode(' ',$lim[0]);
                                                
                                                    if(count($namex)==2){
                                                    $name_list = $namex[1];
                                                }
                                                
                                                elseif(count($namex)==3){
                                                    $name_list = $namex[2];
                                                }else{
                                                    $name_list = $namex[3];
                                                }
                                        
                                            @endphp
                                    
                                            <tr>
                                                <td style="background-color: #333333;color:white;padding:10px; display:block !important;">
                                                    {{ $name_list }}  Nombre d'unités : {{ $lim[1] }}
                                                </td>
                                            </tr>
                                            @foreach($vv as $ls =>$vc)
                                                <tr>
                                                    <td style="display:flex; min-height:50px; align-items:center; gap:20px; justify-content:space-between;padding-bottom:25px !important; padding-top:8px !important;">
                                                            <span style="font-weight:500; width:300px;">{{ $vc['libelle_family'] }} </span>
                                                            <div class="d-flex align-items-center justify-content-center">
                                                                Quantité :
                                                                <input type="text" name="qts[]" value="{{ $vc['quantite'] }}" readonly style="border:none !important;"> 
                                                            </div>
                                                            <input type="text" name="qte[]" placeholder="Nouveau stock"> 
                                                    </td>
                                                </tr>
                                            @endforeach
                                        @endforeach
                                    @endforeach  
                                @endforeach
                            </table>
                            <div class="d-flex flex-wrap align-items-center justify-content-center"style="width:25%;position:fixed;right:30px; top:30%;">
                            @foreach($list_faible_stocks as $vl)
                                    @if($vl['produit'] == "Aucun mouvement produit")
                                    <p style="text-align: center;width:90%;">
                                        STOCK OK !<br/>
                                    </p>
                                    @else
                                    <p style="text-align: left;width:90%;" class="d-flex gap-1">
                                        <i class="lni lni-warning" style="color:#CD2A28; fill:#CD2A28;"></i>Le stock de {{ $vl['produit'] }} est insuffisant.<br/>
                                    </p>
                                    @endif
                                @endforeach
                                <button type="submit" class="btn btn-primary text-white" style="margin-bottom:20px;">Modifier le stock</button>
                                @foreach($list_product as $val)
                                    @if($val['libelle'] == "Aucun mouvement de stock")
                                        {{ $val['libelle'] }}<br/>
                                    @else
                                    <p style="text-align: left;width:90%;" class="d-flex gap-1">
                                        <i class="fadeIn animated bx bx-check-circle" style="color:#198754; fill:#198754;"></i>Le stock de {{ $val['libelle'] }} a été modifié.<br/>
                                    </p>
                                    @endif    
                                @endforeach
                            <div>                        </form>
                    </div>
            </div>
        </div>
    </div>
@endsection


@section("script")
    <script src="{{asset('assets/plugins/datatable/js/jquery.dataTables.min.js')}}"></script>
	<script src="{{asset('assets/plugins/datatable/js/dataTables.bootstrap5.min.js')}}"></script>

    <script>
        $(document).ready(function() {
            $('.example').DataTable({})
           
            $('.example3').DataTable({
                "order": [[7, 'DESC']],
                "columnDefs": [
                    { "visible": false, "targets": 7 },
                ],
            })

    
            $('.example2').DataTable({
                "order": [[4, 'DESC']],
                "columnDefs": [
                    { "visible": false, "targets": 4 },
                ],
            })
        })

        $(".change_date_input").on('change', function(){
            $(".change_date").submit();
        })

        $(".addMovementForm").submit(function(e){
            var caisse_id = $(this).attr('data-caisse-id')
            if(parseFloat($("#amount_"+caisse_id).val()) > parseFloat($("#amountCaisse_"+caisse_id).val())){
                e.preventDefault();
                $("#amount_"+caisse_id).css('border', '1px solid red')
                $("#exampleSmallModal_"+caisse_id+" .my-1").css('color', 'red')
            }
        });

        $(".validMovement").on('click', function(){
            $("#movement_id").val($(this).attr('data-id'))
            $("#caisse").val($(this).attr('data-name'))
            $('#validMovementModal').modal({
                backdrop: 'static',
                keyboard: false
            })
            $("#validMovementModal").modal('show')
        })

        $(".cancelMovement").on('click', function(){
            $("#cancel_movement_id").val($(this).attr('data-id'))
            $("#cancel_caisse").val($(this).attr('data-name'))
            $('#cancelMovementModal').modal({
                backdrop: 'static',
                keyboard: false
            })
            $("#cancelMovementModal").modal('show')
        })
    </script>
@endsection


