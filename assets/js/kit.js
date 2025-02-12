function items_picked(kit_selected){
    // Récupère le localstorage pour mettre les produits "pick" qui le sont déjà
    if (localStorage.getItem('barcode')) {
        var list_barcode = localStorage.getItem('barcode')
        if (list_barcode) {
            Object.keys(JSON.parse(list_barcode)).forEach(function (k, v) {
                if (JSON.parse(list_barcode)[k]) {
                    var order_id = JSON.parse(list_barcode)[k].order_id
                    JSON.parse(list_barcode)[k].products.forEach(function (item, key) {
                        if(typeof JSON.parse(list_barcode)[k].quantity != "undefined"){
                            $(".parent_" + order_id + " .to_scan.barcode_" + item).find('.quantity_pick_in').text(JSON.parse(list_barcode)[k].quantity[key])
                            
                            if (parseInt($(".parent_" + order_id + " .to_scan.barcode_" + item).find('.quantity_pick_in').text()) ==
                                parseInt($(".parent_" + order_id + " .to_scan.barcode_" + item).find('.quantity_to_pick_in').text())) {
                                setTimeout(function () {
                                    $(".parent_" + order_id + " .to_scan.barcode_" + item).addClass('pick')
                                }, 0)

                                // Calcul avancée pick in
                               progress(kit_selected)
                            }
                        }
                    });
                }
            })
        }
    }

    if (localStorage.getItem('product_quantity_verif')) {
        $(".barcode_" + localStorage.getItem('product_quantity_verif')).removeClass('pick')
    }
}

function progress(order_id){

    var products_to_pick = 0
    $($(".parent_" + order_id + " .to_scan") ).each(function( index ) {
        products_to_pick = products_to_pick + parseInt($(this).find('.quantity_to_pick_in').text());
    });

    var products_pick_in = 0
    $($(".parent_" + order_id + " .to_scan") ).each(function( index ) {
        products_pick_in = products_pick_in + parseInt($(this).find('.quantity_pick_in').text());
    });

    var percent = parseFloat(products_pick_in *100) / products_to_pick
    $(".show .modal-content .validate_kit").css("background", "linear-gradient(to right, #3edd76 "+percent+"%, black 0%)")
}

$(document).ready(function() {
    $(".card").on('click', function(){
        var id = $(this).attr('data-id')
        $("#kit_category_"+id+" .next2").remove()

        if(!$("#kit_category_"+id).find('.form2').hasClass('active')){
            $("#kit_category_"+id+" .back1").addClass("d-none")
            $("#kit_category_"+id+" .next1").removeClass('d-none')
        } else {
            $("#kit_category_"+id+" .back1").removeClass("d-none")
            $("#kit_category_"+id+" .next1").addClass('d-none')
            $(".back1").after(`
                <button class="btn btn-dark px-5 next2 validate_kit" type="button">Valider</button>
            `)
        }

        const kit_selected = $("#kit_category_"+id+" .form2").attr('data-parent')
    
        $('#kit_category_'+id).modal({
            backdrop: 'static',
            keyboard: false
        })
        $('#kit_category_'+id).modal('show')

        setTimeout(function(){
            if(kit_selected){
                progress(kit_selected)
            }
        },500)
        
    })

    $('.table_kit').each(function() {
        var table = $(this).DataTable({
            "scrollY": "56vh",
            "paginate": false,
            "initComplete": function(settings, json) {
                $(this).removeClass('d-none');
                // Manipulation spécifique à cette instance de table
                $(this).closest('.dataTables_wrapper').find('.col-sm-12').first().remove();
            }
        });
    });
})

