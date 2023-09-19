$('body').on('click', '.show_order_history', function () {
    var id = $(this).attr('data-order')

    $(".order_" + id).modal('show')
})


$('body').on('click', '.show_order_history_code', function () {
    var id = $(this).attr('data-order')

    var product = $(this).attr('data-product')
    var customer_name = $(this).attr('data-customer')
    var preparateur_name = $(this).attr('data-preparateur')


    const href = id + "," + product + "," + accentsTidy(customer_name) + "," + preparateur_name;
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


var printer = null;
var ePosDev = new epson.ePOSDevice();
var reconnect = 0;

function imprimerPages() {
    
    // IP à mettre dynamiquement
    var printer_ip = "192.168.0.159"; // $(".printer_ip").val() ?? false
    var printer_port = 9100; //$(".printer_port").val() ?? false
    
    
    if (!printer_ip) {
        window.print()
    } else {
        ePosDev.connect(printer_ip, printer_port, cbConnect, { "eposprint": true, "timeout": 30000 });
    }
}

function cbConnect(data, ePos) {
    var printer_ip = "192.168.0.159"; // $(".printer_ip").val() ?? false
    var printer_port = 9100; //$(".printer_port").val() ?? false
    
    console.log(data)
    if (data == 'OK' || data == 'SSL_CONNECT_OK') {
        var deviceID = "local_printer";
        ePosDev.createDevice(deviceID, ePosDev.DEVICE_TYPE_PRINTER, { 'crypto': false, 'buffer': false }, cbCreateDevice_printer);
    } else if (reconnect < 3 && printer_ip != false){
        reconnect = reconnect + 1
        ePosDev.connect(printer_ip, printer_port, cbConnect, { "eposprint": true, "timeout": 30000 });
    } else {
        console.log('Erreur 1:' + data)
        $(".impression_code span").removeClass('d-none')
        $(".impression_code div").addClass('d-none')
        $(".impression_code").attr('disabled', false)
        window.print()
    }
}

function cbCreateDevice_printer(devobj, retcode) {
    if (retcode == 'OK') {
        printer = devobj;
        printer.timeout = 60000;
        printer.onreceive = function (res) {
            if (!res.success) {
                window.print()
            }
        };
        printer.oncoveropen = function () { alert('coveropen'); };
        printOrder()
    } else {
        console.log('Erreur 2:' + retcode)
        $(".impression_code span").removeClass('d-none')
        $(".impression_code div").addClass('d-none')
        $(".impression_code").attr('disabled', false)
        window.print()
    }
}

function printOrder() {
    printer.addTextLang('fr');
    printer.addTextAlign(printer.ALIGN_CENTER);
    printer.addTextDouble(true, true);
    printer.addTextSize(1, 1);
    printer.addSymbol($(".show #qrcode").attr('title'), printer.SYMBOL_QRCODE_MODEL_2, printer.LEVEL_DEFAULT, 8, 0, 0);
    printer.addText("\n"+$(".show .info_order").text());

    // $('.show .info_order_product').find('span').each(function () {
    //     printer.addText("\n\n" + $(this).text());
    // });

    printer.addText("\n\n\n");
    printer.addCut(printer.CUT_FEED);
    printer.send();
    $(".impression_code span").removeClass('d-none')
    $(".impression_code div").addClass('d-none')
    $(".impression_code").attr('disabled', false)
}


window.addEventListener("afterprint", (event) => {
    $(".impression_code span").removeClass('d-none')
    $(".impression_code div").addClass('d-none')
    $(".impression_code").attr('disabled', false)
});