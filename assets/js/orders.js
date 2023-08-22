$(document).ready(function() {
    $(".list_product_to_add").select2({width: "350px", dropdownParent: $("#addProductOrderModal")})
    // Sélection de la div
    const paceProgress = document.querySelector('.pace-progress');

    // Configuration de l'observer
    const observerConfig = {
        attributes: true,
        attributeFilter: ['data-progress']
    };

    // Fonction de callback de l'observer
    const observerCallback = function(mutationsList) {
        for (let mutation of mutationsList) {
            if (mutation.type === 'attributes' && mutation.attributeName === 'data-progress') {
                $(".percent").remove()

                if(mutation.target.getAttribute('data-progress') != 99){
                    $(".number_order_pending").append('<span class="percent">'+mutation.target.getAttribute('data-progress')+' %</span>')
                }

            }
        }
    };

    // Création de l'observer
    const observer = new MutationObserver(observerCallback);
    // Démarrage de l'observer
    observer.observe(paceProgress, observerConfig);


    const options = {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: 'numeric',
        minute: 'numeric',
        hour12: false,
        timeZone: 'Europe/Paris'
    };
    var to = 0


    $('#example').DataTable({
        scrollY: '59vh',
        scrollCollapse: true,
        order: [ 0, 'asc' ],
        ajax: {
            url: 'getAllOrders',
            dataSrc: function(json) {

                // Récupérer les données des commandes (orders)
                var orders = json.orders;
                // Récupère la liste des produits déjà pick
                var products_pick = json.products_pick
                // Récupère la liste des status
                var status_list = json.status_list
                // Récupérer les données des utilisateurs (users)
                var users = json.users;
                // Combiner les données des commandes (orders) et des utilisateurs (users)
                var combinedData = orders.map(function(order) {
                var coupons = false;
                var coupons_amount = false;
                var shipping_amount = 0


                    order['coupon_lines'].forEach(function(cp){
                        coupons = cp['code'];
                        coupons_amount = cp['discount']
                    })

                    order['shipping_lines'].forEach(function(sp){
                        shipping_amount = sp['total'];
                    })

                    return {
                        id: order.id,
                        first_name: order.billing.first_name,
                        last_name: order.billing.last_name,
                        total: order.total,
                        total_tax: order.total_tax,
                        name: order.name,
                        billing: order.billing,
                        shipping: order.shipping,
                        status: order.status,
                        status_text: order.status_text ?? 'En cours',
                        status_list: status_list,
                        date_created: order.date_created,
                        line_items: order.line_items,
                        user_id: order.user_id,
                        coupons: coupons,
                        discount_total: order.discount_total,
                        coupons_amount: coupons_amount,
                        gift_card: order.pw_gift_cards_redeemed ?? false,
                        users: users,
                        products_pick: products_pick,
                        shipping_amount: shipping_amount,
                    };
                });
                return combinedData;
            }
        },
    
        columns: [
            { 
            data: null, 
                render: function(data, type, row) {
                    return "#"+row.id+' '+row.first_name + ' ' + row.last_name;
                }
            },
            {data: null,
                render: function(data, type, row) {
                    var selectOptions = '<option selected>Non attribuée</option>';
                
                    Object.entries(row.users).forEach(([key, value]) => {
                        if(value.user_id == row.user_id){
                            selectOptions += `<option selected value="${value.user_id}">${value.name}</option>`;
                        } else {
                            selectOptions += `<option value="${value.user_id}">${value.name}</option>`;
                        }

                    })
                    
                    var selectHtml = `<select onchange="changeOneOrderAttribution(${row.id})" id="select_${row.id}" class="order_attribution select_user">${selectOptions}</select>`;

                    if($("#select_"+row.id).val() == "Non attribuée"){
                        $("#select_"+row.id).addClass('empty_select')
                    } else {
                        $("#select_"+row.id).addClass('no_empty_select')
                    }
                    
                    return selectHtml;
                }
            },
            {data: null,
                render: function(data, type, row) {
                    const date = new Date(row.date_created);
                    const dateEnFrancais = date.toLocaleString('fr-FR', options);
                    return dateEnFrancais
                }
            },
            {data: null,
                render: function(data, type, row) {
                    var selectOptions = '';
                    Object.keys(row.status_list).forEach(function(key) {
                        selectOptions += `<option ${key == row.status ? "selected" : ""} value="`+key+`">`+row.status_list[key]+`</option>`
                    });
                    
                    var selectHtml = `<select onchange="changeStatusOrder(${row.id}, ${row.user_id})" id="selectStatus_${row.id}" class="${row.status} select_status select_user empty_select">${selectOptions}</select>`;
                    return selectHtml;
                }
            },
            {data: null,
                render: function(data, type, row) {
                    return `
                        <div id="order_total_${row.id}" class="w-100 d-flex flex-column">
                            ${row.total != 0.00 ? '<span>Total (HT): <strong class="total_ht_order">' +parseFloat(row.total - row.total_tax).toFixed(2)+'</strong></span>' : '<span>Total (HT): <strong class="total_ht_order">' +parseFloat(row.total).toFixed(2)+'</strong></span>'}
                            <span>TVA: <strong class="total_tax_order">` +row.total_tax+`</strong></span>
                            <span>Payé: <strong class="total_ttc_order">`+parseFloat(row.total).toFixed(2)+`</strong></span>
                        </div>`;
                }
            },
            {data: null,
                render: function(data, type, row) {
                    var total = parseFloat(row.total)
                    var discount_total = parseFloat(row.discount_total)
                    var gift_card = row.gift_card.length > 0 ? parseFloat(row.gift_card[0].amount): 0
                    var total_tax = parseFloat(row.total_tax)
                    var sub_total = parseFloat(total) + parseFloat(discount_total) + parseFloat(gift_card) - parseFloat(total_tax) - parseFloat(row.shipping_amount)

                    var id = []
                    Object.entries(row.products_pick).forEach(([key, value]) => {
                        if (value.order_id == row.id){
                            id[value.product_woocommerce_id] = value.pick
                        } 
                    }) 

                    return `
                        <div class="modal_order_admin modal_order modal fade" id="order_`+row.id+`" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered" role="document">
                                <div class="modal-content">
                                    <div class="modal-body detail_product_order">
                                        <div class="detail_product_order_head d-flex flex-column">
                                            <div class="p-1 mb-2 head_detail_product_order d-flex w-100 justify-content-between">
                                                <span class="column1 name_column">Article</span>
                                                <span class="column2 name_column">Coût</span>
                                                <span class="column3 name_column">Pick / Qté</span>
                                                <span class="column4 name_column">Total</span>
                                                <span class="column5 name_column">Action</span>
                                            </div>	

                                            <div class="body_detail_product_order">
                                                ${row.line_items.map((element) => `
                                                    <div class="${element.product_id}  ${element.variation_id} ${row.id}_${element.id} ${id[element.product_id] ? (id[element.product_id] == element.quantity ? 'pick' : '') : ''} ${id[element.variation_id] ? (id[element.variation_id] == element.quantity ? 'pick' : '') : ''} d-flex w-100 align-items-center justify-content-between detail_product_order_line">
                                                        <div class="column11 d-flex align-items-center detail_product_name_order">
                                                            ${element.price == 0 ? `<span><span class="text-success">(Cadeau)</span> `+element.name+`</span>` : `<span>`+element.name+`</span>`}
                                                        </div>
                                                        <span class="column22">	`+parseFloat(element.subtotal / element.quantity).toFixed(2)+ `</span>
                                                        <span class="column33 quantity">${id[element.product_id] ? id[element.product_id] : (id[element.variation_id] ? id[element.variation_id] : 0)} / ${element.quantity}</span>
                                                        <span class="column44">`+parseFloat(element.price * element.quantity).toFixed(2)+`</span>
                                                        <span class="column55"><i onclick="deleteProduct(`+row.id+`,`+element.id+`,`+element.variation_id+`,`+element.product_id+`,`+element.quantity+`)" class="edit_order bx bx-trash"></i></span>
                                                    </div>`
                                            ).join('')}
                                            </div>
                                            <div class="align-items-end mt-2 d-flex justify-content-between footer_detail_order"> 
                                                <div class="d-flex flex-column justify-content-between">
                                                    <div class="d-flex flex-column align-items-center justify-content-end">
                                                        ${row.coupons ? `<span class="order_customer_coupon mb-2 badge bg-success">`+row.coupons+`</span>` : ``}
                                                    </div>
                                                    <button type="button" data-order=`+row.id+` class="add_product_order btn btn-dark px-5" >Ajouter un produit</button>
                                                </div>
                                                <div class="d-flex flex-column list_amount">
                                                    <span class="montant_total_order">Sous-total des articles:<strong class="total_ht_order">`+parseFloat(sub_total).toFixed(2)+`€</strong></span> 
                                                    ${row.coupons && row.coupons_amount > 0 ? `<span class="text-success">Code(s) promo: <strong>`+row.coupons+` (-`+row.coupons_amount+`€)</strong></span>` : ``}
                                                    <span class="montant_total_order">Expédition:<strong> `+row.shipping_amount+`€</strong></span>
                                                    <span class="montant_total_order">TVA: <strong class="total_tax_order">`+total_tax+`€</strong></span>
                                                    ${row.gift_card.length > 0 ? `<span class="text-success">PW Gift Card: <strong>`+row.gift_card[0].number+` (-`+row.gift_card[0].amount+`€)</strong></span>` : ``}
                                                    <span class="mt-1 mb-2 montant_total_order">Payé: <strong class="total_paid_order">`+row.total+`€</strong></span>
                                                    <div class="d-flex justify-content-end">
                                                        <button style="width:-min-content" type="button" class="btn btn-dark px-5" data-bs-dismiss="modal">Fermer</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                    </div>
                                </div>
                            </div>
                        </div>
                        <i onclick="show(`+row.id+`)" class="show_detail bx bx-cube"></i>

                        <div class="modal fade" id="order_detail_customer_`+row.id+`" tabindex="-1" role="dialog" aria-labelledby="exampleModalLongTitle" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered" role="document">
                                <div class="modal-content">
                                <div class="modal-body">
                                    <div class="mt-2 d-flex flex-column w-100 customer_billing">
                                        <span class="customer_detail_title badge bg-dark">Facturation</span>
                                        <span>${row.billing.first_name} ${row.billing.last_name}</span>
                                        <div>
                                            <i class="bx bx-envelope"></i>
                                            <a href="mailto:${row.billing.email}"><span>${row.billing.email}</span></a>
                                        </div>
                                        <div>
                                            <i class="bx bx-phone"></i>
                                            <span>${row.billing.phone}</span>
                                        </div>
                                        <span>${row.billing.company}</span>
                                        <span>${row.billing.address_1}</span>
                                        <span>${row.billing.address_2}</span>
                                        <span>${row.billing.state}</span>
                                        <span>${row.billing.postcode}</span>
                                        <span>${row.billing.country}</span>
                                    </div>
                                    <div class="mt-3 d-flex flex-column w-100 customer_shipping">
                                        <span class="customer_detail_title badge bg-dark">Expédition</span>
                                        <span>${row.shipping.first_name} ${row.shipping.last_name}</span>
                                        <span>${row.shipping.company}</span>
                                        <span>${row.shipping.address_1}</span>
                                        <span>${row.shipping.address_2}</span>
                                        <span>${row.shipping.state}</span>
                                        <span>${row.shipping.postcode}</span>
                                        <span>${row.shipping.country}</span>
                                    </div>
                                </div>
                                <div class="modal-footer d-flex w-100 justify-content-between">
                                    <span>Commande #${row.id}</span>
                                    <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Fermer</button>
                                </div>
                                </div>
                            </div>
                        </div>
                        <i onclick="showCustomerOrderDetail(`+row.id+`)" class="show_detail_customer bx bx-user"></i>
                        `;
                }

            },
        ],

        "initComplete": function(settings, json) {

            var info = $('#example').DataTable().page.info();
            var total = 0
            var attribution = 0
            var order_progress = 0

            // Calcul total valeur des commandes
            $('#example').DataTable().rows().eq(0).each( function ( index ) {
                var row = $('#example').DataTable().row( index );
                var data = row.data();
                total = parseFloat(total) + parseFloat(data.total)
            } );

            // Check nombre attribution
            $('#example').DataTable().rows().eq(0).each( function ( index ) {
                var row = $('#example').DataTable().row( index );
                var data = row.data();

                data.name != "Non attribuée" && typeof data.name != "undefined" ? attribution = attribution + 1 : attribution = attribution
                data.status == "processing" || data.status == "waiting_validate" ? order_progress = order_progress + 1 : order_progress = order_progress 
            } );
            
            $(".number_order_pending").append('<span>'+info.recordsTotal+' dont <span id="number_attribution">'+attribution+'</span> attribuée(s) - '+order_progress+' en cours</span>')
            $(".total_amount").append('('+parseFloat(total).toFixed(2)+'€ )')
            $(".allocation_of_orders").attr('disabled', false)
            $(".dataTables_paginate").parent().removeClass("col-md-7")
        },

        "fnRowCallback": function(nRow, aData, iDisplayIndex, iDisplayIndexFull) {
            var selectElements = nRow.getElementsByClassName('order_attribution');
            for (var i = 0; i < selectElements.length; i++) {
                var select = selectElements[i];
                if (select.value != 'Non attribuée') {
                    select.classList.add('no_empty_select');
                    // select.classList.remove('no_empty_select');
                } else {
                    select.classList.add('empty_select');
                    // select.classList.remove('empty_select');
                }
            }
                
            $('td:nth-child(1)', nRow).attr('data-label', 'Commande');
            $('td:nth-child(2)', nRow).attr('data-label', 'Attribution');
            $('td:nth-child(3)', nRow).attr('data-label', 'Date');
            $('td:nth-child(4)', nRow).attr('data-label', 'État');
            $('td:nth-child(5)', nRow).attr('data-label', 'Total');
            $('td:nth-child(6)', nRow).attr('data-label', 'Détail');

            return nRow;

        }
    })

})

