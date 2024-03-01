@extends("layouts.app")

@section("style")
    <link href="{{asset('assets/plugins/datatable/css/dataTables.bootstrap5.min.css')}}" rel="stylesheet" />
    <link href="assets/plugins/select2/css/select2.min.css" rel="stylesheet" />
    <link href="assets/plugins/select2/css/select2-bootstrap4.css" rel="stylesheet" />
@endsection

@section("wrapper")









    {{-- Modal de confirmation Mise à zéro des quantités des kits limes --}}

    <div class="modal fade" id="exampleDarkModal1" tabindex="-1" aria-hidden="true" style="display: none;">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content bg-dark">
                <div class="modal-header">
                    <h5 class="modal-title text-white">Mise à zéro des quantités des kits limes</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-white">
                    <!-- Début du formulaire -->
                    <form id="resetQuantitiesForm1" method="POST" action="{{ route('initialQtyLot') }}">
                        @csrf
                        <p>Êtes-vous sûr de vouloir mettre les quantités des kits de lime à zéro ?</p>
                        <!-- Ajoutez d'autres champs de formulaire si nécessaire -->
                    </form>
                    <!-- Fin du formulaire -->
                </div>
                <div class="modal-footer">
                    <button id="cancelle1" type="button" class="btn btn-light" data-bs-dismiss="modal">Annuler</button>
                    <button id="cofirme1" type="submit" form="resetQuantitiesForm1" class="btn btn-dark">Confirmer</button>
                </div>
            </div>
        </div>
    </div>
    {{-- -------------------------------------------------------------------------------------------------------------------- --}}

     {{-- Modal de confirmation Mise à zéro des quantités des kits limes --}}

    <div class="modal fade" id="exampleDarkModal2" tabindex="-1" aria-hidden="true" style="display: none;">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content bg-dark">
                <div class="modal-header">
                    <h5 class="modal-title text-white">Synchronisation des quantité dolibarr & preparation</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-white">
                    <!-- Début du formulaire -->
                    <form id="resetQuantitiesForm2" method="POST" action="{{ route('updateProducts') }}">
                        @csrf
                        <p>Êtes-vous sûr de vouloir mettre à jour les quantitées des produits de preparation(caisse) ?</p>
                        <p>Attention cela va écraser les anciennes quantitées de preparation et caisse</p>
                    </form>
                    <!-- Fin du formulaire -->
                </div>
                <div class="modal-footer">
                    <button id="cancelle2" type="button" class="btn btn-light" data-bs-dismiss="modal">Annuler</button>
                    <button id="cofirme2" type="submit" form="resetQuantitiesForm2" class="btn btn-dark">Confirmer</button>
                </div>
            </div>
        </div>
    </div>
    {{-- Modal de confirmation --}}

    <div class="page-wrapper">
        <div class="page-content">

            @if(session()->has('success'))
            <div id="success-message" class="alert alert-success border-0 bg-success alert-dismissible fade show" style="z-index: 100;">
                <div class="text-white">{{ session()->get('success') }}</div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <script>
                setTimeout(function() {
                    $('#success-message').remove();
                }, 2000);
            </script>

            @endif
            @if(session()->has('error'))
            <div id="error-message" class="alert alert-danger border-0 bg-danger alert-dismissible fade show" style="z-index: 100;">
                <div class="text-white">{{ session()->get('error') }}</div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <script>
                setTimeout(function() {
                    $('#error-message').remove();
                }, 2000);
            </script>
            @endif

            <div class="d-flex w-100 justify-content-between page-breadcrumb d-sm-flex align-items-center mb-3">
                <div class="d-flex align-items-center multiple_title">
                    <div class="breadcrumb-title pe-3">
                        Beauty Prof's
                    </div>
                    <div class="ps-3">
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb mb-0 p-0">
                                <li class="breadcrumb-item active" aria-current="page">Gestion des stocks</li>
                            </ol>
                        </nav>
                    </div>

                    <div class="d-flex gap-2" style="margin-left:20px">
                        <button type="button" class="btn btn-dark" data-bs-toggle="modal" data-bs-target="#exampleDarkModal1">Mettre les kits à zéro</button>
                        <button type="button" class="btn btn-dark" data-bs-toggle="modal" data-bs-target="#exampleDarkModal2">Synchro dolibarr &amp; préparation</button>
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
                        <form  method="POST" action="{{ route('admin.stockscat') }}" style="overflow:hidden; width:70%;" class="radius-10">
                            @csrf
                            <table id="" style="width:100%" class="kitStock">
                                @foreach($data as $key => $val)
                                    <tr style="padding:3%">
                                        <td style="background-color:black;font-size:16px !important; font-weight:500; text-transform:uppercase;color:white;width:50%;border-top-left-radius: 10px;border-top-right-radius: 10px;">{{  $key }} </td>
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
                                                <td style="background-color: #333333;color:white;padding:10px;">{{ $name_list  }}  Nombre d'unités : {{  $lim[1] }} </td>
                                            </tr>
                                            
                                            @foreach($vv as $ls =>$vc)
                                                <tr>
                                                    <td style="display:flex; min-height:50px; align-items:center; gap:20px; justify-content:space-between;padding-bottom:25px !important; padding-top:8px !important;">
                                                                <span style="font-weight:500; width:300px;">{{ $vc['libelle_family'] }} </span>
                                                                <div class="d-flex align-items-center justify-content-center">
                                                                    Quantité :  
                                                                    <input type="text" name="qts[]" value ="{{  $vc['quantite']  }}" readonly style="border:none !important;"> 
                                                                </div>
                                                            <input type="text" name="qte[]" placeholder="Nouveau stock"> 
                                                    </td>
                                                </tr>
                                            @endforeach
                                        @endforeach
                                    @endforeach  
                                @endforeach
                            </table>
                            <div class="d-flex flex-wrap align-items-center justify-content-center"style="width:25%;position:fixed;right:30px;top:50%;">
                                <button type="submit" class="btn btn-primary text-white"  style="margin-bottom:20px;">Modifier le stock</button>
                                @foreach($list_product as $val)
                                    @if($val['libelle'] == "Aucun mouvement de stock")
                                    <p style="text-align: center;width:90%;">
                                        {{ $val['libelle'] }}<br/>
                                    </p>
                                    @else
                                    <p style="text-align: left;width:90%;" class="d-flex gap-1">
                                        <i class="fadeIn animated bx bx-check-circle" style="color:#198754; fill:#198754;"></i>Le stock de {{ $val['libelle'] }} a été modifié.<br/>
                                    </p>
                                    @endif
                                @endforeach
                            <div>                                    </form>
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

{{-- <script>
    $(document).ready(function() {
        $('#cofirme').click(function() {
            // Envoyer une requête Ajax vers la route
            $.ajax({
                url: '{{ route("initialQtyLot") }}',
                type: 'GET',
                dataType: 'json', // Changez ceci en fonction de votre retour de données
                success: function(response) {
                    // Traitement des données de retour si nécessaire
                    console.log(response);
                },
                error: function(xhr, status, error) {
                    // Gérer les erreurs
                    console.error('Erreur lors de la requête : ' + status);
                }
            });
        });
    });
</script> --}}


@endsection


