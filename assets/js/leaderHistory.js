$("#history_by_date").on('click', function(){
    $('#modalGenerateHistory').modal('show')
})

$(document).ready(function() {
    $('#example').DataTable({
        "ordering": false,
        "initComplete": function( settings, json ) {
            $(".order_research").appendTo('.dataTables_length')
            $(".dataTables_length").css('display', 'flex')
            $(".dataTables_length").addClass('select2_custom')
            $(".order_research").removeClass('d-none')
            $(".order_research input").css('margin-left', '10px')

            $(".loading").addClass('d-none')
            $('#example').removeClass('d-none');
        }
    })

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
            method: 'GET',
            method: 'POST',
            data: {_token: $('input[name=_token]').val(), order_id: order_id, status: status, from_dolibarr: from_dolibarr}
        }).done(function(data) {
            if(JSON.parse(data).success){
                // Remove order from commandeId and update dolibarr id command
                if(status == "processing"){
                    $.ajax({
                        url: "orderReInvoicing",
                        method: 'GET',
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
                alert('Erreur !')
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
                    var gift_card = !order[0].from_dolibarr ? (order[0].gift_card_amount > 0 ? parseFloat(order[0].gift_card_amount): 0) : 0
                    var total_tax = !order[0].from_dolibarr ? parseFloat(order[0].total_tax_order) : parseFloat(order[0].total_tax)
                    var sub_total = parseFloat(total) + parseFloat(discount_total) + parseFloat(gift_card) - parseFloat(total_tax) - (!order[0].from_dolibarr ? parseFloat(order[0].shipping_amount) : 0)
                }

                $(".modal_order_admin").remove()
                $('body').append(`<div class="modal_order_admin modal_order modal fade" id="order_`+order[0].order_woocommerce_id+`" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
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
                                                <span class="montant_total_order">TVA: <strong class="total_tax_order">`+total_tax+`€</strong></span>
                                                ${gift_card > 0 ? `<span class="text-success">PW Gift Card: <strong>`+gift_card+`€</strong></span>` : ``}
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
    