if($(window).width() < 650){
    $(".dataTables_scrollBody").css('max-height', '100%')
}

$(window).resize(function(){
    if($(window).width() < 650){
        $(".dataTables_scrollBody").css('max-height', '100%')
    } else {
        $(".dataTables_scrollBody").css('max-height', '59vh')
    }
})


$(".allocation_of_orders").on("click", function(){
    $('#allocationOrders').modal({
        backdrop: 'static',
        keyboard: false
    })
    $("#allocationOrders").modal('show')
})

$(".allocationOrdersConfirm").on("click", function(){
    
    $("#allocationOrders button").addClass('d-none')
    $(".loading_allocation").removeClass("d-none")

    $.ajax({
        url: "distributionOrders",
        method: 'GET',
    }).done(function(data) {
        if(JSON.parse(data).success){
            $(".loading_allocation").addClass("d-none")
            $(".lni-checkmark-circle").removeClass('d-none')
            $(".allocationOrdersTitle").text("Commandes réparties avec succès !")
            setTimeout(function(){ location.reload(); }, 2500);
        } else {
            alert(JSON.parse(data).message ?? 'Erreur !')
            $("#allocationOrders button").removeClass('d-none')
            $(".loading_allocation").addClass("d-none")
            $("#allocationOrders").modal('hide')
        }
    });
})


