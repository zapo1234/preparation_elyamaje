$('body').on('click', '.show_order', function () {
    var id = $(this).attr('id')

    // Stock l'id de la comande en cours de prépa pour la récupérer plus tard
    $("#order_in_progress").val(id)
    $('#order_' + id).modal({
        backdrop: 'static',
        keyboard: false
    })
    $("#order_" + id).modal('show')
})

$(document).ready(function () {

    // Récupère le localstorage pour mettre les produits "pick" qui le sont déjà
    if (localStorage.getItem('barcode')) {
        var list_barcode = localStorage.getItem('barcode')
        if (list_barcode) {
            Object.keys(JSON.parse(list_barcode)).forEach(function (k, v) {
                if (JSON.parse(list_barcode)[k]) {
                    var order_id = JSON.parse(list_barcode)[k].order_id
                    JSON.parse(list_barcode)[k].products.forEach(function (item, key) {
                        if(typeof JSON.parse(list_barcode)[k].quantity != "undefined"){
                            $("#order_" + order_id + " .barcode_" + item).find('.quantity_pick_in').text(JSON.parse(list_barcode)[k].quantity[key])
                            if (parseInt($("#order_" + order_id + " .barcode_" + item).find('.quantity_pick_in').text()) ==
                                parseInt($("#order_" + order_id + " .barcode_" + item).find('.quantity_to_pick_in').text())) {
                                setTimeout(function () {
                                    $("#order_" + order_id + " .barcode_" + item).addClass('pick')
                                    if($("#"+order_id).text() != "Reprendre"){
                                        $("#"+order_id).text("Reprendre")
                                    }
                                }, 0)
                            }
                        }
                    });

                    if ($("#order_" + order_id + " .pick").length == $("#order_" + order_id + " .product_order").length) {
                        $("#order_" + order_id + " .validate_pick_in").css('background-color', '#16e15e')
                        $("#order_" + order_id + " .validate_pick_in").css('border', 'none')
                    }
                }
            })
        }
    }

    if (localStorage.getItem('product_quantity_verif')) {
        $(".barcode_" + localStorage.getItem('product_quantity_verif')).removeClass('pick')
    }

    $(".show_order").removeClass("d-none")
    progress_bar()
 
})

// Affiche la progression de l'avancée de la commande
function progress_bar(){
       // Progress bar for order
       $(".modal_order").each(function(){
        order_id = $(this).attr('data-order')
        
        var quantity_to_pick_in_order = [] 
        var quantity_pick_in_order = [] 
        var progress = []

        $("#"+$(this).attr('id')).find('.product_order').each(function(){
            typeof quantity_to_pick_in_order[order_id] == "undefined" ? quantity_to_pick_in_order[order_id]  = 0 : quantity_to_pick_in_order[order_id];
            typeof quantity_pick_in_order[order_id] == "undefined" ? quantity_pick_in_order[order_id]  = 0 : quantity_pick_in_order[order_id];
            quantity_to_pick_in_order[order_id] = quantity_to_pick_in_order[order_id] + parseInt($(this).find('.quantity_to_pick_in').text())
            quantity_pick_in_order[order_id] = quantity_pick_in_order[order_id] + parseInt($(this).find('.quantity_pick_in').text())
        })

        progress[order_id] = (quantity_pick_in_order[order_id] * 100) / quantity_to_pick_in_order[order_id]

        if(progress[order_id] < 10){
            $("#progress_"+order_id).find(".progress-bar").addClass('bg-danger')
        } else if(progress[order_id] < 50){
            $("#progress_"+order_id).find(".progress-bar").addClass('bg-warning')
        } else {
            $("#progress_"+order_id).find(".progress-bar").addClass('bg-success')
        }

        $("#progress_"+order_id).find(".progress-bar").css('width', progress[order_id]+'%')
        $("#progress_"+order_id).find(".progress-bar").attr('aria-valuenow', progress[order_id])
    })
}

