
@extends("layouts.app")

@section("style")
    <link href="{{asset('assets/plugins/datatable/css/dataTables.bootstrap5.min.css')}}" rel="stylesheet" />
    <link href="assets/plugins/select2/css/select2.min.css" rel="stylesheet" />
    <link href="assets/plugins/select2/css/select2-bootstrap4.css" rel="stylesheet" />
@endsection

@section("wrapper")
    <div class="page-wrapper">
        <div class="page-content">
      
        <div class="page-breadcrumb d-sm-flex align-items-center mb-3 justify-content-between">
            <div class="d-flex align-items-baseline">
                <div class="breadcrumb-title pe-3">Beauty Prof's</div>
                <div class="ps-3 caisse_page_details">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0 p-0">
                            <li class="breadcrumb-item active" aria-current="page">Caisse</li>
                        </ol>
                    </nav>
                </div>
            </div>
            

            <div>
                <form class="change_date" action="{{ route('admin.cashier') }}">
                    @csrf
                    <input value="{{ $date }}" style="cursor:pointer" class="change_date_input custom_input p-2" name="date" value="{{ date('Y-m-d') }}" type="date">
                </form>
            </div>
            <div style="width: 210px"></div>
        </div>

        @if(session()->has('success'))
            <div class="alert alert-success border-0 bg-success alert-dismissible fade show">
                <div class="text-white">{{ session()->get('success') }}</div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
        @if(session()->has('warning'))
            <div class="alert alert-warning border-0 bg-warning alert-dismissible fade show">
                <div class="text-white">{{ session()->get('warning') }}</div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
        @if(session()->has('error'))
            <div class="alert alert-danger border-0 bg-danger alert-dismissible fade show">
                <div class="text-white">{{ session()->get('error') }}</div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="d-flex w-100 justify-content-center">
            <div class="card_caisse row row-cols-1 row-cols-md-2 row-cols-lg-4 row-cols-xl-4 d-flex justify-content-center">
                @foreach($caisse as $key => $c)
                    <div class="col">
                        <div class="radius-10 card border-dark border-bottom border-3 border-0">
                            <div class="card-body" style="
                                color: black !important;
                                ">
                                <h5 class="card-title text-primary text-center fw-bolder text-uppercase font-20">{{ $c['name'] }}</h5>
                                <div class="d-flex align-items-center mt-3">
                                <div>
                                    <p class="mb-0 fw-bold" style="
                                        font-size: 15px;
                                        ">Montant Caisse :</p>
                                </div>
                                <div class="d-flex ms-auto align-items-center font-35">
                                    <h4 class="my-1">
                                        {{ number_format(floatval($c['total_cash']) - (isset($ammount_to_deduct[$key]) ? $ammount_to_deduct[$key] : 0) + (isset($ammount_to_add[$key]) ? $ammount_to_add[$key] : 0), 2, ',', ' ') }} €
                                    </h4>
                                </div>
                                </div>
                                <hr>
                                <div class="d-flex align-items-center">
                                <div>
                                    <p class="mb-0 fw-bold" style="
                                        font-size: 13px;
                                        ">Total Espèces</p>
                                </div>
                                <div class="d-flex ms-auto align-items-center font-20">
                                    <h4 class="my-1 font-20">{{ floatval($c['total_cash']) }} €</h4>
                                </div>
                                </div>
                                <div class="d-flex align-items-center">
                                    <div>
                                        <p class="mb-0 fw-bold" style="
                                            font-size: 13px;
                                            ">Total CB</p>
                                    </div>
                                    <div class="d-flex ms-auto align-items-center font-20">
                                        <h4 class="my-1 font-20">{{ floatval($c['total_card']) }} €</h4>
                                    </div>
                                </div>
                                <div class="d-flex align-items-center">
                                    <div>
                                        <p class="mb-0 fw-bold text-danger" style="
                                            font-size: 13px;
                                            ">Total décaissé</p>
                                    </div>
                                    <div class="text-danger d-flex ms-auto align-items-center font-20">
                                        {{ isset($ammount_to_deduct[$key]) ? -$ammount_to_deduct[$key] : 0 }}  €
                                    </div>
                                </div>

                                <hr>
                                <div class="d-flex justify-content-center flex-wrap gap-3">
                                <button class="btn btn-primary text-white" data-bs-toggle="modal" data-bs-target="#exampleSmallModal_{{ $key }}"><i class="bx bx-coin"></i>Décaisser</button>
                                <button class="btn btn-primary text-white" data-bs-toggle="modal" data-bs-target="#addCashModal_{{ $key }}"><i class="bx bx-coin"></i>Ajouter des fonds</button>

                                <button class="btn btn-inverse-primary" data-bs-toggle="modal" data-bs-target="#exampleVerticallycenteredModal_{{ $key }}"><i class="bx bx-detail"></i>Voir plus d'informations</button>
                                <div class="modal_detail_movement modal fade modal_radius modal_detail_order modal_order" id="exampleVerticallycenteredModal_{{ $key }}" tabindex="-1" style="display: none;" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                            <h5 class="modal-title text-uppercase fw-bolder">{{ $c['name'] }}</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body d-flex flex-wrap" style="overflow-y: auto !important;">
                                            <div class="container-fluid mt-5 d-flex justify-content-center">
                                                <div class="row w-100">
                                                    <div class="orders_cashed">
                                                        <h5 class="text-center mb-3 w-100 title_orders_cashed">Commandes encaissées</h5>
                                                        <table class="example w-100 table_list_order table_mobile_responsive table table-striped table-bordered dataTable no-footer" role="grid">
                                                        <thead>
                                                            <tr role="row">
                                                                <th scope="col" rowspan="1" colspan="1">Commande</th>
                                                                <th scope="col" rowspan="1" colspan="1">Caissière</th>
                                                                <th scope="col" rowspan="1" colspan="1">Date</th>
                                                                <th scope="col" rowspan="1" colspan="1">Encaissement</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            @foreach($c['details_orders'] as $details_orders)
                                                                <tr role="row" class="odd">
                                                                    <td data-label="Commande"><span>{{ $details_orders->ref_order }}</span></td>
                                                                    <td data-label="Caissière"><span class="p-2 badge bg-dark">{{ $details_orders->cashierName }}</span></td>
                                                                    <td data-label="Date"><span>{{ $details_orders->date }}</span></td>
                                                                    <td data-label="Encaissement">
                                                                        <div class="d-flex align-items-center">
                                                                        <div>
                                                                            <p class="mb-0 mr-2 fw-bold" style="font-size: 13px;">Espèces</p>
                                                                        </div>
                                                                        <div class="d-flex align-items-center font-20">
                                                                            <h4 class="my-1 font-20">{{ floatval($details_orders->total_order_ttc -  $details_orders->amountCard) }} €</h4>
                                                                        </div>
                                                                        </div>
                                                                        <div class="d-flex align-items-center">
                                                                        <div>
                                                                            <p class="mb-0 fw-bold mr-2" style="font-size: 13px;">CB</p>
                                                                        </div>
                                                                        <div class="d-flex align-items-center font-20">
                                                                            <h4 class="my-1 font-20">{{ floatval($details_orders->amountCard) }} €</h4>
                                                                        </div>
                                                                        </div>
                                                                    </td>
                                                                </tr>
                                                            @endforeach
                                                        </tbody>
                                                        </table>
                                                    </div>
                                                    <div class="movement_cash">
                                                        <h5 class="text-center mb-3 w-100">Mouvements de caisse</h5>
                                                        <table class="example2 w-100 table_list_order table_mobile_responsive table table-striped table-bordered dataTable no-footer" role="grid">
                                                        <thead>
                                                            <tr role="row">
                                                                <th scope="col" rowspan="1" colspan="1">N°</th>
                                                                <th scope="col" rowspan="1" colspan="1">Heure</th>
                                                                <th scope="col" rowspan="1" colspan="1">Montant</th>
                                                                <th scope="col" rowspan="1" colspan="1">Nom</th>
                                                                <th scope="col" rowspan="1" colspan="1">ID</th>
                                                            </tr>
                                                        </thead>
                                                            <tbody>
                                                                @if(isset($list_movements[$key]))
                                                                    @foreach($list_movements[$key] as $keyMovement => $movement)
                                                                        <tr role="row" class="odd">
                                                                            <td data-label="N°"><span>{{ $keyMovement }}</span></td>
                                                                            <td data-label="Heure"><span>{{ $movement['date'] }}</span></td>
                                                                            <td data-label="Montant">
                                                                                <div class="p-2 d-flex align-items-center font-20">
                                                                                    <h4 class="{{ $movement['type']  == 'withdrawal' ? 'text-danger' : 'text-success' }} my-1 font-20">{{ $movement['type']  == "withdrawal" ? '-' : '+' }} {{ $movement['amount'] }} €</h4>
                                                                                </div>
                                                                            </td>
                                                                            <td data-label="Nom"><span>{{ $movement['name'] }}</span></td>
                                                                            <td data-label="ID"><span>{{ $movement['movementId'] }}</span></td>
                                                                        </tr>
                                                                    @endforeach
                                                                @else 
                                                                    <tr role="row" class="odd">
                                                                        <td data-label="N°"></td>
                                                                        <td data-label="Heure"></td>
                                                                        <td data-label="Montant"></td>
                                                                        <td data-label="Nom"></td>
                                                                        <td data-label="ID"></td>
                                                                    </tr>
                                                                @endif
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>
                                            </div>
                                            </div>
                                            <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                    <!-- Décaisser -->
                                    <div class="modal fade" id="exampleSmallModal_{{ $key }}" tabindex="-1" style="display: none;" aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title text-uppercase fw-bolder">{{ $c['name'] }}</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <form data-caisse-id="{{ $key }}" class="addMovementForm" method="POST" action="{{ route('admin.cashMovement') }}">
                                                    @csrf
                                                    <div class="modal-body">
                                                        <div class="d-flex align-items-center">
                                                            <div>
                                                                <p class="mb-0 fw-bold" style="
                                                                    font-size: 15px;
                                                                    ">Montant Caisse</p>
                                                            </div>
                                                            <div class="d-flex ms-auto align-items-center font-35">
                                                                <h4 class="my-1">{{ number_format(floatval($c['total_cash']) - (isset($ammount_to_deduct[$key]) ? $ammount_to_deduct[$key] : 0) + (isset($ammount_to_add[$key]) ? $ammount_to_add[$key] : 0), 2, ',', ' ') }} €</h4>
                                                            </div>
                                                        </div>
                                                        <div class="row mb-2 mt-3">
                                                            <label for="inputEnterYourName" class="col-sm-5 col-form-label">Montant à décaisser</label>
                                                            <div class="col-sm-7">
                                                                <input type="hidden" name="caisse" value="{{ $key }}">
                                                                <input id="amountCaisse_{{ $key }}" type="hidden" name="amountCaisse" value="{{ number_format(floatval($c['total_cash']) - (isset($ammount_to_deduct[$key]) ? $ammount_to_deduct[$key] : 0) + (isset($ammount_to_add[$key]) ? $ammount_to_add[$key] : 0), 2, ',', ' ') }}">
                                                                <input id="amount_{{ $key }}" type="text" class="form-control" name="amount" placeholder="Saisir Montant">
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                                                        <button type="submit" class="btn btn-primary">Valider</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>

                                    
                                    <!-- Ajout de fonds -->
                                    <div class="modal fade" id="addCashModal_{{ $key }}" tabindex="-1" style="display: none;" aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title text-uppercase fw-bolder">{{ $c['name'] }}</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <form data-caisse-id="{{ $key }}" method="POST" action="{{ route('admin.addCashMovement') }}">
                                                    @csrf
                                                    <div class="modal-body">
                                                        <div class="d-flex align-items-center">
                                                            <div>
                                                                <p class="mb-0 fw-bold" style="
                                                                    font-size: 15px;
                                                                    ">Montant Caisse</p>
                                                            </div>
                                                            <div class="d-flex ms-auto align-items-center font-35">
                                                                <h4 class="my-1">{{ number_format(floatval($c['total_cash']) - (isset($ammount_to_deduct[$key]) ? $ammount_to_deduct[$key] : 0) + (isset($ammount_to_add[$key]) ? $ammount_to_add[$key] : 0), 2, ',', ' ') }} €</h4>
                                                            </div>
                                                        </div>
                                                        <div class="row mb-2 mt-3">
                                                            <label for="inputEnterYourName" class="col-sm-5 col-form-label">Fonds à ajouter</label>
                                                            <div class="col-sm-7">
                                                                <input type="hidden" name="caisse" value="{{ $key }}">
                                                                <input id="amountCaisse_{{ $key }}" type="hidden" name="amountCaisse" value="{{ number_format(floatval($c['total_cash']) - (isset($ammount_to_deduct[$key]) ? $ammount_to_deduct[$key] : 0) + (isset($ammount_to_add[$key]) ? $ammount_to_add[$key] : 0), 2, ',', ' ') }}">
                                                                <input id="amount_{{ $key }}" type="text" class="form-control" name="amount" placeholder="Saisir Montant">
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                                                        <button type="submit" class="btn btn-primary">Valider</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>


        <!-- List of all movement, pending and validate -->
        <div class="card card_table_mobile_responsive radius-10 w-100">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="example3 w-100 table_list_order table_mobile_responsive table table-striped table-bordered dataTable no-footer" role="grid">
                        <thead>
                            <tr role="row">
                                <th scope="col" rowspan="1" colspan="1">Caisse</th>
                                <th scope="col" rowspan="1" colspan="1">Heure</th>
                                <th scope="col" rowspan="1" colspan="1">Montant caisse</th>
                                <th scope="col" rowspan="1" colspan="1">Mouvement</th>
                                <th scope="col" rowspan="1" colspan="1">Nom</th>
                                <th scope="col" rowspan="1" colspan="1">Status</th>
                                <th scope="col" rowspan="1" colspan="1">Action</th>
                                <th scope="col" rowspan="1" colspan="1">ID</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($list_movements as $key_caisse => $mov)
                                @foreach($mov as $m)
                                    <tr role="row">
                                        <td>{{ $m['caisse'] }}</td>
                                        <td>{{ $m['date'] }}</td>
                                        <td><span class="font-20 font-bold">{{ $m['before_movement'] }} €</span></td>
                                        <td><span class="font-20 {{ $m['type']  == 'withdrawal' ? 'text-danger' : 'text-success' }}">{{ $m['type']  == "withdrawal" ? '-' : '+' }} {{ $m['amount'] }} €</span></td>
                                        <td><span>{{ $m['name'] }}</span></td>
                                        <td>
                                            @if($m['status'] == 1)
                                                <span class="p-2 badge bg-success">Validé</span>
                                            @else 
                                                <span class="p-2 badge bg-warning">En attente</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($m['status'] == 0)
                                                <button data-name="{{ $m['caisse'] }}" data-id="{{ $m['movementId'] }}" class="removeClassButton validMovement text-success"><i class="font-30 fadeIn animated bx bx-check"></i></button>
                                                <button data-name="{{ $m['caisse'] }}" data-id="{{ $m['movementId'] }}" class="removeClassButton cancelMovement text-danger"><i class="font-30 fadeIn animated bx bx-x"></i></button>
                                            @endif
                                        </td>
                                        <td><span>{{ $m['movementId'] }}</span></td>
                                    </tr>
                                @endforeach
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Modal de confirmation de mouvement caisse en attente -->
        <div class="modal fade modal_radius" id="validMovementModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <form method="POST" action="{{ route('admin.updateCashMovement') }}">
                        @csrf
                        <div class="modal-body">
                            <h2 class="text-center validMovementModalTitle mb-3">Voulez-vous valider ce mouvement ?</h2>
                            <input type="hidden" name="movement_id" id="movement_id" value="">
                            <input type="hidden" name="caisse" id="caisse" value="">
                            <div class="d-flex w-100 justify-content-center">
                                <button type="button" class="btn btn-dark px-5" data-bs-dismiss="modal">Annuler</button>
                                <button style="margin-left:15px" type="submit" class="btn btn-dark px-5 ">Oui</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Modal d'annulation de mouvement caisse en attente -->
        <div class="modal fade modal_radius" id="cancelMovementModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <form method="POST" action="{{ route('admin.cancelCashMovement') }}">
                        @csrf
                        <div class="modal-body">
                            <h2 class="text-center cancelMovementModalTitle mb-3">Voulez-vous annuler ce mouvement ?</h2>
                            <input type="hidden" name="movement_id" id="cancel_movement_id" value="">
                            <input type="hidden" name="caisse" id="cancel_caisse" value="">
                            <div class="d-flex w-100 justify-content-center">
                                <button type="button" class="btn btn-dark px-5" data-bs-dismiss="modal">Annuler</button>
                                <button style="margin-left:15px" type="submit" class="btn btn-dark px-5 ">Oui</button>
                            </div>
                        </div>
                    </form>
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


