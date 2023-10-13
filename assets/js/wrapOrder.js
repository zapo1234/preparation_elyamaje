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
            var country = getCountry(order[0]);
            var order_shipping_method = order[0].shipping_method ? order[0].shipping_method : [];
           
            // Type de commande, devis, transfers ou commande from_dolibarr à false et transfers à false
            var from_dolibarr = JSON.parse(data).from_dolibarr
            var transfers = JSON.parse(data).transfers

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

                        ${order_shipping_method.includes("chrono") ? '<div class="shipping_chrono_logo"></div>' : '<span style="width: fit-content" class="badge bg-primary shipping_method">'+order[0].shipping_method_detail ?? ''+'</span>'}
                            <span class="badge bg-dark distributor">${is_distributor ? 'Distributrice' : ''}</span>
                        </div>

                        ${country ?  `<div class="d-flex w-100 justify-content-center">
                            <span style="width: fit-content" class="mb-3 badge bg-default">${country}</span>
                        </div>`: ''} 
                   
                        ${!transfers ?
                            `<div class="d-flex w-100 justify-content-center">
                                <span style="width: fit-content" class="mb-3 badge status_order bg-default bg-light-${order[0]['status']}">${JSON.parse(data).status}</span>
                            </div>`
                        : ''}

                        ${!transfers ?
                            `<div class="shipping_detail d-flex flex-wrap justify-content-around mb-2">
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
                                        <span class="customer_shipping_company">${order[0].shipping_customer_company ?? ``}</span>
                                        <span class="customer_shipping_adresss1">${order[0].shipping_customer_address_1}</span>
                                        <span class="customer_shipping_adresss2">${order[0].shipping_customer_address_2}</span>
                                        <span class="customer_shipping_country">${order[0].shipping_customer_city+' '+order[0].shipping_customer_postcode}</span>
                                    </div>
                                </div>
                            </div>`
                    : ``}
                </div>`
            )

            // Afficher les informations de la commande, total, numéro et préparateur
            $("#orderno").text('Commande #'+order[0].order_woocommerce_id)
            $("#prepared").text(order[0].preparateur)
            $(".total_order").text('Total :')

            if(!transfers){
                $(".amount_total_order").text(order[0].total_order+'€ (TTC)')
            }

            $(".total_order_details").append(`
                <div class="d-flex">
                    <div class="to_hide action_button d-flex w-100 justify-content-center flex-wrap">
                        <button id="validWrapper" transfers="`+transfers+`" from_dolibarr="`+from_dolibarr+`" type="button" onclick="validWrapOrder(true)" class="btn btn-primary d-flex mx-auto"> Valider avec étiquette</button>
                    </div>
                    <div class="to_hide action_button d-flex w-100 justify-content-center flex-wrap">
                        <button id="validWrapper" transfers="`+transfers+`" from_dolibarr="`+from_dolibarr+`" type="button"  onclick="$('.modal_no_label').modal('show')" class="btn btn-primary d-flex mx-auto"> Valider </button>
                    </div>
                </div>
            `)
            
            //  <button type="button" onclick=validWrapOrder(true) class="btn btn-primary d-flex mx-auto"> Générer une étiquette </button>
            
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

$('body').on('click', '.check_all', function() {
    if($(this).prop('checked')){
        $("#order_"+$(this).attr('data-id')+' .checkbox_label').each(function( index ) {
            if($(this).attr('disabled') != "disabled"){
                $(this).prop('checked', true)
            }
        });
    } else {
        $("#order_"+$(this).attr('data-id')+' .checkbox_label').prop('checked', false)
    }

    total_weight()
})

$('body').on('click', '.checkbox_label', function() {
    total_weight()
})

$('body').on('change', '.quantity_product_label', function(e) {
    total_weight()
})

// Calcul du poids total de la commande
function total_weight(){
    var total_weight = 0;
    $(".line_items_label").each(function( index ) {
        // Si la valeur renseignée est supérieure à la quantité de produit, alors on force la quantité max du produit
        var value = $(this).find('.quantity_product_label').val()
        var max_value = $(this).find('.quantity_product_label').attr('max')

        if(parseInt(value) > parseInt(max_value)){
            $(this).find('.quantity_product_label').val(max_value)
        }
        
        if($(this).find('.checkbox_label').prop('checked')){
            total_weight = parseFloat(total_weight) + (parseFloat($( this ).find('.weight').text()) * $(this).find('.quantity_product_label').val())
        }
    }); 
    $(".total_weight ").text('Poids : '+total_weight.toFixed(2)+' Kg')
}


function validWrapOrder(label, redirection = false, error = false){

  
    var order_id = $("#order_id").val()
    var from_dolibarr = $("#validWrapper").attr('from_dolibarr')
    var transfers = $("#validWrapper").attr('transfers')
    // Affiche les infos pour générer l'étiquette
    if(label){
        $.ajax({
            url: "getProductOrderLabel",
            method: 'POST',
            data : {_token: $('input[name=_token]').val(), order_id: order_id, from_validWraper: true,
            from_dolibarr: from_dolibarr, transfers: transfers}
        }).done(function(data) {
            if(JSON.parse(data).success){
                $(".line_items_label").remove()
                $(".total_weight").remove()

                $("#order_id_label").val(order_id)
                $(".check_all").attr('data-id', order_id)
                $(".check_all").prop('checked', true)
                $(".body_line_items_label").attr('id', 'order_'+order_id)

                var product_order = JSON.parse(data).products_order;
                var innerHtml = '';
                var product = 0;
                var total_weight = 0;

                Object.entries(product_order).forEach(([key, value]) => {
                    product = value.quantity - value.total_quantity == 0 ? product + 0 : product + 1;

                    if(value.quantity - value.total_quantity == 0){
                        total_weight = parseFloat(total_weight)
                    } else {
                        total_weight = value.weight ? parseFloat(total_weight) + (parseFloat(value.weight) * value.quantity) : parseFloat(total_weight)
                    }

                    innerHtml +=
                        `<div class="line_${value.product_woocommerce_id} ${value.quantity - value.total_quantity == 0 ? 'disabled_text' : '' } line_items_label d-flex w-100 align-items-center justify-content-between">
                            <span style="width: 50px">
                                <input name="label_product[]" ${value.quantity - value.total_quantity == 0 ? 'disabled' : 'checked' } class="checkbox_label form-check-input" type="checkbox" value="${value.product_woocommerce_id}" aria-label="Checkbox for product order">	
                            </span>
                            <span class="w-50">${value.name}</span>
                            <span class="w-25">${value.cost}</span>
                            <span class="w-25" ><input class="quantity_product_label" ${value.quantity - value.total_quantity == 0 ? 'disabled' : '' } min="1" max="${value.quantity - (value.total_quantity ?? 0) }" value="${value.quantity -  (value.total_quantity ?? 0) }" name="quantity[${value.product_woocommerce_id}]" type="number"> 
                            / <span class="total_quantity">${value.quantity}</span>
                            <input class="base_total_quantity" type="hidden" value="${value.quantity}">
                            
                            </span>
                            <span class="weight w-25">${value.weight ? value.weight : 0}</span>
                        </div>`
                });
                
                innerHtml += `<div class="total_weight mt-3 w-100 d-flex justify-content-end">Poids : `+parseFloat(total_weight).toFixed(2)+` Kg</div>`
                // Si tous les produits sont déjà dans des étiquettes alors désactiver le button de génération
                if(product == 0){
                    $(".button_validate_modal_label").children('button').last().attr('disabled', true)
                } else {
                    $(".button_validate_modal_label").children('button').last().attr('disabled', false)
                }

                $(".body_line_items_label").append(innerHtml)

                // Check si tous les produits ont été générées dans une étiquette
                if(localStorage.getItem('labels')){
                    data_labels = JSON.parse(localStorage.getItem('labels'))
                    Object.keys(data_labels).forEach(function (k, v) {
                        if(data_labels[k].order_id == order_id){
                            // Désactive les lignes de produits qui vont être générées dans l'étiquette
                            checkProductOnLabel(data_labels[k].data_array[0])
                        }
                    })
                }

                $(".generate_label_modal").modal('show')
            } else {
                $(".alert").remove()
                $(".show_messages").prepend(`
                    <div class="alert alert-danger border-0 bg-danger alert-dismissible fade show">
                        <div class=" text-white">`+JSON.parse(data).message+`</div>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                `)
            }
        })
    } else {
        $(".generate_label_modal").modal('hide')
        $(".action_button button").attr('disabled', true)
        $(".row-main").css('opacity', 0.5)
        $(".detail_shipping_billing_div").css('opacity', 0.5)
        $(".loading_detail_order").removeClass('d-none')
        $(".confirm_valid_order span").addClass('d-none')
        $(".confirm_valid_order .loading_valid_wrapper").removeClass('d-none')
        $(".confirm_valid_order ").attr('disabled', true)

    
        var order_id = $("#order_id").val()
        var from_dolibarr = $("#validWrapper").attr('from_dolibarr')
        var transfers = $("#validWrapper").attr('transfers')
        
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
            data : {_token: $('input[name=_token]').val(), order_id: order_id, label: label, pick_items: pick_items, 
            pick_items_quantity: pick_items_quantity, transfers: transfers, from_dolibarr: from_dolibarr},
            dataType: 'html' ,
        }).done(function(data) {
            $(".action_button button").attr('disabled', false)
            $(".row-main").css('opacity', 1)
            $(".detail_shipping_billing_div").css('opacity', 1)
            $(".loading_detail_order").addClass('d-none')
            $('.modal_no_label').modal('hide')    
            $(".confirm_valid_order span").removeClass('d-none')
            $(".confirm_valid_order .loading_valid_wrapper").addClass('d-none')
            $(".confirm_valid_order ").attr('disabled', false)

            if(!error){
                $(".back_labels").attr('disabled', true)
            }

            try {
                if(JSON.parse(data).success){
                    
                    if(error){
                        $(".show_messages").prepend(`
                            <div class="success_message alert alert-warning border-0 bg-warning alert-dismissible fade show">
                                <div class="text-center text-white">
                                    <span class="response_detail_type">Facturation </span>: `+JSON.parse(data).message+`
                                    ${error ? '<br><span class="response_detail_type">Étiquette </span>: '+error+'' : ''}
                                </div>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        `)
                    } else {
                        $(".show_messages").prepend(`
                        <div class="success_message alert alert-success border-0 bg-success alert-dismissible fade show">
                            <div class="text-center text-white">`+JSON.parse(data).message+`</div>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    `)
                    }
                   
    
                    $('.order_input').each(function(){
                        $(this).val('');
                    });
                    
                    $(".order_id_input").val('')
    
                    localStorage.removeItem('barcode_verif_wrapper');
                    $(".valid_order_and_generate_label").show()
    
                    show_empty_order()

                    // Si commande bien facturée et étiquette générée mais de type chronopost ou nécessite documents douane, redirections vers labels
                    if(redirection && !error){
                        document.location.href = "http://localhost/preparation.elyamaje.com/labels?status=&created_at=&order_woocommerce_id="+order_id; 
                    }
                    
                } else {
                    // var message = error ? JSON.parse(data).message+' - '+error : JSON.parse(data).message
                    $(".show_messages").prepend(`
                        <div class="alert alert-danger border-0 bg-danger alert-dismissible fade show">
                            <div class="text-center text-white">
                                <span class="response_detail_type">Facturation </span>: `+JSON.parse(data).message+`
                                ${error ? '<br><span class="response_detail_type">Étiquette </span>: '+error+'' : ''}
                            </div>
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

}

