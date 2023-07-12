@extends("layouts.app")

		@section("style")
		
		@endsection 

		@section("wrapper")
			<div class="page-wrapper">
				<div class="page-content">
					<div class="page-breadcrumb d-sm-flex align-items-center mb-2">
						<div class="breadcrumb-title pe-3 mb-2"></div>
                        <input id="barcode" type="hidden" value="">
						<input id="barcode_verif" type="hidden" value="">
                        @csrf
					</div>


                    <div class="d-flex">
                        <div class="col-xl-12">
                            <div class="card border-top border-0 border-4 border-dark">
                                <div class="card-body p-5">
                                    <div class="card-title d-flex align-items-center">
                                        <div><i class="bx bxs-box me-1 font-22 text-dark"></i>
                                        </div>
                                        <h5 class="mb-0 text-dark">Commandes</h5>
                                        <input type="hidden" value="" id="detail_order">
                                    </div>
                                    <hr>


                                    <div class="show_messages"></div>

                                    <div class="form_valid_wrap_order row g-3">
                                        <div class="col-md-3">
                                            <label for="order_id" class="form-label">N° Commande</label>
                                            <input required type="text" name="order_id" class="order_input form-control" id="order_id">
                                        </div>
                                        <div class="col-md-3">
                                            <label for="product_count" class="form-label">Nombre de produit(s)</label>
                                            <input required type="number" name="product_count" class="order_input form-control" id="product_count">
                                        </div>

                                        <div class="col-md-3">
                                            <label for="customer" class="form-label">Client</label>
                                            <input required type="text" name="customer" class="order_input form-control" id="customer">
                                        </div>

                                        <div class="col-md-3">
                                            <label for="preparateur" class="form-label">Préparateur</label>
                                            <input required type="text" name="preparateur" class="order_input form-control" id="preparateur">
                                        </div>
                                        
                                        <div class="col-12">
                                            <button disabled type="button" class="validate_order btn btn-primary px-5">Valider</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
				</div>
			</div>



            <!-- Modal création étiquette -->
            <div class="modal fade" id="exampleModalCenter" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered" role="document">
                    <div class="modal-content">
                        <div class="modal-body d-flex flex-column">
                            <h4 class="order_number"></h4>  
                            <div class="d-flex w-100 justify-content-between mb-3">
                                <span style="width: fit-content" class="badge bg-primary shipping_method"></span>
                                <span class="badge bg-dark distributor"></span>
                            </div>

                            <span style="width: fit-content" class="mb-3 badge status_order"></span>

                            <div class="mb-3 d-flex flex-column">
                                <strong>Facturation :</strong>
                                <span class="customer_name"></span>
                                <span class="customer_email"></span>
                                <span class="customer_billing_adresss1"></span>
                                <span class="customer_billing_adresss2"></span>
                            </div>

                            <div class="d-flex flex-column">
                                <strong>Expédition :</strong>
                                <span class="customer_shipping_name"></span>
                                <span class="customer_shipping_company"></span>
                                <span class="customer_shipping_adresss1"></span>
                                <span class="customer_shipping_adresss2"></span>
                                <span class="customer_shipping_country"></span>
                            </div>

                            <div class="d-flex w-100 justify-content-end">
                                <span class="font-bold"><span class="total_order"></span>{{ config('app.currency_symbol') }}</span>
                            </div>
                        
                        </div>
                        <div class="modal-footer">
                            <div class="loading_div d-none d-flex w-100 justify-content-center">
                                <div class="spinner-border text-dark" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                            </div>

                            <div class="valid_order_and_generate_label button_modal_form">
                                <button type="button" onclick=validWrapOrder(true) class="btn btn-primary" data-dismiss="modal">Générer l'étiquette</button>
                                <button type="button"  onclick=validWrapOrder(false) type="button" class="btn btn-primary">Continuer</button>
                            </div>
                            <div class="verif_order button_modal_form">
                                <button type="button" class="verif_order_product btn btn-primary">Vérifier la commande</button>
                            </div>
                         
                        </div>
                    </div>
                </div>
            </div>



            <!-- Modal reset commande -->
            <div style="z-index:1061" class="modal_reset_order modal fade" id="modalReset" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered" role="document">
                    <div class="modal-content">
                
                        <div class="modal_body_reset modal-body d-flex flex-column justify-content-center">
                            <h2 class="text-center">Recommencer la commande ?</h2>
                            <div class="w-100 d-flex justify-content-center">
                                <button type="button" class="btn btn-dark px-5" data-bs-dismiss="modal">Non</button>
                                <button style="margin-left:15px" type="button" class="btn btn-dark px-5 confirmation_reset_order ">Oui</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal verif order product -->
            <div class="modal_order modal fade" data-order="" id="" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered" role="document">
                    <div class="modal-content">
                        <div class="modal-body detail_product_order">
                            <div class="detail_product_order_head d-flex flex-column">
                                <div class="p-1 mb-2 head_detail_product_order d-flex w-100 justify-content-between">
                                    <span class="column1 name_column">Article</span>
                                    <span class="column2 name_column">Coût</span>
                                    <span class="column3 name_column">Pick / Qté</span>
                                    <!-- <span class="column4 name_column">Code Barre</span> -->
                                </div>	

                                <div class="body_detail_product_order">
                                   
                                </div>

                                <div class="align-items-end flex-column mt-2 d-flex justify-content-end"> 
                                    <div class="w-100 d-flex align-items-end justify-content-between flex-wrap">
                                        <span class="mt-1 mb-2 montant_total_order">
                                       
                                    </div>
                                    <div class="w-100 d-flex justify-content-between">
                                        <button type="button" class="btn btn-dark px-5" data-bs-dismiss="modal"><i class="d-none responsive-icon lni lni-arrow-left"></i><span class="responsive-text">Retour</button>
                                        <button type="button" class="reset_order btn btn-dark px-5" ><i class="d-none responsive-icon lni lni-reload"></i><span class="responsive-text">Recommencer la commande</span></button>
                                        <button type="button" class="validate_pick_in btn btn-dark px-5"><i class="d-none responsive-icon lni lni-checkmark"></i><span class="responsive-text">Valider</button>
                                    </div>
                                    
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>

           <!-- Modal vérification quantité -->
            <div class="modal_reset_order modal_verif_order modal fade" data-order="" id="modalverification" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered" role="document">
                    <div class="modal-content">
                        <div class="modal_body_reset modal-body d-flex flex-column justify-content-center">
                            <h2 class="text-center">Attention, cette commande contient <span class="quantity_product"></span> <span class="name_quantity_product"></span></h2>
                            <span style="font-size:25px" class="mb-3 text-center">Produit(s) restant(s) à bipper : <span class="text-danger" style="font-size:30px" id="quantity_product_to_verif"></span></span>
                            <input type="hidden" value="" id="product_to_verif">
                        </div>
                    </div>
                </div>
            </div>

		@endsection

	
	@section("script")
		<script>


        $(".reset_order").on('click', function(){
            $("#modalReset").modal("show")
        })

        $(".confirmation_reset_order").on('click', function(){
            var order_id = $("#order_id").val()

            $("#order_"+order_id+" .product_order").removeClass("pick")
            $("#barcode").val("")
            $("#barcode_verif").val("")

            var pick_items = localStorage.getItem('barcode_verif')
            if(pick_items){
                var pick_items = JSON.parse(pick_items)
                Object.keys(pick_items).forEach(function(k, v){
                    if(pick_items[k]){
                        if(order_id == pick_items[k].order_id){
                            pick_items.splice(pick_items.indexOf(pick_items[k]), pick_items.indexOf(pick_items[k]) + 1);
                        }
                    }
                })
            }

            if(pick_items.length == 0){
                localStorage.removeItem('barcode');
            } else {
                localStorage.setItem('barcode', JSON.stringify(pick_items));
            }

            location.reload()
            // $.ajax({
            //         url: "{{ route('orders.reset') }}",
            //         method: 'POST',
            //         data: {_token: $('input[name=_token]').val(), order_id: order_id}
            // }).done(function(data) {
            //     if(JSON.parse(data).success){
            //         location.reload()
            //     } else {
            //         alert("Erreur !")
            //     }
            // });
        })


        $(".validate_order").on("click", function(){
            $.ajax({
                url: "{{ route('checkExpedition') }}",
                metho: 'GET',
                data : {order_id: $("#order_id").val()}
            }).done(function(data) {
                if(JSON.parse(data).success){
                    order = JSON.parse(data).order
                    is_distributor = JSON.parse(data).is_distributor
                    
                    $(".order_number").text('Détails Commande n°'+order[0].order_woocommerce_id)
                    if(is_distributor){
                        $(".distributor").text('Distributrice')
                    }
                    $(".status_order").text(JSON.parse(data).status)
                    $(".status_order").addClass('bg-default bg-light-'+order[0]['status'])
                    $(".customer_name").text(order[0].billing_customer_last_name+' '+order[0].billing_customer_first_name)
                    $(".customer_email").text(order[0].billing_customer_email)
                    $(".customer_billing_adresss1").text(order[0].billing_customer_address_1 ?? '')
                    $(".customer_billing_adresss2").text(order[0].billing_customer_address_2 ?? '')

                    $(".shipping_method").text(order[0].shipping_method_detail ?? '')
                    $(".customer_shipping_company").text(order[0].shipping_customer_company ?? '')
                    $(".customer_shipping_name").text(order[0].shipping_customer_last_name+' '+order[0].shipping_customer_first_name)
                    $(".customer_shipping_adresss1").text(order[0].shipping_customer_address_1)
                    $(".customer_shipping_adresss2").text(order[0].shipping_customer_address_2)
                    $(".customer_shipping_country").text(order[0].shipping_customer_city+' '+order[0].shipping_customer_postcode)
                    $(".total_order").text(order[0].total_order)
                    
                    if(is_distributor){
                        $(".valid_order_and_generate_label").hide()
                        $(".product_order").remove()
                        var innerHtml = ""

                        Object.entries(order).forEach(([key, value]) => {
                            innerHtml += `<div class="barcode_${value.barcode  ?? 0 } ${value.pick_control ==  value.quantity ? 'pick' : '' } product_order p-2 d-flex w-100 align-items-center justify-content-between detail_product_order_line">
                                <div class="column11 d-flex align-items-center detail_product_name_order">
                                    <span>${value.cost == 0 ? `<span class="text-danger">(Cadeau) </span>` : ``}${value.name}</span>
                                </div>
                                <span class="column22">${parseFloat(value.cost).toFixed(2)}</span>
                                <span class="quantity column33"><span class="quantity_pick_in">${value.pick_control}</span> / <span class="quantity_to_pick_in">${value.quantity}</span> </span>
                            </div>`
                        })

                        $(".modal_order").attr('data-order', $("#order_id").val())
                        $(".modal_order").attr('id', 'order_'+$("#order_id").val())
                        $(".body_detail_product_order").append(innerHtml)


                        /* ------Récupère le localstorage pour mettre les produits "pick" qui le sont déjà ----- */
                        if(localStorage.getItem('barcode_verif')){
                            var list_barcode = localStorage.getItem('barcode_verif')
                            if(list_barcode){
                                Object.keys(JSON.parse(list_barcode)).forEach(function(k, v){
                                    if(JSON.parse(list_barcode)[k]){
                                            var order_id = JSON.parse(list_barcode)[k].order_id
                                            JSON.parse(list_barcode)[k].products.forEach(function(item, key){
                        
                                            $("#order_"+order_id+" .barcode_"+item).find('.quantity_pick_in').text(JSON.parse(list_barcode)[k].quantity[key])
                                            if(parseInt($("#order_"+order_id+" .barcode_"+item).find('.quantity_pick_in').text()) == 
                                            parseInt($("#order_"+order_id+" .barcode_"+item).find('.quantity_to_pick_in').text())){
                                                setTimeout(function(){
                                                    $("#order_"+order_id+" .barcode_"+item).addClass('pick')
                                                },0)
                                            }
                                        });

                                        if($("#order_"+order_id+" .pick").length == $("#order_"+order_id+" .product_order").length){
                                            $("#order_"+order_id+" .validate_pick_in").css('background-color', '#16e15e')
                                            $("#order_"+order_id+" .validate_pick_in").css('border', 'none')
                                        }
                                    }
                                })
                            }
                        } 

                        if(localStorage.getItem('product_quantity_verif')){
                            $(".barcode_"+localStorage.getItem('product_quantity_verif')).removeClass('pick')
                        }
                        /* ----------------------------------------------------------------------------- */


                    } else {
                        $(".verif_order").hide()
                    }
                    
                    $('#exampleModalCenter').modal({
                        backdrop: 'static',
                        keyboard: false
                    })
                    $("#exampleModalCenter").modal('show')

                } else {
                    $(".show_messages").prepend(`
                        <div class="alert alert-danger border-0 bg-danger alert-dismissible fade show">
                            <div class=" text-white">`+JSON.parse(data).message+`</div>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    `)
                }
            });
        })

        $(".verif_order_product").on('click', function(){
            $(".modal_order").modal('show')
        })

        $(".validate_pick_in").on('click', function(){
            var order_id = $("#order_id").val()
            if($("#order_"+order_id+" .pick").length == $("#order_"+order_id+" .product_order").length && localStorage.getItem('barcode_verif')
				&& !localStorage.getItem('product_quantity_verif')){
                $(".verif_order").hide()
                $(".valid_order_and_generate_label").show()
                $(".modal_order").modal('hide')
            } else {
                alert('Veuillez vérifier tous les produits !')
            }
        })


        function validWrapOrder(label){
            $(".button_modal_form").addClass('d-none')
            $(".loading_div").removeClass('d-none')
            var order_id = $("#order_id").val()

            if(localStorage.getItem('barcode_verif')){
                var pick_items = JSON.parse(localStorage.getItem('barcode_verif'))

                // Récupère les produits de cette commande
                const order_object = pick_items.find(
                    element => element.order_id == order_id
                )

                if(order_object){
                    pick_items = order_object.products
                    pick_items_quantity = order_object.quantity
                } else {
                    pick_items = false
                    pick_items_quantity = false
                }
            } else {
                pick_items = false
                pick_items_quantity = false
            }

            $.ajax({
                url: "{{ route('validWrapOrder') }}",
                metho: 'POST',
                data : {_token: $('input[name=_token]').val(), order_id: order_id, label: label, pick_items: pick_items, pick_items_quantity: pick_items_quantity},
                dataType: 'html' 
            }).done(function(data) {
                $(".button_modal_form").removeClass('d-none')
                $(".loading_div").addClass('d-none')
                $("#exampleModalCenter").modal('hide')

                if(JSON.parse(data).success){

                    $(".show_messages").prepend(`
                        <div class="success_message alert alert-success border-0 bg-success alert-dismissible fade show">
                            <div class="text-white">`+JSON.parse(data).message+`</div>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    `)

                    $('.order_input').each(function(){
                        $(this).val('');
                    });

                     localStorage.removeItem('barcode_verif');
                    $(".valid_order_and_generate_label").show()
                } else {
                    $(".show_messages").prepend(`
                        <div class="alert alert-danger border-0 bg-danger alert-dismissible fade show">
                            <div class=" text-white">`+JSON.parse(data).message+`</div>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    `)
                    $(".verif_order").show()
                }
            })
        }

        document.addEventListener("keydown", function(e) {
            
            if(e.key.length == 1 && !$(".modal_order").hasClass('show')){
                $("#detail_order").val($("#detail_order").val()+e.key)
                var array = $("#detail_order").val().split(',')
                if(array.length == 4){
                    $("#order_id").val(array[0])
                    $("#product_count").val(array[1])
                    $("#customer").val(array[2])
                    $("#preparateur").val(array[3])
                    $(".validate_order").attr('disabled', false)
                }
            } else if($(".modal_order").hasClass('show') && !$(".modal_verif_order").hasClass('show')){
                var order_id = $("#order_id").val()
                if (!isNaN(parseInt(e.key))) {
                    $("#barcode").val($("#barcode").val()+e.key)
                    if($("#barcode").val().length == 13){
                        if($("#order_"+order_id+" .barcode_"+$("#barcode").val()).length > 0){
                            if($("#order_"+order_id+" .barcode_"+$("#barcode").val()).hasClass('pick')){
                                alert('Ce produit à déjà été bippé')
                            } else {
                                $("#order_"+order_id+" .barcode_"+$("#barcode").val()).addClass('pick')
                                var quantity_pick_in = parseInt($("#order_"+order_id+" .barcode_"+$("#barcode").val()).find('.quantity_pick_in').text())
                                quantity_pick_in = quantity_pick_in + 1

                                if($("#order_"+order_id+" .barcode_"+$("#barcode").val()).find('.quantity_to_pick_in').text() > 1 && 
                                (parseInt($("#order_"+order_id+" .barcode_"+$("#barcode").val()).find('.quantity_to_pick_in').text()) - quantity_pick_in) > 0 ){
                                    // Update pick quantity
                                    $("#order_"+order_id+" .barcode_"+$("#barcode").val()).find('.quantity_pick_in').text(quantity_pick_in)

                                    $(".quantity_product").text('')
                                    $(".quantity_product").text($("#order_"+order_id+" .barcode_"+$("#barcode").val()).find('.quantity_to_pick_in').text())
                                    $(".name_quantity_product").text($("#order_"+order_id+" .barcode_"+$("#barcode").val()).children('.detail_product_name_order').children('span').text())
                                    $("#product_to_verif").val($("#barcode").val())
                                    $("#quantity_product_to_verif").text(parseInt($("#order_"+order_id+" .barcode_"+$("#barcode").val()).find('.quantity_to_pick_in').text()) - quantity_pick_in)

                                    $('#modalverification').modal({
                                        backdrop: 'static',
                                        keyboard: false
                                    })

                                    $("#modalverification").attr('data-order', order_id)
                                    $("#modalverification").modal('show')
                                    $("#barcode_verif").val($("#barcode").val())
                                    saveItem(order_id, true)
                                } else if($("#order_"+order_id+" .barcode_"+$("#barcode").val()).find('.quantity_to_pick_in').text() > 1){
                                    $("#order_"+order_id+" .barcode_"+$("#barcode").val()).find('.quantity_pick_in').text(quantity_pick_in)
                                    $("#barcode_verif").val($("#barcode").val())
                                    saveItem(order_id, true)
                                } else {
                                    $("#order_"+order_id+" .barcode_"+$("#barcode").val()).find('.quantity_pick_in').text(1)
                                    saveItem(order_id, false)
                                }

                                if($("#order_"+order_id+" .pick").length == $("#order_"+order_id+" .product_order").length){
                                    $("#order_"+order_id+" .validate_pick_in").css('background-color', '#16e15e')
                                    $("#order_"+order_id+" validate_pick_in").css('border', 'none')
                                }
                            }
                            $("#barcode").val("")
                        } else {
                            $("#barcode").val("")
                            alert("Aucun produit ne correspond à ce code barre !")
                        }
                    }
                }
            } else if($(".modal_verif_order").hasClass('show')){
                var order_id = $(".modal_verif_order").attr('data-order')
                localStorage.setItem('product_quantity_verif', $("#product_to_verif").val());
                if (!isNaN(parseInt(e.key))) {
                    console.log($("#barcode_verif").val())
                    $("#barcode_verif").val($("#barcode_verif").val()+e.key)
                   
                    if($("#barcode_verif").val().length == 13){
                        if($("#barcode_verif").val() == localStorage.getItem('product_quantity_verif')){
                            $("#quantity_product_to_verif").text(parseInt($("#quantity_product_to_verif").text()) - 1)
                            
                            // Update pick quantity
                            var quantity_pick_in = parseInt($("#order_"+order_id+" .barcode_"+$("#barcode_verif").val()).find('.quantity_pick_in').text())
                            $("#order_"+order_id+" .barcode_"+$("#barcode_verif").val()).find('.quantity_pick_in').text(quantity_pick_in + 1)
                            saveItem(order_id, true)

                            if(parseInt($("#quantity_product_to_verif").text()) == 0){
                                $("#modalverification").modal('hide')
                                localStorage.removeItem('product_quantity_verif');
                            }
                            $("#barcode_verif").val('')
                        } else {
                            $("#barcode_verif").val('')
                            alert("Aucun produit ne correspond à ce code barre !")
                        }
                    }
                }
            }
        });


        function saveItem(order_id, mutiple_quantity){
            if(mutiple_quantity){
                var quantity_pick_in = parseInt($("#order_"+order_id+" .barcode_"+$("#barcode_verif").val()).find('.quantity_pick_in').text())
            } else {
                var quantity_pick_in = parseInt($("#order_"+order_id+" .barcode_"+$("#barcode").val()).find('.quantity_pick_in').text())
            }

            if(localStorage.getItem('barcode_verif')){
                var list_barcode = JSON.parse( localStorage.getItem('barcode_verif')) 
                const order_object = list_barcode.find(
                    element => element.order_id == order_id
                )

                // Un objet pour cette commande existe déjà, alors on rajoute dans cet objet
                if(order_object){
                    if(mutiple_quantity){
                        var index = order_object.products.indexOf($("#barcode_verif").val())
                        if(index != -1){
                            order_object.quantity[index] = quantity_pick_in
                            localStorage.setItem('barcode_verif', JSON.stringify(list_barcode))
                        } else {
                            order_object.products.push($("#barcode_verif").val())
                            order_object.quantity.push(1)
                            localStorage.setItem('barcode_verif', JSON.stringify(list_barcode))
                        }
                    } else {
                        order_object.products.push($("#barcode").val())
                        order_object.quantity.push(1)
                        localStorage.setItem('barcode_verif', JSON.stringify(list_barcode))
                    }
                } else {
                    const data = {
                        order_id : order_id,
                        products: [
                            $("#barcode").val()
                        ],
                        quantity: [quantity_pick_in ?? 1]
                    }

                    list_barcode.push(data)
                    localStorage.setItem('barcode_verif', JSON.stringify(list_barcode))
                }

            } else {
                const data = [{
                    order_id : order_id,
                    products: [
                        $("#barcode").val()
                    ],
                    quantity: [quantity_pick_in]
                }]
                localStorage.setItem('barcode_verif', JSON.stringify(data));
            }	
            $("#barcode_verif").val('')
		}

        </script>
	@endsection
