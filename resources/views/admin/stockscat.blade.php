
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
<li class="breadcrumb-item active" aria-current="page">Gestion des stocks</li>
</ol>
</nav>
</div>
</div>

 
            <div>
</div>
<div style="width: 210px"></div>
</div>
 
                 <div class="gap-5 w-50 d-flex flex-nowrap justify-content-start align-items-center">
<h5 class="card-title text-primary text-leftfw-bolder">Kit à afficher</h5>
<div class="form-check d-flex gap-2">
<input class="form-check-input" type="checkbox" value="" id="flexCheckDefault" checked="">
<label class="form-check-label" for="flexCheckDefault">Limes</label>
</div>
<div class="form-check d-flex gap-2">
<input class="form-check-input" type="checkbox" value="" id="flexCheckChecked">
<label class="form-check-label" for="flexCheckChecked">Rapes</label>
</div></div><div class="card-body" style="background-color:white;">
   
    <div class="d-flex w-100">

     <div class="card" style="width:50%;">
     <form  method="POST" action="{{ route('admin.stockscat') }}">
     @csrf
     <table id="" style="width:100%">
      @foreach($data as $key => $val)
      <tr style="padding:3%">
      <td style="background-color:black;font-size:16px;text-transform:uppercase;color:white;width:50%;">{{  $key }} </td>
      </tr>
      @foreach($val as $kj => $vals)

      @foreach($vals as $lm => $vv)
      
      @php
      
       $lim = explode('%',$lm);
       
       $namex = explode(' ',$lim[0]);
       
        if(count($namex)==2){
          $name_list = $namex[1];
       }
       
      elseif(count($namex)==3){
          $name_list = $namex[2];
       }else{
         $name_list = $namex[3];
       }
       
       
      
      @endphp
      
      <tr>
        <td style="background-color:black;color:white;padding-bottom:5px">{{ $name_list  }}  qte(unite) {{  $lim[1] }} </td>
       </tr>
       
       @foreach($vv as $ls =>$vc)
       <tr>
           <td>{{ $vc['libelle_family'] }}  Qte :  <span style="black;font-weight:bold">{{  $vc['quantite']  }}</span>  <input type="text" name="qte[]" style="margin-left:2%" placeholder="Ajouter"> </td>
           
       </tr>
       
       @endforeach
      
      @endforeach
      @endforeach  
      @endforeach

      </table>
      <button style="position:fixed;top:50%;margin-left:53%" type="submit" class="btn btn-primary text-white" data-bs-toggle="modal" data-bs-target="">Modifier le stocks</button>
      </form>
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

