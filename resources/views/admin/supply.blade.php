
@extends("layouts.app")

@section("style")
    <link href="{{asset('assets/plugins/datatable/css/dataTables.bootstrap5.min.css')}}" rel="stylesheet" />
    <link href="assets/plugins/select2/css/select2.min.css" rel="stylesheet" />
    <link href="assets/plugins/select2/css/select2-bootstrap4.css" rel="stylesheet" />
    <link href="assets/css/style_reassort.css" rel="stylesheet" />

   
@endsection

@section("wrapper")
    <div class="page-wrapper">
        <div class="page-content">



        {{-- alert succes --}}
        <div class="alert alert-success border-0 bg-success alert-dismissible fade show alert-succes-calcul" style="display: none">

            <div class="text-white text_alert">Transfère réussit</div>

            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>

        </div>

        {{-- alert erreur --}}
        <div class="alert alert-danger border-0 bg-danger alert-dismissible fade show alert-danger-calcul" style="display: none">

            <div class="text-white text_alert">Erreur de transfère</div>

            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>

        </div>


            
            <div class="card">
                <form method="POST" action="{{route('createReassort')}}">
                    @csrf
                    <div class="card-body row">
                        <div id="sender" class="col-md-4">

                            <label for="" class="form-label">Dépot d'éxpediteur</label>

                            <select id="entrepot_source" name="entrepot_source" class="form-select" aria-label="Default select example">
                                <option value="0" selected="">Selectionner l'entrepot à déstoquer</option>
                                @foreach ($listWarehouses as $listWarehouse)
                                  
                                    @if (isset($products_reassort))
                                        @if ($listWarehouse["id"] == $entrepot_source)
                                            <option value="{{$listWarehouse["id"]}}" selected>"{{$listWarehouse["libelle"]}}"</option>
                                        @else
                                            <option value="{{$listWarehouse["id"]}}">"{{$listWarehouse["libelle"]}}"</option>
                                        @endif 
                                    @else  
                                        <option value="{{$listWarehouse["id"]}}">"{{$listWarehouse["libelle"]}}"</option>
                                    @endif
                                    
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-4 d-flex justify-content-center align-items-center">
                            <p class="mb-0">>>>>>>>>>>>>>>>>>>>>>></p>
                        </div>

                        <div id="recipient" class="col-md-4">
                            <label for="" class="form-label">Dépot de reception</label>

                            <select id="entrepot_destination" name="entrepot_destination" class="form-select" aria-label="Default select example">
                                <option value="0" selected="">Selectionner l'entrepot à approvisionner</option>
                                @foreach ($listWarehouses as $listWarehouse)
                                    @if (isset($products_reassort))
                                        @if ($listWarehouse["id"] == $entrepot_destination)
                                            <option value="{{$listWarehouse["id"]}}" selected>"{{$listWarehouse["libelle"]}}"</option>
                                        @else
                                            <option value="{{$listWarehouse["id"]}}">"{{$listWarehouse["libelle"]}}"</option>
                                        @endif
                                    @else  
                                        <option value="{{$listWarehouse["id"]}}">"{{$listWarehouse["libelle"]}}"</option>
                                    @endif
                                    
                                @endforeach
                            </select>
                        </div>

                        <div class="col-12 d-flex justify-content-center mt-5">
                            <button id="id_sub_calcul_reassort" onclick="this.disabled=true;this.form.submit();" class="btn btn-primary" type="submit">Générer le réassort</button>
                        </div>
                    </div>

                </form>
               
            </div>

            @if (isset($products_reassort))


            <div id="id_reassor1" class="card card_product_commande">
                <div class="table-responsive p-3">
                    <table id="example2" class="table mb-0 dataTable">
                        <thead>
                            <tr>
                                <th title="L'entrepôt qui va être décrémenté">ID</th>
                                <th title="L'entrepôt qui va être décrémenté">Code barre</th>
                                <th title="L'entrepôt qui va être décrémenté">Nom produit</th>
                                <th title="L'entrepôt qui va être décrémenté">Prix d'achat unitaire</th>
                                <th title="L'entrepôt qui va être décrémenté">Entrepôt source (Qté)</th>
                                <th title="Points actuellement valide de l'utilisateur">Demande/sem</th>
                                <th title="L'entrepôt qui va être alimenter">Entrepôt de destination (Qté)</th>
                                <th title="Points actuellement valide de l'utilisateur">Qté souhaité</th>

                                <th title="Points actuellement valide de l'utilisateur">Qté a transférer</th>
                                <th title="Points actuellement valide de l'utilisateur">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="tbody_id_1">
                            @foreach ($products_reassort as $k1 => $value)
                        
                                <tr data_id_product="{{$value["product_id"]}}" id="{{$value["product_id"]}}_line" class="class_line1">
                                    <td data-key="product_id" data-value="{{$value["product_id"]}}" id="{{$value["product_id"]}}_product_id" style="text-align: left !important;">{{$value["product_id"]}}</td>
                                    <td data-key="barcode" data-value="{{$value["barcode"]}}" id="{{$value["barcode"]}}_product_id" style="text-align: left !important;">{{$value["barcode"]}}</td>

                                    <td data-key="libelle" data-value="{{$value["libelle"]}}" id="{{$value["product_id"]}}_libelle" style="text-align: left !important;">{{$value["libelle"]}}</td>
                                    <td data-key="price" data-value="{{$value["price"]}}" id="{{$value["price"]}}_price" style="text-align: left !important;">{{round($value["price"],2)}}</td>

                                    <td data-key="qte_en_stock_in_source" data-value="{{$value["qte_en_stock_in_source"]}}" id="{{$value["product_id"]}}_qte_en_stock_in_source" style="text-align: left !important;">{{$value["name_entrepot_a_destocker"]}} ({{$value["qte_en_stock_in_source"]}})</td>
                                    <td data-key="demande" data-value="{{$value["demande"]}}" id="{{$value["product_id"]}}_demande">{{$value["demande"]}}</td>
                                    <td data-key="qte_act" data-value="{{$value["qte_act"]}}" id="{{$value["product_id"]}}_qte_act">{{$value["entrepot_a_alimenter"]}} ({{$value["qte_act"]}})</td>
                                    <td data-key="qte_optimale" data-value="{{$value["qte_optimale"]}}" id="{{$value["product_id"]}}_qte_optimale">{{$value["qte_optimale"]}}</td>
                                    <td data-key="qte_transfere" data-value="{{$value["qte_optimale"] - $value["qte_act"]}}" id="{{$value["product_id"]}}_qte_transfere"><input class="text-center" style="width: 50px" type="text" value="{{$value["qte_optimale"] - $value["qte_act"]}}" disabled></td>

                                    <td data-key="action" id="{{$value["product_id"]}}_action">
                                        <button onclick='delete_line({{$value["product_id"]}})' type="button" class="btn" title="Supprimer l'offre" style="margin: 0;padding: 0;" class="update_line">
                                            <a class="" title="Supprimer l'offre" href="javascript:void(0)">
                                                <i class="fadeIn animated bx bx-trash"></i>
                                            </a>
                                        </button>
                                        <button onclick='update_line({{$value["product_id"]}})' type="button" class="btn" title="Supprimer l'offre" style="margin: 0;padding: 0;" class="remove_line">
                                            <a class="" title="Supprimer l'offre" href="javascript:void(0)">
                                                <i class="fadeIn animated bx bxs-edit"></i>
                                            </a>
                                        </button>
                                    </td>

                                    
                                    
                                </tr>
                            
                            @endforeach

                        </tbody>

                    

                    </table>
                </div>

                <div class="col-12 d-flex justify-content-center mt-5">
                    <button id="id_sub_validation_reassort" onclick="valide_reassort1()" class="btn btn-primary mb-4" type="submit">Valider le réassort</button>
                </div>

              


                <div class="row g-3 mb-4">
                    <div class="col-md-2"></div>

                    <div class="col-md-6">

                        {{-- {{dd($entrepot_source_product)}} --}}

                        <select id="product_add" class="js-states form-control" name="email">
                            <option style="width:100%" value="" selected>Selectionnez le produit</option>
                            @foreach($entrepot_source_product as $k => $val) 
                                <option value="{{json_encode($val)}}"> {{$val["libelle"]}} </option>
                            @endforeach
                        </select>

                    </div>
                    <div class="col-md-2">
                        <input id="qte_id" class="qte_class" type="text" placeholder="Quantité">
                    </div>
                    
                    <div class="font-22 text-primary col-md-2">	
                        <i onclick="add_line()" class="lni lni-circle-plus"></i>
                    </div>
                </div>



            </div>

            <div id="id_reassor2" class="card card_product_no_commande d-none">
                <div class="table-responsive p-3">

                    <div>--------------------------------------------------------------------------------------</div>

                    <table id="example3" class="table mb-0 dataTable">
                        <thead>
                            <tr>
                                <th title="L'entrepôt qui va être décrémenté">ID</th>
                                <th title="L'entrepôt qui va être décrémenté">Code barre</th>
                                <th title="L'entrepôt qui va être décrémenté">Nom produit</th>
                                <th title="L'entrepôt qui va être décrémenté">Prix d'achat unitaire</th>
                                <th title="L'entrepôt qui va être décrémenté">Entrepôt source (Qté)</th>
                                <th title="Points actuellement valide de l'utilisateur">Demande/sem</th>
                                <th title="L'entrepôt qui va être alimenter">Entrepôt de destination (Qté)</th>
                                <th title="Points actuellement valide de l'utilisateur">Qté souhaité</th>

                                <th title="Points actuellement valide de l'utilisateur">Qté a transférer</th>
                                <th title="Points actuellement valide de l'utilisateur">Actions</th>
                            </tr>
                        </thead>  
                            @foreach ($products_non_vendu_in_last_month_inf_5 as $k2 => $value2)
                                <tr data_id_product="{{$value2["product_id"]}}" id="{{$value2["product_id"]}}_line"  class="class_line2">

                                    <td data-key="product_id" data-value="{{$value2["product_id"]}}" id="{{$value2["product_id"]}}_product_id" style="text-align: left !important;">{{$value2["product_id"]}}</td>
                                    <td data-key="barcode" data-value="{{$value2["barcode"]}}" id="{{$value2["barcode"]}}_product_id" style="text-align: left !important;">{{$value2["barcode"]}}</td>

                                    <td data-key="libelle" data-value="{{$value2["libelle"]}}" id="{{$value2["product_id"]}}_libelle" style="text-align: left !important;">{{$value2["libelle"]}}</td>
                                    <td data-key="price" data-value="{{$value2["price"]}}" id="{{$value2["price"]}}_price" style="text-align: left !important;">{{round($value2["price"],2)}}</td>
                                    <td data-key="qte_en_stock_in_source" data-value="{{$value2["qte_en_stock_in_source"]}}" id="{{$value2["product_id"]}}_qte_en_stock_in_source" style="text-align: left !important;">{{$value2["name_entrepot_a_destocker"]}} ({{$value2["qte_en_stock_in_source"]}})</td>
                                    <td data-key="demande" data-value="{{$value2["demande"]}}" id="{{$value2["product_id"]}}_demande">{{$value2["demande"]}}</td>
                                    <td data-key="qte_ac" data-value="{{$value2["qte_act"]}}" id="{{$value2["product_id"]}}_qte_act">{{$value2["entrepot_a_alimenter"]}} ({{$value2["qte_act"]}})</td>
                                    <td data-key="qte_optimale" data-value="{{$value2["qte_optimale"]}}" id="{{$value2["product_id"]}}_qte_optimale">{{$value2["qte_optimale"]}}</td>

                                    <td data-key="qte_transfere" data-value="0" id="{{$value2["product_id"]}}_qte_transfere"><input class="text-center" style="width: 70px" type="text" value="0" disabled></td>

                                    <td data-key="action" id="{{$value2["product_id"]}}_action">
                                    
                                        <button onclick='delete_line({{$value2["product_id"]}})' type="button" class="btn" title="Supprimer l'offre" style="margin: 0;padding: 0;">
                                            <a class="" title="Supprimer l'offre" href="javascript:void(0)">
                                                <i class="fadeIn animated bx bx-trash"></i>
                                            </a>
                                        </button>
                                        <button onclick='update_line({{$value2["product_id"]}})' type="button" class="btn" title="Supprimer l'offre" style="margin: 0;padding: 0;">
                                            <a class="" title="Supprimer l'offre" href="javascript:void(0)">
                                                <i class="fadeIn animated bx bxs-edit"></i>
                                            </a>
                                        </button>

                                    </td>
                                </tr>
                            @endforeach

                        </tbody>

                        
                    </table>

                    


                </div>

                <div class="col-12 d-flex justify-content-center mt-5">
                    <button onclick="valide_reassort2()" class="btn btn-primary mb-4" type="submit">Valider le reassort 2</button>
                </div>

            </div>

            @endif

            @if (isset($liste_reassort))

                <div id="id_reassor1" class="card card_product_commande">
                    <div class="table-responsive p-3">
                        <table id="example4" class="table mb-0 dataTable">
                            <thead>
                                <tr>
                                    <th title="L'entrepôt qui va être décrémenté">Identifiant</th>
                                    <th title="L'entrepôt qui va être décrémenté">Date</th>
                                    <th title="L'entrepôt qui va être décrémenté">Entrepot source</th>
                                    <th title="L'entrepôt qui va être décrémenté">Entrepôt de destination</th>
                                    <th title="Points actuellement valide de l'utilisateur">Etat</th>
                                </tr>
                            </thead>
                            <tbody id="tbody_id_1">
                                @foreach ($liste_reassort as $li => $value)
                            
                                    <tr id="{{$value["identifiant"]}}_transfert">
                                        <td id="{{$value["identifiant"]}}_identifiant" style="text-align: left !important;">{{$value["identifiant"]}}</td>
                                        <td id="{{$value["identifiant"]}}_date" style="text-align: left !important;">{{$value["date"]}}</td>
                                        <td id="{{$value["identifiant"]}}_entrepot_source" style="text-align: left !important;">{{$value["entrepot_source"]}}</td>
                                        <td id="{{$value["identifiant"]}}_entrepot_destination" style="text-align: left !important;">{{$value["entrepot_destination"]}}</td>
                                        <td id="{{$value["identifiant"]}}_etat" style="text-align: left !important;">{!!$value["etat"]!!}</td>
                                    </tr>
                                
                                @endforeach

                            </tbody>
                        </table>
                    </div>   
                </div>

            @endif
       
        </div>
    </div>