document.addEventListener("keydown", function (e) {

    // Vérif si la modal d'info (produits bippé non existant ou déjà bippé) et modal de vérif (plusieurs quantité d'un même produit) non ouverte
    if ($(".modal_order").hasClass('show') && !$(".modal_verif_order").hasClass('show') && !$("#infoMessageModal").hasClass('show')) {
        var order_id = $("#order_in_progress").val()

        if (!isNaN(parseInt(e.key))) {
            $("#barcode").val($("#barcode").val() + e.key)
            if ($("#barcode").val().length == 13) {
                if ($("#order_" + order_id + " .barcode_" + $("#barcode").val()).length > 0) {
                    if ($("#order_" + order_id + " .barcode_" + $("#barcode").val()).hasClass('pick')) {
                        $(".info_message").text("Ce produit à déjà été bippé !")
                        $("#infoMessageModal").modal('show')
                    } else {
                        $("#order_" + order_id + " .barcode_" + $("#barcode").val()).addClass('pick')
                        
                        // Passe de "Préparer" à "Reprendre" si un produit ou plus est bippé
                        if($("#"+order_id).text() != "Reprendre"){
                            $("#"+order_id).text("Reprendre")
                        }
                        var quantity_pick_in = parseInt($("#order_" + order_id + " .barcode_" + $("#barcode").val()).find('.quantity_pick_in').text())
                        quantity_pick_in = quantity_pick_in + 1
                        
                        if ($("#order_" + order_id + " .barcode_" + $("#barcode").val()).find('.quantity_to_pick_in').text() > 1 &&
                            (parseInt($("#order_" + order_id + " .barcode_" + $("#barcode").val()).find('.quantity_to_pick_in').text()) - quantity_pick_in) > 0) {

                            // Update pick quantity
                            $("#order_" + order_id + " .barcode_" + $("#barcode").val()).find('.quantity_pick_in').text(quantity_pick_in)

                            $(".quantity_product").text('')
                            $(".quantity_product").text($("#order_" + order_id + " .barcode_" + $("#barcode").val()).find('.quantity_to_pick_in').text())
                            $(".name_quantity_product").text($("#order_" + order_id + " .barcode_" + $("#barcode").val()).children('.detail_product_name_order').children('span').text())
                            $("#product_to_verif").val($("#barcode").val())
                            $("#quantity_product_to_verif").text(parseInt($("#order_" + order_id + " .barcode_" + $("#barcode").val()).find('.quantity_to_pick_in').text()) - quantity_pick_in)

                            $('#modalverification').modal({
                                backdrop: 'static',
                                keyboard: false
                            })

                            $("#modalverification").attr('data-order', order_id)
                            $("#modalverification").modal('show')
                            $("#barcode_verif").val($("#barcode").val())
                            saveItem(order_id, true)
                        } else if ($("#order_" + order_id + " .barcode_" + $("#barcode").val()).find('.quantity_to_pick_in').text() > 1) {
                            $("#order_" + order_id + " .barcode_" + $("#barcode").val()).find('.quantity_pick_in').text(quantity_pick_in)
                            $("#barcode_verif").val($("#barcode").val())
                            saveItem(order_id, true)
                        } else {
                            saveItem(order_id, false)
                            $("#order_" + order_id + " .barcode_" + $("#barcode").val()).find('.quantity_pick_in').text(1)
                        }

                        if ($("#order_" + order_id + " .pick").length == $("#order_" + order_id + " .product_order").length) {
                            $("#order_" + order_id + " .validate_pick_in").css('background-color', '#16e15e')
                            $("#order_" + order_id + " validate_pick_in").css('border', 'none')
                        }
                        progress_bar()
                    }
                } else {
                    $("#barcode").val("")
                    $(".info_message").text("Aucun produit ne correspond à ce code barre !")
                    $("#infoMessageModal").modal('show')
                }

                $("#barcode").val("")
            }
        }
    } else if ($(".modal_verif_order").hasClass('show')) {
        var order_id = $(".modal_verif_order").attr('data-order')
        localStorage.setItem('product_quantity_verif', $("#product_to_verif").val());
        console.log($("#barcode_verif").val())
        if (!isNaN(parseInt(e.key))) {
            $("#barcode_verif").val($("#barcode_verif").val() + e.key)
            if ($("#barcode_verif").val().length == 13) {

                if ($("#barcode_verif").val() == localStorage.getItem('product_quantity_verif')) {
                    $("#quantity_product_to_verif").text(parseInt($("#quantity_product_to_verif").text()) - 1)

                    // Update pick quantity
                    var quantity_pick_in = parseInt($("#order_" + order_id + " .barcode_" + $("#barcode_verif").val()).find('.quantity_pick_in').text())
                    $("#order_" + order_id + " .barcode_" + $("#barcode_verif").val()).find('.quantity_pick_in').text(quantity_pick_in + 1)
                    saveItem(order_id, true)

                    if (parseInt($("#quantity_product_to_verif").text()) == 0) {
                        $("#modalverification").modal('hide')
                        localStorage.removeItem('product_quantity_verif');
                    }
                    $("#barcode_verif").val('')
                } else {
                    $("#barcode_verif").val('')
                    $(".info_message").text("Aucun produit ne correspond à ce code barre !")
                    $("#infoMessageModal").modal('show')
                }

            }
        }
    }
});