$(".valid_generate_label").on('click', function(){

    var order_id = $("#order_id_label").val()
    var element_is_checked = false;
    var number_element = 0
    var number_element_checked = 0
    var products_left = false;

    // Check si tout est checké ou non
    $('.line_items_label').each(function(){
        if(!$(this).find('.checkbox_label').prop('disabled') && $(this).find('.checkbox_label').prop('checked')){
            element_is_checked = true
            number_element_checked = number_element_checked + 1
        } 

        if(!$(this).find('.checkbox_label').prop('disabled')){
            number_element = number_element + 1
        }

        if($(this).find('.quantity_product_label').val() != parseInt($(this).find('.total_quantity').text())){
            products_left = true
        }
    })

    // Passe dedans si au moins une modif dans le formulaire
    if(element_is_checked){
        if(!localStorage.getItem('labels')){
            const data = [{
                order_id: order_id,
                data: [
                    $(".labelProductsInfo").serialize()
                ],
                data_array: [
                    $(".labelProductsInfo").serializeArray()
                ],
                num: 1
            }]
            localStorage.setItem('labels', JSON.stringify(data))
        } else {
            var labels_data = JSON.parse(localStorage.getItem('labels'))
            const data = {
                order_id: order_id,
                data: [
                    $(".labelProductsInfo").serialize()
                ],
                data_array: [
                    $(".labelProductsInfo").serializeArray()
                ],
                num: labels_data.length + 1
            }
            labels_data.push(data)
            localStorage.setItem('labels', JSON.stringify(labels_data)) 
        }
        // Désactive les lignes de produits qui vont être générées dans l'étiquette
        checkProductOnLabel($(".labelProductsInfo").serializeArray())
    }

    // Si tout les produits on été ajouté à une étiquette, alors on génère et facture
    if(number_element == number_element_checked && localStorage.getItem('labels') && !products_left){
      
        $(".loading_generate_label").removeClass('d-none')
        $(".button_validate_modal_label").find('button').attr('disabled', true)
        var labels_products = JSON.parse(localStorage.getItem('labels'));
        var from_dolibarr = $("#validWrapper").attr('from_dolibarr')
        var transfers = $("#validWrapper").attr('transfers')
        var error = false;
        var redirection = false;

        setTimeout(function(){
            Object.keys(labels_products).forEach(function (k, v) {
                if(order_id == labels_products[k].order_id){
                    $.ajax({
                        url: "generateLabel",
                        method: 'POST',
                        data : labels_products[k].data[0]+'&_token='+$('input[name=_token]').val()+'&from_js=true&from_dolibarr='+from_dolibarr+'&transfers='+transfers,
                        async: false
                    }).done(function(data) {
                        $(".loading_generate_label").addClass('d-none')
                        $(".button_validate_modal_label").find('button').attr('disabled', false)
                        $(".cancel_label_created ").find('button').attr('disabled', true)

                        if(JSON.parse(data).success){
                            localStorage.removeItem('labels');
                            if(JSON.parse(data).file){
                                var label = JSON.parse(data).file
                                $.ajax({
                                    url: "http://localhost:8000/imprimerEtiquetteThermique?port=USB&protocole=DATAMAX&adresseIp=&etiquette="+label,
                                    metho: 'GET',
                                    async: false,
                                    success : function(data){
                                        
                                    },
                                    error : function(xhr){
                                    if(xhr.status == 404){
                                        $.ajax({
                                            url: "http://localhost:8000/imprimerEtiquetteThermique?port=USB&protocole=ZEBRA&adresseIp=&etiquette="+label,
                                            metho: 'GET',
                                            async: false,
                                            success : function(data){
                                            
                                            },
                                        })
                                    }
                                    }
                                })
                            } else {
                                redirection = true;
                            }
                            
                            if(parseInt(k) + parseInt(1) == parseInt(labels_products.length)){
                                // Facture la commande
                                setTimeout(function(){
                                    validWrapOrder(false, redirection, error)
                                }, 200)
                            }
                        } else {
                            if(parseInt(k) + parseInt(1) == parseInt(labels_products.length)){
                                setTimeout(function(){
                                    validWrapOrder(false, redirection, JSON.parse(data).message)
                                }, 200)
                            }
                        }
                    })
                }
            })
        },100)  
    }
})