$(".change_attribution_order").on("change", function(){
    if($(this).val() != ""){
        var from_user = $(this).attr('id').split('_')[1]
        var to_user = $(this).val()
        var to_user_text = $("#user_name_"+$(this).val()).text()
        
        $(".from_to_user").val(from_user+','+to_user)
        $("#from_user").text($("#team_user_"+from_user).text())
        $("#to_user").text(to_user_text)
        $("#reallocationOrders").modal('show')
    }
})

$(".reallocationOrdersConfirm").on("click", function(){
    var from_user = $(".from_to_user").val().split(',')[0]
    var to_user = $(".from_to_user").val().split(',')[1]

    $(".loading_realocation").removeClass('d-none')
    $("#reallocationOrders button").addClass('d-none')

    $.ajax({
        url: "updateAttributionOrder",
        method: 'POST',
        data: {_token: $('input[name=_token]').val(), from_user: from_user, to_user: to_user}
    }).done(function(data) {
        if(JSON.parse(data).success){
            $('#example').DataTable().ajax.reload();
            $(".loading_realocation").addClass('d-none')
            $("#reallocationOrders button").removeClass('d-none')
            $("#reallocationOrders").modal('hide')
        } else {
            alert('Erreur !')
        }
    });

})
    
$('body').on('click', '.add_product_order', function() {
    $("#order_id_add_product").val($(this).attr('data-order'))
    $('#addProductOrderModal').modal({
        backdrop: 'static',
        keyboard: false
    })
    $("#addProductOrderModal").appendTo("body").modal('show')
})

