
@extends("layouts.app")

@section("style")
    <link href="{{asset('assets/plugins/datatable/css/dataTables.bootstrap5.min.css')}}" rel="stylesheet" />
    <link href="assets/plugins/select2/css/select2.min.css" rel="stylesheet" />
    <link href="assets/plugins/select2/css/select2-bootstrap4.css" rel="stylesheet" />
@endsection

@section("wrapper")
    <div class="page-wrapper">
        <div class="page-content">
      
        <div class="page-breadcrumb d-sm-flex align-items-center mb-3 justify-content-between">
            <div class="d-flex align-items-baseline">
                <div class="breadcrumb-title pe-3">Beauty Prof's</div>
                <div class="ps-3 caisse_page_details">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0 p-0">
                            <li class="breadcrumb-item active" aria-current="page">Gestion stocks</li>
                        </ol>
                    </nav>
                </div>
            </div>
            

            <div>
                <form class="change_date" action="">
                    @csrf
                    <input value="" style="cursor:pointer" class="change_date_input custom_input p-2" name="date" value="{{ date('Y-m-d') }}" type="date">
                </form>
            </div>
            <div style="width: 210px"></div>
        </div>

      

        <div class="d-flex w-100 justify-content-center">
          
                                    <!-- Décaisser -->
                                    
                                    
                                    <!-- Ajout de fonds -->
         <!-- List of all movement, pending and validate -->
        <div class="card card_table_mobile_responsive radius-10 w-100">
            <div class="card-body">
                <div class="table-responsive">
                 
                           
                </div>
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
@endsection


