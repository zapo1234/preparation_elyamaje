$('body').on('click', '.show_order_history', function () {
    var id = $(this).attr('data-order')

    $(".order_" + id).modal('show')
})


$('body').on('click', '.show_order_history_code', function () {
    var id = $(this).attr('data-order')

    var product = $(this).attr('data-product')
    var customer_name = $(this).attr('data-customer')
    var preparateur_name = $(this).attr('data-preparateur')


    const href = id + "," + product + "," + accentsTidy(customer_name) + "," + accentsTidy(preparateur_name);
    const size = 150;

    // Quantity of product
    var quantity_to_pick_in_order = 0
    $(".order_"+id).find('.product_order').each(function(){
        quantity_to_pick_in_order = quantity_to_pick_in_order + parseInt($(this).find('.quantity').text())
    })

    $(".info_order").text("#Commande "+id+" - "+quantity_to_pick_in_order+" Produit(s)"+" - "+customer_name+" - "+preparateur_name)
    var list_products = ""
    $(".order_" + id + " .product_order").each(function () {
        list_products += '<span>' + $(this).children("div").children("span").text() + ' - x' + $(this).children(".quantity ").text() + '</span>'
    });

    $(".info_order_product").children('span').remove()
    $(".info_order_product").append(list_products)



    $(".body_qr_code_" + id).children('canvas').remove()
    $(".body_qr_code_" + id).children('img').remove()
    

    new QRCode(document.querySelector(".body_qr_code_" + id), {
        text: href,
        width: size,
        height: size,

        colorDark: "#000000",
        colorLight: "#ffffff"
    });
    

    $("#code_" + id).modal('show')
})

$('body').on('click', '.close_modal', function () {
    id = $(this).attr('data-id')
    $("#code_" + id).hide()
    $(".modal-backdrop").hide()
})


function accentsTidy(r){
    var r=r;
    // r = r.replace(new RegExp(/\s/g),"");
    r = r.replace(new RegExp(/[àáâãäå]/g),"a");
    r = r.replace(new RegExp(/æ/g),"ae");
    r = r.replace(new RegExp(/ç/g),"c");
    r = r.replace(new RegExp(/[èéêë]/g),"e");
    r = r.replace(new RegExp(/[ìíîï]/g),"i");
    r = r.replace(new RegExp(/ñ/g),"n");                
    r = r.replace(new RegExp(/[òóôõö]/g),"o");
    r = r.replace(new RegExp(/œ/g),"oe");
    r = r.replace(new RegExp(/[ùúûü]/g),"u");
    r = r.replace(new RegExp(/[ýÿ]/g),"y");
    // r = r.replace(new RegExp(/\W/g),"");
    return r;
};

$('body').on('click', '.impression_code', function () {
    $(".impression_code span").addClass('d-none')
    $(".impression_code div").removeClass('d-none')
    $(".impression_code").attr('disabled', true)
    imprimerPages($(this).attr('data-id'))
    $(".close_modal_validation").removeClass("d-none")
})


var builder = null;
var reconnect = 0;

function imprimerPages() {
    
    // IP à mettre dynamiquement
    var printer_ip = $(".printer_ip").val() ?? false
    var deviceID = "local_printer";

    //Create an ePOS-Print Builder object
    var builder = new epson.ePOSBuilder();

    builder.addTextLang('fr')
    builder.addTextAlign(builder.ALIGN_CENTER);
    builder.addTextSmooth(true);
    builder.addTextFont(builder.FONT_A);
    builder.addTextSize(1, 1);
    builder.addSymbol($(".show #qrcode").attr('title'), builder.SYMBOL_QRCODE_MODEL_2, builder.LEVEL_DEFAULT, 8, 0, 0);
    builder.addText("\n"+$(".show .info_order").text()+"\n");
    builder.addText("\n");
    builder.addCut(builder.CUT_FEED);

    //Acquire the print document
    var request = builder.toString();
    var address = 'https://'+printer_ip+'/cgi-bin/epos/service.cgi?devid='+deviceID+'&timeout=6000';
    var epos = new epson.ePOSPrint(address);
    epos.onreceive = function (res) {
        if(!res.success){
            console.log(res)
        }

        $(".impression_code span").removeClass('d-none')
        $(".impression_code div").addClass('d-none')
        $(".impression_code").attr('disabled', false)
    }

    // epos.onerror = function (err) {
    //     $(".impression_code span").removeClass('d-none')
    //     $(".impression_code div").addClass('d-none')
    //     $(".impression_code").attr('disabled', false)
    //     alert('Imprimante '+printer_ip+' non trouvée !')
    // }
    //Send the print document
    epos.send(request);
}

window.addEventListener("afterprint", (event) => {
    $(".impression_code span").removeClass('d-none')
    $(".impression_code div").addClass('d-none')
    $(".impression_code").attr('disabled', false)
});