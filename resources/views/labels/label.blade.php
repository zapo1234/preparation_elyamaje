
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
						<div class="breadcrumb-title pe-3">Colissimo</div>
						<div class="ps-3">
							<nav aria-label="breadcrumb">
								<ol class="breadcrumb mb-0 p-0">
									<li class="breadcrumb-item active" aria-current="page">Étiquettes</li>
								</ol>
							</nav>
						</div>
						<div class="ms-auto ms-auto-responsive">
							<button id="show_modal_bordereau" type="button" class="btn btn-dark px-5">Générer bordereau</button>
						</div>
					</div>



					<!-- Modal Génération Bordereau par date -->
					<div class="modal fade" id="modalBordereau" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
						<div class="modal-dialog modal-dialog-centered" role="document">
							<div class="modal-content">
								<form method="POST" action="{{ route('bordereau.generate') }}">
									@csrf
									<div class="modal-body">
										<h2 class="text-center">Choisir la date</h2>
										<div class="d-flex justify-content-center w-100">
											<input class="date_bordereau_input" type="date" name="date" value="{{ date('Y-m-d') }}">
										</div>
									</div>
									<div class="modal-footer">
										<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
										<button type="submit" class="btn btn-primary">Générer</button>
									</div>
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

					<div class="show_messages"></div>

					<div class="row">
						<div class="card card_table_mobile_responsive">
							<div class="card-body">
								<div class="d-flex justify-content-center">
									<div class="loading spinner-border text-dark" role="status"> 
										<span class="visually-hidden">Loading...</span>
									</div>
								</div>
								<table id="example" class="d-none table_mobile_responsive w-100 table table-striped table-bordered">
									<thead>
										<tr>
											<th>Commande</th>
											<th>Status</th>
											<th>Date</th>
											<th class="col-md-2">Étiquette</th>
										</tr>
									</thead>
									<tbody>
										@foreach($orders_labels as $key => $label)
											<tr>
												<td data-label="Commande">
													<span>{{ $label->order_woocommerce_id }}</span>
												</td>
												<td data-label="Status">
													<span class="badge bg-light-{{ $label->status }} text-light">{{ __('status.'.$label->status) }}</span>
												</td>
												<td data-label="Date">{{ date("d-m-Y", strtotime($label->date)) }}</td>
												<td data-label="Étiquette">
													@if($label->label)
													<div class="d-flex w-100 align-items-center justify-content-between">
														<div>
															<form method="POST" action="{{ route('label.show') }}">
																@csrf
																<input name="label_id" type="hidden" value="{{ $label->label_id }}">
																<button type="submit" class="download_label_button"><i class="bx bx-show-alt"></i>{{ $label->tracking_number }}</button>
															</form>
															<form method="POST" action="{{ route('label.download') }}">
																@csrf
																<input name="label_id" type="hidden" value="{{ $label->label_id }}">
																<input name="order_id" type="hidden" value="{{ $label->order_woocommerce_id }}">
																<button type="submit" class="download_label_button"><i class="bx bx-download"></i>{{ $label->tracking_number }}</button>
															</form>
														</div>
														<div>
															<button data-order="{{ $label->order_woocommerce_id }}" data-label="{{ $label->label_id }}" type="submit" class="delete_label download_label_button"><i class="bx bx-trash"></i></button>
														</div>
													</div>
													
													
													@else 
														<div>
															<button data-order="{{ $label->order_woocommerce_id }}" type="button" class="generate_label_button download_label_button"><i class="bx bx-plus"></i>Générer</button>
														</div>
													@endif
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


			<!-- Modal supression -->
			<div class="modal fade" id="deleteLabelModalCenter" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
				<div class="modal-dialog modal-dialog-centered" role="document">
					<div class="modal-content">
						<div class="modal-body">
							<h2 class="text-center">Voulez-vous supprimer cette étiquette ?</h2>
							<form method="POST" action="{{ route('label.delete') }}">
								@csrf
								<input id="label_id_to_delete" name="label_id" type="hidden" value="">
								<input id="order_id_label" name="order_id" type="hidden" value="">
								<div class="d-flex justify-content-center mt-3 w-100">
									<button type="button" class="btn btn-dark px-5" data-bs-dismiss="modal">Annuler</button>
									<button style="margin-left:15px" type="submit" class="btn btn-dark px-5">Oui</button>
								</div>
							</form>
						</div>
					</div>
				</div>
			</div>


			<!-- Modal generate label -->
			<div class="modal fade" id="generateLabelModalCenter" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
				<div class="modal-dialog modal-dialog-centered" role="document">
					<div class="modal-content">
						<div class="modal-body">
							<h2 class="text-center">Voulez-vous générer une étiquette pour cette commande ?</h2>
							<input id="order_id_to_create_label" name="order_id" type="hidden" value="">
							<div class="modal_generate_label_button d-flex justify-content-center mt-3 w-100">
								<button type="button" class="btn btn-dark px-5" data-bs-dismiss="modal">Annuler</button>
								<button style="margin-left:15px" type="submit" class="valid_generate_label btn btn-dark px-5">Oui</button>
							</div>
							<div class="d-none loading_generate_label w-100 d-flex justify-content-center">
								<div class="spinner-border text-dark" role="status"> 
									<span class="visually-hidden">Loading...</span>
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

			$(document).ready(function() {
				$('#example').DataTable({
					"order": [[3, 'desc']],
					"initComplete": function(settings, json) {
						$(".loading").hide()
						$("#example").removeClass('d-none')
					}
				})
			})

			$("#show_modal_bordereau").on('click', function(){
				$("#modalBordereau").modal('show')
			})

			$(".delete_label").on('click', function(){
				$("#label_id_to_delete").val($(this).attr('data-label'))
				$("#order_id_label").val($(this).attr('data-order'))
				$("#deleteLabelModalCenter").modal('show')
			})


			$(".generate_label_button").on('click', function(){
				$("#order_id_to_create_label").val($(this).attr('data-order'))
				$("#generateLabelModalCenter").modal('show')
			})

			$(".valid_generate_label").on('click', function(){

				$(".loading_generate_label").removeClass('d-none')
				$(".modal_generate_label_button").addClass('d-none')

				$.ajax({
                    url: "{{ route('validWrapOrder') }}",
                    metho: 'POST',
                    data : {_token: $('input[name=_token]').val(), order_id: $("#order_id_to_create_label").val(), label: true, from_label: true},
                    dataType: 'html' 
                }).done(function(data) {
                    if(JSON.parse(data).success){
						location.reload()
                    } else {
                        $(".show_messages").prepend(`
                            <div class="alert alert-danger border-0 bg-danger alert-dismissible fade show">
                                <div class=" text-white">`+JSON.parse(data).message+`</div>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        `)
                    }
					$(".loading_generate_label").addClass('d-none')
					$(".modal_generate_label_button").removeClass('d-none')
					$("#generateLabelModalCenter").modal('hide')
                })
			})

		</script>
	@endsection


