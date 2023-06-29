$('body').on('click', '.show_order', function() {
    var id = $(this).attr('id')
    
    // Stock l'id de la comande en cours de prépa pour la récupérer plus tard
    $("#order_in_progress").val(id)
    $('#order_'+id).modal({
        backdrop: 'static',
        keyboard: false
    })
    $("#order_"+id).modal('show')
})

$(document).ready(function() {

    // Récupère le localstorage pour mettre les produits "pick" qui le sont déjà
    if(localStorage.getItem('barcode')){
        var list_barcode = localStorage.getItem('barcode')
        if(list_barcode){
            Object.keys(JSON.parse(list_barcode)).forEach(function(k, v){
                if(JSON.parse(list_barcode)[k]){
                        var order_id = JSON.parse(list_barcode)[k].order_id
                        JSON.parse(list_barcode)[k].products.forEach(function(item, key){
                        
                        $("#order_"+order_id+" .barcode_"+item).find('.quantity_pick_in').text(JSON.parse(list_barcode)[k].quantity[key])
                        if(parseInt($("#order_"+order_id+" .barcode_"+item).find('.quantity_pick_in').text()) == 
                        parseInt($("#order_"+order_id+" .barcode_"+item).find('.quantity_to_pick_in').text())){
                            setTimeout(function(){
                                $("#order_"+order_id+" .barcode_"+item).addClass('pick')
                            },0)
                        }

                    });

                    if($("#order_"+order_id+" .pick").length == $("#order_"+order_id+" .product_order").length){
                        $("#order_"+order_id+" .validate_pick_in").css('background-color', '#16e15e')
                        $("#order_"+order_id+" .validate_pick_in").css('border', 'none')
                    }
                }
            })
        }
    } 

    if(localStorage.getItem('product_quantity_verif')){
        $(".barcode_"+localStorage.getItem('product_quantity_verif')).removeClass('pick')
    }
})

document.addEventListener("keydown", function(e) {
    if($(".modal_order").hasClass('show') && !$(".modal_verif_order").hasClass('show')){
        var order_id = $("#order_in_progress").val()

        if (!isNaN(parseInt(e.key))) {
            $("#barcode").val($("#barcode").val()+e.key)
            if($("#barcode").val().length == 13){
                if($("#order_"+order_id+" .barcode_"+$("#barcode").val()).length > 0){
                    if($("#order_"+order_id+" .barcode_"+$("#barcode").val()).hasClass('pick')){
                        alert('Ce produit à déjà été bippé')
                    } else {
                        $("#order_"+order_id+" .barcode_"+$("#barcode").val()).addClass('pick')
                        var quantity_pick_in = parseInt($("#order_"+order_id+" .barcode_"+$("#barcode").val()).find('.quantity_pick_in').text())
                        quantity_pick_in = quantity_pick_in + 1

                        if($("#order_"+order_id+" .barcode_"+$("#barcode").val()).find('.quantity_to_pick_in').text() > 1 && 
                        (parseInt($("#order_"+order_id+" .barcode_"+$("#barcode").val()).find('.quantity_to_pick_in').text()) - quantity_pick_in) > 0 ){

                            // Update pick quantity
                            $("#order_"+order_id+" .barcode_"+$("#barcode").val()).find('.quantity_pick_in').text(quantity_pick_in)
                            
                            $(".quantity_product").text('')
                            $(".quantity_product").text($("#order_"+order_id+" .barcode_"+$("#barcode").val()).find('.quantity_to_pick_in').text())
                            $(".name_quantity_product").text($("#order_"+order_id+" .barcode_"+$("#barcode").val()).children('.detail_product_name_order').children('span').text())
                            $("#product_to_verif").val($("#barcode").val())
                            $("#quantity_product_to_verif").text(parseInt($("#order_"+order_id+" .barcode_"+$("#barcode").val()).find('.quantity_to_pick_in').text()) - quantity_pick_in)

                            $('#modalverification').modal({
                                backdrop: 'static',
                                keyboard: false
                            })

                            $("#modalverification").attr('data-order', order_id)
                            $("#modalverification").modal('show')
                            $("#barcode_verif").val($("#barcode").val())
                            saveItem(order_id, true)
                        } else if($("#order_"+order_id+" .barcode_"+$("#barcode").val()).find('.quantity_to_pick_in').text() > 1){
                            $("#order_"+order_id+" .barcode_"+$("#barcode").val()).find('.quantity_pick_in').text(quantity_pick_in)
                            $("#barcode_verif").val($("#barcode").val())
                            saveItem(order_id, true)
                        } else {
                            saveItem(order_id, false)
                            $("#order_"+order_id+" .barcode_"+$("#barcode").val()).find('.quantity_pick_in').text(1)
                        }

                        if($("#order_"+order_id+" .pick").length == $("#order_"+order_id+" .product_order").length){
                            $("#order_"+order_id+" .validate_pick_in").css('background-color', '#16e15e')
                            $("#order_"+order_id+" validate_pick_in").css('border', 'none')
                        }
                    }
                } else {
                    $("#barcode").val("")
                    alert("Aucun produit ne correspond à ce code barre !")
                }

                $("#barcode").val("")
            }
        }
    } else if($(".modal_verif_order").hasClass('show')){
        var order_id = $(".modal_verif_order").attr('data-order')
        localStorage.setItem('product_quantity_verif', $("#product_to_verif").val());
        console.log($("#barcode_verif").val())
        if (!isNaN(parseInt(e.key))) {
            $("#barcode_verif").val($("#barcode_verif").val()+e.key)
            if($("#barcode_verif").val().length == 13){
                
                if($("#barcode_verif").val() == localStorage.getItem('product_quantity_verif')){
                    $("#quantity_product_to_verif").text(parseInt($("#quantity_product_to_verif").text()) - 1)
                
                    // Update pick quantity
                    var quantity_pick_in = parseInt($("#order_"+order_id+" .barcode_"+$("#barcode_verif").val()).find('.quantity_pick_in').text())
                    $("#order_"+order_id+" .barcode_"+$("#barcode_verif").val()).find('.quantity_pick_in').text(quantity_pick_in + 1)
                    saveItem(order_id, true)

                    if(parseInt($("#quantity_product_to_verif").text()) == 0){
                        $("#modalverification").modal('hide')
                        localStorage.removeItem('product_quantity_verif');
                    }
                    $("#barcode_verif").val('')
                } else {
                    $("#barcode_verif").val('')
                    alert("Aucun produit ne correspond à ce code barre !")
                }
                
            }
        }
    }
});