// Scan product in kit
document.addEventListener("keyup", function (e) {
    if (!$("#infoMessageModal").hasClass('show') && !$("#modalverification").hasClass('show')){
        if (!isNaN(parseInt(e.key)) && $(".form2").hasClass('active')) {
            $("#barcode").val($("#barcode").val() + e.key)
            if ($("#barcode").val().length == 13) {

                var barcode = $("#barcode").val()
                var parent_id = $(".show .form2").attr("data-parent")
              
                if($(".parent_"+parent_id+" .to_scan.barcode_"+barcode).length > 0) {
                    var quantity_to_pick_in = parseInt($(".parent_"+parent_id+" .to_scan.barcode_"+barcode).find('.quantity_to_pick_in').text())

                    if ($(".parent_"+parent_id+" .to_scan.barcode_"+barcode).hasClass('pick')) {
                        $(".info_message").text("Ce produit à déjà été bippé !")
                        $("#infoMessageModal").modal('show')
                    } else {
                        $(".parent_"+parent_id+" .to_scan.barcode_"+barcode).addClass('pick')
                        var quantity_pick_in = parseInt($(".parent_"+parent_id+" .to_scan.barcode_"+barcode).find('.quantity_pick_in').text())
                        quantity_pick_in = quantity_pick_in + 1
                        quantity_pick_in = quantity_pick_in > quantity_to_pick_in ? quantity_to_pick_in : quantity_pick_in

                        if ((quantity_to_pick_in > 1 && quantity_to_pick_in <= 10) && (quantity_to_pick_in - quantity_pick_in) > 0){
                            // Update pick quantity
                            $(".parent_"+parent_id+" .to_scan.barcode_"+barcode).find('.quantity_pick_in').text(quantity_pick_in)

                            // Modal verification
                            $(".quantity_product").text(quantity_to_pick_in)
                            $(".name_quantity_product").text($(".parent_"+parent_id+" .to_scan.barcode_"+barcode).children('.product_name').text())
                            $("#product_to_verif").val(barcode)
                            $("#quantity_product_to_verif").text(quantity_to_pick_in - quantity_pick_in)
                            localStorage.setItem('product_quantity_verif', barcode);

                            $('#modalverification').modal({
                                backdrop: 'static',
                                keyboard: false
                            })
                            // barcode_3760324819630 
                            $("#modalverification").attr('data-parent', parent_id)
                            $("#modalverification").modal('show')
                            $("#barcode_verif").val(barcode)
                            saveItem(parent_id, true)
                            progress(parent_id)
                        } else if(quantity_to_pick_in > 10){
                            $(".quantity_product").text('')
                            $(".quantity_product").text(quantity_to_pick_in)
                            $(".name_quantity_product").text($(".parent_"+parent_id+" .to_scan.barcode_"+barcode).children('.product_name').text())
                            $("#barcode_verif").val(barcode)
                            $('#modalverification2').modal({
                                backdrop: 'static',
                                keyboard: false
                            })

                            $("#modalverification2").attr('data-parent', parent_id)
                            $("#modalverification2").modal('show')
                            $(".parent_"+parent_id+" .to_scan.barcode_"+barcode).find('.quantity_pick_in').text(quantity_to_pick_in)
                            saveItem(order_id, false, true, true)
                            progress(parent_id)
                        } else {
                            $(".parent_"+parent_id+" .to_scan.barcode_"+barcode).find('.quantity_pick_in').text(1)
                            saveItem(parent_id, false)
                            progress(parent_id)
                        }
                    }
                } else {
                    $(".info_message").text("Ce produit n'est pas dans le kit !")
                    $("#infoMessageModal").modal('show')
                    $("#barcode").val("")
                    $("#barcode_verif").val("")
                }
                $("#barcode").val("")
            } else if($("#barcode").val().length > 13){
                $("#barcode").val("")
            }
        }
    } else if($("#modalverification").hasClass('show') && !$("#infoMessageModal").hasClass('show')){
        var parent_id = $("#modalverification").attr('data-parent')

        if (!isNaN(parseInt(e.key))) {
            $("#barcode_verif").val($("#barcode_verif").val() + e.key)
            if ($("#barcode_verif").val().length == 13) {

                var barcode_verif = $("#barcode_verif").val()

                if (barcode_verif == localStorage.getItem('product_quantity_verif')) {
                    var quantity_pick_in = parseInt($(".parent_"+parent_id+" .barcode_"+barcode_verif).find('.quantity_pick_in').text())
                    $("#quantity_product_to_verif").text(parseInt($("#quantity_product_to_verif").text()) - 1)
                    $(".parent_"+parent_id+" .barcode_"+barcode_verif).find('.quantity_pick_in').text(quantity_pick_in + 1)
                    saveItem(parent_id, true)

                    if (parseInt($("#quantity_product_to_verif").text()) == 0) {
                        $("#modalverification").modal('hide')
                        localStorage.removeItem('product_quantity_verif');
                    }
                    $("#barcode_verif").val('')
                } else {
                    $(".info_message").text("Aucun produit ne correspond à ce code barre !")
                    $("#infoMessageModal").modal('show')
                    $("#barcode_verif").val('')
                    $("#barcode").val('')
                }
            }
        }
    }
})

