
	@extends("layouts.app")

		@section("style")
			<link href="{{asset('assets/plugins/datatable/css/dataTables.bootstrap5.min.css')}}" rel="stylesheet" />
			<link href="{{('assets/plugins/select2/css/select2.min.css')}}" rel="stylesheet" />
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
									<li class="breadcrumb-item active" aria-current="page">Catégories</li>
								</ol>
							</nav>
						</div>
						<div class="ms-auto ms-auto-responsive">
							<form action="{{ route('admin.syncCategories') }}" method="get">
								@csrf
								<button type="submit" class="btn btn-dark px-5">Synchroniser les catégories</button>
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

					<!-- <div class="row"> -->
						<div class="card card_table_mobile_responsive">
							<div class="card-body">
								<div class="table-responsive">
									<table id="example" class="table_mobile_responsive w-100 table_list_order table table-striped table-bordered">
										<thead>
											<tr>
												<th>Nom</th>
												<th>Ordre (Menu & Sous-menu)</th>
											</tr>
										</thead>
										<tbody>
											@foreach ($categories as $category)
												<!-- Appeler la vue récursive pour afficher les catégories et les sous-catégories -->
												@include('partials.category', ['category' => $category])
											@endforeach
										</tbody>
									</table>
								</div>
							</div>
						</div>
					<!-- </div> -->
				</div>
			</div>
		@endsection

	
	@section("script")

		<script src="{{asset('assets/plugins/datatable/js/jquery.dataTables.min.js')}}"></script>
		<script src="{{asset('assets/plugins/datatable/js/dataTables.bootstrap5.min.js')}}"></script>
		<script src="{{asset('assets/plugins/select2/js/select2.min.js')}}"></script>
		<script>

			$('#example').DataTable({
				"pageLength": 100,
				"paging": false,
				"ordering": false,
				"info": false,
				"searching": false
			})

			$(".show_sub_category").on("click", function(){
				
				var id = $(this).attr('id')
				if($(".category_"+id).hasClass('show') || $("#"+id).hasClass('show')){
					$(this).addClass('bx-plus')
					$(this).removeClass('bx-minus')

					$(".category_"+id).removeClass('show')
					$(".category_"+id).hide()
					
					$(".td_"+id).children('div').removeClass('show')
					$(".td_"+id).children('div').hide()
				} else {
					$(this).removeClass('bx-plus')
					$(this).addClass('bx-minus')
					$(".category_"+id).addClass('show')
					$(".category_"+id).show()
				}
			})

			$(".update_order_display").on("change", function(){
				var id = $(this).attr('data-id')
				var order_display = $(this).val()
				var parent = false

				if($(this).hasClass('select_parent_menu')){
					parent = true
				}	


				$('select').each(function(index) { 
					if($(this).attr('data-parent') == id){
						$(this).val(order_display)
					}
				});
			
				
				$.ajax({
					url: "{{ route('admin.updateOrderCategory') }}",
					method: 'POST',
					data: {_token: $('input[name=_token]').val(), id: id, order_display: order_display, parent: parent}
				}).done(function(data) {
					if(JSON.parse(data).success){

					} else {
						alert('Erreur !')
					}
				});

			})	

		</script>
	@endsection


