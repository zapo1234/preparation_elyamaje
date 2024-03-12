
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
                                        <th title="L'id du produit">Nom du fichier</th>
                                        <th title="L'id du produit">Date</th>
                                        <th title="L'entrepôt qui va être décrémenté">Etat</th>
                                        <th style="text-align: center !important" title="L'entrepôt qui va être décrémenté">Action</th>
                                    </tr>
                                </thead>
                                <tbody id="tbody_id_1">
                                    @foreach ($dataAlertes as $key => $alerte)

                                    {{-- @dd($alerte["date"]) --}}
                                
                                        <tr>
                                            <td style="text-align: left !important;">
                                                <a href="{{$alerte["url"]}}" download="{{$alerte["filme_name"]}}" style="color: #8833ff !important;">
                                                    {{$alerte["filme_name"]}}
                                                </a>
                                            </td>
                                            <td style="text-align: left !important;">{{$alerte["date"]}}</td>
                                            <td style="text-align: left !important;">{{$alerte["etat"]}}</td>
                                            <td style="text-align: center !important">
                                                <div class="mt-5p">
                                                    <button data-bs-toggle="modal" data-bs-target="#exampleFullScreenModal_{{$key}}" type="submit" class="btn" title="Visualiser le transfère">
                                                        <i style="color:#333333" class="lni lni-eye"></i>
                                                    </button>
                                                   
                                                   
                                                    @if ($alerte["etat"] != "Traitée")
                                                        <button class="btn icon-container" title="Valider" data-bs-toggle="modal" data-bs-target="#exampleDarkModal_{{$key}}">
                                                            <i class="fadeIn animated bx bx-sync"></i>
                                                        </button>
                                                    @else
                                                        <button class="btn icon-container" title="Déja validée"  style="color:gray">
                                                            <i class="fadeIn animated bx bx-sync"></i>
                                                        </button>
                                                    @endif


                                                  
                                            
                                                    <div class="modal fade" id="exampleFullScreenModal_{{$key}}" tabindex="-1" style="display: none;" aria-hidden="true">
                                                        @include('layouts.transfert.alerteVisualisation', 
                                                        [
                                                            'contenu' => $alerte["contenu"],
                                                            'date' => $alerte["date"],
                                                            'name_alerte' => $alerte["filme_name"],
                                                            'url' => $alerte["url"],
                                                            'etat' => $alerte["etat"],
                                                        ])
                                                    </div>

                                                    <div class="modal fade" id="exampleDarkModal_{{$key}}" tabindex="-1" aria-hidden="true" style="display: none;">
                                                        <div class="modal-dialog modal-lg modal-dialog-centered">
                                                            <div class="modal-content bg-dark">
                                                                <div class="modal-header">
                                                                    <h5 class="modal-title text-white">Alerte {{$alerte["filme_name"]}}</h5>
                                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                                </div>
                                                                <div class="modal-body text-white">
                                                                    <p>Ête vous sur de vouloir passer l'état de cette alerte à l'état traité</p>
                                                                </div>
                                                                <div class="modal-footer">
                                                                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Fermer</button>
                                                                   
                                                                     <a style="color:#fff !important" href="{{ route('deplacerFichier', ['nomFichier' => $alerte["filme_name"]]) }}" class="btn btn-dark">Valider</a>
                                                                  
                                                                </div>
                                                            </div>
                                                        </div>
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


        order: [[0, 'desc']], // Tri par défaut sur la première colonne en ordre décroissant
        pageLength: 15,

    
        lengthMenu: [
            [5,15, 25, 50, -1],
            ['5','15', '25', '50', 'Tout']
        ],

       

    });

    $('.example6').DataTable({
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


        order: [[0, 'desc']], // Tri par défaut sur la première colonne en ordre décroissant
        pageLength: 1000,

        dom: 'Bfrtip',

        buttons: [
            'copy',
            'excel',
            'csv',
            'pdf',
            'print'
        ],

        lengthMenu: [
            [5,10, 25, 50, -1],
            ['5','10', '25', '50', 'Tout']
        ],

    });


</script>


<script>
    $('.datepicker').pickadate({
        selectMonths: true,
        selectYears: true
    })
</script>







@endsection


