$('body').on('click', '.show_order', function () {
    var id = $(this).attr('id')
    
    // Update le status pour mettre le transfer en cours de traitement
    var transfers = $(this).attr('data-transfers')
    if(typeof transfers != "undefined"){
        $.ajax({
            url: "transfersProcesssing",
            method: 'POST',
            data: { _token: $('input[name=_token]').val(), transfer_id: id, status: -1}
        }).done(function (data) {
            // if(data <= 0){
            //     console.log("Erreur !")
            // }
        })
    } 

    // Supprime la classe de rotation du bouton remove product
    $(".remove_product i").removeClass('rotate')

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

    // $(".show_order").removeClass("d-none")
    progress_bar()
 
})

// Affiche la progression de l'avancée de la commande
function progress_bar(){
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
            $("#progress_"+order_id).find(".progress-bar").css('background-color', '#e62e2e')
        } else if(progress[order_id] < 50){
            $("#progress_"+order_id).find(".progress-bar").css('background-color', '#ffc107')
        } else {
            $("#progress_"+order_id).find(".progress-bar").css('background-color', '#29cc39')
        }

        $("#progress_"+order_id).find(".progress-bar").css('width', progress[order_id]+'%')
        $("#progress_"+order_id).find(".progress-bar").attr('aria-valuenow', progress[order_id])
        // $(".validate_pick_in").css('background', 'linear-gradient(to right, #29cc39 '+progress[order_id]+'%, #212529 '+progress[order_id]+'% 100%)')
        // $(".validate_pick_in").css('border', 'none')
    })
}