function addProductOrderConfirm(){
    $(".loading_add").removeClass('d-none')
    $(".add_modal").addClass('d-none')

    var product = $(".list_product_to_add").val()
    var order_id = $("#order_id_add_product").val()
    var quantity = $("#quantity_product").val()

    $.ajax({
        url: "addOrderProducts",
        method: 'POST',
        data: {_token: $('input[name=_token]').val(), order_id: order_id, product: product, quantity: quantity}
    }).done(function(data) {
        if(JSON.parse(data).success){

            var order_id = JSON.parse(data).order.id
            var line_items = JSON.parse(data).order.line_items
            var last_line_items = JSON.parse(data).order.line_items[line_items.length - 1]
            
            $("#order_"+order_id+" .body_detail_product_order").append(`
                <div class="`+order_id+`_`+last_line_items.id+`  d-flex w-100 align-items-center justify-content-between detail_product_order_line">
                    <div class="column11 d-flex align-items-center detail_product_name_order">
                        <span>`+last_line_items.name+`</span>
                    </div>
                    <span class="column22">`+parseFloat(last_line_items.price).toFixed(2)+`</span>
                    <span class="column33 quantity"> `+last_line_items.quantity+` </span>
                    <span class="column44">`+parseFloat(last_line_items.subtotal).toFixed(2)+`</span>
                    <span class="column55"><i onclick="deleteProduct(`+order_id+`,`+last_line_items.id+`,`+last_line_items.variation_id+`,`+last_line_items.product_id+`,`+last_line_items.quantity+`)" class="edit_order bx bx-trash"></i></span>

                </div>`
            )

            // Update total modal detail order
            $("#order_"+order_id+" .total_ht_order").text(JSON.parse(data).order.total != 0.00 ? parseFloat(JSON.parse(data).order.total - JSON.parse(data).order.total_tax).toFixed(2) : parseFloat(JSON.parse(data).order.total).toFixed(2))
            $("#order_"+order_id+" .total_tax_order").text(parseFloat(JSON.parse(data).order.total_tax))
            $("#order_"+order_id+" .total_paid_order").text(JSON.parse(data).order.total)

            // Update total dashboard
            $("#order_total_"+order_id+" .total_ht_order").text(JSON.parse(data).order.total != 0.00 ? parseFloat(JSON.parse(data).order.total - JSON.parse(data).order.total_tax).toFixed(2) : parseFloat(JSON.parse(data).order.total).toFixed(2))
            $("#order_total_"+order_id+" .total_tax_order").text(parseFloat(JSON.parse(data).order.total_tax))
            $("#order_total_"+order_id+" .total_ttc_order").text(JSON.parse(data).order.total)

            $("#addProductOrderModal").modal('hide')
        } else {
            alert('Erreur !')
        }
        $(".loading_add").addClass('d-none')
        $(".add_modal").removeClass('d-none')
    });
    
}

