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
                        Beauty Prof's
                    </div>
                    <div class="ps-3">
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb mb-0 p-0">
                                <li class="breadcrumb-item active" aria-current="page">Facture BPP paris</li>
                            </ol>
                        </nav>
                    </div>

                    <div class="d-flex gap-2" style="margin-left:20px">
                        <button type="button" class="btn btn-dark" data-bs-toggle="modal" data-bs-target="#exampleDarkModal1">Envoyer une facture</button>
                       
                    </div>

                </div>    
            </div>
           

            <div class="card-body p-0" style="background-color:white;">
                <div class="d-flex w-100">
                        <form  method="POST" action="" style="overflow:hidden; width:70%;" class="radius-10">
                            @csrf
                            
                                        
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

        $(".change_date_input").on('change', function(){
            $(".change_date").submit();
        })

        $(".addMovementForm").submit(function(e){
            var caisse_id = $(this).attr('data-caisse-id')
            if(parseFloat($("#amount_"+caisse_id).val()) > parseFloat($("#amountCaisse_"+caisse_id).val())){
                e.preventDefault();
                $("#amount_"+caisse_id).css('border', '1px solid red')
                $("#exampleSmallModal_"+caisse_id+" .my-1").css('color', 'red')
            }
        });

        $(".validMovement").on('click', function(){
            $("#movement_id").val($(this).attr('data-id'))
            $("#caisse").val($(this).attr('data-name'))
            $('#validMovementModal').modal({
                backdrop: 'static',
                keyboard: false
            })
            $("#validMovementModal").modal('show')
        })

        $(".cancelMovement").on('click', function(){
            $("#cancel_movement_id").val($(this).attr('data-id'))
            $("#cancel_caisse").val($(this).attr('data-name'))
            $('#cancelMovementModal').modal({
                backdrop: 'static',
                keyboard: false
            })
            $("#cancelMovementModal").modal('show')
        })
    </script>

{{-- <script>
    $(document).ready(function() {
        $('#cofirme').click(function() {
            // Envoyer une requête Ajax vers la route
            $.ajax({
                url: '{{ route("initialQtyLot") }}',
                type: 'GET',
                dataType: 'json', // Changez ceci en fonction de votre retour de données
                success: function(response) {
                    // Traitement des données de retour si nécessaire
                    console.log(response);
                },
                error: function(xhr, status, error) {
                    // Gérer les erreurs
                    console.error('Erreur lors de la requête : ' + status);
                }
            });
        });
    });
</script> --}}


@endsection

