$(document).ready(function() {

    $("#get_sync_orders").on('click', function(){
        if(!$("#get_sync_orders").hasClass('rotate')){
            $("#get_sync_orders").addClass('rotate')

            $.ajax({
                url: "getOrders/BcVTcO9aqWdtP0ZVvujOJXQxjGT9wtRGG3iGZt8ZvwsZ58kMeJAM9TJlUumqb23C",
                method: 'GET',
            }).done(function(data) {
                $("#get_sync_orders").removeClass('rotate');
    
                if(JSON.parse(data).success){
                    $(".wrapper").append(`
                        <div class="alert alert-success border-0 bg-success alert-dismissible fade show">
                            <div class=" text-white">Les commandes ont bien été récupérées</div>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    `)
    
                    var table = $('#example').DataTable();
                    var order_progress = 0
                    var attribution = 0
    
                    // Pour recharger les données du DataTable en utilisant AJAX
                    table.ajax.reload(function(data){
                        data.orders.map((element) => {
                            if (element.user_id != null && element.user_id != 0) {
                                attribution = attribution + 1;
                            }
                            if (element.status == "processing" || element.status == "waiting_validate" || element.status == "waiting_to_validate" || element.status == "order-new-distrib" || element.status == "en-attente-de-pai") {
                                order_progress = order_progress + 1;
                            }
                        });
        
        
                        $(".number_order_pending").children().remove()
                        $(".number_order_pending").append('<span>'+data.orders.length+' dont <span id="number_attribution">'+attribution+'</span> attribuée(s) - '+order_progress+' à préparer</span>')
                    });
                 
                } else {
                    $(".wrapper").append(`
                        <div class="alert alert-danger border-0 bg-danger alert-dismissible fade show">
                            <div class=" text-white">`+JSON.parse(data).message+`</div>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    `)
                }
            });
        }
    })

    $(".list_product_to_add").select2({width: "350px", dropdownParent: $("#addProductOrderModal")})
    // // Sélection de la div
    // const paceProgress = document.querySelector('.pace-progress');

    // // Configuration de l'observer
    // const observerConfig = {
    //     attributes: true,
    //     attributeFilter: ['data-progress']
    // };

    // // Fonction de callback de l'observer
    // const observerCallback = function(mutationsList) {
    //     for (let mutation of mutationsList) {
    //         if (mutation.type === 'attributes' && mutation.attributeName === 'data-progress') {
    //             $(".percent").remove()

    //             if(mutation.target.getAttribute('data-progress') != 99){
    //                 $(".number_order_pending").append('<span class="percent">'+mutation.target.getAttribute('data-progress')+' %</span>')
    //             }
    //         }
    //     }
    // };

    // // Création de l'observer
    // const observer = new MutationObserver(observerCallback);
    // // Démarrage de l'observer
    // observer.observe(paceProgress, observerConfig);


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
        scrollY: '62vh',
        scrollCollapse: true,
        order: [ 0, 'asc' ],
        ajax: {
            url: 'getAllOrders',
            dataSrc: function(json) {

                // Récupérer les données des commandes (orders)
                var orders = json.orders;

                if(!orders){
                    $(".page-breadcrumb").after(`<div class="alert alert-danger border-0 bg-danger alert-dismissible fade show">
                        <div class="text-white">Oops ! Quelque chose s'est mal passé. Contactez le service informatique si cela persiste.</div>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>`)
                }

                // Récupère la liste des produits déjà pick
                var products_pick = json.products_pick
                // Récupère la liste des status
                var status_list = json.status_list
                // Récupérer les données des utilisateurs (users)
                var users = json.users;

                // Combiner les données des commandes (orders) et des utilisateurs (users)
                var combinedData = orders ? orders.map(function(order) {
                var coupons = false;
                var coupons_amount = false;
                var shipping_amount = 0
                var shipping_method = '';
                var take_order = true;

                    if(order['coupon_lines']){
                        order['coupon_lines'].forEach(function(cp){
                            coupons = cp['code'];
                            coupons_amount = cp['discount']
                        })
                    }
                   
                    if(order['shipping_lines']){
                        order['shipping_lines'].forEach(function(sp){
                            shipping_amount = sp['total'];
                            shipping_method = sp['method_id'];
                        })
                    }
                    
                    // if(order.line_items.length == 1){
                    //     Object.entries(order.line_items).forEach(([key, value]) => {
                    //         if(value.name){
                    //             if(value.name.includes("Carte Cadeau")){
                    //                 take_order = false;
                    //             }
                    //         }
                           
                    //     })
                    // } 

                    if(take_order){
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
                            line_items: [],
                            user_id: order.user_id,
                            coupons: coupons,
                            discount_total: order.discount_total,
                            coupons_amount: coupons_amount,
                            gift_card: order.pw_gift_cards_redeemed ?? false,
                            users: users,
                            products_pick: products_pick,
                            shipping_amount: shipping_amount,
                            shipping_method: shipping_method,
                            customer_note:  order.customer_note,
                            from_dolibarr:  order.from_dolibarr ?? false,
                            orderDolibarId : order.from_dolibarr ? order.dolibarrOrderId : false,
                            is_distributor : order.is_distributor,
                            discount_amount : order.discount_amount ?? 0
                        };
                    } else {
                        return false;
                    }
                    
                }).filter(Boolean) : false;

                return combinedData;
            }
        },
    
        columns: [
            { 
            data: null, 
                render: function(data, type, row) {
                    var country = row.shipping ? row.shipping.country : false
                    return `<div class="${row.is_distributor ? 'card_with_label' : ''} align-items-center d-flex w-100" ${row.is_distributor ? "data-label='Distributeur'" : ''}>
                                ${country ? '<img class="country_flag" src="assets/images/icons/'+country+'.png"/>' : ''}
                                <div class="d-flex flex-column">
                                    #${row.id} ${row.first_name} ${row.last_name} ${row.shipping_method.includes("chrono") ? '<div class="shipping_chrono_logo"></div>' : ''} 
                                 
                                    ${row.customer_note ? '<span class="customer_note">'+row.customer_note+'</span>' : ''}
                                </div>
                            </div>`
                }
            },
            {data: null,
                render: function(data, type, row) {
                    var selectOptions = '<option selected>Non attribuée</option>';
                
                    Object.entries(row.users).forEach(([key, value]) => {
                        if(value.user_id == row.user_id){
                            selectOptions += `<option ${value.role_id.includes(2) ? '' : 'disabled'} selected value="${value.user_id}">${value.name}</option>`;
                        } else {
                            selectOptions += `<option ${value.role_id.includes(2) ? '' : 'disabled'}  value="${value.user_id}">${value.name}</option>`;
                        }

                    })
                    
                    var selectHtml = `<select onchange="changeOneOrderAttribution('${row.id}', ${row.from_dolibarr}, ${row.is_distributor})" id="select_${row.id}" class="order_attribution select_user">${selectOptions}</select>`;

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
                    const options = { day: 'numeric', month: 'short', year: 'numeric' };
                    const date = new Date(row.date_created);
                    const formatter = new Intl.DateTimeFormat('fr-FR', options);
                    const dateEnFrancais = formatter.format(date);
                    return dateEnFrancais;

                    // const date = new Date(row.date_created);
                    // const dateEnFrancais = date.toLocaleString('fr-FR', options);
                    // return dateEnFrancais
                }
            },
            {data: null,
                render: function(data, type, row) {
                    var selectOptions = '';
                    Object.keys(row.status_list).forEach(function(key) {
                        selectOptions += `<option ${key == row.status ? "selected" : ""} value="`+key+`">`+row.status_list[key]+`</option>`
                    });
                    
                    var selectHtml = `<select onchange="changeStatusOrder('${row.id}', ${row.user_id}, ${row.from_dolibarr})" id="selectStatus_${row.id}" class="${row.status} select_status select_user empty_select">${selectOptions}</select>`;
                    return selectHtml;
                }
            },
            {data: null,
                render: function(data, type, row) {
                    return `
                        <div id="order_total_${row.id}" class="${row.shipping_method.includes("chrono") ? "chronopost_shipping" : ""} w-100 d-flex flex-column">
                            ${row.total != 0.00 ? '<span>Total (HT): <strong class="total_ht_order">' +parseFloat(row.total - row.total_tax).toFixed(2)+'</strong></span>' : '<span>Total (HT): <strong class="total_ht_order">' +parseFloat(row.total).toFixed(2)+'</strong></span>'}
                            <span>TVA: <strong class="total_tax_order">` +parseFloat(row.total_tax).toFixed(2)+`</strong></span>
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
                       
                        <div class="action_dashboard">
                            <button onclick="show('`+row.id+`')" class="detail_products">
                                <i class="show_detail bx bx-cube"></i>
                            </button>

                            <div class="${row.from_dolibarr ? "from_dolibarr_order_detail" : ""} modal fade modal_radius" id="order_detail_customer_`+row.id+`" tabindex="-1" role="dialog" aria-labelledby="exampleModalLongTitle" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered" role="document">
                                    <div class="modal-content">
                                    <div class="modal-body">
                                        <div class="mt-2 d-flex flex-column w-100 customer_billing">
                                            <div class="d-flex w-100 justify-content-between">
                                                <span class="customer_detail_title badge bg-dark">Facturation</span>
                                                ${row.shipping_method.includes("chrono") ? '<div class="shipping_chrono_logo"></div>' : ''}
                                            </div>
                                            

                                            ${row.billing.first_name ? `<div class="d-flex w-100 justify-content-between">
                                                <span class="billing_customer_first_name">`+row.billing.first_name+`</span>
                                                <i data-edit="billing_customer_first_name" class="edit_detail_order bx bx-pencil"></i>
                                            </div>` : ``}

                                            ${row.billing.last_name ? `<div class="d-flex w-100 justify-content-between">
                                                <span class="billing_customer_last_name">`+row.billing.last_name+`</span>
                                                <i data-edit="billing_customer_last_name" class="edit_detail_order bx bx-pencil"></i>
                                            </div>` : ``}

                                            ${row.billing.email ? `<div class="d-flex w-100 justify-content-between">
                                                <div class="d-flex w-100">
                                                    <i class="bx bx-envelope"></i>
                                                    <span class="billing_customer_email">`+row.billing.email+`</span>
                                                </div>
                                                <i data-edit="billing_customer_email" class="edit_detail_order bx bx-pencil"></i>
                                            </div>` : ``}
                                            
                                            ${row.billing.phone ? `<div class="d-flex w-100 justify-content-between">
                                                <div class="d-flex w-100">
                                                    <i class="bx bx-phone"></i>
                                                    <span class="billing_customer_phone">`+row.billing.phone+`</span>
                                                </div>
                                                <i data-edit="billing_customer_phone" class="edit_detail_order bx bx-pencil"></i>
                                            </div>` : ``}

                                            ${row.billing.company ? `<div class="d-flex w-100 justify-content-between">
                                                <span class="billing_customer_comapny">`+row.billing.company+`</span>
                                                <i data-edit="billing_customer_comapny" class="edit_detail_order bx bx-pencil"></i>
                                            </div>` : ``}

                                            ${row.billing.address_1 ? `<div class="d-flex w-100 justify-content-between">
                                                <span class="billing_customer_address_1">`+row.billing.address_1+`</span>
                                                <i data-edit="billing_customer_address_1" class="edit_detail_order bx bx-pencil"></i>
                                            </div>` : ``}

                                            ${row.billing.address_2 ? `<div class="d-flex w-100 justify-content-between">
                                                <span class="billing_customer_address_2">`+row.billing.address_2+`</span>
                                                <i data-edit="billing_customer_address_2" class="edit_detail_order bx bx-pencil"></i>
                                            </div>` : ``}

                                            ${row.billing.state ? `<div class="d-flex w-100 justify-content-between">
                                                <span class="billing_customer_state">`+row.billing.state+`</span>
                                                <i data-edit="billing_customer_state" class="edit_detail_order bx bx-pencil"></i>
                                            </div>` : ``}

                                            ${row.billing.postcode ? `<div class="d-flex w-100 justify-content-between">
                                                <span class="billing_customer_postcode">`+row.billing.postcode+`</span>
                                                <i data-edit="billing_customer_postcode" class="edit_detail_order bx bx-pencil"></i>
                                            </div>` : ``}

                                            ${row.billing.city ? `<div class="d-flex w-100 justify-content-between">
                                                <span class="billing_customer_city">`+row.billing.city+`</span>
                                                <i data-edit="billing_customer_city" class="edit_detail_order bx bx-pencil"></i>
                                            </div>` : ``}

                                            ${row.billing.country ? `<div class="d-flex w-100 justify-content-between">
                                                <span class="billing_customer_country">`+row.billing.country+`</span>
                                            </div>` : ``}
                                        </div>

                                        <div class="mt-3 d-flex flex-column w-100 customer_shipping">
                                            <span class="customer_detail_title badge bg-dark">Expédition</span>

                                            ${row.shipping.first_name ? `<div class="d-flex w-100 justify-content-between">
                                                <span class="shipping_customer_first_name">`+row.shipping.first_name+`</span>
                                                <i data-edit="shipping_customer_first_name" class="edit_detail_order bx bx-pencil"></i>
                                            </div>` : ``}

                                            ${row.shipping.last_name ? `<div class="d-flex w-100 justify-content-between">
                                                <span class="shipping_customer_last_name">`+row.shipping.last_name+`</span>
                                                <i data-edit="shipping_customer_last_name" class="edit_detail_order bx bx-pencil"></i>
                                            </div>` : ``}

                                            ${row.shipping.company ? `<div class="d-flex w-100 justify-content-between">
                                                <span class="shipping_customer_company">`+row.shipping.company+`</span>
                                                <i data-edit="shipping_customer_company" class="edit_detail_order bx bx-pencil"></i>
                                            </div>` : ``}

                                            ${row.shipping.address_1 ? `<div class="d-flex w-100 justify-content-between">
                                                <span class="shipping_customer_address_1">`+row.shipping.address_1+`</span>
                                                <i data-edit="shipping_customer_address_1" class="edit_detail_order bx bx-pencil"></i>
                                            </div>` : ``}

                                            ${row.shipping.address_2 ? `<div class="d-flex w-100 justify-content-between">
                                                <span class="shipping_customer_address_2">`+row.shipping.address_2+`</span>
                                                <i data-edit="shipping_customer_address_2" class="edit_detail_order bx bx-pencil"></i>
                                            </div>` : ``}

                                            ${row.shipping.state ? `<div class="d-flex w-100 justify-content-between">
                                                <span class="shipping_customer_state">`+row.shipping.state+`</span>
                                                <i data-edit="shipping_customer_state" class="edit_detail_order bx bx-pencil"></i>
                                            </div>` : ``}

                                            ${row.shipping.postcode ? `<div class="d-flex w-100 justify-content-between">
                                                <span class="shipping_customer_postcode">`+row.shipping.postcode+`</span>
                                                <i data-edit="shipping_customer_postcode" class="edit_detail_order bx bx-pencil"></i>
                                            </div>` : ``}

                                            ${row.shipping.city ? `<div class="d-flex w-100 justify-content-between">
                                                <span class="shipping_customer_city">`+row.shipping.city+`</span>
                                                <i data-edit="shipping_customer_city" class="edit_detail_order bx bx-pencil"></i>
                                            </div>` : ``}

                                            ${row.shipping.country ? `<div class="d-flex w-100 justify-content-between">
                                                <span class="shipping_customer_country">`+row.shipping.country+`</span>
                                            </div>` : ``}
                                        </div>
                                    </div>
                                    <input type="hidden" value="${row.id}" id="order_detail_id">
                                    <input type="hidden" value="${row.user_id}" id="order_attributed">

                                    <div class="modal-footer d-flex w-100 justify-content-between">
                                        <span>Commande #${row.id}</span>
                                        <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Fermer</button>
                                    </div>
                                    </div>
                                </div>
                            </div>
                            <i onclick="showCustomerOrderDetail('`+row.id+`')" class="show_detail_customer bx bx-user"></i>
                            <button onclick="deleteConfirm('`+row.id+`',`+row.from_dolibarr+`)" class="detail_products">
                                <i class="show_detail bx bx-trash"></i>
                            </button>
                        </div>
                        `;
                }

            },
            {data: null, 
                render: function(data, type, row) {
                    return row.shipping_method.includes("chrono") ? "chrono" : "classic"
                }
            },
            {data: null, 
                render: function(data, type, row) {
                    return row.status
                }
            },
            {data: null, 
                render: function(data, type, row) {
                    var preparateur = ""
                    Object.entries(row.users).forEach(([key, value]) => {
                        if(value.user_id == row.user_id){
                            preparateur = value.user_id;
                        }
                    })
                    return '<span>'+preparateur+'</span>';
                }
            },
            {data: null, 
                render: function(data, type, row) {
                    var country = row.shipping ? row.shipping.country : false
                    return country
                }
            },
        ],

        "columnDefs": [
            { "visible": false, "targets": 6 },
            { "visible": false, "targets": 7 },
            { "visible": false, "targets": 8 },
            { "visible": false, "targets": 9 },
        ],
        "initComplete": function(settings, json) {
            $(".shipping_dropdown").appendTo('.dataTables_length')
            $(".status_dropdown").appendTo('.dataTables_length')
            $(".preparateur_dropdown").appendTo('.dataTables_length')
            $(".country_dropdown").appendTo('.dataTables_length')

            // Design
            $("#example_length").parent().css('width', '70%')
            $("#example_length").parent().css('margin-bottom', '5px')
            $("#example_filter").parent().css('width', '30%')
            $("#example_filter").parent().css('margin-bottom', '5px')
            $("#example_filter").parent().parent().css('justify-content', 'space-between')
            $("#example_filter").parent().parent().css('flex-wrap', 'wrap')
            $("#example_filter").parent().css('min-width', '210px')

            $(".dataTables_length").css('display', 'flex')
            $(".dataTables_length").addClass('select2_custom')
            $(".shipping_dropdown").removeClass('d-none')
            $(".status_dropdown").removeClass('d-none')
            $(".preparateur_dropdown").removeClass('d-none')
            $(".country_dropdown").removeClass('d-none')

            $(".preparateur_dropdown").select2({
                width: '130px',
            });

            $(".shipping_dropdown").select2({
                width: '130px',
            });

            $(".status_dropdown").select2({
                width: '150px',
            });

            $(".country_dropdown").select2({
                width: '130px',
            });

            $(".select2-container").css('margin-left', '10px')

            $(".percent").remove()
            $(".loading_table").remove()
            $(".loading_table_content").removeClass('loading_table_content')

            var info = $('#example').DataTable().page.info();
            var attribution = 0
            var order_progress = 0

            // Calcul total valeur des commandes
            $('#example').DataTable().rows().eq(0).each( function ( index ) {
                var row = $('#example').DataTable().row( index );
                var data = row.data();
            } );

            // Check nombre attribution
            $('#example').DataTable().rows().eq(0).each( function ( index ) {
                var row = $('#example').DataTable().row( index );
                var data = row.data();

                data.name != "Non attribuée" && typeof data.name != "undefined" ? attribution = attribution + 1 : attribution = attribution
                data.status == "processing" || data.status == "waiting_validate" || data.status == "waiting_to_validate" || data.status == "order-new-distrib" || data.status == "en-attente-de-pai" ? order_progress = order_progress + 1 : order_progress = order_progress 
            } );
            
            $(".number_order_pending").children('.spinner-border').remove()
            $(".number_order_pending").append('<span>'+info.recordsTotal+' dont <span id="number_attribution">'+attribution+'</span> attribuée(s) - '+order_progress+' à préparer</span>')
            $(".allocation_of_orders").attr('disabled', false)
            $(".dataTables_paginate").parent().removeClass("col-md-7")
        },

        "fnRowCallback": function(nRow, aData, iDisplayIndex, iDisplayIndexFull) {

            var info = $('#example').DataTable().page.info();
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


    $('thead').remove()

    $('.shipping_dropdown').on('change', function(e){
        var shipping_dropdown = $(this).val();
        $('#example').DataTable()
        .column(6).search(shipping_dropdown, true, false)
        .draw();
    })

    $('.status_dropdown').on('change', function(e){
        var status_dropdown = $(this).val();
        $('#example').DataTable()
        .column(7).search(status_dropdown, true, false)
        .draw();
    })

    $('.preparateur_dropdown').on('change', function(e){
        var preparateur_dropdown = $(this).val();
        $('#example').DataTable()
        .column(8).search(preparateur_dropdown, true, false)
        .draw();
    })

    $('.country_dropdown').on('change', function(e){
        var preparateur_dropdown = $(this).val();
        $('#example').DataTable()
        .column(9).search(preparateur_dropdown, true, false)
        .draw();
    })
})

