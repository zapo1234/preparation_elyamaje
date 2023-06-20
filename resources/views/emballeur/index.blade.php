@extends("layouts.app")

		@section("style")
		
		@endsection 

		@section("wrapper")
			<div class="page-wrapper">
				<div class="page-content">
					<div class="page-breadcrumb d-sm-flex align-items-center mb-2">
						<div class="breadcrumb-title pe-3 mb-2"></div>
					</div>


                    <div class="d-flex">
                        <div class="col-xl-12">
                            <div class="card border-top border-0 border-4 border-dark">
                                <div class="card-body p-5">
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
                                            <input type="text" name="order_id" class="order_input form-control" id="order_id">
                                        </div>
                                        <div class="col-md-3">
                                            <label for="product_count" class="form-label">Nombre de produit(s)</label>
                                            <input type="number" name="product_count" class="order_input form-control" id="product_count">
                                        </div>

                                        <div class="col-md-3">
                                            <label for="customer" class="form-label">Client</label>
                                            <input type="text" name="customer" class="order_input form-control" id="customer">
                                        </div>

                                        <div class="col-md-3">
                                            <label for="preparateur" class="form-label">Préparateur</label>
                                            <input type="text" name="preparateur" class="order_input form-control" id="preparateur">
                                        </div>
                                        
                                        <div class="col-12">
                                            <button  type="button" class="validate_order btn btn-primary px-5">Valider</button>
                                        </div>
                                    </div>
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
                            <span style="width: fit-content" class="mb-3 badge bg-primary shipping_method"></span>

                            <div class="mb-3 d-flex flex-column">
                                <strong>Facturation :</strong>
                                <span class="customer_name"></span>
                                <span class="customer_email"></span>
                            </div>

                            <div class="d-flex flex-column">
                                <strong>Expédition :</strong>
                                <span class="customer_shipping_name"></span>
                                <span class="customer_shipping_adresss1"></span>
                                <span class="customer_shipping_adresss2"></span>
                                <span class="customer_shipping_country"></span>
                            </div>
                        
                        </div>
                        <div class="modal-footer">
                            <div class="loading_div d-none d-flex w-100 justify-content-center">
                                <div class="spinner-border text-dark" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                            </div>

                            <div class="button_modal_form">
                                <button type="button" onclick=validWrapOrder(true) class="btn btn-primary" data-dismiss="modal">Générer l'étiquette</button>
                                <button  type="button"  onclick=validWrapOrder(false) type="button" class="btn btn-primary">Continuer</button>
                            </div>
                         
                        </div>
                    </div>
                </div>
            </div>

		@endsection

	
	@section("script")
		<script>

            // $(document).ready(function() {
            //     $("#exampleModalCenter").modal('show')
            // })
                
            $(".validate_order").on("click", function(){
                $.ajax({
                    url: "{{ route('checkExpedition') }}",
                    metho: 'GET',
                    data : {order_id: $("#order_id").val()}
                }).done(function(data) {
                    if(JSON.parse(data).success){
                        order = JSON.parse(data).order
          
                        $(".order_number").text('Détails Commande n°'+order.order_woocommerce_id)
                        $(".customer_name").text(order.billing_customer_last_name+' '+order.billing_customer_first_name)
                        $(".customer_email").text(order.billing_customer_email)
                        $(".shipping_method").text(order.shipping_method_detail ?? '')

                        $(".customer_shipping_name").text(order.shipping_customer_last_name+' '+order.shipping_customer_first_name)
                        $(".customer_shipping_adresss1").text(order.shipping_customer_address_1)
                        $(".customer_shipping_adresss2").text(order.shipping_customer_address_2)
                        $(".customer_shipping_country").text(order.shipping_customer_city+' '+order.shipping_customer_postcode)
                        $("#exampleModalCenter").modal('show')
                    } else {

                        $(".show_messages").prepend(`
                            <div class="alert alert-danger border-0 bg-danger alert-dismissible fade show">
                                <div class=" text-white">`+JSON.parse(data).message+`</div>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        `)
                    }
                });
            })


            function validWrapOrder(label){
                $(".button_modal_form").addClass('d-none')
                $(".loading_div").removeClass('d-none')

                $.ajax({
                    url: "{{ route('validWrapOrder') }}",
                    metho: 'POST',
                    data : {_token: $('input[name=_token]').val(), order_id: $("#order_id").val(), label: label},
                    dataType: 'html' 
                }).done(function(data) {
                    if(JSON.parse(data).success){

                        $(".show_messages").prepend(`
                            <div class="success_message alert alert-success border-0 bg-success alert-dismissible fade show">
                                <div class="text-white">`+JSON.parse(data).message+`</div>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        `)

                        $('.order_input').each(function(){
                            $(this).val('');
                        });

                    } else {
                        $(".show_messages").prepend(`
                            <div class="alert alert-danger border-0 bg-danger alert-dismissible fade show">
                                <div class=" text-white">`+JSON.parse(data).message+`</div>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        `)
                    }
                    $(".button_modal_form").removeClass('d-none')
                    $(".loading_div").addClass('d-none')
                    $("#exampleModalCenter").modal('hide')
                })
            }

		    document.addEventListener("keydown", function(e) {
                if(e.key.length == 1){
                    $("#detail_order").val($("#detail_order").val()+e.key)
                    var array = $("#detail_order").val().split(',')
                    if(array.length == 4){
                        $("#order_id").val(array[0])
                        $("#product_count").val(array[1])
                        $("#customer").val(array[2])
                        $("#preparateur").val(array[3])
                        $(".validate_order").attr('disabled', false)
                    }
                }
			});
        </script>
	@endsection
