
@extends("layouts.app")

@section("style")
    <link href="{{asset('assets/plugins/datatable/css/dataTables.bootstrap5.min.css')}}" rel="stylesheet" />
    <link href="{{asset('assets/plugins/select2/css/select2.min.css')}}" rel="stylesheet" />
    {{-- <link href="assets/plugins/select2/css/select2-bootstrap4.css" rel="stylesheet" /> --}}
    {{-- <link href="assets/css/style_reassort.css" rel="stylesheet" /> --}}

    {{-- <link href="{{asset('assets/plugins/datetimepicker/css/classic.css')}}" rel="stylesheet" />
    <link href="{{asset('assets/plugins/datetimepicker/css/classic.time.css')}}" rel="stylesheet" />
    <link href="{{asset('assets/plugins/datetimepicker/css/classic.date.css')}}" rel="stylesheet" />
    <link href="{{asset('assets/plugins/bootstrap-material-datetimepicker/css/bootstrap-material-datetimepicker.min.css')}}" rel="stylesheet" /> --}}

   
@endsection

@section("wrapper")
    <div class="page-wrapper">
        <div class="page-content">

            {{-- Alert d erreur --}}
            @include('layouts.transfert.alertSuccesError')

            {{-- @dd(asset('assets/plugins/datatable/css/dataTables.bootstrap5.min.css')) --}}

            <div>       


                <div class="card-body p-0 mt-2" id="list_reassort_id">
                    <div id="id_reassor1" class="card card_product_commande">
                        <div class="table-responsive p-3">
                            <table id="example4" class="table mb-0 dataTable table_mobile_responsive w-100 table_list_order table-striped table-bordered">
                                <thead>
                                    <tr>
                                        <th title="L'id du produit">ID produit</th>
                                        <th title="L'id du produit">label</th>
                                        <th title="L'entrepôt qui va être décrémenté">Entrepot</th>
                                        <th title="L'id du produit">Seuil d'alerte</th>
                                        <th title="L'entrepôt qui va être décrémenté">Stock min</th>
                                        <th title="L'id du produit">Stock actuel</th>
                                        <th title="L'id du produit">{{$interval}}</th>
                                       

                                    </tr>
                                </thead>
                                <tbody id="tbody_id_1">
                                    @foreach ($vente_by_product as $key => $vente)
                                
                                        <tr>
                                            <td style="text-align: left !important;">{{$vente["fk_product"]}}</td>
                                            <td style="text-align: left !important;">{{$vente["label"]}}</td>
                                            <td style="text-align: left !important;">{{$name_entrepot}}</td>
                                            <td style="text-align: left !important;">{{$vente["seuil_stock_alerte"]}}</td>
                                            <td style="text-align: left !important;">{{$vente["desiredstock"]}}</td>
                                            <td style="text-align: left !important;">{{$vente["stock_actuel"]}}</td>
                                            <td style="text-align: left !important;">{{$vente["total_vente"]}}</td>
                                            

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
<script src="{{asset('assets/plugins/select2/js/select2.min.js')}}"></script>

<script src="{{asset('assets/plugins/datetimepicker/js/legacy.js')}}"></script>
<script src="{{asset('assets/plugins/datetimepicker/js/picker.js')}}"></script>

<script src="{{asset('assets/plugins/datetimepicker/js/picker.time.js')}}"></script>
<script src="{{asset('assets/plugins/datetimepicker/js/picker.date.js')}}"></script>

<script src="{{asset('assets/plugins/bootstrap-material-datetimepicker/js/moment.min.js')}}"></script>
<script src="{{asset('assets/plugins/bootstrap-material-datetimepicker/js/bootstrap-material-datetimepicker.min.js')}}"></script>


<script>
     csrfToken = $('input[name=_token]').val();http:

    $('#example4').DataTable({
        language: {
            info: "_START_ à _END_ sur _TOTAL_ entrées",
            infoEmpty: "Aucune données",
            infoFiltered: "(filtrés sur un total de _MAX_ éléments)",
            lengthMenu: "_MENU_",
            search: "",
            paginate: {
                first: ">>",
                last: "<<",
                next: ">",
                previous: "<"
            }
        },


        order: [[6, 'desc']], // Tri par défaut sur la première colonne en ordre décroissant
        pageLength: 15,

    
        lengthMenu: [
            [5,15, 25, 50, -1],
            ['5','15', '25', '50', 'Tout']
        ],

        initComplete: function() {      
            
            liste_entrepot = "{{$liste_entrepot}}";
            liste_entrepot = $("<textarea/>").html(liste_entrepot).text();
            liste_entrepot = JSON.parse(liste_entrepot);

            conntainer = $('#example4_wrapper').find('.dataTables_length');
            conntainer.addClass('d-flex');


            var input = $('<input style="width:80px;margin-left: 5%;;text-align: center;" type="text" class="form-control form-control-sm" placeholder="N Jour">').appendTo(conntainer)

            var button = $('<a id="btn_actualiser" href="#" style="width:90px;margin-left: 5%;;text-align: center;color: #fff !important;" type="button" class="btn btn-primary btn-sm">Actualiser</a>').appendTo(conntainer);

            
            var select = $('<select class="form-select form-select-sm"" style="margin-left: 5%;"> <option value="">Selectionner un entrepôt</option> </select>')
            .insertAfter($('#example4_wrapper').find('.dataTables_length').find('label'))
            .on('change', function() {

                console.log($(this).val());
                
            });


            Object.entries(liste_entrepot).forEach(([key, value]) => {

                var id_entrepot = value["id_entrepot"];
                var name_entrepot = value["name_entrepot"];
                
                select.append('<option value="' + id_entrepot + '">' + name_entrepot + '</option>');
            });

            // Rendre le bouton Actualiser grisé par défaut
            button.addClass('disabled');

            // Ajouter un gestionnaire d'événements sur l'input et le select pour vérifier si les valeurs sont définies
            input.on('input', toggleButtonState);
            select.on('change', toggleButtonState);

            // Fonction pour activer/désactiver le bouton en fonction de la présence de valeurs dans l'input et le select
            function toggleButtonState() {
                var id_entrepot = select.val();
                var Njour = input.val();

                console.log("ddddd");

                // Vérifier si les valeurs de id_entrepot et Njour sont définies
                if (id_entrepot && Njour) {
                    // Activer le bouton Actualiser
                    button.removeClass('disabled');
                } else {
                    // Désactiver le bouton Actualiser
                    button.addClass('disabled');
                }
            }


            button.click(function() {
                var id_entrepot = select.val();
                var Njour = input.val(); // Définissez la valeur de Njour ici
                var href = "{{ route('alertStocks', ['idEntrepot' => ':idEntrepot', 'Njour' => ':Njour']) }}";
                href = href.replace(':idEntrepot', id_entrepot).replace(':Njour', Njour);

                console.log(href);
                window.location.href = href;
            });



        }

    });


</script>


<script>
    $('.datepicker').pickadate({
        selectMonths: true,
        selectYears: true
    })
</script>







@endsection


