$(document).ready(function() {
    $('#example').DataTable({
        "order": [[0, 'desc']],
        "initComplete": function(settings, json) {
            $(".loading").hide()
            $("#example").removeClass('d-none')
            $("#example_length select").css('margin-right', '10px')
            $(".status_dropdown").appendTo('.dataTables_length')
            $(".dataTables_length").css('display', 'flex')
            $(".dataTables_length").addClass('select2_custom')
            $(".status_dropdown").removeClass('d-none')
            $(".status_dropdown").select2({
            	width: '150px',
            });

        }
    })
})

$('.status_dropdown').on('change', function(e){
    var status_dropdown = $(this).val();
    $('#example').DataTable()
    .column(1).search(status_dropdown, true, false)
    .draw();
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
        $.ajax({
            url: "getProductOrderLabel",
            method: 'POST',
            data : {_token: $('input[name=_token]').val(), order_id: order_id}
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
                        total_weight = parseFloat(total_weight) + (parseFloat(value.weight) * value.quantity);
                    }

                    innerHtml +=
                        `<div class="${value.quantity - value.total_quantity == 0 ? 'disabled_text' : '' } line_items_label d-flex w-100 align-items-center justify-content-between">
                            <span style="width: 50px">
                                <input name="label_product[]" ${value.quantity - value.total_quantity == 0 ? 'disabled' : 'checked' } class="checkbox_label form-check-input" type="checkbox" value="${value.product_woocommerce_id}" aria-label="Checkbox for product order">	
                            </span>
                            <span class="w-50">${value.name}</span>
                            <span class="w-25">${value.cost}</span>
                            <span class="w-25" ><input class="quantity_product_label" ${value.quantity - value.total_quantity == 0 ? 'disabled' : '' } min="1" max="${value.quantity - (value.total_quantity ?? 0) }" value="${value.quantity -  (value.total_quantity ?? 0) }" name="quantity[${value.product_woocommerce_id}]" type="number"> / ${value.quantity}</span>
                            <span class="weight w-25">${value.weight}</span>
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
                $(".generate_label_modal").modal('show')
            } else {
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