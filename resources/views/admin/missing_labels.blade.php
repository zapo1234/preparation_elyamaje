@extends("layouts.app")

		@section("style")
			<link href="{{asset('assets/plugins/datatable/css/dataTables.bootstrap5.min.css')}}" rel="stylesheet" />
		@endsection


        @section("wrapper")
			<div class="page-wrapper">
				<div class="page-content">
                    <div class="page-breadcrumb d-sm-flex align-items-center mb-3">
						<div class="breadcrumb-title pe-3">Colissimo</div>
						<div class="ps-3">
							<nav aria-label="breadcrumb">
								<ol class="breadcrumb mb-0 p-0">
									<li class="breadcrumb-item active" aria-current="page">Étiquettes manquantes</li>
								</ol>
							</nav>
						</div>
					</div>
                


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
                                        <th>Commandes</th>
                                        <th>Date</th>
                                        <td data-label="Status">Status</td>
                                    </tr>
                                </thead>

                                <tbody>
                                    @foreach ($missingLabels as $label_order)
                                        <tr>
                                            <td data-label="Commandes">{{ $label_order }}</td>
                                            <td data-label="Commandes">{{ $orders_with_date[$label_order] }}</td>
                                            <td data-label="Status">
                                                @if(isset($labelMissingStatusArray[$label_order]))
                                                    <div class="d-flex justify-content-between align-items-center">
                                                        <i class="text-success font-30 bx bx-check"></i>
                                                        <form method="POST" action="{{ route('cancelLabelMissing') }}">
                                                            @csrf
                                                            <input type="hidden" name="order_id" value="{{ $label_order }}">
                                                            <button type="submit" class="btn btn-danger px-2">Annuler</button>
                                                        </form>
                                                    </div>
                                                @else 
                                                    <div class="d-flex justify-content-between align-items-center">
                                                        <i class="text-danger font-30 bx bx-x"></i>
                                                        <form method="POST" action="{{ route('validLabelMissing') }}">
                                                            @csrf
                                                            <input type="hidden" name="order_id" value="{{ $label_order }}">
                                                            <button type="submit" class="btn btn-success px-2">Valider</button>
                                                        </form>
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

        @endsection

@section("script")
    <script src="{{asset('assets/plugins/datatable/js/jquery.dataTables.min.js')}}"></script>
    <script src="{{asset('assets/plugins/datatable/js/dataTables.bootstrap5.min.js')}}"></script>
    <script>

		$(document).ready(function() {
            $('#example').DataTable({
                "order": [[0, 'desc']],
                "initComplete": function(settings, json) {
                    $(".loading").hide()
                    $("#example").removeClass('d-none')
                }
            })
        })

    </script>
@endsection


    