$(".validate_pick_in").on('click', function(){
    var order_id = $("#order_in_progress").val()
    if($("#order_"+order_id+" .pick").length == $("#order_"+order_id+" .product_order").length && localStorage.getItem('barcode')
    && !localStorage.getItem('product_quantity_verif')){

        // Ouvre la modal de loading
        $(".loading_prepared_command").removeClass('d-none')
        $("#modalSuccess").modal('show')
        var pick_items = JSON.parse(localStorage.getItem('barcode'))

        // Récupère les produits de cette commande
        const order_object = pick_items.find(
            element => element.order_id == order_id
        )

        if(order_object){
            pick_items = order_object.products
            pick_items_quantity = order_object.quantity
        } else {
            alert("Erreur !")
            return;
        }

        var customer_name = $(".customer_name_"+order_id).text()
        var user_name = $("#userinfo").val()
        $.ajax({
            url: "ordersPrepared",
            method: 'POST',
            data: {_token: $('input[name=_token]').val(), order_id: order_id, pick_items: pick_items, pick_items_quantity: pick_items_quantity, partial: 0}
        }).done(function(data) {
            
            $(".loading_prepared_command").addClass('d-none')

            if(JSON.parse(data).success){
                $(".success_prepared_command").removeClass('d-none')
                const href =order_id+","+pick_items.length+","+customer_name+","+user_name;
                const size = 300;
                $(".info_order").text("#Commande "+order_id+" - "+pick_items.length+" Produit(s)"+" - "+customer_name)

                var list_products = ""
                $(".product_order" ).each(function() {
                    list_products += '<span>'+$( this ).children( "div" ).children( "span" ).text()+' - x'+$( this ).children( ".quantity " ).text()+'</span>'
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


                if(localStorage.getItem('barcode')){
                    pick_items = JSON.parse(localStorage.getItem('barcode'))
                    Object.keys(pick_items).forEach(function(k, v){
                        if(pick_items[k]){
                            if(order_id == pick_items[k].order_id){
                                pick_items.splice(pick_items.indexOf(pick_items[k]), pick_items.indexOf(pick_items[k]) + 1);
                            }
                        }
                    })
                }

                if(pick_items.length == 0){
                    localStorage.removeItem('barcode');
                } else {
                    localStorage.setItem('barcode', JSON.stringify(pick_items));
                }

            } else {
                alert('Produits manquants !')
                $(".error_prepared_command").removeClass('d-none')
            }
        });
    } else {
        // Récupère les produits de cette commande
        if($("#order_"+order_id+" .pick").length >= 1){
            $('#modalPartial').modal({
                backdrop: 'static',
                keyboard: false
            })
            $("#modalPartial").modal('show')
        }
    }
}) 

$(".reset_order").on('click', function(){
    $("#modalReset").modal("show")
})

$(".confirmation_reset_order").on('click', function(){
    var order_id = $("#order_in_progress").val()

    $("#order_"+order_id+" .product_order").removeClass("pick")
    $("#barcode").val("")
    $("#barcode_verif").val("")

    var pick_items = localStorage.getItem('barcode')
    if(pick_items){
        pick_items = JSON.parse(pick_items)
        Object.keys(pick_items).forEach(function(k, v){
            if(pick_items[k]){
                if(order_id == pick_items[k].order_id){
                    pick_items.splice(pick_items.indexOf(pick_items[k]), pick_items.indexOf(pick_items[k]) + 1);
                }
            }
        })
    }


    if(pick_items.length == 0){
        localStorage.removeItem('barcode');
    } else {
        localStorage.setItem('barcode', JSON.stringify(pick_items));
    }

    $.ajax({
            url: "ordersReset",
            method: 'POST',
            data: {_token: $('input[name=_token]').val(), order_id: order_id}
    }).done(function(data) {
        if(JSON.parse(data).success){
            location.reload()
        } else {
            alert("Erreur !")
        }
    });
})

$('body').on('click', '.close_modal_order', function() {
    if(!$(".error_prepared_command").hasClass("d-none")){
        $("#modalSuccess").modal('hide')
        $(".success_prepared_command").addClass("d-none")
        $(".error_prepared_command").addClass("d-none")
        $(".loading_prepared_command").addClass("d-none")
    } else {
        location.reload()
    }
})

$('body').on('click', '.valid_partial_order', function() {

    var pick_items = JSON.parse(localStorage.getItem('barcode'))
    var order_id = $("#order_in_progress").val()
    var note_partial_order = $("#note_partial_order").val()
    // Récupère les produits de cette commande

    if(pick_items){
        const order_object = pick_items.find(
        element => element.order_id == order_id
    )

        if(order_object){
            pick_items = order_object.products
            pick_items_quantity = order_object.quantity
        } else {
            alert("Erreur !")
            return;
        }

        var customer_name = $(".customer_name").text()
        var user_name = $('#userinfo').val()

        $.ajax({
                url: "ordersPrepared",
                method: 'POST',
                data: {_token: $('input[name=_token]').val(), order_id: order_id, pick_items: pick_items, pick_items_quantity: pick_items_quantity, partial: 1, note_partial_order: note_partial_order}
        }).done(function(data) {
            if(JSON.parse(data).success){
                location.reload()
            } else {
                alert("Erreur !")
            }
        })
    }
})


$('body').on('click', '.impression_code', function() {
    imprimerPages()
    $(".close_modal_validation").removeClass("d-none")
})

function saveItem(order_id, mutiple_quantity){
    if(mutiple_quantity){
        var quantity_pick_in = parseInt($("#order_"+order_id+" .barcode_"+$("#barcode_verif").val()).find('.quantity_pick_in').text())
    } else {
        var quantity_pick_in = parseInt($("#order_"+order_id+" .barcode_"+$("#barcode").val()).find('.quantity_pick_in').text() + 1)
    }


    if(localStorage.getItem('barcode')){
        var list_barcode = JSON.parse(localStorage.getItem('barcode')) 
        const order_object = list_barcode.find(
            element => element.order_id == order_id
        )

        // Un objet pour cette commande existe déjà, alors on rajoute dans cet objet
        if(order_object){
            if(mutiple_quantity){
                var index = order_object.products.indexOf($("#barcode_verif").val())
                if(index != -1){
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
                order_id : order_id,
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
            order_id : order_id,
            products: [
                $("#barcode").val()
            ],
            quantity: [quantity_pick_in]
        }]
        localStorage.setItem('barcode', JSON.stringify(data));
    }
    $("#barcode_verif").val('')
}

function imprimerPages() {
    var pageHeight = window.innerHeight;
    var scrollHeight = document.documentElement.scrollHeight;
    var position = 0;

    var originalContents = document.body.innerHTML;
    var printReport= document.querySelector('.success_prepared_command').innerHTML;
    document.body.innerHTML = printReport;
    window.print();
    document.body.innerHTML = originalContents;
}
