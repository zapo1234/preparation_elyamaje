
@extends("layouts.app")

@section("style")
    <link href="{{asset('assets/plugins/datatable/css/dataTables.bootstrap5.min.css')}}" rel="stylesheet" />
    <link href="assets/plugins/select2/css/select2.min.css" rel="stylesheet" />
    <link href="assets/plugins/select2/css/select2-bootstrap4.css" rel="stylesheet" />
@endsection

@section("wrapper")
    <div class="page-wrapper">
        <div class="page-content">
            <div class="page-breadcrumb d-sm-flex align-items-center mb-3">
                <div class="breadcrumb-title pe-3">Colissimo</div>
                <div class="ps-3">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0 p-0">
                            <li class="breadcrumb-item active" aria-current="page">Bordereaux</li>
                        </ol>
                    </nav>
                </div>
                <div class="ms-auto ms-auto-responsive">
                    <button id="show_modal_bordereau" type="button" class="btn btn-dark px-5">Générer bordereau</button>
                </div>
            </div>


            <div class="switcher-wrapper">
                <div class="switcher-btn"> <i class="bx bx-help-circle"></i></div>
                <div class="switcher-body">
                    <div class="d-flex align-items-center">
                        <h5 class="mb-0 text-uppercase">Informations</h5>
                        <button type="button" class="btn-close ms-auto close-switcher" aria-label="Close"></button>
                    </div>
                    <hr>
                    <div class="d-flex align-items-center justify-content-between">
                        Ici, vous pouvez retrouver la liste des bordereaux et vous pouvez également en générer un avec le bouton en haut à droite
                    </div>
                </div>
            </div>


            <!-- Modal Génération Bordereau par date -->
            <div class="modal fade" id="modalBordereau" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered" role="document">
                    <div class="modal-content">
                        <div class="modal-body">
                            <form method="POST" action="{{ route('bordereau.generate') }}">
                                @csrf
                                <h2 class="text-center">Choisir la date</h2>
                                <div class="d-flex justify-content-center w-100">
                                    <input class="date_bordereau_input" type="date" name="date" value="{{ date('Y-m-d') }}">
                                </div>
                                <div class="d-flex justify-content-center mt-3 w-100">
                                    <button type="button" class="btn btn-dark px-5" data-bs-dismiss="modal">Annuler</button>
                                    <button style="margin-left:15px" type="submit" class="btn btn-dark px-5">Générer</button>
                                </div>
                            </form>
                        </div>
                    </div>
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
            
            <div class="card card_table_mobile_responsive">
                <div class="card-body">
                    <div class="d-flex justify-content-center">
                        <div class="loading spinner-border text-dark" role="status"> 
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                    <table id="example" class="d-none table_mobile_responsive w-100 table table-striped table-bordered">
                        <thead>
                            <tr>
                                <th>Généré le</th>
                                <th>Date</th>
                                <th class="col-md-2">Nombre de commandes</th>
                                <th class="col-md-3">Bordereau</th>
                            </tr>
                        </thead>
                        <tbody>

                            @foreach($bordereaux as $bordereau)
                                <tr>
                                    <td data-label="Généré le">{{ $bordereau['created_at'] }}</td>
                                    <td data-label="Date">{{ $bordereau['label_date'] }}</td>
                                    <td data-label="Nombre de commandes">{{ $bordereau['number_order'] }}</td>
                                    <td data-label="Bordereau">
                                        <div class="d-flex w-100 justify-content-between">
                                            <form method="POST" action="{{ route('bordereau.download') }}">
                                                @csrf
                                                <input name="bordereau_id" type="hidden" value="{{ $bordereau['parcel_number'] }}">
                                                <button type="submit" class="download_bordereau_button"><i class="bx bx-show-alt"></i>Bordereau n°{{ $bordereau['parcel_number'] }} <span class="label_created_at text-secondary">({{ $bordereau['label_date'] }})</span></button>
                                            </form>
                                            <div>
                                                <button title="Supprimer le bordereau" data-id="{{ $bordereau['parcel_number'] }}" type="submit" class="delete_bordereau download_label_button"><i class="bx bx-trash"></i></button>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>


    	<!-- Modal supression -->
        <div class="modal fade" id="deleteBordereauModalCenter" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-body">
                        <h2 class="text-center">Voulez-vous supprimer ce bordereau ?</h2>
                        <form method="POST" action="{{ route('bordereau.delete') }}">
                            @csrf
                            <input id="bordereau_parcel_number_to_delete" name="parcel_number" type="hidden" value="">
                            <div class="d-flex justify-content-center mt-3 w-100">
                                <button type="button" class="btn btn-dark px-5" data-bs-dismiss="modal">Annuler</button>
                                <button style="margin-left:15px" type="submit" class="btn btn-dark px-5">Oui</button>
                            </div>
                        </form>
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
                "initComplete": function(settings, json) {
                    $(".loading").hide()
                    $("#example").removeClass('d-none')
                }
            })
        })

        $("#show_modal_bordereau").on('click', function(){
            $("#modalBordereau").modal('show')
        })

        $(".delete_bordereau").on('click', function(){
            $("#bordereau_parcel_number_to_delete").val($(this).attr('data-id'))
            $("#deleteBordereauModalCenter").modal('show')
        })


    </script>
@endsection


