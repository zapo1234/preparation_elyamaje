@extends("layouts.app")

		@section("style")
		
		@endsection 

		@section("wrapper")
			<div class="page-wrapper">
				<div class="page-content">
					<div class="page-breadcrumb d-sm-flex align-items-center mb-2">
						<div class="breadcrumb-title pe-3 mb-2"></div>
                        <input id="barcode" type="hidden" value="">
						<input id="barcode_verif" type="hidden" value="">
                        @csrf
					</div>


                    <div class="d-flex">
                        <div class="col-xl-12">
                            <div class="card border-top border-0 border-4 border-dark">
                                <div class="card-body p-4">
                                    <div class="card-title d-flex align-items-center">
                                        <div><i class="bx bxs-box me-1 font-22 text-dark"></i>
                                        </div>
                                        <h5 class="mb-0 text-dark">Commandes</h5>
                                        <input type="hidden" value="" id="detail_order">
                                    </div>
                                    <hr>

                                    <div class="show_messages"></div>

                                    <div class="form_valid_wrap_order row g-3">
                                        <div class="col-md-3">
                                            <label for="order_id" class="form-label">N° Commande</label>
                                            <input required type="text" name="order_id" class="order_input form-control" id="order_id">
                                        </div>
                                        <div class="col-md-3">
                                            <label for="product_count" class="form-label">Nombre de produit(s)</label>
                                            <input required type="number" name="product_count" class="order_input form-control" id="product_count">
                                        </div>

                                        <div class="col-md-3">
                                            <label for="customer" class="form-label">Client</label>
                                            <input required type="text" name="customer" class="order_input form-control" id="customer">
                                        </div>

                                        <div class="col-md-3">
                                            <label for="preparateur" class="form-label">Préparateur</label>
                                            <input required type="text" name="preparateur" class="order_input form-control" id="preparateur">
                                        </div>
                                        
                                        <div class="col-12">
                                            <button disabled type="button" class="validate_order btn btn-primary px-5">Valider</button>
                                        </div>
                                    </div>
                                </div>
                                <div class="p-3 col-12">
                                    <span>(Scanner le QR Code ou renseigner le numéro de commande manuellement)</span>
                                </div>
                            </div>
                        </div>
                    </div>
				</div>
			</div>



            <!-- Modal création étiquette -->
            <div class="modal fade" id="exampleModalCenter" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered" role="document">
                    <div class="modal-content">
                        <div class="modal-body d-flex flex-column">
                            <h4 class="order_number"></h4>  
                            <div class="d-flex w-100 justify-content-between mb-3">
                                <span style="width: fit-content" class="badge bg-primary shipping_method"></span>
                                <span class="badge bg-dark distributor"></span>
                            </div>

                            <span style="width: fit-content" class="mb-3 badge status_order"></span>

                            <div class="mb-3 d-flex flex-column">
                                <strong>Facturation :</strong>
                                <span class="customer_name"></span>
                                <span class="customer_email"></span>
                                <span class="customer_billing_adresss1"></span>
                                <span class="customer_billing_adresss2"></span>
                            </div>

                            <div class="d-flex flex-column">
                                <strong>Expédition :</strong>
                                <span class="customer_shipping_name"></span>
                                <span class="customer_shipping_company"></span>
                                <span class="customer_shipping_adresss1"></span>
                                <span class="customer_shipping_adresss2"></span>
                                <span class="customer_shipping_country"></span>
                            </div>

                            <div class="d-flex w-100 justify-content-end">
                                <span class="font-bold"><span class="total_order"></span>{{ config('app.currency_symbol') }}</span>
                            </div>
                        
                        </div>
                        <div class="modal-footer">
                            <div class="loading_div d-none d-flex w-100 justify-content-center">
                                <div class="spinner-border text-dark" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                            </div>

                            <div class="valid_order_and_generate_label button_modal_form">
                                <button type="button" data-bs-dismiss="modal" type="button" class="btn btn-secondary">Annuler</button>
                                <button type="button" onclick=validWrapOrder(true) class="btn btn-primary">Générer l'étiquette</button>
                                <button type="button"  onclick=validWrapOrder(false) type="button" class="btn btn-primary">Continuer</button>
                            </div>
                            <div class="verif_order button_modal_form">
                                <button type="button" class="verif_order_product btn btn-primary">Vérifier la commande</button>
                            </div>
                         
                        </div>
                    </div>
                </div>
            </div>



            <!-- Modal reset commande -->
            <div style="z-index:1061" class="modal_reset_order modal fade" id="modalReset" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered" role="document">
                    <div class="modal-content">
                
                        <div class="modal_body_reset modal-body d-flex flex-column justify-content-center">
                            <h2 class="text-center">Recommencer la commande ?</h2>
                            <div class="w-100 d-flex justify-content-center">
                                <button type="button" class="btn btn-dark px-5" data-bs-dismiss="modal">Non</button>
                                <button style="margin-left:15px" type="button" class="btn btn-dark px-5 confirmation_reset_order ">Oui</button>
                            </div>
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
                                    <!-- <span class="column4 name_column">Code Barre</span> -->
                                </div>	

                                <div class="body_detail_product_order">
                                   
                                </div>

                                <div class="align-items-end flex-column mt-2 d-flex justify-content-end"> 
                                    <div class="w-100 d-flex align-items-end justify-content-between flex-wrap">
                                        <span class="mt-1 mb-2 montant_total_order">
                                       
                                    </div>
                                    <div class="w-100 d-flex justify-content-between">
                                        <button type="button" class="btn btn-dark px-5" data-bs-dismiss="modal"><i class="d-none responsive-icon lni lni-arrow-left"></i><span class="responsive-text">Retour</button>
                                        <button type="button" class="reset_order btn btn-dark px-5" ><i class="d-none responsive-icon lni lni-reload"></i><span class="responsive-text">Recommencer la commande</span></button>
                                        <button type="button" class="validate_pick_in btn btn-dark px-5"><i class="d-none responsive-icon lni lni-checkmark"></i><span class="responsive-text">Valider</button>
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

		@endsection

	
@section("script")
<script src="{{asset('assets/js/wrapOrder.js')}}"></script>
@endsection
