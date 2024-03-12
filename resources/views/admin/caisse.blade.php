
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
                            <li class="breadcrumb-item active" aria-current="page">Caisses</li>
                        </ol>
                    </nav>
                </div>
                <div class="ms-auto ms-auto-responsive">
                    <button id="show_modal_add_caisse" type="button" class="btn btn-dark px-5">Ajouter une caisse</button>
                </div>
            </div>


            @if($errors->any())
                <div class="alert alert-danger border-0 bg-danger alert-dismissible fade show">
                    @foreach ($errors->all() as $error)
                        <div class="text-white">{{ $error }}</div>
                    @endforeach
                </div>
            @endif

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
                        <table id="example" class="table_mobile_responsive w-100 table table-striped table-bordered">
                            <thead>
                                <tr>
                                    <th>Nom</th>
                                    <th>Identifiant</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($caisses as $caisse)
                                    <tr>
                                        <td data-label="Nom">{{  $caisse->name }}</td>
                                        <td data-label="Identifiant">{{  $caisse->uniqueId }}</td>
                                        <td class="d-flex justify-content-between" data-label="Action" >
                                            <div class="d-flex">
                                                <div data-id="{{ $caisse->id }}" class="update_action action_table font-22 text-primary">	
                                                    <i class="text-primary fadeIn animated bx bx-edit"></i>
                                                </div>
                                                <div data-id="{{ $caisse->id }}" style="margin-left:10px;" class="delete_action action_table font-22">	
                                                    <i class="text-danger fadeIn animated bx bx-trash-alt"></i>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>

                                    <!-- Modal update caisse -->
                                    <div class="modal modal_radius fade" id="updateCaisseModal_{{ $caisse->id }}" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered" role="document">
                                            <div class="modal-content">
                                                <form data-bitwarden-watching="1" method="POST" action="{{ route('admin.updateCaisse') }}">
                                                    @csrf
                                                    <div class="modal-body">
                                                        <div class="card-body p-3">
                                                            <div class="card-title d-flex align-items-center">
                                                                <div><i class="bx bx-printer me-1 font-22 text-primary"></i>
                                                                </div>
                                                                <h5 class="mb-0 text-primary">Modifier une caisse</h5>
                                                                <input type="hidden" value="{{ $caisse->id }}" required name="caisse_id" id="caisse_id">
                                                            </div>
                                                            <hr>
                                                            <div class="row g-3">
                                                                <div class="col-md-12">
                                                                    <label for="update_name" class="form-label">Nom*</label>
                                                                    <input value="{{ $caisse->name }}" required name="update_name" type="text" class="form-control" id="update_name">
                                                                </div>
                                                            </div>
                                                            <div class="row g-3 mt-2">
                                                                <div class="col-md-12">
                                                                    <label for="update_uniqueId" class="form-label">Identifiant*</label>
                                                                    <input value="{{ $caisse->uniqueId }}" required name="update_uniqueId" type="text" class="form-control" id="update_uniqueId">
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                                                        <button type="submit" class="btn btn-primary px-5">Modifier</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- Modal update caisse -->
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
        </div>
    </div>

    <!-- Modal ajout de caisse -->
    <div class="modal modal_radius fade" id="addCaisseModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <form data-bitwarden-watching="1" method="POST" action="{{ route('admin.addCaisse') }}">
                    @csrf
                    <div class="modal-body">
                        <div class="card-body p-3">
                            <div class="card-title d-flex align-items-center">
                                <div><i class="bx bx-printer me-1 font-22 text-primary"></i>
                                </div>
                                <h5 class="mb-0 text-primary">Ajouter une caisse</h5>
                            </div>
                            <hr>
                            <div class="row g-3">
                                <div class="col-md-12">
                                    <label for="name" class="form-label">Nom*</label>
                                    <input required name="name" type="text" class="form-control" id="name">
                                </div>
                            </div>
                            <div class="row g-3 mt-2">
                                <div class="col-md-12">
                                    <label for="uniqueId" class="form-label">Identifiant*</label>
                                    <input required name="uniqueId" type="text" class="form-control" id="uniqueId">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-primary px-5">Ajouter</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Suppression -->
    <div class="modal modal_radius fade" id="deleteCaisse" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <form method="POST" action="{{ route('admin.deleteCaisse') }}">
                    @csrf
                    <div class="modal-body">
                        <h2 class="text-center">Supprimer cette caisse ?</h2>
                        <input name="caisse_id" type="hidden" id="caisse_id_to_delete" value="">
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-primary">Oui</button>
                    </div>
                </form>
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
            
        })
    })

    $("#show_modal_add_caisse").on('click', function(){
        $('#addCaisseModal').modal({
            backdrop: 'static',
            keyboard: false
        })
        $("#addCaisseModal").modal('show')
    })

    // Supprimer imprimante
    $(".delete_action").on('click', function(){
        var caisse_id = $(this).attr('data-id')
        $("#caisse_id_to_delete").val(caisse_id)
        $("#deleteCaisse").modal('show')
    })

    // Modifier imprimante
    $(".update_action").on('click', function(){
        var caisse_id = $(this).attr('data-id')
        $("#updateCaisseModal_"+caisse_id).modal('show')
    });

</script>
@endsection


