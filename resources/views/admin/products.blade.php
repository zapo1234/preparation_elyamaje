
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
						<div class="breadcrumb-title pe-3">Configuration</div>
						<div class="ps-3">
							<nav aria-label="breadcrumb">
								<ol class="breadcrumb mb-0 p-0">
									<li class="breadcrumb-item active" aria-current="page">Produits</li>
								</ol>
							</nav>
						</div>
						<div class="ms-auto ms-auto-responsive d-flex flex-wrap">
							<button disabled type="button" class="update_multiple_products btn btn-dark px-5">Modifier</button>
							<form action="{{ route('admin.syncProducts') }}" method="get">
								@csrf
								<button type="submit" class="btn btn-dark px-5">Synchroniser les produits</button>
							</form>
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

					<div class="card card_table_mobile_responsive">
						<div class="card-body">
							<div class="loading_div d-flex justify-content-center">
								<div class="loading spinner-border text-dark" role="status"> 
									<span class="visually-hidden">Loading...</span>
								</div>
							</div>

							<!-- categories -->
							<select class="d-none select2_custom category_dropdown input_form_type">
								<option value="">Catégorie</option>
									@foreach($categories as $category)
										@include('partials.category_select', ['category' => $category])
									@endforeach
							</select>

							<!-- publish -->
							<select class="d-none select2_custom product_dropdown input_form_type">
								<option value="">Status</option>
								<option value="publish">Publié</option>
								<option value="draft">Brouillon</option>
								<option value="private">Privé</option>
							</select>


							<table id="example" class="products_table d-none table_mobile_responsive w-100 table_list_order table table-striped table-bordered">
								<thead>
									<tr>
										<th>
											<span style="margin-left:3px">
												<input data-id="" class="form-check-input check_all" type="checkbox" value="" aria-label="Checkbox for product">
											</span>
										</th>
										<th>Nom</th>
										<th>Prix (TTC)</th>
										<th>Code Barre</th>
										<th>Catégorie</th>
										<th>Status</th>
										<th>Stock</th>
										<th>Location</th>
									</tr>
								</thead>
								<tbody>
									@foreach($products as $product)
										<tr>
											<td>
												<span>
													<input data-id="{{ $product->product_woocommerce_id }}" class="form-check-input checkbox_label" type="checkbox" value="" aria-label="Checkbox for product">
												</span>
											</td>
											<td data-label="Nom">{{ $product->name }}</td>
											<td data-label="Prix (TTC)">{{ $product->price }} {{ config('app.currency_symbol') }}</td>
											<td data-label="Code Barre">{{ $product->barcode != "" ?  $product->barcode : "Aucun"}}</td>
											<td data-label="Catégorie">{{ str_replace(',', ' / ', $product->category) }}</td>
											<td data-label="Status">
												<span class="badge bg-{{ $product->status }}">{{ $product->status }}</span>	
											</td>
											<td data-label="Stock">
												@if($product->manage_stock)
													@if($product->stock > 10)
														<span class="text-success">{{ $product->stock }}</span>
													@else
														<span class="text-danger">{{ $product->stock }}</span>
													@endif
												@else 
													Non géré
												@endif
											</td>
											<td data-label="Location" class="product_location">
												<div class="d-flex location_product w-100">
													<input disabled id="input_{{ $product->product_woocommerce_id }}" class="custom_input" type="text" value="{{ $product->location }}">
													<i id="edit_{{ $product->product_woocommerce_id }}" data-id="{{ $product->product_woocommerce_id }}" class="edit_product_location font-20 bx bx-edit"></i>
													<i onclick="save_location( {{ $product->product_woocommerce_id }} )" id="save_{{ $product->product_woocommerce_id }}" class="d-none font-20 fadeIn animated bx bx-save"></i>
												</div>
											</td>
										</tr>
									@endforeach
								</tbody>
							</table>
						</div>
					</div>
				</div>
			</div>


			<!-- Modal modif products -->
			<div class="modal modal_radius fade" id="updateProductsMultiple" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
				<div class="modal-dialog modal-dialog-centered" role="document">
					<div class="modal-content">
						<div class="modal-body">
							<form method="POST" action="{{ route('admin.updateProductsMultiple') }}">
								@csrf
								<input  type="hidden" id="products_id" name="products_id" value="">
								<h2 class="text-center reallocationOrdersTitle">Localisation</h2>
								<div class="mb-2 d-flex w-100 justify-content-center">
									<input class="custom_input" type="text" id="location" name="location" value="">
								</div>
								<div class="w-100 d-flex justify-content-center">
									<button type="button" class="btn btn-dark px-5" data-bs-dismiss="modal">Annuler</button>
									<button style="margin-left:15px" type="submit" class="reallocationOrdersConfirm btn btn-dark px-5 ">Valider</button>
								</div>
							</form>
						</div>
					</div>
				</div>
			</div>
		@endsection

	@section("script")
		<script src="{{asset('assets/plugins/datatable/js/jquery.dataTables.min.js')}}"></script>
		<script src="{{asset('assets/plugins/datatable/js/dataTables.bootstrap5.min.js')}}"></script>
		<script src="assets/plugins/select2/js/select2.min.js"></script>
		<script src="{{asset('assets/js/product.js')}}"></script>
	@endsection


	