@extends("layouts.app")

@section("style")

@endsection

@section("wrapper")
<div class="page-wrapper">
    <div class="page-content">
        <div class="page-breadcrumb d-sm-flex align-items-center">
            <div class="breadcrumb-title pe-3"></div>
            <input id="barcode" type="hidden" value="">
            <input id="barcode_verif" type="hidden" value="">

            <input id="order_id" type="hidden" value="">
            <input id="product_count" type="hidden" value="">
            <input id="customer" type="hidden" value="">
            <input type="hidden" value="" id="detail_order">
            @csrf
        </div>


        <div class="switcher-wrapper">
            <div class="switcher-btn"> <i class="bx bx-help-circle"></i></div>
            <div class="switcher-body">
                <div class="d-flex align-items-center">
                    <h5 class="mb-0 text-uppercase">Informations</h5>
                    <button type="button" class="btn-close ms-auto close-switcher" aria-label="Close"></button>
                </div>
                <hr>
                <div class="d-flex align-items-center justify-content-between details_information">
                    Ici, il vous suffit de scanner le QR code imprimé par le préparateur ou<br>
                    de taper le numéro de commande à emballer dans le champ adéquat
                </div>
            </div>
        </div>

        <div class="detail_order_to_wrap card mb-50 mt-3">
            <div class="col d-flex justify-content-between align-items-baseline">
                <span class="text-muted" id="orderno"></span>
                <span class="text-muted" id="prepared"></span>
            </div>
            <div class="gap">
                <div class="qrcode_background col-2 d-flex mx-auto">

                </div>
            </div>

            <div class="empty_order to_hide card_empty card_empty_product is-loading d-flex w-100 justify-content-between ">
                <div class="content">
                    <h3></h3>
                </div>
                <div class="content">
                    <h3></h3>
                </div>
            </div>


            <div class="mt-2 show_messages"></div>
            <div class="empty_order title mx-auto to_hide"> Scanner le QR Code </div>
            <span class="empty_order mb-2 text-center to_hide">OU</span>
            <div class="empty_order to_hide mb-3 d-flex justify-content-center input_order_id">
                <input class="empty_order order_id_input" type="text" placeholder="Renseigner le numéro de commande">
            </div>


            <div class="empty_order mt-3 to_hide">
                <div class="d-flex justify-content-around">
                    <div class="card_empty is-loading">
                        <div class="content">
                            <h2></h2>
                            <p></p>
                        </div>
                    </div>
                    <div class="card_empty is-loading">
                        <div class="content">
                            <h2></h2>
                            <p></p>
                        </div>
                    </div>
                </div>
                <hr>

                <div>
                    <div class="card_empty card_empty_product is-loading d-flex w-100 justify-content-around align-items-center">
                        <div class="image"></div>
                        <div class="content w-25">
                            <h2></h2>
                            <p></p>
                        </div>
                        <div class="content  d-flex justify-content-end">
                            <h3></h3>
                        </div>
                    </div>
                    <div class="card_empty card_empty_product is-loading d-flex w-100 justify-content-around align-items-center">
                        <div class="image"></div>
                        <div class="content w-25">
                            <h2></h2>
                            <p></p>
                        </div>
                        <div class="content  d-flex justify-content-end">
                            <h3></h3>
                        </div>
                    </div>
                </div>
            </div>

            <div class="detail_shipping_billing"></div>
            <hr class="d-none main_hr">
            <div class="main">
                <div class="w-100 d-none loading_detail_order">
                    <div class="spinner-grow text-dark" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            </div>
            <div class="total_order_details">
                <div class="row">
                    <div class="total_order col"> <b></b> </div>
                    <div class="text-center total_product_order col"> <b></b> </div>

                    <div class="text-end amount_total_order col d-flex justify-content-end"> <b></b> </div>
                </div>
            
                <button disabled type="button" class="empty_order validate_order btn btn-primary d-flex mx-auto">Valider </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal verif order product -->
<div class="modal_order modal fade" data-order="" id="" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-body detail_product_order">
                <div class="detail_product_order_head d-flex flex-column">
                    <div class="p-1 mb-2 head_detail_product_order d-flex w-100 justify-content-between">
                        <span class="column1 name_column">Article</span>
                        <span class="column2 name_column">Coût</span>
                        <span class="column3 name_column">Pick / Qté</span>
                    </div>

                    <div class="body_detail_product_order"></div>

                    <div class="align-items-end flex-column mt-2 d-flex justify-content-end">
                        <div class="w-100 d-flex align-items-end justify-content-between flex-wrap">
                            <span class="mt-1 mb-2 montant_total_order"></span>
                        </div>
                        <div class="w-100 d-flex justify-content-between">
                            <button type="button" class="btn btn-dark px-5" data-bs-dismiss="modal"><i class="d-none responsive-icon lni lni-arrow-left"></i><span class="responsive-text">Retour</span></button>
                            <button type="button" class="validate_pick_in btn btn-dark px-5"><i class="d-none responsive-icon lni lni-checkmark"></i><span class="responsive-text">Valider</span></button>
                        </div>

                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<!-- Modal vérification quantité -->
