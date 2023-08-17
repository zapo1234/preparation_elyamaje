$('body').on('click', '.show_order_history', function() {
    var id = $(this).attr('data-order')

    $(".order_"+id).modal('show')
})


$('body').on('click', '.show_order_history_code', function() {
    var id = $(this).attr('data-order')

    var product = $(this).attr('data-product')
    var customer_name = $(this).attr('data-customer')

    const href = id+","+product+","+customer_name;
    const size = 150;
    
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
    $(".impression_code span").addClass('d-none')
    $(".impression_code div").removeClass('d-none')

    imprimerPages($(this).attr('data-id'))
    $(".close_modal_validation").removeClass("d-none")
})


var printer = null;
var ePosDev = new epson.ePOSDevice();

function imprimerPages() {
    ePosDev.connect('192.168.0.252', "9100", cbConnect, { "eposprint": true });
}

function cbConnect(data, ePos) {
    if (data == 'OK' || data == 'SSL_CONNECT_OK') {
      var deviceID = "local_printer";
      ePosDev.createDevice(deviceID, ePosDev.DEVICE_TYPE_PRINTER, { 'crypto': false, 'buffer': false }, cbCreateDevice_printer);
    } else {
        console.log('Erreur 1:'+data)
        $(".impression_code span").removeClass('d-none')
        $(".impression_code div").addClass('d-none')
        window.print()
    } 
  }

function cbCreateDevice_printer(devobj, retcode) {
    if( retcode == 'OK' ) {
        printer = devobj;
        printer.timeout = 60000;
        printer.onreceive = function (res) { 
            if(!res.success){
                window.print()
            }
        };
        printer.oncoveropen = function () { alert('coveropen'); };
        printOrder()
    } else {
        console.log('Erreur 2:'+retcode)
        $(".impression_code span").removeClass('d-none')
        $(".impression_code div").addClass('d-none')
        window.print()
    }
}

function printOrder() {
    printer.addTextLang('fr');
    printer.addTextAlign(printer.ALIGN_CENTER);
    printer.addTextDouble(true, true);
    printer.addTextSize(1, 1);
    printer.addSymbol($(".show #qrcode").attr('title'), printer.SYMBOL_QRCODE_MODEL_2, printer.LEVEL_DEFAULT, 8, 0, 0);
    $('.show .info_order_product').find('span').each(function(){
        printer.addText("\n\n"+$(this).text());
    });

    printer.addText("\n\n\n");
    printer.addCut(printer.CUT_FEED);
    printer.send();
    $(".impression_code span").removeClass('d-none')
    $(".impression_code div").addClass('d-none')
}


