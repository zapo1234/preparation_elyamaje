
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
                                        <th title="Id de la commande">ID</th>
                                        <th title="Référence de la commande">Ref commandes</th>
                                        <th title="Réference du fournisseur">Ref Fournisseur</th>
                                        <th title="Date de la commande">Date commande</th>
                                        <th title="Date de livraison de la commande">Date Livraison</th>
                                        <th title="Montant de la commande en ttc">TTC</th>
                                        <th title="Statut de la commande">statut</th>
                                        <th title="">Action</th>

                                    </tr>
                                </thead>
                                <tbody id="tbody_id_1">
                                    @foreach ($datasOrders as $key => $order)
                               
                                        <tr>                                          
                                            <td style="text-align: left !important;">{{$order["id"]}}</td>
                                            <td style="text-align: left !important;">{{$order["ref"]}}</td>

                                            <td style="text-align: left !important;">{{$order["ref_supplier"]}}</td>
                                            <td style="text-align: left !important;">{{$order["date_commande"]}}</td>
                                            <td style="text-align: left !important;">{{$order["date_livraison"]}}</td>
                                            <td style="text-align: left !important;">{{$order["total_ttc"]}}</td>
                                            <td style="text-align: left !important;">{!!$order["statut"]!!}</td>
                                            




                                            <td style="text-align: center !important">
                                                <div class="mt-5p">

                                                    <button data-bs-toggle="modal" data-bs-target="#exampleFullScreenModal_{{$key}}" type="submit" class="btn" title="Visualiser le transfère">
                                                        <i style="color:#333333" class="lni lni-eye"></i>
                                                    </button>
                                            
                                                    <div class="modal fade" id="exampleFullScreenModal_{{$key}}" tabindex="-1" style="display: none;" aria-hidden="true">
                                                        @include('layouts.transfert.detailCommandeFournisseur', 
                                                        [
                                                            'ref_commande' => $order["ref"],
                                                            'lines' => $order["lines"],
                                                        ])
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


