@extends("layouts.app")

@section("style")
    <link href="{{asset('assets/plugins/datatable/css/dataTables.bootstrap5.min.css')}}" rel="stylesheet" />
    <link href="{{('assets/plugins/select2/css/select2.min.css')}}" rel="stylesheet" />
    <link href="assets/plugins/select2/css/select2-bootstrap4.css" rel="stylesheet" />
@endsection 

@section("wrapper")
    <div class="page-wrapper page-preparateur-order">
        <div class="page-content">
            <div class="page-breadcrumb d-sm-flex align-items-center mb-2">
                <div class="breadcrumb-title pe-3">Préparation</div>
                <div class="ps-3">kits</div>
                <input type="hidden" value="" id="barcode">
                <input type="hidden" value="" id="barcode_verif">
                @csrf
            </div>

            <div class="wrapper_kit">
                @foreach($kits as $key => $value)
                    <div data-id="{{ $key }}" class="box card">
                        <span class="text-center">{{ $value['name'] }}</span>
                        @if($value['image'])
                            <img src="{{ asset('assets/images/products/' . $value['image']) }}"/> 
                        @else  
                            <img src="{{ asset('assets/images/products/default_product.png') }}"/>
                        @endif
                    </div>

                    <div class="modal fade modal_radius modal_kit" id="kit_category_{{ $key }}" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered modal-lg">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="exampleModalLabel">{{ $value['name'] }}</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="d-flex flex-column">
                                        @if(isset($value['kits']))
                                            <div class="container_multy_step">
                                                <div class="step_form form1">
                                                    <div class="title_step_kit">Sélection du kit à préparer</div>
                                                    <table id="example" class="table_kit d-none table_mobile_responsive w-100 table table-striped table-bordered">
                                                        <thead>
                                                            <tr>
                                                                <th class="col-md-1">Kit</th>
                                                                <th class="col-md-1"></th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            @foreach($value['kits'] as $kit)
                                                                <tr>
                                                                    <td><span id="kit_name_{{ $kit['id'] }}" >{{ $kit['name'] }}<span></td>
                                                                    <td>
                                                                        <span>
                                                                            <input data-id="{{ $kit['id'] }}" class="form-check-input checkbox_label" type="checkbox" value="" aria-label="Checkbox for product">
                                                                        </span>
                                                                    </td>
                                                                </tr>
                                                            @endforeach
                                                        </tbody>
                                                    </table>
                                                    
                                                </div>
                                                <div style="overflow-y:auto; max-height: 62vh" class="step_form form2">
                                                    <div class="title_step2 title_step_kit mb-4">Sélection du kit à préparer</div>
                                                    @foreach($value['kits'] as $kit)
                                                        @foreach($kit['children'] as $children)
                                                            <div class="barcode_{{ $children['barcode'] }} d-none product_in_kit parent_{{ $children['parent_id'] }} d-flex justify-content-between w-100">
                                                                <div class="product_name">{{ $children['label'] }}</div>
                                                                <div>
                                                                    <span class="quantity_pick_in">0</span>
                                                                    <span>/</span>
                                                                    <span class="quantity_to_pick_in">{{ $children['quantity'] }}</span>
                                                                </div>
                                                            </div>
                                                        @endforeach
                                                    @endforeach
                                                   
                                                </div>
                                                <div class="progress_container">
                                                    <div class="progress"></div>
                                                    <div class="circle active_progress">1</div>
                                                    <div class="circle">2</div>
                                                </div>
                                            </div>
                                        @else 
                                            <div>Aucun kit</div>
                                        @endif
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <div class="btn_box">
                                        <button class="d-none btn btn-dark px-5 back1" type="button">Retour</button>
                                        <button class="btn btn-dark px-5 next1" type="button">Suivant</button>
                                    </div>
                                </div>
                                </form>
                            </div>
                        </div>
                    </div>
                @endforeach


                <!-- Modal info produit déjà bippé ou inexistant-->
                <div class="modal fade modal_reset_order" id="infoMessageModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered" role="document">
                        <div class="modal-content">
                            <div class="modal-body">
                                <h3 class="text-center info_message"></h3>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Fermer</button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Modal vérification quantité -->
                <div class="modal_reset_order modal_verif_order modal fade" data-order="" id="modalverification" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered" role="document">
                        <div class="modal-content">
                            <div class="modal_body_reset modal-body d-flex flex-column justify-content-center">
                                <h2 class="text-center">Attention, ce kit contient <span class="quantity_product"></span> <span class="name_quantity_product"></span></h2>
                                <span style="font-size:25px" class="mb-3 text-center">Produit(s) restant(s) à bipper : <span class="text-danger" style="font-size:30px" id="quantity_product_to_verif"></span></span>
                                <input type="hidden" value="" id="product_to_verif">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Modal vérification quantité -->
                <div class="modal_reset_order modal_verif_order modal fade" data-order="" id="modalverification2" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered" role="document">
                        <div class="modal-content">
                            <div class="modal_body_reset modal-body d-flex flex-column justify-content-center">
                                <h2 class="text-center">Attention, cette commande contient <span class="quantity_product"></span> <span class="name_quantity_product"></span></h2>
                            </div>
                            <div class="w-100 d-flex justify-content-center p-2">
                                <button type="button" class="btn btn-dark px-5" data-bs-dismiss="modal">Valider</button>
                            </div>
                        </div>
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

    <script>

        function items_picked(){
            // Récupère le localstorage pour mettre les produits "pick" qui le sont déjà
            if (localStorage.getItem('barcode')) {
                var list_barcode = localStorage.getItem('barcode')
                if (list_barcode) {
                    Object.keys(JSON.parse(list_barcode)).forEach(function (k, v) {
                        if (JSON.parse(list_barcode)[k]) {
                            var order_id = JSON.parse(list_barcode)[k].order_id
                            JSON.parse(list_barcode)[k].products.forEach(function (item, key) {
                                if(typeof JSON.parse(list_barcode)[k].quantity != "undefined"){
                                    $(".parent_" + order_id + " .to_scan.barcode_" + item).find('.quantity_pick_in').text(JSON.parse(list_barcode)[k].quantity[key])
                                    
                                    if (parseInt($(".parent_" + order_id + " .to_scan.barcode_" + item).find('.quantity_pick_in').text()) ==
                                        parseInt($(".parent_" + order_id + " .to_scan.barcode_" + item).find('.quantity_to_pick_in').text())) {
                                        setTimeout(function () {
                                            $(".parent_" + order_id + " .to_scan.barcode_" + item).addClass('pick')
                                        }, 0)
                                    }
                                }
                            });

                            // if ($("#order_" + order_id + " .pick").length == $(".parent_" + order_id + " .product_order").length) {
                            //     $("#order_" + order_id + " .validate_pick_in").css('background-color', '#16e15e')
                            //     $("#order_" + order_id + " .validate_pick_in").css('border', 'none')
                            // }
                        }
                    })
                }
            }

            if (localStorage.getItem('product_quantity_verif')) {
                $(".barcode_" + localStorage.getItem('product_quantity_verif')).removeClass('pick')
            }
        }

        $(document).ready(function() {
            $(".card").on('click', function(){
                var id = $(this).attr('data-id')

                if(currectActive == 1){
                    $(".next2").remove()
                    $(".back1").addClass("d-none")
                    $(".next1").removeClass('d-none')
                    $(".form2").removeClass('active')
                }
               

                $('#kit_category_'+id).modal({
                    backdrop: 'static',
                    keyboard: false
                })
                $('#kit_category_'+id).modal('show')
            })

            $('.table_kit').each(function() {
                var table = $(this).DataTable({
                    "scrollY": "56vh",
                    "paginate": false,
                    "initComplete": function(settings, json) {
                        $(this).removeClass('d-none');
                        // Manipulation spécifique à cette instance de table
                        $(this).closest('.dataTables_wrapper').find('.col-sm-12').first().remove();
                    }
                });
            });
        })

        // Scan product in kit
        document.addEventListener("keyup", function (e) {
            if (!$("#infoMessageModal").hasClass('show') && !$("#modalverification").hasClass('show')){
                if (!isNaN(parseInt(e.key)) && $(".form2").hasClass('active')) {
                    $("#barcode").val($("#barcode").val() + e.key)
                    if ($("#barcode").val().length == 13) {

                        var barcode = $("#barcode").val()
                        var parent_id = $(".form2").attr("data-parent")

                        if($(".parent_"+parent_id+" .to_scan.barcode_"+barcode+".to_scan").length > 0) {
                            var quantity_to_pick_in = parseInt($(".parent_"+parent_id+" .to_scan.barcode_"+barcode).find('.quantity_to_pick_in').text())
                            console.log(quantity_to_pick_in)

                            if ($(".parent_"+parent_id+" .to_scan.barcode_"+barcode).hasClass('pick')) {
                                $(".info_message").text("Ce produit à déjà été bippé !")
                                $("#infoMessageModal").modal('show')
                            } else {
                                $(".parent_"+parent_id+" .to_scan.barcode_"+barcode).addClass('pick')
                                var quantity_pick_in = parseInt($(".parent_"+parent_id+" .to_scan.barcode_"+barcode).find('.quantity_pick_in').text())
                                quantity_pick_in = quantity_pick_in + 1
                                quantity_pick_in = quantity_pick_in > quantity_to_pick_in ? quantity_to_pick_in : quantity_pick_in

                                if ((quantity_to_pick_in > 1 && quantity_to_pick_in <= 10) && (quantity_to_pick_in - quantity_pick_in) > 0){
                                    // Update pick quantity
                                    $(".parent_"+parent_id+" .to_scan.barcode_"+barcode).find('.quantity_pick_in').text(quantity_pick_in)

                                    // Modal verification
                                    $(".quantity_product").text(quantity_to_pick_in)
                                    $(".name_quantity_product").text($(".parent_"+parent_id+" .to_scan.barcode_"+barcode).children('.product_name').text())
                                    $("#product_to_verif").val(barcode)
                                    $("#quantity_product_to_verif").text(quantity_to_pick_in - quantity_pick_in)
                                    localStorage.setItem('product_quantity_verif', barcode);

                                    $('#modalverification').modal({
                                        backdrop: 'static',
                                        keyboard: false
                                    })
                                    // barcode_3760324819630 
                                    $("#modalverification").attr('data-parent', parent_id)
                                    $("#modalverification").modal('show')
                                    $("#barcode_verif").val(barcode)
                                    saveItem(parent_id, true)
                                } else if(quantity_to_pick_in > 10){
                                    $(".quantity_product").text('')
                                    $(".quantity_product").text(quantity_to_pick_in)
                                    $(".name_quantity_product").text($(".parent_"+parent_id+" .to_scan.barcode_"+barcode).children('.product_name').text())
                                    $("#barcode_verif").val(barcode)
                                    $('#modalverification2').modal({
                                        backdrop: 'static',
                                        keyboard: false
                                    })

                                    $("#modalverification2").attr('data-parent', parent_id)
                                    $("#modalverification2").modal('show')
                                    $(".parent_"+parent_id+" .to_scan.barcode_"+barcode).find('.quantity_pick_in').text(quantity_to_pick_in)
                                    saveItem(order_id, false, true, true)
                                } else {
                                    $(".parent_"+parent_id+" .to_scan.barcode_"+barcode).find('.quantity_pick_in').text(1)
                                    saveItem(parent_id, false)
                                }
                            }
                        } else {
                            $(".info_message").text("Ce produit n'est pas dans le kit !")
                            $("#infoMessageModal").modal('show')
                            $("#barcode").val("")
                            $("#barcode_verif").val("")
                        }
                        $("#barcode").val("")
                    } else if($("#barcode").val().length > 13){
                        $("#barcode").val("")
                    }
                }
            } else if($("#modalverification").hasClass('show') && !$("#infoMessageModal").hasClass('show')){
                var parent_id = $("#modalverification").attr('data-parent')

                if (!isNaN(parseInt(e.key))) {
                    $("#barcode_verif").val($("#barcode_verif").val() + e.key)
                    if ($("#barcode_verif").val().length == 13) {

                        var barcode_verif = $("#barcode_verif").val()

                        if (barcode_verif == localStorage.getItem('product_quantity_verif')) {
                            var quantity_pick_in = parseInt($(".parent_"+parent_id+" .barcode_"+barcode_verif).find('.quantity_pick_in').text())
                            $("#quantity_product_to_verif").text(parseInt($("#quantity_product_to_verif").text()) - 1)
                            $(".parent_"+parent_id+" .barcode_"+barcode_verif).find('.quantity_pick_in').text(quantity_pick_in + 1)
                            saveItem(parent_id, true)

                            if (parseInt($("#quantity_product_to_verif").text()) == 0) {
                                $("#modalverification").modal('hide')
                                localStorage.removeItem('product_quantity_verif');
                            }
                            $("#barcode_verif").val('')
                        } else {
                            $(".info_message").text("Aucun produit ne correspond à ce code barre !")
                            $("#infoMessageModal").modal('show')
                            $("#barcode_verif").val('')
                            $("#barcode").val('')
                        }
                    }
                }
            }
        })

        function saveItem(order_id, mutiple_quantity, barcode, manually = false) {
            var quantity_to_pick_in = parseInt($(".parent_"+order_id+" .to_scan.barcode_"+$("#barcode").val()).find('.quantity_to_pick_in').text())
            if(manually){
                var quantity_pick_in = quantity_to_pick_in
            } else {
                if (mutiple_quantity) {
                    var quantity_pick_in = parseInt($(".parent_"+order_id+" .to_scan.barcode_"+$("#barcode_verif").val()).find('.quantity_pick_in').text())
                } else {
                    var quantity_pick_in = parseInt($(".parent_"+order_id+" .to_scan.barcode_"+$("#barcode").val()).find('.quantity_pick_in').text() + 1)
                }
            }

            // Sécurité quantité pickée ne peut pas dépasser quantité à picker
            quantity_pick_in = quantity_pick_in > quantity_to_pick_in ? quantity_to_pick_in : quantity_pick_in

            if (localStorage.getItem('barcode')) {
                var list_barcode = JSON.parse(localStorage.getItem('barcode'))
                const order_object = list_barcode.find(
                    element => element.order_id == order_id
                )

                // Un objet pour cette commande existe déjà, alors on rajoute dans cet objet
                if (order_object) {
                    if(manually){
                        var index = order_object.products.indexOf($("#barcode").val())
                        if (index != -1) {
                            order_object.quantity[index] = quantity_pick_in
                            if(JSON.stringify(list_barcode)){
                                localStorage.setItem('barcode', JSON.stringify(list_barcode))
                            }
                        } else {
                            order_object.products.push($("#barcode").val())
                            order_object.quantity.push(quantity_pick_in)
                            if(JSON.stringify(list_barcode)){
                                localStorage.setItem('barcode', JSON.stringify(list_barcode))
                            }
                        }
                    } else {
                        if (mutiple_quantity) {
                            var index = order_object.products.indexOf($("#barcode_verif").val())
                            if (index != -1) {
                                order_object.quantity[index] = quantity_pick_in
                                localStorage.setItem('barcode', JSON.stringify(list_barcode))
                            } else {
                                order_object.products.push($("#barcode_verif").val())
                                order_object.quantity.push(1)
                                if(JSON.stringify(list_barcode)){
                                    localStorage.setItem('barcode', JSON.stringify(list_barcode))
                                }
                            }
                        } else {
                            order_object.products.push($("#barcode").val())
                            order_object.quantity.push(1)
                            if(JSON.stringify(list_barcode)){
                                localStorage.setItem('barcode', JSON.stringify(list_barcode))
                            }
                        }
                    }
                
                } else {
                    const data = {
                        order_id: order_id,
                        products: [
                            $("#barcode").val()
                        ],
                        quantity: [quantity_pick_in ?? 1]
                    }

                    list_barcode.push(data)

                    if(JSON.stringify(list_barcode)){
                        localStorage.setItem('barcode', JSON.stringify(list_barcode))
                    }
                }
            } else {
                if($(".parent_"+order_id+" .to_scan.barcode_"+$("#barcode").val()).length > 0 ){
                    const data = [{
                        order_id: order_id,
                        products: [
                            $("#barcode").val()
                        ],
                        quantity: [quantity_pick_in]
                    }]

                    if(JSON.stringify(data)){
                        localStorage.setItem('barcode', JSON.stringify(data));
                    }
                }
                
            }

            $("#barcode").val('')
            $("#barcode_verif").val('')
        }

        $(".checkbox_label").on("change", function(){
            $(".checkbox_label").not($(this)).prop("checked", false);
        })

        let currectActive = 1;
        //============== Next Form===============
        $(".next1").on('click', function(){

            // Get checked checkbox 
            var id = $(".checkbox_label:checked").attr('data-id')
            var kit_selected = $("#kit_name_"+id).text()

            $(".form2").addClass('active')
            $(".form2").addClass("parent_"+id)
            $(".form2").attr("data-parent", id)
            $(".title_step2").text(kit_selected)
            $(".product_in_kit").addClass('d-none')
            $(".product_in_kit").removeClass('to_scan')

            $(".parent_"+id).removeClass('d-none')
            $(".parent_"+id).addClass('to_scan')

            const form1 = $(".show .form1")[0];
            const form2 = $(".show .form2")[0];
            
            $(".next1").addClass('d-none')
            $(".back1").removeClass('d-none')
            $(".back1").after(`
                <button class="btn btn-dark px-5 next2" type="button">Valider</button>
            `)

            form1.style.left = "-990px";
            form2.style.left = "0px";

            increamentNumber();
            update();
            items_picked()
        })

        $("body").on("click", ".next2", function(e) {
            console.log("aa")
        })

        //=============== Back One==================
        $(".back1").on('click', function(){
            $(".form2").removeClass('active')
            $(".back1").addClass('d-none')
            $(".next2").remove()
            $(".next1").removeClass('d-none')

            const form1 = $(".show .form1")[0];
            const form2 = $(".show .form2")[0];

            form1.style.left = "0px";
            form2.style.left = "990px";
            // back slide
            decreametNumber();
            // update progress bar
            update();
        })
        
        //============= Progress update====================
        function update() {
            const circles = document.querySelectorAll(".show .circle");
            const progressEl = $(".show .progress")[0]

            circles.forEach((circle, indx) => {
                if (indx < currectActive) {
                    circle.classList.add("active_progress");
                } else {
                    circle.classList.remove("active_progress");
                }
                // get all of active classes
                const active_progress = document.querySelectorAll(".show .active_progress");

                progressEl.style.width =
                ((active_progress.length - 1) / (circles.length - 1)) * 100 + "%";
            });
        }
        //================== Increament Number===============
        function increamentNumber() {
            const circles = document.querySelectorAll(".show .circle");
            // next progress number
            currectActive++;
                if (currectActive > circles.length) {
                    currectActive = circles.length;
                }
        }
        //================ Decreament Number=================
        function decreametNumber() {
            currectActive--;
                if (currectActive < 1) {
                    currectActive = 1;
                }
        }
    </script>

@endsection