if($(window).width() < 650){
    $(".dataTables_scrollBody").css('max-height', '100%')
}

$(window).resize(function(){
    if($(window).width() < 650){
        $(".dataTables_scrollBody").css('max-height', '100%')
    } else {
        $(".dataTables_scrollBody").css('max-height', '62vh')
    }
})


$(".allocation_of_orders").on("click", function(){
    $('#allocationOrders').modal({
        backdrop: 'static',
        keyboard: false
    })
    $("#allocationOrders").modal('show')
})

// Attribuer les commandes
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
            setTimeout(function(){ location.reload(); }, 800);
        } else {
            $(".loading_allocation").addClass("d-none")
            $(".bx-error-circle").removeClass('d-none')
            $(".allocationOrdersTitle").text(JSON.parse(data).message ?? 'Erreur !')
            setTimeout(function(){ location.reload(); }, 1500);
        }
    });
})

// Désatribuer les commandes
$(".unassignOrdersConfirm").on("click", function(){
    
    $("#allocationOrders button").addClass('d-none')
    $(".loading_allocation").removeClass("d-none")

    $.ajax({
        url: "unassignOrders",
        method: 'GET',
    }).done(function(data) {
        if(JSON.parse(data).success){
            $(".loading_allocation").addClass("d-none")
            $(".lni-checkmark-circle").removeClass('d-none')
            $(".allocationOrdersTitle").text("Commandes désattribuées avec succès !")
            setTimeout(function(){ location.reload(); }, 1000);
        } else {
            $(".loading_allocation").addClass("d-none")
            $(".bx-error-circle").removeClass('d-none')
            $(".allocationOrdersTitle").text(JSON.parse(data).message ?? 'Erreur !')
            setTimeout(function(){ location.reload(); }, 1500);
        }
    });
})