$(".validate_pick_in").on('click', function () {
    var order_id = $("#order_in_progress").val()

    if ($("#order_" + order_id + " .pick").length == $("#order_" + order_id + " .product_order").length && localStorage.getItem('barcode')) {
        // Ouvre la modal de loading
        $(".loading_prepared_command").removeClass('d-none')
        $("#modalSuccess").modal('show')
        var pick_items = JSON.parse(localStorage.getItem('barcode'))
        var order_object = false

        if (pick_items) {
            // Récupère les produits de cette commande
            order_object = pick_items.find(
                element => element.order_id == order_id
            )
        }

        if (order_object) {
            pick_items = order_object.products
            pick_items_quantity = order_object.quantity
        } else {
            pick_items = false
            pick_items_quantity = false
        }

        var customer_name = $(".customer_name_" + order_id).text()
        var user_name = $("#userinfo").val()

        $(".modal_order").modal('hide')

        $.ajax({
            url: "ordersPrepared",
            method: 'POST',
            data: { _token: $('input[name=_token]').val(), order_id: order_id, pick_items: pick_items, pick_items_quantity: pick_items_quantity, partial: 0 }
        }).done(function (data) {
            $(".loading_prepared_command").addClass('d-none')

            if (JSON.parse(data).success) {
                $(".success_prepared_command").removeClass('d-none')
                const href = order_id + "," + pick_items.length + "," + customer_name + "," + user_name;
                const size = 150;

                $(".info_order").text("#Commande " + order_id + " - " + pick_items.length + " Produit(s)" + " - " + customer_name + " - "+user_name)
                var list_products = ""
                $("#order_" + order_id + " .product_order").each(function () {
                    list_products += '<span>' + $(this).children("div").children("span").text() + ' - x' + $(this).children(".quantity ").text() + '</span>'
                });

                $(".info_order_product").children('span').remove()
                $(".info_order_product").append(list_products)

                new QRCode(document.querySelector("#qrcode"), {
                    text: href,
                    width: size,
                    height: size,

                    colorDark: "#000000",
                    colorLight: "#ffffff"
                });


                if (localStorage.getItem('barcode')) {
                    pick_items = JSON.parse(localStorage.getItem('barcode'))
                    Object.keys(pick_items).forEach(function (k, v) {
                        if (pick_items[k]) {
                            if (order_id == pick_items[k].order_id) {
                                pick_items.splice(pick_items.indexOf(pick_items[k]), pick_items.indexOf(pick_items[k]) + 1);
                            }
                        }
                    })
                }

                if (pick_items.length == 0) {
                    localStorage.removeItem('barcode');
                } else {
                    localStorage.setItem('barcode', JSON.stringify(pick_items));
                }

            } else {
                $(".info_message").text("Produits manquants !")
                $("#infoMessageModal").modal('show')
                $(".error_prepared_command").removeClass('d-none')
            }
        });
    } else {
        // Récupère les produits de cette commande
        if ($("#order_" + order_id + " .pick").length >= 1) {
            $('#modalPartial').modal({
                backdrop: 'static',
                keyboard: false
            })
            $("#modalPartial").modal('show')
        }
    }
})

$(".reset_order").on('click', function () {
    $("#modalReset").modal("show")
})

$(".confirmation_reset_order").on('click', function () {
    var order_id = $("#order_in_progress").val()

    $("#order_" + order_id + " .product_order").removeClass("pick")
    $("#barcode").val("")
    $("#barcode_verif").val("")

    var pick_items = localStorage.getItem('barcode')
    if (pick_items) {
        pick_items = JSON.parse(pick_items)
        Object.keys(pick_items).forEach(function (k, v) {
            if (pick_items[k]) {
                if (order_id == pick_items[k].order_id) {
                    pick_items.splice(pick_items.indexOf(pick_items[k]), pick_items.indexOf(pick_items[k]) + 1);
                }
            }
        })

        if (pick_items.length == 0) {
            localStorage.removeItem('barcode');
        } else {
            localStorage.setItem('barcode', JSON.stringify(pick_items));
        }
    }

    $.ajax({
        url: "ordersReset",
        method: 'POST',
        data: { _token: $('input[name=_token]').val(), order_id: order_id }
    }).done(function (data) {
        if (JSON.parse(data).success) {
            location.reload()
        } else {
            alert("Erreur !")
        }
    });
})

$('body').on('click', '.close_modal_order', function () {
    if (!$(".error_prepared_command").hasClass("d-none")) {
        $("#modalSuccess").modal('hide')
        $(".success_prepared_command").addClass("d-none")
        $(".error_prepared_command").addClass("d-none")
        $(".loading_prepared_command").addClass("d-none")
    } else {
        location.reload()
    }
})

