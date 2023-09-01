$(".order_input").on('input', function(){
    if($(".order_input").val() != ""){
        $(".validate_order").attr('disabled', false)
    }
})

// Action orsque qu'on rentre le numéro de commande manuellement
$(".order_id_input").on('input', function(){
    $("#order_id").val($(".order_id_input").val())
    $(".validate_order").attr('disabled', false)
})

// Validation une fois le numéro de commande entré manuellement ou qr code scnanné
$(".validate_order").on("click", function(){
    $(".loading_detail_order").removeClass('d-none')
    $.ajax({
        url: "checkExpedition",
        metho: 'GET',
        data : {order_id: $("#order_id").val()}
    }).done(function(data) {
        if(JSON.parse(data).success){

            // Tooltip details
            $(".details_information").text(`Les détails de la commande sont affichés, 
            vous pouvez générer une étiquette ou valider simplement sans étiquettes (vous pourrez en générer une plus tard)`);

            var order = JSON.parse(data).order;
            var is_distributor = JSON.parse(data).is_distributor;

            // Supprime le visuel par défaut d'arrivé sur la page
            $(".empty_order").addClass('d-none')
            $(".detail_shipping_billing_div").remove()
            $(".action_button").remove()
            $("hr").removeClass("d-none")
            $(".alert").remove()

            // Afficher les informations de facturation et expédition
            $(".detail_shipping_billing").append(`
                <div class="to_hide detail_shipping_billing_div">
                    <div class="d-flex w-100 justify-content-around mb-3">
                        ${order[0].shipping_method.includes("chrono") ? '<div class="shipping_chrono_logo"></div>' : '<span style="width: fit-content" class="badge bg-primary shipping_method">'+order[0].shipping_method_detail ?? ''+'</span>'}
                        <span class="badge bg-dark distributor">${is_distributor ? 'Distributrice' : ''}</span>
                    </div>

                    <div class="d-flex w-100 justify-content-center">
                        <span style="width: fit-content" class="mb-3 badge status_order bg-default bg-light-${order[0]['status']}">${JSON.parse(data).status}</span>
                    </div>

                    <div class="shipping_detail d-flex flex-wrap justify-content-around mb-2">
                        <div class="d-flex flex-column justify-content-between billing_block align-items-center">
                            <strong>Facturation :</strong>
                            <div class="d-flex flex-column align-items-center">
                                <span class="customer_name">${order[0].billing_customer_last_name+' '+order[0].billing_customer_first_name}</span>
                                <span class="customer_email">${order[0].billing_customer_email}</span>
                                <span class="customer_billing_adresss1">${order[0].billing_customer_address_1 ?? ''}</span>
                                <span class="customer_billing_adresss2">${order[0].billing_customer_address_2 ?? ''}</span>
                            </div>
                        </div>
                        <div class="d-flex flex-column justify-content-between shipping_block align-items-center">
                            <strong>Expédition :</strong>
                            <div class="d-flex flex-column align-items-center">
                                <span class="customer_shipping_name">${order[0].shipping_customer_last_name+' '+order[0].shipping_customer_first_name}</span>
                                <span class="customer_shipping_company">${order[0].shipping_customer_company ?? ''}</span>
                                <span class="customer_shipping_adresss1">${order[0].shipping_customer_address_1}</span>
                                <span class="customer_shipping_adresss2">${order[0].shipping_customer_address_2}</span>
                                <span class="customer_shipping_country">${order[0].shipping_customer_city+' '+order[0].shipping_customer_postcode}</span>
                            </div>
                        </div>
                    </div>
                </div>
            `)

            // Afficher les informations de la commande, total, numéro et préparateur
            $("#orderno").text('Commande #'+order[0].order_woocommerce_id)
            $("#prepared").text(order[0].preparateur)
            $(".total_order").text('Total :')
            $(".amount_total_order").text(order[0].total_order+'€ (TTC)')

            $(".total_order_details").append(`
                <div class="to_hide action_button d-flex w-100 justify-content-center flex-wrap">
                    <button type="button" onclick=validWrapOrder(true) class="btn btn-primary d-flex mx-auto"> Générer une étiquette </button>
                    <button type="button"  onclick="$('.modal_no_label').modal('show')" class="btn btn-primary d-flex mx-auto"> Valider </button>
                </div>
            `)
            
            // Afficher les produits de la commande avec les détails
            var listProduct = "";
            var total_product_order = 0;
            Object.entries(order).forEach(([key, value]) => {
                var gift = parseFloat(value.cost) == 0.00 ? true : false;
                total_product_order = total_product_order + value.quantity
                listProduct += `
                    <div class="row row-main to_hide">
                        <div class="col-2"> 
                            <img loading="lazy" class="img-fluid" src="${value.image}"> </div>
                            <div class="col-8">
                                <div class="row d-flex">
                                    <p><b>${gift ? '<span class="text-success">(Cadeau)</span>' : ''} ${value.name} (x${value.quantity})</b></p>
                                </div>
                                <div class="row d-flex">
                                    <p class="text-muted">${gift ? '<span class="text-success">'+parseFloat(value.cost).toFixed(2)+'€' : parseFloat(value.cost).toFixed(2)}</p>
                                </div>
                            </div>
                        <div class="col-2 d-flex justify-content-end">
                            <p><b>
                            ${gift ? '<span class="text-success">'+parseFloat(value.cost * value.quantity).toFixed(2)+'€</span>' 
                            : parseFloat(value.cost * value.quantity).toFixed(2)+'€'}  </b></p>
                        </div>
                    </div>`
            })

            $(".total_product_order").text(total_product_order+' produits')
            $(".main").prepend(listProduct).fadeIn('slow')
            $(".loading_detail_order").addClass('d-none')

            // Si c'est un distributeur, re bipper les produits
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
                if(localStorage.getItem('barcode_verif_wrapper')){
                    var list_barcode = localStorage.getItem('barcode_verif_wrapper')
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

                if(localStorage.getItem('product_quantity_verif_wrapper')){
                    $(".barcode_"+localStorage.getItem('product_quantity_verif_wrapper')).removeClass('pick')
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
            $(".loading_detail_order").addClass('d-none')
            $(".alert-danger").remove()
            $(".show_messages").prepend(`
                <div class="alert alert-danger border-0 bg-danger alert-dismissible fade show">
                    <div class=" text-white">`+JSON.parse(data).message+`</div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            `)
            clean_scan()
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
    if($("#order_"+order_id+" .pick").length == $("#order_"+order_id+" .product_order").length && localStorage.getItem('barcode_verif_wrapper')
        && !localStorage.getItem('product_quantity_verif_wrapper')){
        $(".verif_order").hide()
        $(".valid_order_and_generate_label").show()
        $(".modal_order").modal('hide')
    }
})


function validWrapOrder(label){
    $(".action_button button").attr('disabled', true)
    $(".row-main").css('opacity', 0.5)
    $(".detail_shipping_billing_div").css('opacity', 0.5)
    $(".loading_detail_order").removeClass('d-none')

    var order_id = $("#order_id").val()

    if(localStorage.getItem('barcode_verif_wrapper')){
        var pick_items = JSON.parse(localStorage.getItem('barcode_verif_wrapper'))

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
        $(".action_button button").attr('disabled', false)
        $(".row-main").css('opacity', 1)
        $(".detail_shipping_billing_div").css('opacity', 1)
        $(".loading_detail_order").addClass('d-none')
        $('.modal_no_label').modal('hide')

        try {
            if(JSON.parse(data).success){
                // Generate label colissimo
                if(JSON.parse(data).file){
                    $.ajax({
                        url: "http://localhost:8000/imprimerEtiquetteThermique?port=USB&protocole=DATAMAX&adresseIp=&etiquette="+JSON.parse(data).file,
                        metho: 'GET',
                    }).done(function(data) {

                    }) 
                }
              
                $(".show_messages").prepend(`
                    <div class="success_message alert alert-success border-0 bg-success alert-dismissible fade show">
                        <div class="text-white">`+JSON.parse(data).message+`</div>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                `)

                $('.order_input').each(function(){
                    $(this).val('');
                });

                localStorage.removeItem('barcode_verif_wrapper');
                $(".valid_order_and_generate_label").show()

                show_empty_order()
            } else {
                $(".show_messages").prepend(`
                    <div class="alert alert-danger border-0 bg-danger alert-dismissible fade show">
                        <div class=" text-white">`+JSON.parse(data).message+`</div>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                `)

                if(JSON.parse(data).verif){
                    $(".verif_order").show()
                    $(".modal_order").modal('show')
                } else {
                    show_empty_order()
                }
            }
        } catch (e) {
            $(".embed_pdf").attr('src', "data:application/pdf;base64,"+data)
            $(".modal_pdf_viewer").modal('show')
        }
    })
}

document.addEventListener("keydown", function(e) {
    if(e.key.length == 1 && !$(".modal_order").hasClass('show')){
        $("#detail_order").val($("#detail_order").val()+e.key)
        var array = $("#detail_order").val().split(',')
        if(array.length == 4 && $("#order_id").val() == ""){
            $("#order_id").val(array[0])
            $("#product_count").val(array[1])
            $("#customer").val(array[2])
            $(".validate_order").attr('disabled', false)
            $(".validate_order").click()
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
                    $(".info_message").text("Aucun produit ne correspond à ce code barre !")
                    $("#infoMessageModal").modal('show')
                }
            }
        }
    } else if($(".modal_verif_order").hasClass('show')){
        var order_id = $(".modal_verif_order").attr('data-order')
        localStorage.setItem('product_quantity_verif_wrapper', $("#product_to_verif").val());
        if (!isNaN(parseInt(e.key))) {
            console.log($("#barcode_verif").val())
            $("#barcode_verif").val($("#barcode_verif").val()+e.key)
           
            if($("#barcode_verif").val().length == 13){
                if($("#barcode_verif").val() == localStorage.getItem('product_quantity_verif_wrapper')){
                    $("#quantity_product_to_verif").text(parseInt($("#quantity_product_to_verif").text()) - 1)
                    
                    // Update pick quantity
                    var quantity_pick_in = parseInt($("#order_"+order_id+" .barcode_"+$("#barcode_verif").val()).find('.quantity_pick_in').text())
                    $("#order_"+order_id+" .barcode_"+$("#barcode_verif").val()).find('.quantity_pick_in').text(quantity_pick_in + 1)
                    saveItem(order_id, true)

                    if(parseInt($("#quantity_product_to_verif").text()) == 0){
                        $("#modalverification").modal('hide')
                        localStorage.removeItem('product_quantity_verif_wrapper');
                    }
                    $("#barcode_verif").val('')
                } else {
                    $("#barcode_verif").val('')
                    $(".info_message").text("Aucun produit ne correspond à ce code barre !")
                    $("#infoMessageModal").modal('show')
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

    if(localStorage.getItem('barcode_verif_wrapper')){
        var list_barcode = JSON.parse( localStorage.getItem('barcode_verif_wrapper')) 
        const order_object = list_barcode.find(
            element => element.order_id == order_id
        )

        // Un objet pour cette commande existe déjà, alors on rajoute dans cet objet
        if(order_object){
            if(mutiple_quantity){
                var index = order_object.products.indexOf($("#barcode_verif").val())
                if(index != -1){
                    order_object.quantity[index] = quantity_pick_in
                    localStorage.setItem('barcode_verif_wrapper', JSON.stringify(list_barcode))
                } else {
                    order_object.products.push($("#barcode_verif").val())
                    order_object.quantity.push(1)
                    localStorage.setItem('barcode_verif_wrapper', JSON.stringify(list_barcode))
                }
            } else {
                order_object.products.push($("#barcode").val())
                order_object.quantity.push(1)
                localStorage.setItem('barcode_verif_wrapper', JSON.stringify(list_barcode))
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
            localStorage.setItem('barcode_verif_wrapper', JSON.stringify(list_barcode))
        }

    } else {
        const data = [{
            order_id : order_id,
            products: [
                $("#barcode").val()
            ],
            quantity: [quantity_pick_in]
        }]
        localStorage.setItem('barcode_verif_wrapper', JSON.stringify(data));
    }	
    $("#barcode_verif").val('')
}


function show_empty_order(){
    $(".detail_shipping_billing_div").remove()
    $(".action_button").remove()
    $(".row-main").remove()
    $(".total_order").text("")
    $(".amount_total_order").text("")
    $("#orderno").text("")
    $("#prepared").text("")
    $(".main_hr").addClass('d-none')
    $(".empty_order").removeClass('d-none')
}


function clean_scan(){
    $("#order_id").val("")
    $("#product_count").val("")
    $("#customer").val("")
    $(".validate_order").attr('disabled', true)
    $("#detail_order").val("")
}