// Update user attribution order
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

// Add product on order
$('body').on('click', '.add_product_order', function() {
    $("#order_id_add_product").val($(this).attr('data-order'))
    $('#addProductOrderModal').modal({
        backdrop: 'static',
        keyboard: false
    })
    $("#addProductOrderModal").appendTo("body").modal('show')
})

function add_product(order_id){
    if(order_id){
        $("#addProductOrderModal #order_id_add_product").val(order_id)
        $('#addProductOrderModal').modal({
            backdrop: 'static',
            keyboard: false
        })
        $("#addProductOrderModal").appendTo("body").modal('show');
    }
}

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
            alert(JSON.parse(data).message ?? 'Erreur')
        }
        $(".loading_add").addClass('d-none')
        $(".add_modal").removeClass('d-none')
    });
    
}

// Show detail product order
function show(id){

    $("#example").css('opacity', '0.3')
    $(".loading_show_detail_order ").removeClass('d-none')
    $(".detail_products").attr('disabled', true)

    $.ajax({
        url: "getDetailsOrder",
        method: 'GET',
        data: {order_id: id}
    }).done(function(data) {
        $("#example").css('opacity', '1')
        $(".loading_show_detail_order ").addClass('d-none')
        $(".detail_products").attr('disabled', false)

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
                $('body').append(`<div class="modal_order_admin modal_dashboard modal_detail_order modal_order modal fade" id="order_`+order[0].order_woocommerce_id+`" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered" role="document">
                        <div class="modal-content">
                            <div class="modal-body detail_product_order">
                                <div class="detail_product_order_head d-flex flex-column">
                                    <div class="p-1 mb-2 head_detail_product_order d-flex justify-content-between">
                                        <span class="column1 name_column">Article</span>
                                        <span class="column2 name_column">Coût</span>
                                        <span class="column3 name_column">Pick / Qté</span>
                                        <span class="column4 name_column">Total</span>
                                    </div>	

                                    <div class="body_detail_product_order">
                                        ${order.map((element) =>
                                            `
                                            <div class="${element.product_woocommerce_id}_${order[0].from_dolibarr ? element.product_dolibarr_id : element.line_item_id} ${order[0].from_dolibarr ? element.product_dolibarr_id : ''} ${element.pick == element.quantity ? 'pick' : ''} ${id[element.variation_id] ? (id[element.variation_id] == element.quantity ? 'pick' : '') : ''} d-flex w-100 align-items-center justify-content-between detail_product_order_line">
                                                <div class="column11 d-flex align-items-center detail_product_name_order">
                                                    ${element.name != null ? (parseFloat(element.cost).toFixed(2) == 0 ? `<span><span class="text-success">(Cadeau)</span> `+element.name+`</span>` : `<span>`+element.name+`</span>`) : '<span class="text-danger"> (Produit manquant) Identifiant : '+element.product_woocommerce_id+'</span>'}
                                                </div>
                                                <span class="column22">${parseFloat(element.cost).toFixed(2)}</span>
                                                <span class="column33 quantity">${element.pick } / ${element.quantity}</span>
                                                <span class="column44">${parseFloat(element.price * element.quantity).toFixed(2)}

                                                    ${!order[0].from_dolibarr ? '<span style="margin-left: 5px" class="column55"><i onclick="deleteProduct( \''+ order[0].order_woocommerce_id+ '\',' + element.line_item_id + ',' + element.product_woocommerce_id + ',' + element.quantity + ')" class="edit_order bx bx-trash"></i></span>' : 
                                                    '<span style="margin-left: 5px" class="column55_action"><i onclick="deleteProductDolibarr(' + order[0].orderDoliId + ', \'' + order[0].order_woocommerce_id + '\', ' + element.product_dolibarr_id + ',' + element.quantity + ')" class="edit_order bx bx-trash"></i></span>'}
                                                </span>
                                               
                                                
                                            </div>`
                                    ).join('')}
                                    </div>
                                    <div class="align-items-end mt-2 d-flex justify-content-between footer_detail_order flex-column"> 
                                        <div class="d-flex flex-column justify-content-between">
                                            <div class="d-flex flex-column align-items-center justify-content-end">
                                                ${order[0].coupons ? `<span class="order_customer_coupon mb-2 badge bg-success">`+order[0].coupons+`</span>` : ``}
                                            </div>
                                        </div>
                                        <div class="d-flex flex-column list_amount">
                                            <span class="montant_total_order">Sous-total des articles:<strong class="total_ht_order">`+parseFloat(sub_total).toFixed(2)+`€</strong></span> 
                                            ${order[0].coupons && order[0].coupons_amount > 0 ? `<span class="text-success">Code(s) promo: <strong>`+order[0].coupons+` (-`+order[0].coupons_amount+`€)</strong></span>` : ``}
                                            ${order[0].discount_amount > 0 ? `<span class="text-success">Réduction: <strong>-`+order[0].discount+`€ (-`+order[0].discount_amount+`%)</strong></span>` : ``}
                                        
                                            ${order[0].shipping_amount ? `<span class="montant_total_order">Expédition:<strong>`+order[0].shipping_amount+`€</strong></span>` : `<span class="montant_total_order">Expédition:<strong>0€</strong></span>`}
                                            <span class="montant_total_order">TVA: <strong class="total_tax_order">`+parseFloat(total_tax).toFixed(2)+`€</strong></span>

                                            ${gift_card > 0 ? `<span class="text-success">PW Gift Card: <strong>-`+gift_card+`€</strong></span>` : ``}

                                            <span class="mt-1 mb-2 montant_total_order">Payé: <strong class="total_paid_order">`+total+`€</strong></span>
                                           
                                        </div>
                                        <div class="d-flex justify-content-between w-100">
                                            <button onclick="add_product('`+order[0].order_woocommerce_id+`')" style="width:-min-content" type="button" class="btn btn-dark px-5">Ajouter un produit</button>
                                            <button style="margin-left: 10px" type="button" class="btn btn-dark px-5" data-bs-dismiss="modal">Fermer</button>
                                        </div>
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
                $(".detail_products").attr('disabled', false)
                alert('Aucune information pour cette commande !')
            }
        } else {
            alert('Aucune information pour cette commande !')
        }
    })	
}


function deleteConfirm(id, from_dolibarr){
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
                                <button onclick="deleteOrder('`+id+`',`+from_dolibarr+`)" type="button" class="btn btn-dark px-5 ">Oui</button>
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

function deleteOrder(id, from_dolibarr){

    $("#deleteOrder").find('button').attr('disabled', true)

    $.ajax({
        url: "deleteOrder",
        method: 'POST',
        data: {_token: $('input[name=_token]').val(), order_id: id,from_dolibarr: from_dolibarr}
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

            
            var table = $('#example').DataTable();
            var order_progress = 0
            var attribution = 0

            // Pour recharger les données du DataTable en utilisant AJAX
            table.ajax.reload(function(data){
                data.orders.map((element) => {
                    if (element.user_id != null && element.user_id != 0) {
                        attribution = attribution + 1;
                    }
                    if (element.status == "processing" || element.status == "waiting_validate" || element.status == "waiting_to_validate" || element.status == "order-new-distrib" || element.status == "en-attente-de-pai") {
                        order_progress = order_progress + 1;
                    }
                });


                $(".number_order_pending").children().remove()
                $(".number_order_pending").append('<span>'+data.orders.length+' dont <span id="number_attribution">'+attribution+'</span> attribuée(s) - '+order_progress+' à préparer</span>')
            });
        } else {
            $(".wrapper").append(`
                <div class="alert alert-danger border-0 bg-danger alert-dismissible fade show">
                    <div class=" text-white">Oops, une erreur est survenue !</div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            `)
        }
    });
}

// Show detail billing and shipping order
function showCustomerOrderDetail(id){

    var order_attributed = $('#order_detail_customer_'+id+' #order_attributed').val() != "null" ? true: false
    if(!order_attributed){
        $('#order_detail_customer_'+id+' .edit_detail_order').remove()
    }
    $('#order_detail_customer_'+id).modal({
        backdrop: 'static',
        keyboard: false
    })

    $("#order_detail_customer_"+id).appendTo("body").modal('show');

}

// Delete product's order Woocommerce
function deleteProduct(order_id, line_item_id, product_id, quantity){

    if(order_id.includes('SAV')){
        alert('Impossible de supprimer des produits d\'une commande SAV !')
    } else {
        var id = product_id

        // For SAV ORDER
        var name = $("#order_"+order_id).find("."+product_id).find('.detail_product_name_order').children('span').text()
    
        $("#order_id").val(order_id)
        $("#line_item_id").val(line_item_id)
        $("#product_order_id").val(id)
        $("#quantity_order").val(quantity)
        $(".product_name_to_delete").text(name)
        $("#deleteProductOrderModal").appendTo("body").modal('show')
    }
   
}

// Delete product's confirm order woocommerce
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

            $('.'+product_id+'_'+line_item_id).fadeOut()
            $('.'+product_id+'_'+line_item_id).remove()
            $(".loading_delete").addClass('d-none')
        } else {
            alert('Erreur !')
        }
        $(".delete_modal").removeClass('d-none')
        $("#deleteProductOrderModal").modal('hide')
    });
}


// Delete product's order dolibarr
function deleteProductDolibarr(order_id, ref_order, product_dolibarr_id, quantity){
    var name = $("#order_"+ref_order).find('.'+product_dolibarr_id).find('.detail_product_name_order').children('span').text()

    $("#order_id_dolibarr").val(order_id)
    $("#ref_order").val(ref_order)
    $("#product_dolibarr_id").val(product_dolibarr_id)
    $(".product_dolibarr_name_to_delete").text(name)
    $("#quantity_order_dolibarr").attr('max', quantity)
    $("#deleteProductOrderDolibarrModal").appendTo("body").modal('show')
}

// Delete product's confirm order dolibarr
function deleteProductOrderDolibarrConfirm(){

    $(".loading_delete").removeClass('d-none')
    $(".delete_modal").addClass('d-none')
    var order_id = $("#order_id_dolibarr").val()
    var product_dolibarr_id = $("#product_dolibarr_id").val()
    var ref_order = $("#ref_order").val()
    var quantity_to_delete = $("#quantity_order_dolibarr").val()
    var quantity =  $("#quantity_order_dolibarr").attr('max')

    $.ajax({
        url: "deleteOrderProductsDolibarr",
        method: 'POST',
        data: {_token: $('input[name=_token]').val(), order_id: order_id, quantity_to_delete: quantity_to_delete, quantity:quantity, product_dolibarr_id: product_dolibarr_id}
    }).done(function(data) {
        if(JSON.parse(data).success){
            if(quantity_to_delete >= quantity){
                $("#order_"+ref_order).find('.'+product_dolibarr_id).fadeOut()
                $("#order_"+ref_order).find('.'+product_dolibarr_id).remove()
            } else {
                var new_quantity = parseInt(quantity) - parseInt(quantity_to_delete)
                $("#order_"+ref_order).find('.'+product_dolibarr_id).find('.quantity').text('0 / '+new_quantity)
            }
           
            $(".loading_delete").addClass('d-none')
        } else {
            alert('Erreur !')
        }
        $(".delete_modal").removeClass('d-none')
        $("#deleteProductOrderDolibarrModal").modal('hide')
    });
}

// Update status order
function changeStatusOrder(order_id, user_id, from_dolibarr){
    
    var status = $("#selectStatus_"+order_id).val()
  
    $.ajax({
        url: "updateOrderStatus",
        method: 'POST',
        data: {_token: $('input[name=_token]').val(), order_id: order_id, status: status, user_id: user_id, from_dolibarr: from_dolibarr}
    }).done(function(data) {
        if(JSON.parse(data).success){
            $("#selectStatus_"+order_id).removeClass()
            $("#selectStatus_"+order_id).addClass("select_status")
            $("#selectStatus_"+order_id).addClass("select_user")
            $("#selectStatus_"+order_id).addClass('no_empty_select')
            $("#selectStatus_"+order_id).addClass(status)
        } else {
            alert('Erreur !')
        }
    });

}

// Update attribution order
function changeOneOrderAttribution(order_id, from_dolibarr, is_distributor){

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
        data: {_token: $('input[name=_token]').val(), order_id: order_id, user_id: user_id, from_dolibarr: from_dolibarr, is_distributor: is_distributor}
    }).done(function(data) {
        if(JSON.parse(data).success){
            $("#number_attribution").text(JSON.parse(data).number_order_attributed)
        } else {
            alert('Erreur !')
        }
    });
}