$("#history_by_date").on('click', function(){
    $('#modalGenerateHistory').modal('show')
})

$(document).ready(function() {


    //============== FORM RETURN ORDER SAV ==============
    let currectActive = 1;

    //============== Next Form===============
    function nextOne() {
        const form1 = $("#return_form1");
        const form2 = $("#return_form2");
       
        form1.css('left', '-3000px');
        form2.css('left', '0px');
        //next slide
        increamentNumber();
        // update progress bar
        update();
       
    }

    //=============== Back One==================
    function backOne() {
        const form1 = $("#return_form1");
        const form2 = $("#return_form2");

        form1.css('left', '0px');
        form2.css('left', '3000px');
        // back slide
        decreametNumber();
        // update progress bar
        update();
    }

    //============= Progress update====================
    function update() {
        const progressEl = $("#progress");
        const circles = $(".circle");

        circles.each(function(indx, circle) {
            if (indx < currectActive) {
                $(circle).addClass("active_progress");
            } else {
                $(circle).removeClass("active_progress");
            }
            // get all of active classes
            const active_progress = $(".active_progress");
            progressEl.css('width', ((active_progress.length - 1) / (circles.length - 1)) * 100 + "%");
        });
    }

    //================== Increament Number===============
    function increamentNumber() {
        const circles = $(".circle");

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


    $('body').on('click', '#next1', function () {
        if($(".checkbox_label ").is(':checked')){
            nextOne()
        } else {
            $(".checkbox_label ").css('border', '1px solid red')
        }
    })

    $('body').on('click', '#back1', function () {
        backOne()
    })

    //============== FORM RETURN ORDER SAV ==============

    $('#example').DataTable({
        "ordering": false,
        "initComplete": function(settings, json) {
            $(".order_research").appendTo('.dataTables_length')
            $(".dataTables_length").css('display', 'flex')
            $(".dataTables_length").addClass('select2_custom')
            $(".order_research").removeClass('d-none')
            $(".order_research input").css('margin-left', '10px')

            $(".loading").addClass('d-none')
            $('#example').removeClass('d-none');
        }
    });


    $('body').on('change', '.select_status', function () {

        var order_id = $(this).attr('data-order')
        var status = $(this).val()
        var from_dolibarr = $(this).attr('data-from_dolibarr') == "true" ? 1 : 0

        $(this).removeClass()
        $(this).addClass($(this).val())
        $(this).addClass("select_status")
        $(this).addClass("select_user")

        // Change status order
        $.ajax({
            url: "updateOrderStatus",
            method: 'POST',
            data: {_token: $('input[name=_token]').val(), order_id: order_id, status: status, from_dolibarr: from_dolibarr}
        }).done(function(data) {
            if(JSON.parse(data).success){
                // Remove order from commandeId and update dolibarr id command
                if(status == "processing"){
                    $.ajax({
                        url: "orderReInvoicing",
                        method: 'POST',
                        data: {_token: $('input[name=_token]').val(), order_id: order_id}
                    }).done(function(data) {
                        if(JSON.parse(data).success){
                        } else {
                            alert(JSON.parse(data).message)
                        }
                    });
                }
            } else {
                alert(JSON.parse(data).message ?? 'Erreur !')
            }
        });

    })
})

// Show detail product order
function show(id){

    $("#example").css('opacity', '0.3')
    $(".loading_show_detail_order ").removeClass('d-none')
    $(".show_detail").attr('disabled', true)

    $.ajax({
        url: "getDetailsOrder",
        method: 'GET',
        data: {order_id: id}
    }).done(function(data) {
        $("#example").css('opacity', '1')
        $(".loading_show_detail_order ").addClass('d-none')

        if(JSON.parse(data).success){
        
            var order = JSON.parse(data).order
            if(order.length > 0){
                // Dolibarr et Woocommerce
                if(!order[0].transfers){
                    var total = parseFloat(order[0].total_order)
                    var discount_total = !order[0].from_dolibarr ? parseFloat(order[0].discount) : 0
                    // var gift_card = !order[0].from_dolibarr ? (order[0].gift_card_amount > 0 ? parseFloat(order[0].gift_card_amount): 0) : 0
                    var gift_card = order[0].gift_card_amount ? (order[0].gift_card_amount > 0 ? parseFloat(order[0].gift_card_amount) : 0) : 0
                    var total_tax = !order[0].from_dolibarr ? parseFloat(order[0].total_tax_order) : parseFloat(order[0].total_tax)
                    var sub_total = parseFloat(total) + parseFloat(discount_total) + parseFloat(gift_card) - parseFloat(total_tax) - (!order[0].from_dolibarr ? parseFloat(order[0].shipping_amount) : 0)
                    console.log(gift_card)
                }

                $(".modal_order_admin").remove()
                $('body').append(`<div class="modal_order_admin modal_detail_order modal_order modal fade" id="order_`+order[0].order_woocommerce_id+`" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered" role="document">
                        <div class="modal-content">
                            <div class="modal-body detail_product_order">
                                <div class="detail_product_order_head d-flex flex-column">
                                    <div class="p-1 mb-2 head_detail_product_order d-flex w-100 justify-content-between">
                                        <span class="column1 name_column">Article</span>
                                        <span class="column2 name_column">Coût</span>
                                        <span class="column3 name_column">Pick / Qté</span>
                                        <span class="column4 name_column">Total</span>
                                    </div>	

                                    <div class="body_detail_product_order">
                                        ${order.map((element) => `
                                            <div class="${element.product_id}  ${element.variation_id} ${order[0].id}_${element.id} ${element.pick == element.quantity ? 'pick' : ''} ${id[element.variation_id] ? (id[element.variation_id] == element.quantity ? 'pick' : '') : ''} d-flex w-100 align-items-center justify-content-between detail_product_order_line">
                                                <div class="column11 d-flex align-items-center detail_product_name_order">
                                                    ${element.price == 0 ? `<span><span class="text-success">(Cadeau)</span> `+element.name+`</span>` : `<span>`+element.name+`</span>`}
                                                </div>
                                                ${!order[0].transfers ? '<span class="column22">'+parseFloat(element.cost).toFixed(2)+'</span>' : '<span class="column22">'+parseFloat(element.price_ttc).toFixed(2)+'</span>'}
                                                <span class="column33 quantity">${element.pick } / ${element.quantity}</span>
                                                ${!order[0].transfers ? '<span class="column44">'+parseFloat(element.price * element.quantity).toFixed(2)+'</span>' : '<span class="column44">'+parseFloat(element.price_ttc * element.quantity).toFixed(2)+'</span>'}
                                            </div>`
                                    ).join('')}
                                    </div>
                                    <div class="align-items-end mt-2 d-flex justify-content-between footer_detail_order"> 
                                        <div class="d-flex flex-column justify-content-between">
                                            <div class="d-flex flex-column align-items-center justify-content-end">
                                                ${order[0].coupons ? `<span class="order_customer_coupon mb-2 badge bg-success">`+order[0].coupons+`</span>` : ``}
                                            </div>
                                        </div>

                                        ${!order[0].transfers ?
                                            `<div class="d-flex flex-column list_amount">
                                                <span class="montant_total_order">Sous-total des articles:<strong class="total_ht_order">`+parseFloat(sub_total).toFixed(2)+`€</strong></span> 
                                                ${order[0].coupons && order[0].coupons_amount > 0 ? `<span class="text-success">Code(s) promo: <strong>`+order[0].coupons+` (-`+order[0].coupons_amount+`€)</strong></span>` : ``}
                                                ${!order[0].from_dolibarr ? '<span class="montant_total_order">Expédition:<strong>'+order[0].shipping_amount+'€</strong></span>' : ''}
                                                <span class="montant_total_order">TVA: <strong class="total_tax_order">`+parseFloat(total_tax).toFixed(2)+`€</strong></span>
                                                ${gift_card > 0 ? `<span class="text-success">Carte cadeau / Bon d'achat: <strong>`+gift_card+`€</strong></span>` : ``}
                                                ${order[0].remise_percent > 0 ? `<span class="text-success">Réduction: <strong>(-`+order[0].remise_percent+`%)</strong></span>` : ``}
                                                <span class="mt-1 mb-2 montant_total_order">Payé: <strong class="total_paid_order">`+total+`€</strong></span>
                                                <div class="d-flex justify-content-end">
                                                    <button style="width:-min-content" type="button" class="close_show_order btn btn-dark px-5" data-bs-dismiss="modal">Fermer</button>
                                                </div>
                                            </div>` : 
                                            `<div class="d-flex flex-column list_amount">
                                                <div class="d-flex justify-content-end">
                                                    <button style="width:-min-content" type="button" class="close_show_order btn btn-dark px-5" data-bs-dismiss="modal">Fermer</button>
                                                </div>
                                            </div>`
                                        }
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>`)
                $('#order_'+id).modal({
                    backdrop: 'static',
                    keyboard: false
                })

                $("#order_"+id).appendTo("body").modal('show')
            } else {
                $(".show_detail").attr('disabled', false)
                alert('Aucune information pour cette commande !')
            }
        } else {
            $(".show_detail").attr('disabled', false)
            alert('Aucune information pour cette commande !')
        }
    })	
}


function show_detail_customer(id){
    $("#example").css('opacity', '0.3')
    $(".loading_show_detail_order ").removeClass('d-none')
    $(".show_detail").attr('disabled', true)

    $.ajax({
        url: "getDetailsOrder",
        method: 'GET',
        data: {order_id: id}
    }).done(function(data) {
        $("#example").css('opacity', '1')
        $(".loading_show_detail_order ").addClass('d-none')

        if(JSON.parse(data).success){
            var order = JSON.parse(data).order
            if(order.length > 0){
                $('.modal_detail_customer').remove()
                $('body').append(`<div class="modal_detail_customer modal fade" id="order_detail_customer_`+order[0].order_woocommerce_id+`" tabindex="-1" role="dialog" aria-labelledby="exampleModalLongTitle" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered" role="document">
                        <div class="modal-content">
                            <div class="modal-body">
                                <div class="mt-2 d-flex flex-column w-100 customer_billing">
                                    <div class="d-flex w-100 justify-content-between">
                                        <span class="customer_detail_title badge bg-dark">Facturation</span>
                                        ${order[0].shipping_method_detail.includes("Livraison express") ? '<div class="shipping_chrono_logo"></div>' : ''}
                                    </div>
                                    

                                    ${order[0].billing_customer_first_name ? `<div class="d-flex w-100 justify-content-between">
                                        <span class="billing_customer_first_name">`+order[0].billing_customer_first_name+`</span>
                                        <i data-edit="billing_customer_first_name" class="edit_detail_order bx bx-pencil"></i>
                                    </div>` : ``}

                                    ${order[0].billing_customer_last_name ? `<div class="d-flex w-100 justify-content-between">
                                        <span class="billing_customer_last_name">`+order[0].billing_customer_last_name+`</span>
                                        <i data-edit="billing_customer_last_name" class="edit_detail_order bx bx-pencil"></i>
                                    </div>` : ``}

                                    ${order[0].billing_customer_email ? `<div class="d-flex w-100 justify-content-between">
                                        <div class="d-flex w-100">
                                            <i class="bx bx-envelope"></i>
                                            <span class="billing_customer_email">`+order[0].billing_customer_email+`</span>
                                        </div>
                                        <i data-edit="billing_customer_email" class="edit_detail_order bx bx-pencil"></i>
                                    </div>` : ``}
                                    
                                    ${order[0].billing_customer_phone ? `<div class="d-flex w-100 justify-content-between">
                                        <div class="d-flex w-100">
                                            <i class="bx bx-phone"></i>
                                            <span class="billing_customer_phone">`+order[0].billing_customer_phone+`</span>
                                        </div>
                                        <i data-edit="billing_customer_phone" class="edit_detail_order bx bx-pencil"></i>
                                    </div>` : ``}

                                    ${order[0].billing_customer_comapny ? `<div class="d-flex w-100 justify-content-between">
                                        <span class="billing_customer_comapny">`+order[0].billing_customer_comapny+`</span>
                                        <i data-edit="billing_customer_comapny" class="edit_detail_order bx bx-pencil"></i>
                                    </div>` : ``}

                                    ${order[0].billing_customer_address_1 ? `<div class="d-flex w-100 justify-content-between">
                                        <span class="billing_customer_address_1">`+order[0].billing_customer_address_1+`</span>
                                        <i data-edit="billing_customer_address_1" class="edit_detail_order bx bx-pencil"></i>
                                    </div>` : ``}

                                    ${order[0].billing_customer_address_2 ? `<div class="d-flex w-100 justify-content-between">
                                        <span class="billing_customer_address_2">`+order[0].billing_customer_address_2+`</span>
                                        <i data-edit="billing_customer_address_2" class="edit_detail_order bx bx-pencil"></i>
                                    </div>` : ``}

                                    ${order[0].billing_customer_state ? `<div class="d-flex w-100 justify-content-between">
                                        <span class="billing_customer_state">`+order[0].billing_customer_state+`</span>
                                        <i data-edit="billing_customer_state" class="edit_detail_order bx bx-pencil"></i>
                                    </div>` : ``}

                                    ${order[0].billing_customer_postcode ? `<div class="d-flex w-100 justify-content-between">
                                        <span class="billing_customer_postcode">`+order[0].billing_customer_postcode+`</span>
                                        <i data-edit="billing_customer_postcode" class="edit_detail_order bx bx-pencil"></i>
                                    </div>` : ``}

                                    ${order[0].billing_customer_city ? `<div class="d-flex w-100 justify-content-between">
                                        <span class="billing_customer_city">`+order[0].billing_customer_city+`</span>
                                        <i data-edit="billing_customer_city" class="edit_detail_order bx bx-pencil"></i>
                                    </div>` : ``}

                                    ${order[0].billing_customer_country ? `<div class="d-flex w-100 justify-content-between">
                                        <span class="billing_customer_country">`+order[0].billing_customer_country+`</span>
                                    </div>` : ``}
                                </div>

                                <div class="mt-3 d-flex flex-column w-100 customer_shipping">
                                    <span class="customer_detail_title badge bg-dark">Expédition</span>

                                    ${order[0].shipping_customer_first_name ? `<div class="d-flex w-100 justify-content-between">
                                        <span class="shipping_customer_first_name">`+order[0].shipping_customer_first_name+`</span>
                                        <i data-edit="shipping_customer_first_name" class="edit_detail_order bx bx-pencil"></i>
                                    </div>` : ``}

                                    ${order[0].shipping_customer_last_name ? `<div class="d-flex w-100 justify-content-between">
                                        <span class="shipping_customer_last_name">`+order[0].shipping_customer_last_name+`</span>
                                        <i data-edit="shipping_customer_last_name" class="edit_detail_order bx bx-pencil"></i>
                                    </div>` : ``}

                                    ${order[0].shipping_customer_company ? `<div class="d-flex w-100 justify-content-between">
                                        <span class="shipping_customer_company">`+order[0].shipping_customer_company+`</span>
                                        <i data-edit="shipping_customer_company" class="edit_detail_order bx bx-pencil"></i>
                                    </div>` : ``}

                                    ${order[0].shipping_customer_address_1 ? `<div class="d-flex w-100 justify-content-between">
                                        <span class="shipping_customer_address_1">`+order[0].shipping_customer_address_1+`</span>
                                        <i data-edit="shipping_customer_address_1" class="edit_detail_order bx bx-pencil"></i>
                                    </div>` : ``}

                                    ${order[0].shipping_customer_address_2 ? `<div class="d-flex w-100 justify-content-between">
                                        <span class="shipping_customer_address_2">`+order[0].shipping_customer_address_2+`</span>
                                        <i data-edit="shipping_customer_address_2" class="edit_detail_order bx bx-pencil"></i>
                                    </div>` : ``}

                                    ${order[0].shipping_customer_state ? `<div class="d-flex w-100 justify-content-between">
                                        <span class="shipping_customer_state">`+order[0].shipping_customer_state+`</span>
                                        <i data-edit="shipping_customer_state" class="edit_detail_order bx bx-pencil"></i>
                                    </div>` : ``}

                                    ${order[0].shipping_customer_postcode ? `<div class="d-flex w-100 justify-content-between">
                                        <span class="shipping_customer_postcode">`+order[0].shipping_customer_postcode+`</span>
                                        <i data-edit="shipping_customer_postcode" class="edit_detail_order bx bx-pencil"></i>
                                    </div>` : ``}

                                    ${order[0].shipping_customer_city ? `<div class="d-flex w-100 justify-content-between">
                                        <span class="shipping_customer_city">`+order[0].shipping_customer_city+`</span>
                                        <i data-edit="shipping_customer_city" class="edit_detail_order bx bx-pencil"></i>
                                    </div>` : ``}

                                    ${order[0].shipping_customer_country ? `<div class="d-flex w-100 justify-content-between">
                                        <span class="shipping_customer_country">`+order[0].shipping_customer_country+`</span>
                                    </div>` : ``}
                                </div>
                            </div>
                            <input type="hidden" value="${order[0].order_woocommerce_id}" id="order_detail_id">
                            <input type="hidden" value="${order[0].user_id}" id="order_attributed">

                            <div class="modal-footer d-flex w-100 justify-content-between">
                                <span>Commande #${order[0].order_woocommerce_id}</span>
                                <button type="button" class="close_show_order btn btn-primary" data-bs-dismiss="modal">Fermer</button>
                            </div>
                        </div>
                    </div>
                </div>`)

                $('#order_detail_customer_'+id).modal({
                    backdrop: 'static',
                    keyboard: false
                })
            
                $("#order_detail_customer_"+id).appendTo("body").modal('show');
            } else {
                $(".show_detail").attr('disabled', false)
                alert('Aucune information pour cette commande !')
            }
        } else {
            $(".show_detail").attr('disabled', false)
            alert('Aucune information pour cette commande !')
        }
    })

   
}

$('body').on('click', '.close_show_order', function () {
    $(".show_detail").attr('disabled', false)
})


// Edit details orders billing and shipping
$('body').on('click', '.edit_detail_order', function() {
    // Change icon edit to valid
    $(this).removeClass('bx-pencil')
    $(this).removeClass('.edit_detail_order')

    $(this).addClass('bx-check')
    $(this).addClass('valid_edit_detail_order')

    var field = $(this).attr('data-edit')
    $(".show ."+field).attr('contentEditable', true);
    $(".show ."+field).addClass('editableContent');
})

// Valid edit details orders billing and shipping
$('body').on('click', '.valid_edit_detail_order', function() {
    // Change icon valid to edit
    $(this).removeClass('bx-check')
    $(this).removeClass('valid_edit_detail_order')

    $(this).addClass('bx-pencil')
    $(this).addClass('edit_detail_order')

    var field = $(this).attr('data-edit')
    var field_value = $(".show ."+field).text()
    var order_id = $(".show #order_detail_id").val()
    $(".show ."+field).attr('contentEditable', false);
    $(".show ."+field).removeClass('editableContent');

    $.ajax({
        url: "updateDetailsOrders",
        method: 'POST',
        data: {_token: $('input[name=_token]').val(), order_id: order_id, field: field, field_value: field_value}
    }).done(function(data) {
        if(JSON.parse(data).success){
           
        } else {
            alert('Erreur')
        }
    })
})

// Close modal return order
$('body').on('click', '.close_modal_return_order', function() {
    $("#example").css('opacity', '1')
    $(".loading_show_detail_order ").addClass('d-none')
    $(".show_detail").attr('disabled', false)
})

// Check or uncheck all products return order
$('body').on('click', '.check_all_products', function() {
    if($(this).prop('checked')){
        $(".checkbox_label").prop('checked', true)
    } else {
        $(".checkbox_label").prop('checked', false)
    }
})


function deleteConfirm(id){
    $("#deleteOrder").remove()
    $('body').append(`
        <div class="modal fade modal_radius" id="deleteOrder" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div style="padding: 10px; position: absolute;" class="d-flex w-100 justify-content-end">
                        <i style="color: black; z-index:10;cursor:pointer;" data-bs-dismiss="modal" class="font-20 bx bx-x"></i>
                    </div>	
                    <div class="modal-body">
                        <h2 class="mt-2 text-center">Voulez-vous supprimer la commande `+id+` ?</h2>
                        <div class="w-100 d-flex justify-content-center">
                            <div class="d-flex justify-content-center w-75">
                                <button onclick="deleteOrder('`+id+`')" type="button" class="btn btn-dark px-5 ">Oui</button>
                                <button data-bs-dismiss="modal" style="margin-left: 10px" type="button" class="btn btn-dark px-5 ">Non</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    `)

    $('#deleteOrder').modal({
        backdrop: 'static',
        keyboard: false
    })
    $("#deleteOrder").modal('show');
}

function deleteOrder(id){

    $("#deleteOrder").find('button').attr('disabled', true)

    $.ajax({
        url: "deleteOrder",
        method: 'POST',
        data: {_token: $('input[name=_token]').val(), order_id: id, from_history: true}
    }).done(function(data) {
        $("#deleteOrder").find('button').attr('disabled', false)
        $("#deleteOrder").modal('hide');

        if(JSON.parse(data).success){
            $(".wrapper").append(`
                <div class="alert alert-success border-0 bg-success alert-dismissible fade show">
                    <div class=" text-white">La commande `+id+` a bien été supprimée</div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            `)

            setTimeout(function(){
                location.reload()
            },500)
        } else {
            $(".wrapper").append(`
                <div class="alert alert-danger border-0 bg-danger alert-dismissible fade show">
                    <div class=" text-white">Oops, une erreur est survenue !</div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            `)
        }
    })
}

function returnOrder(id){
    $("#example").css('opacity', '0.3')
    $(".loading_show_detail_order ").removeClass('d-none')
    $(".show_detail").attr('disabled', true)

    $.ajax({
        url: "getDetailsOrder",
        method: 'GET',
        data: {order_id: id}
    }).done(function(data) {
        $("#example").css('opacity', '1')
        $(".loading_show_detail_order ").addClass('d-none')

        if(JSON.parse(data).success){
        
            var order = JSON.parse(data).order
            if(order.length > 0){

                var list_paiement = {
                    "DONS" : "Don",
                    "stripe" : "Stripe",
                    "payplug" : "Payplug",
                    "apple_pay" : "Apple Pay",
                    "oney_x3_with_fees" : "Onex x3",
                    "oney_x4_with_fees" : "Oney x4",
                    "wc-scalapay-payin3" : "Scalapay x3",
                    "wc-scalapay-payin4" : "Scalapay x4",
                    "bacs" : "Virement bancaire",
                    "gift_card" : "Carte cadeau",
                    "bancontact" : "Payer avec Bancontact",
                    "american_express" : "Payer avec Amex",
                }

                var shipping_method = {
                    "lpc_sign" : "Colissimo avec signature",
                    "lpc_relay" : "Colissimo relais",
                    "lpc_expert" : "Colissimo Expert (4 à 6 jours ouvrés )",
                    "local_pickup" : "Retrait dans notre magasin à Marseille 13002",
                    "chronotoshopdirect" : "Chronopost - Livraison en relais Pickup",
                    "chronorelais" : "Livraison express en point relais",
                    "chrono13" : "Livraison express avant 13h",
                    "advanced_shipping" : "Retrait Distributeur Malpassé",
                }

                var disabled_pickup_relay = true;

                if(order[0].shipping_method.includes("relais") || order[0].shipping_method.includes("relay") || order[0].shipping_method.includes("toshop")){
                    disabled_pickup_relay = false;
                }
                // Dolibarr et Woocommerce

                $(".modal_order_admin").remove()

                $('body').append(`
                    <div class="modal_return_order modal_order_admin modal_detail_order modal_order modal fade" id="order_`+order[0].order_woocommerce_id+`" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered" role="document">
                            <div class="modal-content">
                                <div style="padding: 10px; position: absolute;" class="d-flex w-100 justify-content-end">
                                    <i style="color: black; z-index:10;cursor:pointer;" data-bs-dismiss="modal" class="close_modal_return_order font-20 bx bx-x"></i>
                                </div>
                                <div class="modal-body">
                                    <h5 class="text-center">Renvoie d'une commande</h5>
                                    <form class="h-100" method="POST" action="returnOrder">
                                    <input type="hidden" name="_token" value="`+$('input[name=_token]').val()+`">
                                    <input type="hidden" name="order_id" value="`+order[0].order_woocommerce_id+`">

                                        <div class="return_order_form_multy_step container_multy_step d-flex justify-content-center h-100">
                                            <div class="step_form" id="return_form1">
                                                <div class="detail_product_order_head d-flex flex-column">
                                                    <div class="p-1 mb-2 head_detail_product_order d-flex w-100 justify-content-between">
                                                        <span class="column1 name_column">
                                                            <input checked class="check_all_products checkbox_label form-check-input" type="checkbox" aria-label="Checkbox for product order">	
                                                            <span style="margin-left: 50px">Article</span>
                                                        </span>
                                                        <span class="column2 name_column">Prix</span>
                                                        <span class="column3 name_column">Qté</span>
                                                        <span class="column4 name_column">Total HT</span>
                                                        <span class="column5 name_column">Total TTC</span>
                                                    </div>	
                                                    <div class="body_detail_product_order">
                                                        ${order.map((element) => `
                                                            <div class="${element.product_id}  ${element.variation_id} ${order[0].id}_${element.id} ${id[element.variation_id] ? (id[element.variation_id] == element.quantity ? 'pick' : '') : ''} d-flex w-100 align-items-center justify-content-between detail_product_order_line">
                                                                <div class="column11 d-flex align-items-center detail_product_name_order_to_return">
                                                                    <span>
                                                                        <input name="product_ids[]" checked class="checkbox_label form-check-input" type="checkbox" value="${element.product_woocommerce_id}" aria-label="Checkbox for product order">	
                                                                    </span>
                                                                    ${element.image ? `<image style="margin-right: 25px; margin-left: 25px" src="`+element.image+`" width="65px" height="65px">` : ``}
                                                                    ${element.price == 0 ? `<span class="product_name_return_order"><span class="text-success">(Cadeau)</span> `+element.name+`</span>` : `<span class="product_name_return_order">`+element.name+`</span>`}
                                                                </div>
                                                                ${!order[0].transfers ? '<input class="input_product_return_order column22" type="text" value="'+parseFloat(element.cost).toFixed(2)+'">' 
                                                                : '<input class="input_product_return_order column22" type="text"  name="details['+element.product_woocommerce_id+']" value="'+parseFloat(element.price_ttc).toFixed(2)+'">'}
                                                                <span class="column33 quantity">
                                                                    <input class="quantity_product_label" min="1" max="${element.quantity}" value="${element.quantity}" name="quantity[${element.product_woocommerce_id}]" type="number">
                                                                </span>

                                                                
                                                                ${!order[0].transfers ? '<input type="hidden" name="total_without_tax['+element.product_woocommerce_id+']" class="input_product_return_order column44" value="'+parseFloat(element.cost).toFixed(2)+'">' 
                                                                : '<input type="text" name="total_with_tax['+element.product_woocommerce_id+']" class="input_product_return_order column44" value="'+parseFloat(element.price_ttc * element.quantity).toFixed(2)+'">'}
                                                                ${!order[0].transfers ? '<input type="hidden" name="total_with_tax['+element.product_woocommerce_id+']" class="input_product_return_order column55" value="'+parseFloat((element.total_tax / element.quantity) + element.cost).toFixed(2)+'">' 
                                                                : '<input type="text" name="total_with_tax['+element.product_woocommerce_id+']" class="input_product_return_order column55" value="'+parseFloat(element.price_ttc * element.quantity).toFixed(2)+'">'}

                                                                ${!order[0].transfers ? '<input type="text" class="input_product_return_order column44" value="'+parseFloat(element.total_price).toFixed(2)+'">' 
                                                                : '<input type="text" class="input_product_return_order column44" value="'+parseFloat(element.price_ttc * element.quantity).toFixed(2)+'">'}
                                                                ${!order[0].transfers ? '<input type="text" class="input_product_return_order column55" value="'+parseFloat(element.total_price + element.total_tax).toFixed(2)+'">' 
                                                                : '<input type="text" class="input_product_return_order column55" value="'+parseFloat(element.price_ttc * element.quantity).toFixed(2)+'">'}
                                                            </div>`
                                                        ).join('')}
                                                    </div>
                                                </div>
                                                <div class="btn_box">
                                                    <button class="btn btn-dark px-5" id="next1" type="button">Suivant</button>
                                                </div>
                                            </div>
                                            <div class="step_form" id="return_form2">
                                                <h3 class="text-dark">Expédition</h3>
                                                <div class="detail_return_order_shipping">
                                                    <div class="mt-2 d-flex flex-column w-100 detail_customer_return_order customer_billing">

                                                        <div class="mb-3 d-flex w-100 justify-content-between">
                                                            <span style="height:22px" class="customer_detail_title badge bg-dark">Expédition et paiement</span>
                                                        </div>

                                                        <div class="mb-3 d-flex w-100 justify-content-between">
                                                            <span class="title_span ">Méthode de paiement : </span>
                                                            <select name="payment_method" style="width: 250px" class="custom_input">
                                                                ${Object.keys(list_paiement).map((element) => 
                                                                    order[0].payment_method == "DONS" ? `<option selected value="`+element+`">`+list_paiement[element]+`</option>` : `<option value="`+element+`">`+list_paiement[element]+`</option>`
                                                                )}
                                                            </select>
                                                        </div>

                                                        <div class="mb-3 d-flex w-100 justify-content-between">
                                                            <span class="title_span ">Méthode d'expédition : </span>
                                                            <select name="shipping_method" style="width: 250px" class="custom_input">
                                                                ${Object.keys(shipping_method).map((element) => 
                                                                    order[0].shipping_method == element ? `<option ${disabled_pickup_relay && (element.includes("relais") || element.includes("relay") || element.includes("toshop")) ? "disabled" : ""} selected value="`+element+`">`+shipping_method[element]+`</option>` 
                                                                    : `<option ${disabled_pickup_relay && (element.includes("relais") || element.includes("relay") || element.includes("toshop")) ? "disabled" : ""} value="`+element+`">`+shipping_method[element]+`</option>`
                                                                )}
                                                            </select>
                                                        </div>

                                                        <div class="d-flex w-100 justify-content-between">
                                                            <span class="customer_detail_title badge bg-dark">Facturation</span>
                                                            ${order[0].shipping_method_detail.includes("Livraison express") ? '<div class="shipping_chrono_logo"></div>' : ''}
                                                        </div>
                                                        

                                                        ${order[0].billing_customer_last_name ? `<div class="d-flex w-100 justify-content-between">
                                                            <span class="title_span ">Nom : </span>
                                                            <input name="billing_customer_last_name" value="`+order[0].billing_customer_last_name+`">
                                                        </div>` : ``}

                                                        ${order[0].billing_customer_first_name ? `<div class="d-flex w-100 justify-content-between">
                                                            <span class="title_span ">Prénom : </span>
                                                            <input name="billing_customer_first_name" value="`+order[0].billing_customer_first_name+`">
                                                        </div>` : ``}

                                                        ${order[0].billing_customer_email ? `<div class="d-flex w-100 justify-content-between">
                                                            <span class="title_span ">Email : </span>
                                                            <input name="billing_customer_email" value="`+order[0].billing_customer_email+`"> 
                                                        </div>` : ``}

                                                        ${order[0].billing_customer_phone ? `<div class="d-flex w-100 justify-content-between">
                                                            <span class="title_span ">Téléphone : </span>
                                                            <input name="billing_customer_phone" value="`+order[0].billing_customer_phone+`"> 
                                                        </div>` : ``}

                                                        
                                                        ${order[0].billing_customer_comapny ? `<div class="d-flex w-100 justify-content-between">
                                                            <span class="title_span ">Entreprise : </span>
                                                            <input name="billing_customer_comapny" value="`+order[0].billing_customer_comapny+`"> 
                                                        </div>` : ``}

                                                        ${order[0].billing_customer_address_1 ? `<div class="d-flex w-100 justify-content-between">
                                                            <span class="title_span ">Adresse : </span>
                                                            <textarea name="billing_customer_address_1">`+order[0].billing_customer_address_1+`</textarea> 
                                                        </div>` : ``}

                                                        ${order[0].billing_customer_address_2 ? `<div class="d-flex w-100 justify-content-between">
                                                            <span class="title_span ">Complément d'adresse : </span>
                                                            <textarea name="billing_customer_address_2">`+order[0].billing_customer_address_2+`</textarea> 
                                                        </div>` : ``}

                                                        ${order[0].billing_customer_state ? `<div class="d-flex w-100 justify-content-between">
                                                            <span class="title_span ">State : </span>
                                                            <input name="billing_customer_state" value="`+order[0].billing_customer_state+`"> 
                                                        </div>` : ``}

                                                        ${order[0].billing_customer_postcode ? `<div class="d-flex w-100 justify-content-between">
                                                            <span class="title_span ">Code postal : </span>
                                                            <input name="billing_customer_postcode" value="`+order[0].billing_customer_postcode+`"> 
                                                        </div>` : ``}

                                                        ${order[0].billing_customer_city ? `<div class="d-flex w-100 justify-content-between">
                                                            <span class="title_span ">Ville : </span>
                                                            <input name="billing_customer_city" value="`+order[0].billing_customer_city+`"> 
                                                        </div>` : ``}

                                                        ${order[0].billing_customer_country ? `<div class="d-flex w-100 justify-content-between">
                                                            <span class="title_span ">Pays : </span>
                                                            <input name="billing_customer_country" value="`+order[0].billing_customer_country+`"> 
                                                        </div>` : ``}
                                                    </div>

                                                    <div class="mt-3 d-flex flex-column w-100 customer_shipping detail_customer_return_order">
                                                        <span class="customer_detail_title badge bg-dark">Expédition</span>

                                                        ${order[0].shipping_customer_last_name ? `<div class="d-flex w-100 justify-content-between">
                                                            <span class="title_span ">Nom : </span>
                                                            <input name="shipping_customer_last_name" value="`+order[0].shipping_customer_last_name+`"> 
                                                        </div>` : ``}

                                                        ${order[0].shipping_customer_first_name ? `<div class="d-flex w-100 justify-content-between">
                                                            <span class="title_span ">Prénom : </span>
                                                            <input name="shipping_customer_first_name" value="`+order[0].shipping_customer_first_name+`"> 
                                                        </div>` : ``}

                                                        ${order[0].shipping_customer_company ? `<div class="d-flex w-100 justify-content-between">
                                                            <span class="title_span ">Entreprise : </span>
                                                            <input name="shipping_customer_company" value="`+order[0].shipping_customer_company+`"> 
                                                        </div>` : ``}

                                                        ${order[0].shipping_customer_address_1 ? `<div class="d-flex w-100 justify-content-between">
                                                            <span class="title_span ">Adresse : </span>
                                                            <input name="shipping_customer_address_1" value="`+order[0].shipping_customer_address_1+`"> 
                                                        </div>` : ``}

                                                        ${order[0].shipping_customer_address_2 ? `<div class="d-flex w-100 justify-content-between">
                                                            <span class="title_span ">Complément d'adresse : </span>
                                                            <input name="shipping_customer_address_2" value="`+order[0].shipping_customer_address_2+`"> 
                                                        </div>` : ``}

                                                        ${order[0].shipping_customer_state ? `<div class="d-flex w-100 justify-content-between">
                                                            <span class="title_span ">State : </span>
                                                            <input name="shipping_customer_state" value="`+order[0].shipping_customer_state+`"> 
                                                        </div>` : ``}

                                                        ${order[0].shipping_customer_postcode ? `<div class="d-flex w-100 justify-content-between">
                                                            <span class="title_span ">Code postal : </span>
                                                            <input name="shipping_customer_postcode" value="`+order[0].shipping_customer_postcode+`"> 
                                                        </div>` : ``}

                                                        ${order[0].shipping_customer_city ? `<div class="d-flex w-100 justify-content-between">
                                                            <span class="title_span ">Ville : </span>
                                                            <input name="shipping_customer_city" value="`+order[0].shipping_customer_city+`"> 
                                                        </div>` : ``}

                                                        ${order[0].shipping_customer_country ? `<div class="d-flex w-100 justify-content-between">
                                                            <span class="title_span ">Pays : </span>
                                                            <input name="shipping_customer_country" value="`+order[0].shipping_customer_country+`"> 
                                                        </div>` : ``}
                                                    </div>
                                                </div>
                                                <div class="btn_box">
                                                    <button class="btn btn-dark px-5" id="back1" type="button">Retour</button>
                                                    <button class="btn btn-dark px-5" type="submit">Valider</button>
                                                </div>
                                            </div>
                                            <div class="progress_container return_order_form">
                                                <div class="progress" id="progress"></div>
                                                <div class="circle active_progress">1</div>
                                                <div class="circle">2</div>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                `)

                $('#order_'+id).modal({
                    backdrop: 'static',
                    keyboard: false
                })

                $("#order_"+id).appendTo("body").modal('show')
            } else {
                $(".show_detail").attr('disabled', false)
                alert('Aucune information pour cette commande !')
            }
        } else {
            $(".show_detail").attr('disabled', false)
            alert('Aucune information pour cette commande !')
        }
    })	
}