document.addEventListener("keydown", function (e) {

    // Vérif si la modal d'info (produits bippé non existant ou déjà bippé) et modal de vérif (plusieurs quantité d'un même produit) non ouverte
    if ($(".modal_order").hasClass('show') && !$(".modal_verif_order").hasClass('show') 
    && !$("#infoMessageModal").hasClass('show') && !$("#modalManuallyBarcode").hasClass('show')
    && !$(".info_message").hasClass('show')) {
        var order_id = $("#order_in_progress").val()
       
        if (!isNaN(parseInt(e.key))) {
            $("#barcode").val($("#barcode").val() + e.key)
            if ($("#barcode").val().length == 13) {

                var quantity_to_pick_in = parseInt($("#order_" + order_id + " .barcode_" + $("#barcode").val()).find('.quantity_to_pick_in').text())

                if ($("#order_" + order_id + " .barcode_" + $("#barcode").val()).length > 0) {
                    if ($("#order_" + order_id + " .barcode_" + $("#barcode").val()).hasClass('pick')) {
                        $(".info_message").text("Ce produit à déjà été bippé !")
                        $("#infoMessageModal").modal('show')
                    } else {
                        $("#order_" + order_id + " .barcode_" + $("#barcode").val()).addClass('pick')
                        
                        // Ajout d'un visuel produit bippé
                        // $(".product_order").removeClass('product_pick')
                        // $("#order_" + order_id + " .barcode_" + $("#barcode").val()).addClass('product_pick')

                        // Passe de "Préparer" à "Reprendre" si un produit ou plus est bippé
                        if($("#"+order_id).text() != "Reprendre"){
                            $("#"+order_id).text("Reprendre")
                        }

                        var quantity_pick_in = parseInt($("#order_" + order_id + " .barcode_" + $("#barcode").val()).find('.quantity_pick_in').text())
                        quantity_pick_in = quantity_pick_in + 1
                        quantity_pick_in = quantity_pick_in > quantity_to_pick_in ? quantity_to_pick_in : quantity_pick_in

                        if ((quantity_to_pick_in > 1 && quantity_to_pick_in <= 10) &&
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
                        } else if(quantity_to_pick_in > 10){

                            $(".quantity_product").text('')
                            $(".quantity_product").text($("#order_" + order_id + " .barcode_" + $("#barcode").val()).find('.quantity_to_pick_in').text())
                            $(".name_quantity_product").text($("#order_" + order_id + " .barcode_" + $("#barcode").val()).children('.detail_product_name_order').children('span').text())
                            $("#barcode_verif").val($("#barcode").val())
                            $('#modalverification2').modal({
                                backdrop: 'static',
                                keyboard: false
                            })

                            $("#modalverification2").attr('data-order', order_id)
                            $("#modalverification2").modal('show')

                            $("#order_" + order_id + " .barcode_" + $("#barcode").val()).find('.quantity_pick_in').text($("#order_" + order_id + " .barcode_" + $("#barcode").val()).find('.quantity_to_pick_in').text())
                            saveItem(order_id, false, true, true)
                        } else if ($("#order_" + order_id + " .barcode_" + $("#barcode").val()).find('.quantity_to_pick_in').text() > 1) {
                            $("#order_" + order_id + " .barcode_" + $("#barcode").val()).find('.quantity_pick_in').text(quantity_pick_in)
                            $("#barcode_verif").val($("#barcode").val())
                            saveItem(order_id, true)
                        } else {
                            $("#order_" + order_id + " .barcode_" + $("#barcode").val()).find('.quantity_pick_in').text(1)
                            $(".show .barcode_"+$("#barcode").val())[0].scrollIntoView({behavior: 'smooth'}, true)
                            saveItem(order_id, false)
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
    } else if ($(".modal_verif_order").hasClass('show') && !$("#modalManuallyBarcode").hasClass('show') 
    && !$(".info_message").hasClass('show') && !$("#infoMessageModal").hasClass('show')) {
        var order_id = $(".modal_verif_order").attr('data-order')

        if($("#product_to_verif").val().length == 13){
            localStorage.setItem('product_quantity_verif', $("#product_to_verif").val());
        }
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
                        $(".show .barcode_"+localStorage.getItem('product_quantity_verif'))[0].scrollIntoView({behavior: 'smooth'}, true)
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

    var from_transfers = $(this).attr('from_transfers') == "1" || $(this).attr('from_transfers') == "true" ? true : false
    var order_id = $("#order_in_progress").val()
    var from_dolibarr = $(this).attr('from_dolibarr') == "1" || $(this).attr('from_dolibarr') == "true" ? true : false

    if ($("#order_" + order_id + " .pick").length == $("#order_" + order_id + " .product_order").length) {
        // Ouvre la modal de loading
        $(".loading_prepared_command").removeClass('d-none')
        $("#modalSuccess").modal('show')

        if(localStorage.getItem('barcode')){
            var order_object = false
            var pick_items = JSON.parse(localStorage.getItem('barcode'))
            if (pick_items) {
                // Récupère les produits de cette commande
                order_object = pick_items.find(
                    element => element.order_id == order_id
                )
            }
        } else {
            var pick_items = false
            var order_object = false
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
            data: { _token: $('input[name=_token]').val(), order_id: order_id, pick_items: pick_items, pick_items_quantity: pick_items_quantity, partial: 0, 
            from_dolibarr: from_dolibarr, from_transfers: from_transfers }
        }).done(function (data) {
            
            $(".loading_prepared_command").addClass('d-none')

            if (JSON.parse(data).success) {
                $(".success_prepared_command").removeClass('d-none')

                if(pick_items){
                    number_products = pick_items.length
                } else {
                    number_products = $("#order_"+order_id).find('.product_order').length
                }

                const href = order_id + "," + number_products + "," + accentsTidy(customer_name) + "," + accentsTidy(user_name);
                const size = 150;

                $(".info_order").text("#Commande " + order_id + " - " + number_products + " Produit(s)" + " - " + customer_name + " - "+user_name)
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

                if(pick_items){
                    if (pick_items.length == 0) {
                        localStorage.removeItem('barcode');
                    } else {
                        localStorage.setItem('barcode', JSON.stringify(pick_items));
                    }
                }
              
            } else {
                $(".info_message").text("Produits manquants ou incorrects !")
                $("#infoMessageModal").modal('show')
                $(".error_prepared_command").removeClass('d-none')
            }
        });
    } else {
        // Récupère les produits de cette commande
        $(".valid_partial_order").attr('from_dolibarr', from_dolibarr)
        $(".valid_partial_order").attr('from_transfers', from_transfers)
        if ($("#order_" + order_id + " .pick").length >= 0) {
            $('#modalPartial').modal({
                backdrop: 'static',
                keyboard: false
            })
            $("#modalPartial").modal('show')
        }
    }
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

$(".reset_order").on('click', function () {
    $("#modalReset").modal("show")
})

$(".confirmation_reset_order").on('click', function () {
    var order_id = $("#order_in_progress").val()
    var from_dolibarr = $(".validate_pick_in").attr('from_dolibarr') == "1" || $(".validate_pick_in").attr('from_dolibarr') == "true" ? true : false
    var from_transfers = $(".validate_pick_in").attr('from_transfers') == "1" || $(".validate_pick_in").attr('from_transfers') == "true" ? true : false

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

        if(pick_items){
            if (pick_items.length == 0) {
                localStorage.removeItem('barcode');
            } else {
                localStorage.setItem('barcode', JSON.stringify(pick_items));
            }
        }
    }

    $.ajax({
        url: "ordersReset",
        method: 'POST',
        data: { _token: $('input[name=_token]').val(), order_id: order_id, from_dolibarr: from_dolibarr, from_transfers: from_transfers}
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

    var pick_items = JSON.parse(localStorage.getItem('barcode')) ?? [];
    var order_id = $("#order_in_progress").val()
    var note_partial_order = $("#note_partial_order").val()
    var from_dolibarr = $(this).attr('from_dolibarr') == "1" || $(this).attr('from_dolibarr') == "true" ? true : false
    var from_transfers = $(this).attr('from_transfers') == "1" || $(this).attr('from_transfers') == "true" ? true : false
    // Récupère les produits de cette commande

    if (pick_items.length > 0) {
        const order_object = pick_items.find(
            element => element.order_id == order_id
        )

        if (order_object) {
            pick_items = order_object.products
            pick_items_quantity = order_object.quantity
        } else {
            pick_items = [];
            pick_items_quantity = [];
        }
    } else {
        pick_items = [];
        pick_items_quantity = [];
    }

        var customer_name = $(".customer_name").text()
        var user_name = $('#userinfo').val()
    
        $.ajax({
            url: "ordersPrepared",
            method: 'POST',
            data: { _token: $('input[name=_token]').val(), order_id: order_id, pick_items: pick_items, pick_items_quantity: pick_items_quantity, partial: 1, note_partial_order: note_partial_order, 
            from_dolibarr: from_dolibarr, from_transfers: from_transfers }
        }).done(function (data) {
            if (JSON.parse(data).success) {
                location.reload()
            } else {
                alert("Erreur !")
            }
        })
    // }
})


$('body').on('click', '.impression_code', function () {
    $(".impression_code span").addClass('d-none')
    $(".impression_code div").removeClass('d-none')
    $(".impression_code").attr('disabled', true)
    imprimerPages()
    $(".close_modal_validation").removeClass("d-none")
})

function saveItem(order_id, mutiple_quantity, barcode, manually = false) {

    var quantity_to_pick_in = parseInt($("#order_" + order_id + " .barcode_" + $("#barcode").val()).find('.quantity_to_pick_in').text())
    if(manually){
        var quantity_pick_in = parseInt($("#order_" + order_id + " .barcode_" + $("#barcode").val()).find('.quantity_to_pick_in').text())
    } else {
        if (mutiple_quantity) {
            var quantity_pick_in = parseInt($("#order_" + order_id + " .barcode_" + $("#barcode_verif").val()).find('.quantity_pick_in').text())
        } else {
            var quantity_pick_in = parseInt($("#order_" + order_id + " .barcode_" + $("#barcode").val()).find('.quantity_pick_in').text() + 1)
        }
    }

    // Sécurité quantité pickée ne peut pas dépasser quantité à picker
    quantity_pick_in = quantity_pick_in > quantity_to_pick_in ? quantity_to_pick_in : quantity_pick_in

    if (localStorage.getItem('barcode')) {
        var list_barcode = JSON.parse(localStorage.getItem('barcode'))
        const order_object = list_barcode.find(
            element => element.order_id == order_id
        )

        // Un objet pour cette commande existe déjà, alors on rajoute dans cet objet
        if (order_object) {
            if(manually){
                var index = order_object.products.indexOf($("#barcode").val())
                if (index != -1) {
                    order_object.quantity[index] = quantity_pick_in
                    if(JSON.stringify(list_barcode)){
                        localStorage.setItem('barcode', JSON.stringify(list_barcode))
                    }
                } else {
                    order_object.products.push($("#barcode").val())
                    order_object.quantity.push(quantity_pick_in)
                    if(JSON.stringify(list_barcode)){
                        localStorage.setItem('barcode', JSON.stringify(list_barcode))
                    }
                }
            } else {
                if (mutiple_quantity) {
                    var index = order_object.products.indexOf($("#barcode_verif").val())
                    if (index != -1) {
                        order_object.quantity[index] = quantity_pick_in
                        localStorage.setItem('barcode', JSON.stringify(list_barcode))
                    } else {
                        order_object.products.push($("#barcode_verif").val())
                        order_object.quantity.push(1)
                        if(JSON.stringify(list_barcode)){
                            localStorage.setItem('barcode', JSON.stringify(list_barcode))
                        }
                    }
                } else {
                    order_object.products.push($("#barcode").val())
                    order_object.quantity.push(1)
                    if(JSON.stringify(list_barcode)){
                        localStorage.setItem('barcode', JSON.stringify(list_barcode))
                    }
                }
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

            if(JSON.stringify(list_barcode)){
                localStorage.setItem('barcode', JSON.stringify(list_barcode))
            }
        }
    } else {
        if($("#order_" + order_id + " .barcode_" + $("#barcode").val()).length > 0 ){
            const data = [{
                order_id: order_id,
                products: [
                    $("#barcode").val()
                ],
                quantity: [quantity_pick_in]
            }]

            if(JSON.stringify(data)){
                localStorage.setItem('barcode', JSON.stringify(data));
            }
        }
        
    }

    $("#barcode").val('')
    $("#barcode_verif").val('')
}

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

    epos.onerror = function (err) {
        $(".impression_code span").removeClass('d-none')
        $(".impression_code div").addClass('d-none')
        $(".impression_code").attr('disabled', false)
        alert('Imprimante '+printer_ip+' non trouvée !')
    }

    //Send the print document
    epos.send(request);
}


window.addEventListener("afterprint", (event) => {
    $(".impression_code span").removeClass('d-none')
    $(".impression_code div").addClass('d-none')
    $(".impression_code").attr('disabled', false)
});


function enter_manually_barcode(product_id, order_id){
    $("#barcode_manually").css('border', '1px solid #ced4da')
    $('#barcode_manually').attr('placeholder','');
    $("#product_id_barcode").val(product_id)
    $("#product_id_barcode_order_id").val(order_id)
    $('#modalManuallyBarcode').modal({
        backdrop: 'static',
        keyboard: false
    })
    $("#modalManuallyBarcode").modal('show')
}

function remove_product(barcode, order_id){

    if($(".barcode_"+barcode).hasClass('pick')){
        $(".remove_"+barcode+"_"+order_id+" i").addClass('rotate')
    }
    $(".barcode_"+barcode).removeClass('pick')
    $(".barcode_"+barcode).find('.quantity_pick_in').text("0")

    if (localStorage.getItem('barcode')) {
        pick_items = JSON.parse(localStorage.getItem('barcode'))

        if (pick_items.length > 0) {
            Object.keys(pick_items).forEach(function (k, v) {
                if (pick_items[k]) {
                    if (order_id == pick_items[k].order_id) {
                        Object.keys(pick_items[k].products).forEach(function (l, w) {
                            if(pick_items[k].products[l] == barcode){
                                pick_items[k].products.splice(l, 1);
                                pick_items[k].quantity.splice(l, 1);

                                if(JSON.stringify(pick_items)){
                                    localStorage.setItem('barcode', JSON.stringify(pick_items));
                                    progress_bar()
                                    return;
                                }
                            }
                        }) 
                    }
                }
            })
        }  
    }  
}

// Commandes classiques
$(".valid_manually_barcode").on('click', function(){

    var product_id = $("#product_id_barcode").val()
    var barcode = $("#barcode_manually").val()
    var order_id = $("#product_id_barcode_order_id").val()

    // check en base de données
    $.ajax({
        url: "checkProductBarcode",
        method: 'POST',
        data: { _token: $('input[name=_token]').val(), product_id: product_id, barcode: barcode}
    }).done(function (data) {
        if (JSON.parse(data).success) {
            if(!$("#order_"+order_id+" .barcode_"+barcode).hasClass('pick')){
                $("#order_"+order_id+" .barcode_"+barcode).addClass('pick')
                $("#order_"+order_id+" .barcode_"+barcode).find('.quantity_pick_in').text($("#order_"+order_id+" .barcode_"+barcode).find('.quantity_to_pick_in').text())
                $("#modalManuallyBarcode").modal('hide')
                $("#barcode").val(barcode)
                progress_bar()
                saveItem(order_id, false, true, true)
            } else {
                $("#modalManuallyBarcode").modal('hide')
            }
        } else {
           $("#barcode_manually").css('border', '1px solid red')
           $('#barcode_manually').attr('placeholder','Code barre invalide !');
        }
        $("#barcode_manually").val("")
        $("#barcode").val("")
    })
})

/* ----------------- TRANSFER ----------------- */

$(".valid_manually_barcode_transfert").on('click', function(){

    var product_id = $("#product_id_barcode").val()
    var barcode = $("#barcode_manually").val()
    var order_id = $("#product_id_barcode_order_id").val()

    // check en base de données
    $.ajax({
        url: "checkProductBarcodeForTransfers",
        method: 'POST',
        data: { _token: $('input[name=_token]').val(), product_id: product_id, barcode: barcode}
    }).done(function (data) {
        if (JSON.parse(data).success) {
            if(!$(".barcode_"+barcode).hasClass('pick')){
                $(".barcode_"+barcode).addClass('pick')
                $(".barcode_"+barcode).find('.quantity_pick_in').text($(".barcode_"+barcode).find('.quantity_to_pick_in').text())
                $("#modalManuallyBarcode").modal('hide')
                $("#barcode").val(barcode)
                saveItem(order_id, false, true, true)
            } else {
                $("#modalManuallyBarcode").modal('hide')
            }
        } else {
           $("#barcode_manually").css('border', '1px solid red')
           $('#barcode_manually').attr('placeholder','Code barre invalide !');
        }
        $("#barcode_manually").val("")
        $("#barcode").val("")
    })
})

/* ----------------- TRANSFER ----------------- */