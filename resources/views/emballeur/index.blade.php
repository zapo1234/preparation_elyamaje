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

                                    @if(session()->has('success'))
                                        <div class="alert alert-success border-0 bg-success alert-dismissible fade show">
                                            <div class="text-white">{{ session()->get('success') }}</div>
                                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                        </div>
                                    @endif
                                    @if(session()->has('error'))
                                        <div class="alert alert-danger border-0 bg-danger alert-dismissible fade show">
                                            <div class="text-white">{{ session()->get('error') }}</div>
                                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                        </div>
                                    @endif

                                    <form method="POST" action="{{ route('validWrapOrder') }}" class="form_valid_wrap_order row g-3" data-bitwarden-watching="1">
                                        @csrf
                                        <div class="col-md-3">
                                            <label for="order_id" class="form-label">N° Commande</label>
                                            <input type="text" name="order_id" class="form-control" id="order_id">
                                        </div>
                                        <div class="col-md-3">
                                            <label for="product_count" class="form-label">Nombre de produit(s)</label>
                                            <input type="number" name="product_count" class="form-control" id="product_count">
                                        </div>

                                        <div class="col-md-3">
                                            <label for="customer" class="form-label">Client</label>
                                            <input type="text" name="customer" class="form-control" id="customer">
                                        </div>

                                        <div class="col-md-3">
                                            <label for="preparateur" class="form-label">Préparateur</label>
                                            <input type="text" name="preparateur" class="form-control" id="preparateur">
                                        </div>
                                        
                                        <div class="col-12">
                                            <button disabled type="button" class="validate_order btn btn-primary px-5">Valider</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
				</div>
			</div>
		@endsection

	
	@section("script")
		<script>

            $(".validate_order").on("click", function(){
                $(".form_valid_wrap_order").submit()
            })

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
