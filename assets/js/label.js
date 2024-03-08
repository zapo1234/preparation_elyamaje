"use strict";

        const form1 = document.getElementById("form1");
        const form2 = document.getElementById("form2");
        const progressEl = document.getElementById("progress");
        const circles = document.querySelectorAll(".circle");
        let currectActive = 1;
        //============== Next Form===============
        function nextOne() {
            if($(".check_all").is(':checked')){
                $(".check_all").css('border', '1px solid black')
                form1.style.left = "-500px";
                form2.style.left = "0px";
                //next slide
                increamentNumber();
                // update progress bar
                update();
            } else {
                $(".check_all").css('border', '1px solid red')
            }
        }
        //=============== Back One==================
        function backOne() {
            form1.style.left = "0px";
            form2.style.left = "500px";
            // back slide
            decreametNumber();
            // update progress bar
            update();
        }
        //============= Progress update====================
        function update() {
            circles.forEach((circle, indx) => {
                if (indx < currectActive) {
                circle.classList.add("active_progress");
                } else {
                circle.classList.remove("active_progress");
                }
                // get all of active classes
                const active_progress = document.querySelectorAll(".active_progress");
                progressEl.style.width =
                ((active_progress.length - 1) / (circles.length - 1)) * 100 + "%";
            });
        }
        //================== Increament Number===============
        function increamentNumber() {
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
        //================= btn Events===================
        const btnsEvents = () => {
            const next1 = document.getElementById("next1");
            const back1 = document.getElementById("back1");
            //next1
            next1.addEventListener("click", nextOne);
            // back1
            back1.addEventListener("click", backOne);
        };
        document.addEventListener("DOMContentLoaded", btnsEvents);


$(document).ready(function() {
    
    var cn23_not_download = false
    $(".cn23_label").each(function(index){
        console.log($(this).val())
       if($(this).val() == "0"){
        cn23_not_download = true
       }
    });

    if(cn23_not_download){
        $('#warningCn23Download').modal({
            backdrop: 'static',
            keyboard: false
        })
        $("#warningCn23Download").modal('show')
    }

    $('#example').DataTable({
        "order": [[0, 'desc']],
        "columnDefs" : [       
            { 
                'searchable'    : false, 
                'targets'       : [2,3] 
            },
        ],
        "initComplete": function(settings, json) {
            $(".loading").hide()
            $("#example").removeClass('d-none')
            $("#example_filter").parent().remove()
            // $("#example_length select").css('margin-right', '10px')
            $(".order_research").appendTo('.dataTables_length')
            $(".dataTables_length").css('display', 'flex')
            $(".dataTables_length").addClass('select2_custom')
            $(".order_research").removeClass('d-none')
            // $(".order_research input").css('margin-left', '10px')

            $(".status_dropdown").select2({
            	width: '150px', 
            });

            $(".type_dropdown").select2({
            	width: '125px', 
            }); 

            $(".select2-container").css('height', '100%')
            $(".select2-container").css('margin-left', '10px')
            $(".custom_input").css('margin-left', '10px')

            

            

        },
    })
})

$("#show_modal_bordereau").on('click', function(){
    $("#modalBordereau").modal('show')
})

$(".delete_label").on('click', function(){
    $("#tracking_number").val($(this).attr('data-tracking'))
    $("#label_id").val($(this).attr('data-label'))
    $("#deleteLabelModalCenter").modal('show')
})


$(".generate_label_button").on('click', function(){
        var order_id = $(this).attr('data-order')
        var from_dolibarr = $(this).attr('from_dolibarr') == "1" ? true : false

        $.ajax({
            url: "getProductOrderLabel",
            method: 'POST',
            data : {_token: $('input[name=_token]').val(), order_id: order_id, from_dolibarr: from_dolibarr}
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
                        var weight_product = value.weight != "" ? value.weight : 0
                        total_weight = parseFloat(total_weight) + (parseFloat(weight_product) * value.quantity);
                    }


                    innerHtml +=
                        `<div class="${value.quantity - value.total_quantity == 0 ? 'disabled_text' : '' } line_items_label d-flex w-100 align-items-center justify-content-between">
                            <span style="width: 50px">
                                <input name="label_product[]" ${value.quantity - value.total_quantity == 0 ? 'disabled' : 'checked' } class="checkbox_label form-check-input" type="checkbox" value="${value.product_woocommerce_id}" aria-label="Checkbox for product order">	
                            </span>
                            <span class="w-50">${value.name}</span>
                            <span class="w-25">${value.cost}</span>
                            <span class="w-25" ><input class="quantity_product_label" ${value.quantity - value.total_quantity == 0 ? 'disabled' : '' } min="1" max="${value.quantity - (value.total_quantity ?? 0) }" value="${value.quantity -  (value.total_quantity ?? 0) }" name="quantity[${value.product_woocommerce_id}]" type="number"> / ${value.quantity}</span>
                            <span class="weight w-25">${value.weight != "" ? value.weight : 0}</span>
                        </div>`
                });
                
                innerHtml += `<div class="total_weight mt-3 w-100 d-flex justify-content-end">Poids : `+parseFloat(total_weight).toFixed(3)+` Kg</div>`
                // Si tous les produits sont déjà dans des étiquettes alors désactiver le button de génération
                if(product == 0){
                    $(".button_validate_modal_label").children('button').last().attr('disabled', true)
                } else {
                    $(".button_validate_modal_label").children('button').last().attr('disabled', false)
                }

                // If is dolibarr order
                $("#from_dolibarr").val(from_dolibarr)

                $(".body_line_items_label").append(innerHtml)
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

// Print ZPL file
$('body').on('click', '.print_zpl_file', function(e) {
    var label_id = $(this).attr('data-label')
    $.ajax({
        url: "labelPrintZPL",
        method: 'POST',
        data : {_token: $('input[name=_token]').val(), label_id: label_id}
    }).done(function(data) {
        if(JSON.parse(data).success){
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
                        error : function(xhr){
                            $(".alert").remove()
                            $(".show_messages").prepend(`
                                <div class="alert alert-danger border-0 bg-danger alert-dismissible fade show">
                                    <div class=" text-white">Aucune imprimante n'a été trouvée</div>
                                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                </div>
                            `)
                        }
                    })
                  }
                }
            })
        }
    })
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