<div class="modal_reset_order modal_verif_order modal fade" data-order="" id="modalverification" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal_body_reset modal-body d-flex flex-column justify-content-center">
                <h2 class="text-center">Attention, cette commande contient <span class="quantity_product"></span> <span class="name_quantity_product"></span></h2>
                <span style="font-size:25px" class="mb-3 text-center">Produit(s) restant(s) à bipper : <span class="text-danger" style="font-size:30px" id="quantity_product_to_verif"></span></span>
                <input type="hidden" value="" id="product_to_verif">
            </div>
        </div>
    </div>
</div>


 <!-- Modal vérification quantité -->
 <div class="modal_reset_order modal_verif_order modal fade" data-order="" id="modalverification2" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal_body_reset modal-body d-flex flex-column justify-content-center">
                <h2 class="text-center">Attention, cette commande contient <span class="quantity_product"></span> <span class="name_quantity_product"></span></h2>
            </div>
            <div class="w-100 d-flex justify-content-center p-2">
                <button type="button" class="btn btn-dark px-5" data-bs-dismiss="modal">Valider</button>
            </div>
        </div>
    </div>
</div>


<!-- Modal info produit déjà bippé ou inexistant-->
<div class="modal fade modal_reset_order" id="infoMessageModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-body">
                <h3 class="text-center info_message"></h3>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Fermer</button>
            </div>
        </div>
    </div>
</div>


<!-- Modal validation génération sans étiquette -->
<div class="modal fade modal_no_label" id="infoMessageModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-body">
                <h3 class="text-center info_message">Confirmer la facturation de la commande sans étiquette ?</h3>
            </div>
            <div class="modal-footer d-flex w-100 justify-content-center">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Non</button>
                <button style="max-height: 50px;" onclick="validWrapOrder(false)" type="button" class="confirm_valid_order btn btn-primary">
                    <span>oui</span>
                    <div style="height:1rem; width:1rem" class="d-none loading_valid_wrapper spinner-border text-light" role="status"> <span class="visually-hidden">Loading...</span></div>
                </button> 
            </div>
        </div>
    </div>
</div>



<!-- Modal pdf étiquette -->
<div class="modal fade modal_pdf_viewer" id="infoMessageModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
    <div class="modal-dialog modal-fullscreen" role="document">
        <div class="modal-content">
            <div class="modal-body">
                <embed class="embed_pdf" src="" type="application/pdf" width="100%" height="100%" />
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Fermer</button>
            </div>
        </div>
    </div>
</div>




 <!-- Modal generate label -->
 <div data-bs-keyboard="false" data-bs-backdrop="static" class="modal_radius generate_label_modal modal fade" id="generateLabelModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-body">	
                <div class="d-none flex-column loading_generate_label w-100 h-100 d-flex align-items-center justify-content-center">
                    <div class="spinner-grow text-dark" role="status"></div>
                    <span class="loading_text"><span>Génération d'étiquette(s)...</span></span>
                </div>
                
                <form class="h-100 labelProductsInfo" method="POST">
                    <input id="order_id_label" type="hidden" name="order_id" value="">
                    <div class="h-100 d-flex flex-column justify-content-between">
                        <div class="d-flex flex-column">
                            <div class="mb-2 d-flex w-100 justify-content-between">
                                <span style="width: 50px"><input data-id="" class="form-check-input check_all" type="checkbox" value="" aria-label="Checkbox for product order"></span>
                                <span class="head_1 w-50">Article</span>
                                <span class="head_2 w-25">P.U (€)</span>
                                <span class="head_3 w-25">Quantité</span>
                                <span class="head_4 w-25">Poids (kg)</span>
                            </div>
                            <div class="body_line_items_label">
                            
                            </div>
                        </div>

                        <div class="button_validate_modal_label d-flex justify-content-center mt-3 w-100">
                            <div class="back_labels">
                                <button type="button" disabled class="border-danger bg-danger cancel_label_created btn btn-dark px-5"><i class="fadeIn animated bx bx-arrow-back"></i></button>
                            </div>
                            <div class="d-flex">
                                <button type="button" class="btn btn-dark px-5" data-bs-dismiss="modal">Annuler</button>
                                <button style="margin-left:15px" type="button" class="valid_generate_label btn btn-dark px-5">Suivant</button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>



@endsection


@section("script")
    <script src="{{asset('assets/js/wrapOrder.js')}}"></script>
@endsection