@extends("layouts.app")

		@section("style")
			<link href="{{asset('assets/plugins/datatable/css/dataTables.bootstrap5.min.css')}}" rel="stylesheet" />
			<link href="assets/plugins/select2/css/select2.min.css" rel="stylesheet" />
			<link href="assets/plugins/select2/css/select2-bootstrap4.css" rel="stylesheet" />
		@endsection

		@section("wrapper")
			<div class="page-wrapper">
				<div class="page-content">
					<div class="page-breadcrumb d-sm-flex align-items-center mb-2">
						<div class="breadcrumb-title pe-3">Configuration</div>
						<div class="ms-auto">
                            <div class="btn-group">
								<form action="{{ route('admin.syncCategories') }}" method="get">
									@csrf
									<button type="submit" class="btn btn-dark px-5">Synchroniser les catégories</button>
								</form>
                            </div>
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

					<div class="row">
						<div class="card card_table_mobile_responsive">
							<div class="card-body">

								<div class="d-flex justify-content-center w-100 loading"> 
									<div class="spinner-border text-dark" role="status"> <span class="visually-hidden">Loading...</span></div>
								</div>
								
								<div class="table-responsive">
									<table id="example" class="d-none w-100 table_list_order table_mobile_responsive table table-striped table-bordered">
										<thead>
											<tr>
												<th scope="col">Catégorie</th>
												<th scope="col">Identifiant</th>
												<th scope="col">Ordre</th>
											</tr>
										</thead>
										<tbody>
											@foreach($categories as $category)
												<tr> 
													<td>{{ $category['name'] }}</td>
													<td>{{ $category['category_id_woocommerce'] }}</td>
													<td>
														<select id="{{ $category['id'] }}" class="update_order_display">
															@for($i = 0; $i < count($categories); $i++)
																@if($category['order_display'] == $i)
																	<option value="{{ $i }}" selected>{{ $i }}</option>
																@else 
																	<option value="{{ $i }}">{{ $i }}</option>
																@endif
															@endfor
														</select>
													</td>
												</tr>
											@endforeach

										</tbody>
									</table>
								</div>
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
			$( document ).ready(function() {

				$("select").select2({"width" : "80px"})
				$('#example').DataTable({
					"initComplete": function(settings, json) {
						$(".loading").addClass('d-none')
						$("#example").removeClass('d-none')

					}
				})
			})

			$(".update_order_display").on("change", function(){
				var id = $(this).attr('id')
				var order_display = $(this).val()

				$.ajax({
					url: "{{ route('admin.updateOrderCategory') }}",
					method: 'POST',
					data: {_token: $('input[name=_token]').val(), id: id, order_display: order_display}
				}).done(function(data) {
					if(JSON.parse(data).success){
					
					} else {
						alert('Erreur !')
					}
				});

			})	
		
		</script>

	@endsection