@endsection


@section("script")

<script src="{{asset('assets/plugins/datatable/js/jquery.dataTables.min.js')}}"></script>
<script src="{{asset('assets/plugins/datatable/js/dataTables.bootstrap5.min.js')}}"></script>
<script src="assets/plugins/select2/js/select2.min.js"></script>

<script>

    $('#product_add').select2({
        width: '100%'
    });

    csrfToken = $('input[name=_token]').val();
    
    $('#example2, #example3').DataTable({
    language: {
        info: "_TOTAL_ lignes",
        infoEmpty: "Aucun utlisateur à afficher",
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


    order: [[2, 'desc']], // Tri par défaut sur la première colonne en ordre décroissant
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

    "drawCallback": function(settings) {
        $('#entrepot_source, #entrepot_destination').prop('disabled', true);
    }

    });

    // function de suppression et de modifications

    function delete_line(id_line){

        var line =  '#'+id_line+'_line';
        console.log(line);
        $(line).fadeOut(600, function() {
            $(this).remove();
        });

    }
    
    function update_line(id_line){
        var line =  '#'+id_line+'_qte_transfere';
        $(line).find('input').prop("disabled", false);

        $(line).on('change', function(){
            val =  $(line).find('input').val();
            $(line).find('input').prop("disabled", true);
            // updater la data_value du tr
            $(line).attr('data-value', val);

        })

    }

    // validation du reassort 1 et 2 



    urlCreateReassort = "{{route('postReassort')}}";
    console.log(urlCreateReassort);
    function valide_reassort1(){



        var tabProduitReassort1 = [];
        $(".class_line1").each(function(index, row) {
            
            var rowAssociatif = {};
            $(row).find("td").each(function(index, cell) {
                if($(cell).attr("data-value")){
                    var key = $(cell).attr("data-key");
                    var value = $(cell).attr("data-value");
                    rowAssociatif[key] = value;
                }
            });
            tabProduitReassort1.push(rowAssociatif);
        });

      //  console.log(tabProduitReassort1);

        var urlCreateReassort = "{{route('postReassort')}}";
        var entrepot_source = $("#entrepot_source").val();
        var entrepot_destination = $("#entrepot_destination").val();


        $("#id_sub_validation_reassort").addClass("disabled-link");
        spinner = `<span id="id_spaner_transfere" class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>Transfère en coours...`;
        $("#id_sub_validation_reassort").html(spinner);



        $.ajax({
            url: urlCreateReassort,
            method: 'POST',
            // data : {tabProduitReassort1:JSON.stringify(tabProduitReassort1)},
            data : {
                tabProduitReassort1:tabProduitReassort1,
                entrepot_source:entrepot_source,
                entrepot_destination:entrepot_destination
                    
            },

            headers: {
                'X-CSRF-TOKEN': csrfToken
            },                       
            success: function(response) {

                console.log(response);

                if (response.response == true) {

                    $(".alert-succes-calcul").show();
                    setTimeout(() => {
                        $(".alert-succes-calcul").hide();
                    }, 4000);   

                    $('#example2 tbody').empty();

                    $("#id_sub_validation_reassort").removeClass("disabled-link");
                    $("#id_sub_validation_reassort").html("Valider le réassort");
                    $("#id_spaner_transfere").remove();

                }else{
					$(".alert-danger-calcul").show();
					setTimeout(() => {
						$(".alert-danger-calcul").hide();
					}, 4000);
                    console.log(response);
                }

            },
            error: function(xhr, status, error) {
                
                $(".alert-danger-calcul").show();
					setTimeout(() => {
						$(".alert-danger-calcul").hide();
					}, 4000);
					
					console.error(error);

            }
        });
        

    }


    function valide_reassort2(){
        var tabProduitReassort2 = [];
        $(".class_line2").each(function(index, row) {
            
            var rowAssociatif = {};
            $(row).find("td").each(function(index, cell) {
                if($(cell).attr("data-value")){
                    var key = $(cell).attr("data-key");
                    var value = $(cell).attr("data-value");
                    rowAssociatif[key] = value;
                }
            });
            tabProduitReassort2.push(rowAssociatif);
        });
        console.log(tabProduitReassort2);
    }

    // ajout d'un produit quantite


    function add_line(){

        // supprimer le tr qui affiche quand on a pas de donnée
        if ($('.dataTables_empty').length > 0) {
            $('.dataTables_empty').closest('tr').remove();
        }

        
        var data_product = JSON.parse($("#product_add").val())
        qte =$("#qte_id").val() ;
        product_id = data_product['product_id'];
        barcode = data_product['barcode'];
        price = (parseFloat(data_product['price'])).toFixed(2);
        stock = data_product['stock'];
        libelle = data_product['libelle'];
        var entrepot_source = $("#entrepot_source").val();
        var entrepot_destination = $("#entrepot_destination").val();
        qte_in_destination = data_product['qte_in_destination'];

        
      
        name_entrepot_a_alimenter = "{{$name_entrepot_a_alimenter}}";
        name_entrepot_a_destocker = "{{$name_entrepot_a_destocker}}";



        console.log(data_product);
        

        var line_add = `
        <tr data_id_product="${product_id}" id="${product_id}_line" class="class_line1 odd" role="row">
            <td data-key="product_id" data-value="${product_id}" id="${product_id}_product_id" style="text-align: left !important;">${product_id}</td>
            <td data-key="barcode" data-value="${barcode}" id="${barcode}_product_id" style="text-align: left !important;">${barcode}</td>
            <td data-key="libelle" data-value="${libelle}" id="${product_id}_libelle" style="text-align: left !important;">${libelle}</td>
            <td data-key="price" data-value="${price}" id="${product_id}_price" style="text-align: left !important;" class="sorting_1">${price}</td>

            <td data-key="qte_en_stock_in_source" data-value="${stock}" id="${product_id}_qte_en_stock_in_source" style="text-align: left !important;">${name_entrepot_a_destocker} (${stock})</td>
            <td data-key="demande" data-value="/" id="${product_id}_demande">/</td>
            <td data-key="qte_act" data-value="/" id="${product_id}_qte_act">${name_entrepot_a_alimenter} (${qte_in_destination})</td>
            <td data-key="qte_optimale" data-value="/" id="${product_id}_qte_optimale">/</td>
            <td data-key="qte_transfere" data-value="${qte}" id="${product_id}_qte_transfere"><input class="text-center" style="width: 50px" type="text" value="${qte}" disabled=""></td>
            <td data-key="action" id="${product_id}_action">
                <button onclick="delete_line(${product_id})" type="button" class="btn" title="Supprimer l'offre" style="margin: 0;padding: 0;">
                    <a class="" title="Supprimer l'offre" href="javascript:void(0)">
                        <i class="fadeIn animated bx bx-trash"></i>
                    </a>
                </button>
                <button onclick="update_line(${product_id})" type="button" class="btn" title="Supprimer l'offre" style="margin: 0;padding: 0;">
                    <a class="" title="Supprimer l'offre" href="javascript:void(0)">
                        <i class="fadeIn animated bx bxs-edit"></i>
                    </a>
                </button>
            </td>
        </tr>
        `
        $("#tbody_id_1").append(line_add);

        $('#product_add').val([""]).trigger('change');
        $("#qte_id").val("");


    }

    $("#id_sub_calcul_reassort").on("click", function(){

        $(this).addClass("disabled-link");
        spinner = `<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>Calcul en cours...`;
        $(this).html(spinner);

    })

</script>

@endsection