function saveItem(order_id, mutiple_quantity, barcode, manually = false) {
    var quantity_to_pick_in = parseInt($(".parent_"+order_id+" .to_scan.barcode_"+$("#barcode").val()).find('.quantity_to_pick_in').text())
    if(manually){
        var quantity_pick_in = quantity_to_pick_in
    } else {
        if (mutiple_quantity) {
            var quantity_pick_in = parseInt($(".parent_"+order_id+" .to_scan.barcode_"+$("#barcode_verif").val()).find('.quantity_pick_in').text())
        } else {
            var quantity_pick_in = parseInt($(".parent_"+order_id+" .to_scan.barcode_"+$("#barcode").val()).find('.quantity_pick_in').text() + 1)
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
        if($(".parent_"+order_id+" .to_scan.barcode_"+$("#barcode").val()).length > 0 ){
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

function remove_kit_prepare(kit_id){
    var pick_items = localStorage.getItem('barcode')
    if (pick_items) {
        pick_items = JSON.parse(pick_items)
        Object.keys(pick_items).forEach(function (k, v) {
            if (pick_items[k]) {
                if (kit_id == pick_items[k].order_id) {
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
}

$(".checkbox_label").on("change", function(){
    $(".checkbox_label").not($(this)).prop("checked", false);
})

// Gestion du clic sur les lignes de table
$(".select_kit").on('click', function(event) {
    // Vérifier si le clic a été sur la case à cocher pour éviter les conflits
    if (!$(event.target).is('.checkbox_label')) {
        var checkbox = $(this).find('.checkbox_label');
        var isChecked = checkbox.prop('checked');
        // Décocher toutes les autres cases à cocher
        $(".checkbox_label").not(checkbox).prop("checked", false);
        // Basculer l'état de la checkbox cliquée
        checkbox.prop('checked', !isChecked);
    }
});

// Empêcher la propagation de l'événement de clic sur les checkboxes
$(".checkbox_label").on('click', function(event) {
    event.stopPropagation();
});

$(".restart_kit").on('click', function(){
    $(this).addClass('rotateRight')
    const kit_id = $(this).parent().parent().attr('data-parent')

    $(".parent_" + kit_id + " .product_in_kit").removeClass("pick")
    $(".parent_" + kit_id + " .quantity_pick_in").text(0)
    $("#barcode").val("")
    $("#barcode_verif").val("")

    remove_kit_prepare(kit_id)
    progress(kit_id)
})

let currectActive = 1;
//============== Next Form===============
$(".next1").on('click', function(){

    // Get checked checkbox 
    var id = $(".checkbox_label:checked").attr('data-id')

    if(typeof id != "undefined"){
        var cat = $(".checkbox_label:checked").attr('data-cat')
        var kit_selected = $("#kit_name_"+id).text()

        $("#kit_category_"+cat+" .form2").addClass('active')
        $("#kit_category_"+cat+" .form2").addClass("parent_"+id)
        $("#kit_category_"+cat+" .form2").attr("data-parent", id)
        $("#kit_category_"+cat+" .title_step2").text(kit_selected)
        $("#kit_category_"+cat+" .modal-title").text(kit_selected)

        $("#kit_category_"+cat+" .product_in_kit").addClass('d-none')
        $("#kit_category_"+cat+" .product_in_kit").removeClass('to_scan')

        $(".parent_"+id).removeClass('d-none')
        $(".parent_"+id).addClass('to_scan')

        const form1 = $(".show .form1")[0];
        const form2 = $(".show .form2")[0];
        const form3 = $(".show .form3")[0];

        $(".next1").addClass('d-none')
        $(".back1").removeClass('d-none')
        $(".back1").after(`
            <button class="validate_kit btn btn-dark px-5 next2" type="button">Valider</button>
        `)

        form1.style.left = "-990px";
        form2.style.left = "0px";
        form3.style.left = "990px";


        increamentNumber();
        update();
        items_picked(id)
    } else {
        $(".info_message").text("Veuillez sélectionner un kit")
        $("#infoMessageModal").modal('show')
    }
  
})

$("body").on("click", ".next2", function(e) {
    const kit_id = $(".checkbox_label:checked").attr('data-id')

    if(kit_id){
        const nbr_product = $(".show .to_scan.product_in_kit").length
        const nbr_product_pick = $(".show .to_scan.pick").length
        
        if(nbr_product == nbr_product_pick){
            if(localStorage.getItem('barcode')){
                var order_object = false
                var pick_items = JSON.parse(localStorage.getItem('barcode'))
                if (pick_items) {
                    // Récupère les produits de cette commande
                    order_object = pick_items.find(
                        element => element.order_id == kit_id
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

            // Send kit to history
            $.ajax({
                url: "kitPrepared",
                method: 'POST',
                data: { _token: $('input[name=_token]').val(), kit_id: kit_id, pick_items: pick_items, pick_items_quantity: pick_items_quantity }
            }).done(function (data) {
                if(JSON.parse(data).success){
                    const form1 = $(".show .form1")[0];
                    const form2 = $(".show .form2")[0];
                    const form3 = $(".show .form3")[0];

                    form1.style.left = "-1980px";
                    form2.style.left = "-990px";
                    form3.style.left = "0px";


                    $(".next2").remove()
                    $(".back1").after(`
                        <button class="btn btn-dark px-5 close_modal_kit_finished" type="button">Fermer</button>
                    `)
                    $(".back1").remove()

                    increamentNumber();
                    update();

                    $(".prepare_by_detail").remove()
                    $(".show #prepare_by").append(`
                        <div class="d-flex flex-column prepare_by_detail info_order">
                            <div class="d-flex align-items-center gap-3 prep"><span class="title">Préparateur :</span><span> `+JSON.parse(data).user+`</span></div>
                            <div class="d-flex align-items-center gap-3 date"><span class="title">Date :</span><span> `+JSON.parse(data).date+`</span></div>
                            <div class="d-flex align-items-center gap-3 identifiant"><span class="title">Identifiant :</span><span> `+JSON.parse(data).unique_id+`</span></div>
                        </div>
                    `)
                    remove_kit_prepare(kit_id)
                } else {
                    alert(JSON.parse(data).message)
                }
            })
        } else {
            $(".info_message").text("Veuillez bipper tous les produits")
            $("#infoMessageModal").modal('show')
        }
    } else {
        $(".info_message").text("Veuillez sélectionner un kit")
        $("#infoMessageModal").modal('show')
    }
})

$("body").on("click", ".close_modal_kit_finished", function(e) {
    window.location.reload();
})


function enter_manually_barcode(product_id, parent_id){
    $("#barcode_manually").css('border', '1px solid #ced4da')
    $('#barcode_manually').attr('placeholder','');
    $("#product_barcode").val(product_id)
    $("#parent_id").val(parent_id)

    $('#modalManuallyBarcode').modal({
        backdrop: 'static',
        keyboard: false
    })
    $("#modalManuallyBarcode").modal('show')
}

function remove_product(barcode, parent_id){

    if($(".parent_"+parent_id+" .to_scan.barcode_"+barcode).hasClass('pick')){
        $(".show .remove_"+barcode+"_"+parent_id+" i").addClass('rotateRight')
    }
    
    $(".parent_"+parent_id+" .to_scan.barcode_"+barcode).removeClass('pick')
    $(".parent_"+parent_id+" .to_scan.barcode_"+barcode).find('.quantity_pick_in').text("0")
    progress(parent_id)

    if (localStorage.getItem('barcode')) {
        pick_items = JSON.parse(localStorage.getItem('barcode'))

        if (pick_items.length > 0) {
            Object.keys(pick_items).forEach(function (k, v) {
                if (pick_items[k]) {
                    if (parent_id == pick_items[k].order_id) {
                        Object.keys(pick_items[k].products).forEach(function (l, w) {
                            if(pick_items[k].products[l] == barcode){
                                pick_items[k].products.splice(l, 1);
                                pick_items[k].quantity.splice(l, 1);

                                if(JSON.stringify(pick_items)){
                                    localStorage.setItem('barcode', JSON.stringify(pick_items));
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

$(".valid_manually_barcode").on('click', function(){

    var product_barcode = $("#product_barcode").val()
    var barcode = $("#barcode_manually").val()
    var parent_id = $("#parent_id").val()

    if(product_barcode === barcode){
        $(".parent_"+parent_id+" .to_scan.barcode_"+barcode).addClass('pick')
        $(".parent_"+parent_id+" .to_scan.barcode_"+barcode).find('.quantity_pick_in').text($(".parent_"+parent_id+" .to_scan.barcode_"+barcode).find('.quantity_to_pick_in').text())
        $("#modalManuallyBarcode").modal('hide')
        $("#barcode").val(barcode)
        saveItem(parent_id, false, true, true)
        progress(parent_id)
        $("#barcode_manually").val("")
    } else {
        $("#barcode_manually").css('border', '1px solid red')
        $('#barcode_manually').attr('placeholder','Code barre invalide !'); 
    }
})

//=============== Back One==================
$(".back1").on('click', function(){

    $(".show .form2").removeClass('active')
    $(".show .back1").addClass('d-none')
    $(".show .next2").remove()
    $(".show .next1").removeClass('d-none')

    const form1 = $(".show .form1")[0];
    const form2 = $(".show .form2")[0];
    const form3 = $(".show .form3")[0];

    form1.style.left = "0px";
    form2.style.left = "990px";
    form3.style.left = "1980px";

    // back slide
    decreametNumber();
    // update progress bar
    update();
})

//============= Progress update====================
function update() {
    const circles = document.querySelectorAll(".show .circle");
    const progressEl = $(".show .progress")[0]

    circles.forEach((circle, indx) => {
        if (indx < currectActive) {
            circle.classList.add("active_progress");
        } else {
            circle.classList.remove("active_progress");
        }
        // get all of active classes
        const active_progress = document.querySelectorAll(".show .active_progress");

        progressEl.style.width =
        ((active_progress.length - 1) / (circles.length - 1)) * 100 + "%";
    });
}
//================== Increament Number===============
function increamentNumber() {
    const circles = document.querySelectorAll(".show .circle");
    // next progress number
    currectActive++;
        if (currectActive > circles.length) {
            currectActive = circles.length;
        }
}
//================ Decreament Number=================
function decreametNumber() {
    currectActive--;
        if (currectActive < 1) {
            currectActive = 1;
        }
}

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
    imprimerPages()
    $(".close_modal_validation").removeClass("d-none")
})

function imprimerPages() {

    var printer_ip = $(".printer_ip").val() ?? false
    var deviceID = "local_printer";

    if(!printer_ip){
        window.print();
    } else {
        //Create an ePOS-Print Builder object
        var builder = new epson.ePOSBuilder();

        builder.addTextLang('fr')
        builder.addTextAlign(builder.ALIGN_CENTER);
        builder.addTextSmooth(true);
        builder.addTextFont(builder.FONT_A);
        builder.addTextSize(1, 1);
        builder.addText("\n"+$(".show .info_order .prep").text()+"\n");
        builder.addText("\n"+$(".show .info_order .date").text()+"\n");
        builder.addText("\n"+$(".show .info_order .identifiant").text()+"\n");
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
            window.print();
        }

        //Send the print document
        epos.send(request);
    }
}

window.addEventListener("afterprint", (event) => {
   console.log(event)
});