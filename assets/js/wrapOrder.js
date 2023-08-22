$(".reset_order").on('click', function(){
    $("#modalReset").modal("show")
})

$(".order_input").on('input', function(){
    if($(".order_input").val() != ""){
        $(".validate_order").attr('disabled', false)
    }
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
})


$(".validate_order").on("click", function(){
    $(".loading_detail_order").removeClass('d-none')
    $.ajax({
        url: "checkExpedition",
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

            $(".title").remove()
            $(".detail_shipping_billing_div").remove()

            $(".detail_shipping_billing").append(`
                <div class="detail_shipping_billing_div">
                    <h4 class="order_number"></h4>  
                    <div class="d-flex w-100 justify-content-around mb-3">
                        <span style="width: fit-content" class="badge bg-primary shipping_method">${order[0].shipping_method_detail ?? ''}</span>
                        <span class="badge bg-dark distributor">${is_distributor ? 'Distributrice' : ''}</span>
                    </div>

                    <div class="d-flex w-100 justify-content-center">
                        <span style="width: fit-content" class="mb-3 badge status_order bg-default bg-light-${order[0]['status']}">${JSON.parse(data).status}</span>
                    </div>

                    <div class="d-flex flex-wrap justify-content-around">
                        <div class="mb-3 d-flex flex-column">
                            <strong>Facturation :</strong>
                            <span class="customer_name">${order[0].billing_customer_last_name+' '+order[0].billing_customer_first_name}</span>
                            <span class="customer_email">${order[0].billing_customer_email}</span>
                            <span class="customer_billing_adresss1">${order[0].billing_customer_address_1 ?? ''}</span>
                            <span class="customer_billing_adresss2">${order[0].billing_customer_address_2 ?? ''}</span>
                        </div>
                        <div class="d-flex flex-column">
                            <strong>Expédition :</strong>
                            <span class="customer_shipping_name">${order[0].shipping_customer_last_name+' '+order[0].shipping_customer_first_name}</span>
                            <span class="customer_shipping_company">${order[0].shipping_customer_company ?? ''}</span>
                            <span class="customer_shipping_adresss1">${order[0].shipping_customer_address_1}</span>
                            <span class="customer_shipping_adresss2">${order[0].shipping_customer_address_2}</span>
                            <span class="customer_shipping_country">${order[0].shipping_customer_city+' '+order[0].shipping_customer_postcode}</span>
                        </div>
                    </div>
                </div>
            `)


            $(".total_order").text('Total: '+order[0].total_order)
            $("#orderno").text('Commande #'+order[0].order_woocommerce_id)
            $("#prepared").text($("#preparateur").val())
            $("#sub-title").text($("#customer").val()+' '+$("#product_count").val())
            $(".total_order").text('Total :')
            $(".amount_total_order").text(order[0].total_order+'€')
            $(".validate_order").remove()
            $(".action_button").remove()

            $(".total_order_details").append(`
                <div class="action_button d-flex w-100 justify-content-center flex-wrap">
                    <button type="button" onclick=validWrapOrder(true) class="btn btn-primary d-flex mx-auto"> Générer une étiquette </button>
                    <button type="button"  onclick=validWrapOrder(false) class="btn btn-primary d-flex mx-auto"> Valider </button>
                </div>
            `)
            
            // Afficher les produits de la commande
            var listProduct = ""
            $(".row-main").remove()
            Object.entries(order).forEach(([key, value]) => {
                listProduct += `
                    <div class="row row-main">
                        <div class="col-3"> 
                            <img class="img-fluid" src="${value.image}"> </div>
                            <div class="col-6">
                                <div class="row d-flex">
                                    <p><b>${value.name} (x${value.quantity})</b></p>
                                </div>
                                <div class="row d-flex">
                                    <p class="text-muted">${parseFloat(value.cost).toFixed(2)}€</p>
                                </div>
                            </div>
                        <div class="col-3 d-flex justify-content-end">
                            <p><b>${(parseFloat(value.cost) * value.quantity).toFixed(2)}€</b></p>
                        </div>
                    </div>`
            })

            $("#sub-title").after(listProduct).fadeIn('slow')
            $(".loading_detail_order").addClass('d-none')

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
                $('.modal_order').modal({
                    backdrop: 'static',
                    keyboard: false
                })
            
                $(".modal_order").modal('show')

            } else {
                $(".verif_order").hide()
            }

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


$('body').on('click', '.details_order', function () {
    $('#exampleModalCenter').modal({
        backdrop: 'static',
        keyboard: false
    })

    $("#exampleModalCenter").modal('show')
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
        url: "validWrapOrder",
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
        console.log($("#detail_order").val())
        console.log(array)

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
