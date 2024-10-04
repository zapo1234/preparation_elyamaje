$(document).ready(function() {
    $('#example').DataTable({
        "lengthMenu": [
            [10, 25, 50, 100, 250, -1],
            [10, 25, 50, 100, 250,'Tous']
        ],
        "info": false,
        "order": [[6, 'asc']],
        "columnDefs": [
            { "visible": false, "targets": 6 },
            { "orderable": false, "targets": 0 }
        ],
        "initComplete": function(settings, json) {
            $(".loading_div").addClass('d-none')
            $("#example").removeClass('d-none')
            $("#example_length select").css('margin-right', '10px')
            $(".category_dropdown").appendTo('.dataTables_length')
            $(".product_dropdown").appendTo('.dataTables_length')
            $(".dataTables_length").css('display', 'flex')
            $(".dataTables_length").addClass('select2_custom')
            $(".category_dropdown").removeClass('d-none')
            $(".product_dropdown").removeClass('d-none')
            $(".category_dropdown").select2({
                width: '250px',
            });
            $(".product_dropdown").select2({
                width: '100px',
            });

            $(".select2-container").css('margin-right', '10px')

                // Generate barcode
                $(".barcode_table_products").each(function( index ) {
                    const ean13 = $( this ).children("span").text()

                    if(ean13.length == 13){
                        const barcodeContainer = $( this ).children('.barcode_image')[0];
                        const canvas = document.createElement('canvas');
                        JsBarcode(canvas, ean13, {
                            format: "ean13",
                            displayValue: true
                        });
                        barcodeContainer.appendChild(canvas);
                        $( this ).children("span").remove()
                    }
                })
        },




        "fnRowCallback": function(nRow, aData, iDisplayIndex, iDisplayIndexFull) {
            // Generate barcode
            $(".barcode_table_products").each(function( index ) {
                const ean13 = $( this ).children("span").text()

                if(ean13.length == 13){
                    const barcodeContainer = $( this ).children('.barcode_image')[0];
                    const canvas = document.createElement('canvas');
                    JsBarcode(canvas, ean13, {
                        format: "ean13",
                        displayValue: true
                    });
                    barcodeContainer.appendChild(canvas);
                    $( this ).children("span").remove()
                }
            })
        }
    })
})

$(".barcode_image").on('click', function(){

    const product_id = $(this).attr('data-id')
    const product_name = $("#product_"+product_id).text()

    $(".product_name_modal").text(product_name)
    $(".barcode_show").children().remove()

  
    const barcodeContainer = $('.barcode_show')[0];
    const canvas = document.createElement('canvas');

    JsBarcode(canvas, $(this).attr('id'), {
        format: "ean13",
        displayValue: true
    });
    barcodeContainer.appendChild(canvas);

    $('#product_barcode').modal({
        backdrop: 'static',
        keyboard: false
    })
    $("#product_barcode").modal('show')
})

$('.category_dropdown').on('change', function(e){
    var category_dropdown = $(this).val();
    $('#example').DataTable()
    .column(4).search(category_dropdown, true, false)
    .draw();
})

$('.product_dropdown').on('change', function(e){
    var category_dropdown = $(this).val();
    $('#example').DataTable()
    .column(5).search(category_dropdown, true, false)
    .draw();
})

$('body').on('click', '.check_all', function() {
    if($(this).prop('checked')){
        $(".update_multiple_products").attr('disabled', false)
        $('.checkbox_label').each(function( index ) {
            if($(this).attr('disabled') != "disabled"){
                $(this).prop('checked', true)
            }
        });
    } else {
        $(".update_multiple_products").attr('disabled', true)
        $('.checkbox_label').prop('checked', false)
    }
})

$('body').on('click', '.checkbox_label', function() {
    var checked = 0
    
    $('.checkbox_label').each(function( index ) {
        if($(this).prop('checked')){
            checked = checked + 1
        }
    });

    checked > 0 ? $(".update_multiple_products").attr('disabled', false) : $(".update_multiple_products").attr('disabled', true)
})

$('body').on('click', '.update_multiple_products', function() {

    var array_products = [];

    $('.checkbox_label').each(function( index ) {
        if($(this).prop('checked')){
            array_products.push($(this).attr('data-id'))
        }
    })

    $("#products_id").val(array_products.join(','))
    $("#updateProductsMultiple").modal('show')
})



$(".edit_product_location").on('click', function(){
    $(this).hide()
    id = $(this).attr('data-id')
    $("#input_"+id).attr('disabled', false)
    $("#save_"+id).removeClass('d-none')
})

function save_location(id_product){

    $("#input_"+id_product).attr('disabled', true)
    $("#edit_"+id_product).show()
    $("#save_"+id_product).addClass('d-none')

    $.ajax({
        url: "products",
        method: "POST",
        async: false,
        data : {_token: $('input[name=_token]').val(), id_product: id_product, location: $("#input_"+id_product).val()}
    }).done(function(data) {
        if(JSON.parse(data).success){
           
        } else {
            $("#input_"+id_product).attr('disabled', false)
            $("#edit_"+id_product).hide()
            $("#save_"+id_product).addClass('d-none')
            alert('Erreur enregistrement, veuillez ralentir')
        }
    });
}
