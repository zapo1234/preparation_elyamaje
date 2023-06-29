$('body').on('click', '.show_order_history', function() {
    var id = $(this).attr('data-order')

    $(".order_"+id).modal('show')
})


$('body').on('click', '.show_order_history_code', function() {
    var id = $(this).attr('data-order')

    var product = $(this).attr('data-product')
    var customer_name = $(this).attr('data-customer')

    const href = id+","+product+","+customer_name;
    const size = 300;
    
    // $(".info_order").text("#Commande "+id+" - "+pick_items.length+" Produit(s)"+" - "+customer_name)
    var list_products = ""
    $(".order_"+id+" .product_order" ).each(function() {
        list_products += '<span>'+$( this ).children( "div" ).children( "span" ).text()+' - x'+$( this ).children( ".quantity " ).text()+'</span>'
    });

    $(".info_order_product").children('span').remove()
    $(".info_order_product").append(list_products)

    $(".body_qr_code_"+id).children('canvas').remove()
    $(".body_qr_code_"+id).children('img').remove()

    new QRCode(document.querySelector(".body_qr_code_"+id), {
        text: href,
        width: size,
        height: size,

        colorDark: "#000000",
        colorLight: "#ffffff"
    });

    $("#code_"+id).modal('show')
})

$('body').on('click', '.close_modal', function() {
    id = $(this).attr('data-id')
    $("#code_"+id).hide()
    $(".modal-backdrop").hide()
})

$('body').on('click', '.impression_code', function() {
    imprimerPages($(this).attr('data-id'))
    $(".close_modal_validation").removeClass("d-none")
})

function imprimerPages(id) {
    var pageHeight = window.innerHeight;
    var originalContents = document.body.innerHTML;
    var printReport= document.querySelector('.qrcode_print_'+id).innerHTML;
    document.body.innerHTML = printReport;
    window.print();
    document.body.innerHTML = originalContents;
}