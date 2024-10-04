@extends("layouts.app")

		@section("style")
			<link href="{{asset('assets/plugins/datatable/css/dataTables.bootstrap5.min.css')}}" rel="stylesheet" />
		@endsection


        @section("wrapper")
			<div class="page-wrapper">
				<div class="page-content">
                    <div class="page-breadcrumb d-sm-flex align-items-center mb-3">
						<div class="breadcrumb-title pe-3">Gala Elyamaje</div>
                        @csrf
						<div class="ps-3">
							<nav aria-label="breadcrumb">
								<ol class="breadcrumb mb-0 p-0">
									<li class="breadcrumb-item active" aria-current="page">Participants</li>
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
                                        <th>Nom</th>
                                        <th>Email</th>
                                        <th>Téléphone</th>
                                        <th>Code</th>
                                        <th>Roue de la fortune</th>
                                    </tr>
                                </thead>

                                <tbody>
                                    @foreach ($participants as $participant)
                                        <tr>
                                            <td data-label="Nom">{{ $participant['nom'] }}</td>
                                            <td data-label="Nom">{{ $participant['email'] }}</td>
                                            <td data-label="Nom">{{ $participant['phone'] }}</td>
                                            <td data-label="Nom"><b>{{ $participant['code_reduction'] }}</b> ({{ $participant['montant_attribue'] }}€)</td>
                                            <td data-label="Nom">{{ $participant['amount_wheel'] }}</td>

                                           
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
                "initComplete": function(settings, json) {
                    $(".loading").addClass('d-none')
                    $("#example").removeClass('d-none')
                },
            })
        })

    </script>

@endsection


    