$(".cancel_label_created").on('click', function(){
    localStorage.removeItem('labels');
    checkProductOnLabel(false)
})

function checkProductOnLabel(data){
    var finished = true;

    if(data){
        var product_id = 0;
        Object.keys(data).forEach(function (k, v) {
            if(data[k].name.includes("label_product")){
                product_id = data[k].value
            } else if(data[k].name.includes("quantity") && data[k].name.includes(product_id)){
                var base_quantity = parseInt($(".line_"+product_id+" .total_quantity").text())
                if(base_quantity > 0){
                    $(".line_"+product_id+" .quantity_product_label").val(base_quantity - data[k].value)
                    $(".line_"+product_id+" .total_quantity").text(base_quantity - data[k].value)
                    $(".line_"+product_id+" .quantity_product_label").attr('max', base_quantity - data[k].value)
                }
                if(base_quantity - data[k].value == 0 && data[k].value != 0){
                    $(".line_"+product_id).addClass('disabled_text')
                    $(".line_"+product_id+" .quantity_product_label ").attr('disabled', true)
                    $(".line_"+product_id+" .checkbox_label ").attr('checked', false)
                    $(".line_"+product_id+" .checkbox_label ").attr('disabled', true)
                } else {
                    finished = false
                }
            }
        })
    } else {
        finished = false;

        $('.line_items_label').each(function(){
            $(this).removeClass('disabled_text')
            $(this).find('.quantity_product_label').attr('disabled', false)
            $(this).find('.checkbox_label').attr('disabled', false)
            $(this).find('.checkbox_label').attr('checked', true)
            $(this).find('.quantity_product_label').val(parseInt($(this).find(".total_quantity").text()))
            $(this).find('.total_quantity').text($(this).find(".base_total_quantity").val())
            $(this).find('.quantity_product_label').attr('max', $(this).find(".base_total_quantity").val())
            $(this).find('.quantity_product_label').val($(this).find(".base_total_quantity").val())


        });
    }

    if(finished){
        $(".cancel_label_created").attr('disabled', false)
    } else if(localStorage.getItem('labels') != null){
        $(".cancel_label_created").attr('disabled', false)
    } else {
        $(".cancel_label_created").attr('disabled', true)
    }   
}

document.addEventListener("keydown", function(e) {
    if(e.key.length == 1 && !$(".modal_order").hasClass('show')){
        $("#detail_order").val($("#detail_order").val()+e.key)
        var array = $("#detail_order").val().split(',')
        if(array.length == 4 && $("#order_id").val() == ""){
            $("#order_id").val(array[0].split(',')[0])
            $(".order_id_input").val(array[0].split(',')[0])
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
    $(".total_product_order ").text("")
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

function getCountry(order){
    if(typeof order['billing_customer_country'] != "undefined"){
        if(order['billing_customer_country'] == 'CH'){
            return "Suisse"
        } else if(order['billing_customer_country'] == 'FR'){
            return "France"
        } else if(order['billing_customer_country'] == 'BE'){
            return "Belgique"
        } else {
            return false
        }
    } else {
        return false
    }
}