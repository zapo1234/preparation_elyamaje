
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
						<div class="ms-auto ms-auto-responsive">
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


							<table id="example" class="d-none table_mobile_responsive w-100 table_list_order table table-striped table-bordered">
								<thead>
									<tr>
										<th>Nom</th>
										<th>Prix (TTC)</th>
										<th>Code Barre</th>
										<th>Catégorie</th>
										<th>Status</th>
										<th>Stock</th>
										<th>Order</th>
										<th>Location</th>
									</tr>
								</thead>
								<tbody>
									@foreach($products as $product)
										<tr>
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
											<td>{{ $product->menu_order }}</td>
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
		@endsection

	@section("script")
		<script src="{{asset('assets/plugins/datatable/js/jquery.dataTables.min.js')}}"></script>
		<script src="{{asset('assets/plugins/datatable/js/dataTables.bootstrap5.min.js')}}"></script>
		<script src="assets/plugins/select2/js/select2.min.js"></script>
		<script src="{{asset('assets/js/product.js')}}"></script>
	@endsection


	