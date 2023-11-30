
	@extends("layouts.app")

		@section("style")
			<link href="{{asset('assets/plugins/datatable/css/dataTables.bootstrap5.min.css')}}" rel="stylesheet" />
			<link href="assets/plugins/select2/css/select2.min.css" rel="stylesheet" />
			<link href="assets/plugins/select2/css/select2-bootstrap4.css" rel="stylesheet" />
		@endsection

		@section("wrapper")
			<div class="page-wrapper">
				<div class="page-content">
					<div class="page-breadcrumb d-sm-flex align-items-center mb-3">
						<div class="breadcrumb-title pe-3">Facturation</div>
						<div class="ps-3">
							<nav aria-label="breadcrumb">
								<ol class="breadcrumb mb-0 p-0">
									<li class="breadcrumb-item active" aria-current="page">Refacturer</li>
								</ol>
							</nav>
						</div>
					</div>
				
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

					<div class="w-100 d-flex justify-content-center">
						<div class="billing_form w-75 card radius-10">
							<div class="header_title hide_mobile d-flex align-items-center">
								<div class="w-100 d-flex justify-content-between">
									<h5>Commande  <span class="text-danger">(WooCommerce seulement)</span></h5>
								</div>
							</div>
							<div class="card-body p-5">
								<form method="post" action="{{ route('admin.reInvoiceOrder') }}">
									@csrf
									<div class="line_order line_1 col-md-12 mt-3">
										<label for="order_id" class="form-label">Numéro de commande</label>
										<input required value="" name="order_id[]" type="number" class="form-control">
									</div>
                                    
                                    <div class="d-flex justify-content-center">
                                        <button type="button" class="add_ligne_order_button btn btn-primary mt-2 mb-2">Ajouter une ligne</i></button>
                                    </div>
                                    
								
                                    <div class="d-flex justify-content-center mt-5">
                                        <button type="sumbit" class="btn btn-primary px-5">Refacturer</button>
                                    </div>
								</form>
							</div>
						</div>
					</div>

				</div>
			</div>

		@endsection

	
	@section("script")

		<script src="{{asset('assets/plugins/datatable/js/jquery.dataTables.min.js')}}"></script>
		<script src="{{asset('assets/plugins/datatable/js/dataTables.bootstrap5.min.js')}}"></script>
		<script src="assets/plugins/select2/js/select2.min.js"></script>
		<script>



		$(document).ready(function() {
            $(".add_ligne_order_button").on('click', function(){
                var line = $(".line_order").length
                if(line < 3){
                    $(".line_"+line).after(`
                        <div class="new_line line_order line_`+parseInt(line+1)+`  col-md-12 mt-3">
                            <label for="order_id" class="form-label">Numéro de commande</label>

                            <div class="d-flex align-item-center delete_line_order">
                                <input required value="" name="order_id[]"  type="number" class="form-control">
                                <i style="cursor:pointer" class="text-danger font-20 animated bx bx-trash-alt"></i>
                            </div>
                           
                        </div>
                    `)
                }

                if(line == 2){
                    $(".add_ligne_order_button").hide()
                }
            })

            $('body').on('click', '.delete_line_order i', function () {
                var line = $(".line_order").length
                $(".line_"+line).remove()
                $(".add_ligne_order_button").show()
            })
        })

		</script>
	@endsection