$('body').on('click', '.valid_partial_order', function () {

    var pick_items = JSON.parse(localStorage.getItem('barcode'))
    var order_id = $("#order_in_progress").val()
    var note_partial_order = $("#note_partial_order").val()
    // Récupère les produits de cette commande

    if (pick_items) {
        const order_object = pick_items.find(
            element => element.order_id == order_id
        )

        if (order_object) {
            pick_items = order_object.products
            pick_items_quantity = order_object.quantity
        } else {
            alert('Erreur !')
            return
        }

        var customer_name = $(".customer_name").text()
        var user_name = $('#userinfo').val()

        $.ajax({
            url: "ordersPrepared",
            method: 'POST',
            data: { _token: $('input[name=_token]').val(), order_id: order_id, pick_items: pick_items, pick_items_quantity: pick_items_quantity, partial: 1, note_partial_order: note_partial_order }
        }).done(function (data) {
            if (JSON.parse(data).success) {
                location.reload()
            } else {
                alert("Erreur !")
            }
        })
    }
})


$('body').on('click', '.impression_code', function () {
    $(".impression_code span").addClass('d-none')
    $(".impression_code div").removeClass('d-none')
    $(".impression_code").attr('disabled', true)
    imprimerPages()
    $(".close_modal_validation").removeClass("d-none")
})

function saveItem(order_id, mutiple_quantity) {
    if (mutiple_quantity) {
        var quantity_pick_in = parseInt($("#order_" + order_id + " .barcode_" + $("#barcode_verif").val()).find('.quantity_pick_in').text())
    } else {
        var quantity_pick_in = parseInt($("#order_" + order_id + " .barcode_" + $("#barcode").val()).find('.quantity_pick_in').text() + 1)
    }


    if (localStorage.getItem('barcode')) {
        var list_barcode = JSON.parse(localStorage.getItem('barcode'))
        const order_object = list_barcode.find(
            element => element.order_id == order_id
        )

        // Un objet pour cette commande existe déjà, alors on rajoute dans cet objet
        if (order_object) {
            if (mutiple_quantity) {
                var index = order_object.products.indexOf($("#barcode_verif").val())
                if (index != -1) {
                    order_object.quantity[index] = quantity_pick_in
                    localStorage.setItem('barcode', JSON.stringify(list_barcode))
                } else {
                    order_object.products.push($("#barcode_verif").val())
                    order_object.quantity.push(1)
                    localStorage.setItem('barcode', JSON.stringify(list_barcode))
                }
            } else {
                order_object.products.push($("#barcode").val())
                order_object.quantity.push(1)
                localStorage.setItem('barcode', JSON.stringify(list_barcode))
            }
        } else {
            const data = {
                order_id: order_id,
                products: [
                    $("#barcode").val()
                ],
                quantity: [quantity_pick_in ?? 1]
            }

            list_barcode.push(data)
            localStorage.setItem('barcode', JSON.stringify(list_barcode))
        }
    } else {
        const data = [{
            order_id: order_id,
            products: [
                $("#barcode").val()
            ],
            quantity: [quantity_pick_in]
        }]
        localStorage.setItem('barcode', JSON.stringify(data));
    }
    $("#barcode_verif").val('')
}

var printer = null;
var ePosDev = new epson.ePOSDevice();
var reconnect = 1;

function imprimerPages() {
    // IP à mettre dynamiquement
    var printer_ip = $(".printer_ip").val() ?? false
    var printer_port = $(".printer_port").val() ?? false

    if (!printer_ip) {
        window.print()
    } else {
        ePosDev.connect(printer_ip, printer_port, cbConnect, { "eposprint": true });
    }
}

function cbConnect(data, ePos) {
    var printer_ip = $(".printer_ip").val() ?? false
    var printer_port = $(".printer_port").val() ?? false

    if (data == 'OK' || data == 'SSL_CONNECT_OK') {
        var deviceID = "local_printer";
        ePosDev.createDevice(deviceID, ePosDev.DEVICE_TYPE_PRINTER, { 'crypto': false, 'buffer': false }, cbCreateDevice_printer);
    } else if (reconnect < 3 && printer_ip != false) {
        reconnect = reconnect + 1
        ePosDev.connect(printer_ip, printer_port, cbConnect, { "eposprint": true });
    } else {
        console.log('Erreur 1:' + data)
        $(".impression_code span").removeClass('d-none')
        $(".impression_code div").addClass('d-none')
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
}


window.addEventListener("afterprint", (event) => {
    $(".impression_code span").removeClass('d-none')
    $(".impression_code div").addClass('d-none')
    $(".impression_code").attr('disabled', false)
});