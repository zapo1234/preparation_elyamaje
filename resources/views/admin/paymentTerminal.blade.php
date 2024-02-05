
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
                            <li class="breadcrumb-item active" aria-current="page">Terminaux</li>
                        </ol>
                    </nav>
                </div>
                <div class="ms-auto ms-auto-responsive">
                    <button id="show_modal_add_terminal" type="button" class="btn btn-dark px-5">Ajouter un terminal</button>
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
                        <table id="example" class="table_mobile_responsive w-100 table table-striped table-bordered">
                            <thead>
                                <tr>
                                    <th>Nom</th>
                                    <th>ID</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($terminals as $terminal)
                                    <tr>
                                        <td data-label="Nom">{{  $terminal->name }}</td>
                                        <td data-label="ID"></td>
                                    </tr>


                                    <!-- Modal update terminal -->
                                    <div class="modal modal_radius fade" id="updateTerminalModal_{{ $terminal->id }}" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered" role="document">
                                            <div class="modal-content">
                                                <form data-bitwarden-watching="1" method="POST" action="{{ route('terminal.update') }}">
                                                    @csrf
                                                    <div class="modal-body">
                                                        <div class="card-body p-3">
                                                            <div class="card-title d-flex align-items-center">
                                                                <div><i class="bx bx-printer me-1 font-22 text-primary"></i>
                                                                </div>
                                                                <h5 class="mb-0 text-primary">Modifier une imprimante</h5>
                                                                <input type="hidden" value="{{ $terminal->id }}" required name="terminal_id" id="terminal_id">
                                                            </div>
                                                            <hr>
                                                            <div class="row g-3">
                                                                <div class="col-md-12">
                                                                    <label for="update_name" class="form-label">Nom*</label>
                                                                    <input value="{{ $terminal->name }}" required name="update_name" type="text" class="form-control" id="update_name">
                                                                </div>
                                                                <!-- <div class="col-md-12">
                                                                    <label for="update_address_ip" class="form-label">Adresse IP*</label>
                                                                    <input value="{{ $terminal->address_ip }}" required name="update_address_ip" type="text" class="form-control" id="update_address_ip">
                                                                </div>
                                                                <div class="col-md-12">
                                                                    <label for="update_port" class="form-label">Port (9100 par défaut)</label>
                                                                    <input value="{{ $terminal->port }}" name="update_port" type="text" class="form-control" id="update_port">
                                                                </div> -->
                                                              
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
                                    <!-- Modal update terminal -->
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
        </div>
    </div>

    <!-- Modal ajout de terminal -->
    <div class="modal modal_radius fade" id="addTerminalModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <form data-bitwarden-watching="1" method="POST" action="{{ route('terminal.add') }}">
                    @csrf
                    <div class="modal-body">
                        <div class="card-body p-3">
                            <div class="card-title d-flex align-items-center">
                                <div><i class="bx bx-printer me-1 font-22 text-primary"></i>
                                </div>
                                <h5 class="mb-0 text-primary">Ajouter un terminal</h5>
                            </div>
                            <hr>
                            <div class="row g-3">
                                <div class="col-md-12">
                                    <label for="name" class="form-label">Nom*</label>
                                    <input required name="name" type="text" class="form-control" id="name">
                                </div>
                                <div class="col-md-12">
                                    <label for="address_ip" class="form-label">Adresse IP*</label>
                                    <input placeholder="192.168.0.0" required name="address_ip" type="text" class="form-control" id="address_ip">
                                </div>
                                <div class="col-md-12">
                                    <label for="port" class="form-label">Port</label>
                                    <input value="9100" placeholder="9100" name="port" type="text" class="form-control" id="port">
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
    <div class="modal modal_radius fade" id="deleteTerminal" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <form method="POST" action="{{ route('terminal.delete') }}">
                    @csrf
                    <div class="modal-body">
                        <h2 class="text-center">Supprimer le terminal ?</h2>
                        <input name="terminal_id" type="hidden" id="terminal_id_to_delete" value="">
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

    $("#show_modal_add_terminal").on('click', function(){
        $('#addTerminalModal').modal({
            backdrop: 'static',
            keyboard: false
        })
        $("#addTerminalModal").modal('show')
    })

    // Supprimer imprimante
    $(".delete_action").on('click', function(){
        var terminal_id = $(this).attr('data-id')
        $("#terminal_id_to_delete").val(terminal_id)
        $("#deleteTerminal").modal('show')
    })

    // Modifier imprimante
    $(".update_action").on('click', function(){
        var terminal_id = $(this).attr('data-id')
        $("#updateTerminalModal_"+terminal_id).modal('show')
    });

</script>
@endsection


