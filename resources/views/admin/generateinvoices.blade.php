@extends("layouts.app")

@section("style")
    <link href="{{asset('assets/plugins/datatable/css/dataTables.bootstrap5.min.css')}}" rel="stylesheet" />
    <link href="assets/plugins/select2/css/select2.min.css" rel="stylesheet" />
    <link href="assets/plugins/select2/css/select2-bootstrap4.css" rel="stylesheet" />
    
@endsection

@section("wrapper")


    <div class="page-wrapper">
        <div class="page-content">

            <div class="d-flex w-100 justify-content-between page-breadcrumb d-sm-flex align-items-center mb-3">
                <div class="d-flex align-items-center multiple_title">
                    <div class="breadcrumb-title pe-3">
                        Gala Elyamaje
                    </div>
                    <div class="ps-3">
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb mb-0 p-0">
                                <li class="breadcrumb-item active" aria-current="page">Facture</li>
                            </ol>
                        </nav>
                    </div>

                    <div class="d-flex gap-2" style="margin-left:20px">
                        <button type="button" class="btn btn-dark" data-bs-toggle="modal" data-bs-target="#exampleDarkModal1">Envoyer une facture</button>
                        <button type="button" class="btn btn-dark" data-bs-toggle="modal" data-bs-target="#exampleDarkModals1">Renvoyer une facture</button>
                        <div id="{{ $divid }}" class="alert alert-{{ $css }}" role="alert">
                            {{ $message  }}
                          </div>
                    
                      </div>

                </div>    
            </div>
           
    {{-- Modal d'envoi de facture --}}

    <div class="modal fade" id="exampleDarkModal1" tabindex="-1" aria-hidden="true" style="display: none;">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content bg-dark">
                <div class="modal-header">
                    <h5 class="modal-title text-white">envoyer  la facture</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-white">
                    <!-- Début du formulaire -->
                    <form  method="POST" action="{{ route('admin.generatefacture') }}">
                        @csrf
                        <div class="col-md-12 mt-3">
								<label for="order_id" class="form-label">Numéro de commande</label>
									<input required value="" name="order_id" type="text" class="form-control" id="order_id">
							</div>

                            <div class="col-md-12 mt-3">
								
							<input type="hidden" name="index_value" value="xxxv1">
							</div>
                        <!-- Ajoutez d'autres champs de formulaire si nécessaire -->
                     </div>
                 <div class="modal-footer">
                    <button id="cancelle1" type="button" class="btn btn-light" data-bs-dismiss="modal">Annuler</button>
                    <button  type="submit"  class="btn btn-dark">Confirmer</button>
                    <div class="alert alert-{{ $css }}" role="alert">
                        {{ $message   }}
                    </div>
                </div>
              </form>
               <!-- Fin du formulaire -->
            </div>
        </div>
    </div>
    {{-- -------------------------------------------------------------------------------------------------------------------- --}}
@endsection


{{-- Modal de renvoi de facture --}}

<div class="modal fade" id="exampleDarkModals1" tabindex="-1" aria-hidden="true" style="display: none;">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content bg-dark">
            <div class="modal-header">
                <h5 class="modal-title text-white">Renvoyer la facture</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-white">
                <!-- Début du formulaire -->
                <form  method="POST" action="{{ route('admin.generatefactures') }}">
                    @csrf
                    <div class="col-md-12 mt-3">
                            <label for="order_id" class="form-label">Numéro de commande</label>
                                <input required value="" name="order_id" type="text" class="form-control" id="order_id">
                        </div>
                        <div class="col-md-12 mt-3">
								
									<input type="hidden"  name="index_value"  value="xxxv2">
							</div>
                    <!-- Ajoutez d'autres champs de formulaire si nécessaire -->
                 </div>
             <div class="modal-footer">
                <button id="cancelle1" type="button" class="btn btn-light" data-bs-dismiss="modal">Annuler</button>
                <button  type="submit"  class="btn btn-dark">Confirmer</button>
            </div>
          </form>
           <!-- Fin du formulaire -->
        </div>
    </div>
</div>


@section("script")
    <script src="{{asset('assets/plugins/datatable/js/jquery.dataTables.min.js')}}"></script>
	<script src="{{asset('assets/plugins/datatable/js/dataTables.bootstrap5.min.js')}}"></script>

    <script>
        $(document).ready(function() {
            $('.example').DataTable({})
           
            $('.example3').DataTable({
                "order": [[7, 'DESC']],
                "columnDefs": [
                    { "visible": false, "targets": 7 },
                ],
            })

    
            $('.example2').DataTable({
                "order": [[4, 'DESC']],
                "columnDefs": [
                    { "visible": false, "targets": 4 },
                ],
            })
        })

    
    </script>

{{-- <script>
    $(document).ready(function() {
       
    });
</script> --}}


@endsection


