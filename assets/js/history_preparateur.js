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
    imprimerPages($(this).attr('data-id'))
    $(".close_modal_validation").removeClass("d-none")
})

const ePosDev = new epson.ePOSDevice();

function imprimerPages() {

 
    // Adresse IP de l'imprimante
    console.log(ePosDev)

    // Connexion à l'imprimante
    // epos.connect('192.168.0.252', "9001", (response) => {
    ePosDev.connect('192.168.0.252', "9100", cbConnect, { "eposprint": true });
    // window.print();
}

function cbConnect(data, ePos) {
    if (data == 'OK' || data == 'SSL_CONNECT_OK') {
      var deviceID = "local_printer";
      ePosDev.createDevice(deviceID, ePosDev.DEVICE_TYPE_PRINTER, { 'crypto': true, 'buffer': false }, cbCreateDevice_printer);
    } else {
     alert(data);
    }
  }

function cbCreateDevice_printer(devobj, retcode) {
    if( retcode == 'OK' ) {
        printer = devobj;
        printer.timeout = 60000;
        printer.onreceive = function (res) { alert(res.success); };
        printer.oncoveropen = function () { alert('coveropen'); };
        print();
    } else {
        alert(retcode);
    }
}

function print() {
    printer.addTextLang('fr');
    printer.addTextDouble(true, true);
    printer.addTextSize(1, 1);

    // var base64Image = $(".show img").attr('src').split(',')[1];
    const canvas = document.createElement('canvas');
    const ctx = canvas.getContext('2d');
   
    canvas.width = 200;
    canvas.height = 200;

    const image = new Image();
    image.src = $(".show img").attr('src')

    image.onload = function() {
        ctx.drawImage(image, 0, 0, 200, 200);

        // Utilisez le contexte canvas comme argument dans addImage
        printer.addImage(ctx, 0, 0, 200, 200);
         // On prépare le texte
         const textLines = [];
         $('.show .info_order_product').find('span').each(function(){
             textLines.push($(this).text());
         });

        printer.addText("\n"+textLines.join("\n\n")+ "\n");
        printer.addCut(printer.CUT_FEED);
        printer.send();
    };
}