function show(id){

    $('#order_'+id).modal({
        backdrop: 'static',
        keyboard: false
    })

    $("#order_"+id).appendTo("body").modal('show')
}


function showCustomerOrderDetail(id){

    $('#order_detail_customer_'+id).modal({
        backdrop: 'static',
        keyboard: false
    })

    $("#order_detail_customer_"+id).appendTo("body").modal('show');

}

function deleteProduct(order_id, line_item_id, variation_id, product_id, quantity){
    var id = variation_id != 0 ? variation_id : product_id
    var name = $("."+order_id+"_"+line_item_id).children('.detail_product_name_order').children('span').text()

    $("#order_id").val(order_id)
    $("#line_item_id").val(line_item_id)
    $("#product_order_id").val(id)
    $("#quantity_order").val(quantity)
    $(".product_name_to_delete").text(name)
    $("#deleteProductOrderModal").appendTo("body").modal('show')
}

function deleteProductOrderConfirm(increase){

    $(".loading_delete").removeClass('d-none')
    $(".delete_modal").addClass('d-none')
    var order_id = $("#order_id").val()
    var line_item_id = $("#line_item_id").val()
    var product_id = $("#product_order_id").val()
    var quantity = $("#quantity_order").val()

    $.ajax({
        url: "deleteOrderProducts",
        method: 'POST',
        data: {_token: $('input[name=_token]').val(), order_id: order_id, line_item_id: line_item_id, increase: increase, quantity: quantity, product_id: product_id}
    }).done(function(data) {
        if(JSON.parse(data).success){
          
            // Update total modal detail order
            $("#order_"+order_id+" .total_ht_order").text(JSON.parse(data).order.total != 0.00 ? parseFloat(JSON.parse(data).order.total - JSON.parse(data).order.total_tax).toFixed(2) : parseFloat(JSON.parse(data).order.total).toFixed(2))
            $("#order_"+order_id+" .total_tax_order").text(parseFloat(JSON.parse(data).order.total_tax))
            $("#order_"+order_id+" .total_paid_order").text(JSON.parse(data).order.total)

            // Update total dashboard
            $("#order_total_"+order_id+" .total_ht_order").text(JSON.parse(data).order.total != 0.00 ? parseFloat(JSON.parse(data).order.total - JSON.parse(data).order.total_tax).toFixed(2) : parseFloat(JSON.parse(data).order.total).toFixed(2))
            $("#order_total_"+order_id+" .total_tax_order").text(parseFloat(JSON.parse(data).order.total_tax))
            $("#order_total_"+order_id+" .total_ttc_order").text(JSON.parse(data).order.total)

            $('.'+order_id+'_'+line_item_id).fadeOut()
            $('.'+order_id+'_'+line_item_id).remove()
            $(".loading_delete").addClass('d-none')
        } else {
            alert('Erreur !')
        }
        $(".delete_modal").removeClass('d-none')
        $("#deleteProductOrderModal").modal('hide')
    });
}