document.addEventListener("keydown", function(e) {
    if(e.key.length == 1){
        $("#detail_order_label").val($("#detail_order_label").val()+e.key)
        var array = $("#detail_order_label").val().split(',')
        if(array.length == 4 && $(".custom_input").val('') == ""){
            $("#order_id").val(array[0].split(',')[0])
            $(".custom_input").val(array[0].split(',')[0])
            $(".research_label_order").click()
            $(".custom_input").val('')
        }
    }
})


function showCustomerOrderDetail(order_id){
    $("#order_detail_customer_"+order_id).modal({
        backdrop: 'static',
        keyboard: false
    })
    $("#order_detail_customer_"+order_id).appendTo("body").modal('show');
}

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

function showTrackingStatus(order_id, tracking_number, origin){
    $("#trackingStatusLabel").modal({
        backdrop: 'static',
        keyboard: false
    })

    $.ajax({
        url: "getTrackingStatus",
        method: 'POST',
        data: {_token: $('input[name=_token]').val(), order_id: order_id, tracking_number: tracking_number, origin: origin}
    }).done(function(data) {
        if(JSON.parse(data).success){
            var data_tracking = JSON.parse(data).details

            $(".shipping_method").removeClass('shipping_chrono_logo')
            $(".shipping_method").removeClass('shipping_colissimo_logo')

            if(origin == "colissimo"){

                $(".shipping_method").addClass('shipping_colissimo_logo')
                $(".details_tracking").children('table').remove()
                $(".details_tracking_wizard").children('.step_tracking').remove()
                $(".detail_status_label_tracking").remove()

                // Step colissimo
                if(data_tracking.parcel){
                    $(".details_tracking_wizard").append(
                        `
                            <ol class="step_tracking tracking_colissimo">
                                <li class="${JSON.parse(data).stepColissimo == 0 ? 'current' : ''}">Votre Colissimo va bientôt nous être confié !</li>
                                <li class="${JSON.parse(data).stepColissimo == 1 ? 'current' : ''}">Votre colis est entre nos mains</li>
                                <li class="${JSON.parse(data).stepColissimo == 2 ? 'current' : ''}">Il est en traitement dans notre réseau</li>
                                <li class="${JSON.parse(data).stepColissimo == 3 ? 'current' : ''}">Votre colis est arrivé sur son site de livraison</li>
                                <li class="${JSON.parse(data).stepColissimo == 4 ? 'current' : ''}">Nous préparons votre colis pour sa livraison</li>
                                <li class="${JSON.parse(data).stepColissimo == 5 ? 'current' : ''}">Votre colis a été livré</li>
                            </ol>
                        `
                    )

                    // Step colissimo board
                    $(".details_tracking").prepend(
                        `<table class="table-scroll table_mobile_responsive table_details_tracking">
                            <thead>
                                <tr>
                                    <th>Date et heure</th>
                                    <th>Étapes de livraison</th>
                                </tr>
                            </thead>
                            <tbody>
                            `
                            +
                                Object.entries(data_tracking.parcel.event).map(([index, value]) => {
                                    return `<tr>
                                                <td data-label="Date et heure">${value.date}</td>
                                                <td data-label="Étapes de livraison">
                                                    <span class="text-bold">${value.labelLong}</span><br>
                                                    <span>${value.siteCode}</span><br>
                                                    <span>${value.siteName}</span><br>
                                                    <span>${value.siteZipCode}</span><br>
                                                </td>
                                            </tr>`;
                                }).join('') 
                            +
                        `</tbody>
                        </table>`
                    );
                } else {
                    $(".details_tracking_wizard").append('<span class="detail_status_label_tracking font-20 text-center font-bold">Votre colis va bientôt nous être confié !</span>')
                }
            } else if(origin == "chronopost"){
               
                $(".details_tracking").children('table').remove()
                $(".details_tracking_wizard").children('.step_tracking').remove()
                $(".shipping_method").addClass('shipping_chrono_logo')

                if(data_tracking.length > 0){
                    $(".details_tracking").prepend(
                        `<table class="table_details_tracking">
                            <thead>
                                <tr>
                                    <th>Date et heure</th>
                                    <th>Étapes de livraison</th>
                                    <th>Complément</th>
                                </tr>
                            </thead>
                          
                            `
                            +
                                Object.entries(data_tracking).map(([index, value]) => {
                                    var detailsHTML = ""
                                    if(value.details){
                                        detailsHTML = value.details.map(detail => {
                                            return `<span><span class="text-bold">${detail.name[0]} :</span><span> ${detail.value[0]}<span><br>`;
                                        }).join('');
                                    }
                                  
                                    return `<tr>
                                                <td>${value.date[0]}</td>
                                                <td>${value.step}</td>
                                                <td>
                                                    ${detailsHTML}
                                                </td>
                                            </tr>`;
                                }).join('') 
                            +
                        `</table>`
                    );
                }
                
                $(".details_tracking_wizard").append(
                    `
                        <ol class="step_tracking">
                            <li class="${JSON.parse(data).stepChrono == 0 ? 'current' : ''}">En préparation chez l'expéditeur</li>
                            <li class="${JSON.parse(data).stepChrono == 1 ? 'current' : ''}">Pris en charge par Chronopost</li>
                            <li class="${JSON.parse(data).stepChrono == 2 ? 'current' : ''}">En cours d'acheminement</li>
                            <li class="${JSON.parse(data).stepChrono == 3 ? 'current' : ''}">Envoi en cours de livraison</li>
                            <li class="${JSON.parse(data).stepChrono == 4 ? 'current' : ''}">Livré</li>
                        </ol>
                    `
                )
            }
            $("#trackingStatusLabel").modal('show')
          

        } else {
            alert('Erreur')
        }
    })
}