function changeStatusOrder(order_id, user_id){
    var order_id = order_id
    var user_id = user_id
    var status = $("#selectStatus_"+order_id).val()
    
    $.ajax({
        url: "updateOrderStatus",
        method: 'POST',
        data: {_token: $('input[name=_token]').val(), order_id: order_id, status: status, user_id: user_id}
    }).done(function(data) {
        if(JSON.parse(data).success){
            $("#selectStatus_"+order_id).removeClass('empty_select')
            $("#selectStatus_"+order_id).addClass('no_empty_select')
        } else {
            alert('Erreur !')
        }
    });

}

function changeOneOrderAttribution(order_id){
    var order_id = order_id
    var user_id = $("#select_"+order_id).val()

    if(user_id == "Non attribuée"){
        $("#select_"+order_id).addClass('empty_select')
        $("#select_"+order_id).removeClass('no_empty_select')
    } else {
        $("#select_"+order_id).removeClass('empty_select')
        $("#select_"+order_id).removeClass('no_empty_select')
        $("#select_"+order_id).addClass('no_empty_select')
    }

    $.ajax({
        url: "updateOneOrderAttribution",
        method: 'POST',
        data: {_token: $('input[name=_token]').val(), order_id: order_id, user_id: user_id}
    }).done(function(data) {
        if(JSON.parse(data).success){
            $("#number_attribution").text(JSON.parse(data).number_order_attributed)
        } else {
            alert('Erreur !')
        }
    });
}