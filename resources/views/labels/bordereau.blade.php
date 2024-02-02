
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
                <div class="breadcrumb-title pe-3">Expédition</div>
                <div class="ps-3">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0 p-0">
                            <li class="breadcrumb-item active" aria-current="page">Bordereaux</li>
                        </ol>
                    </nav>
                </div>
                <div class="ms-auto ms-auto-responsive">
                    <button id="show_modal_bordereau" type="button" class="btn btn-dark px-5">Générer bordereau</button>
                </div>
            </div>


            <div class="switcher-wrapper">
                <div class="switcher-btn"> <i class="bx bx-help-circle"></i></div>
                <div class="switcher-body">
                    <div class="d-flex align-items-center">
                        <h5 class="mb-0 text-uppercase">Informations</h5>
                        <button type="button" class="btn-close ms-auto close-switcher" aria-label="Close"></button>
                    </div>
                    <hr>
                    <div class="d-flex align-items-center justify-content-between">
                        Ici, vous pouvez retrouver la liste des bordereaux et vous pouvez également en générer un avec le bouton en haut à droite
                    </div>
                </div>
            </div>


            <!-- Modal Génération Bordereau par date -->
            <div class="modal fade modal_radius" id="modalBordereau" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered" role="document">
                    <div class="modal-content">
                        <div class="modal-body">
                            <div class="d-flex w-100 justify-content-end">
                                <i style="z-index:10; cursor:pointer;position:absolute" data-bs-dismiss="modal" class="font-20 bx bx-x"></i>
                            </div>
                            <div class="container_multy_step">
                                <form method="POST" action="{{ route('bordereau.generate') }}">
                                    @csrf
                                    <div class="step_form" id="form1">
                                        <h3 class="text-dark">Bordereau</h3>
                                    
                                        <div class="w-100 d-flex justify-content-center align-items-center mb-3 mt-3">
                                        <span class="d-flex align-items-center" style="gap: 10px">
                                            <label class="text-dark font-20">Chronopost</label>
                                            <input name="origin[]" style="width:1.5em; height: 1.5em; cursor: pointer" class="form-check-input check_all" type="checkbox" value="chronopost">
                                        </span>
                                        <span class="d-flex align-items-center" style="gap: 10px; margin-left: 25px">
                                            <label class="text-dark font-20">Colissimo</label>
                                            <input name="origin[]" style="width:1.5em; height: 1.5em; cursor: pointer" class="form-check-input check_all" type="checkbox" value="colissimo">
                                        </span>
                                        </div>

                                        <div class="btn_box">
                                            <button class="btn btn-dark px-5" id="next1" type="button">Suivant</button>
                                        </div>
                                    </div>
                                    <div class="step_form" id="form2">
                                        <h3 class="text-dark">Date</h3>
                                        <div class="d-flex justify-content-center w-100">
                                            <input style="border: 1px solid black" class="date_bordereau_input" type="date" name="date" value="{{ date('Y-m-d') }}">
                                        </div>
                                        <div class="btn_box">
                                        <button class="btn btn-dark px-5" id="back1" type="button">Retour</button>
                                        <button class="btn btn-dark px-5" id="next2" type="submit">Valider</button>
                                        </div>
                                    </div>
                                </form>
                                <div class="progress_container">
                                    <div class="progress" id="progress"></div>
                                    <div class="circle active_progress">1</div>
                                    <div class="circle">2</div>
                                </div>
                            </div>
                        </div>
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


            @if(session('message'))
                <div class="alert alert-{{ session('message')['type'] != 'error' ? session('message')['type'] : 'danger' }} 
                border-0 bg-{{ session('message')['type'] != 'error' ? session('message')['type'] : 'danger' }} alert-dismissible fade show">
                    @foreach(session('message')['message'] as $key => $message)
                        <div class="text-white {{ count(session('message')['message']) > 1 ? 'mb-1' : '' }}"><span class="font-18" style="font-weight:bold;" >{{ $key }} : </span>{{ $message }}</div>
                    @endforeach
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
            
            <div class="card card_table_mobile_responsive">
                <div class="card-body">
                    <div class="d-flex justify-content-center">
                        <div class="loading spinner-border text-dark" role="status"> 
                            <span class="visually-hidden">Loading...</span>
                        </div>

                        <select name="origin" class="select2_custom type_dropdown input_form_type d-none">
                            <option value="">Type de bordereau</option>
                            <option value="colissimo">Colissimo</option>
                            <option value="chronopost">Chronopost</option>
                        </select>

                    </div>
                    <table id="example" class="d-none table_mobile_responsive w-100 table table-striped table-bordered">
                        <thead>
                            <tr>
                                <th>Généré le</th>
                                <th>Date</th>
                                <th class="col-md-2">Nombre de colis</th>
                                <th class="col-md-3">Bordereau</th>
                                <th class="col-md-3">ID</th>
                                <th class="col-md-3">Origine</th>
                            </tr>
                        </thead>
                        <tbody>

                            @foreach($bordereaux as $bordereau)
                                <tr>
                                    <td data-label="Généré le">{{ $bordereau['created_at'] }}</td>
                                    <td data-label="Date">{{ $bordereau['label_date'] }}</td>
                                    <td data-label="Nombre de commandes">{{ $bordereau['number_order'] }}</td>
                                    <td data-label="Bordereau">
                                        <div class="d-flex w-100 justify-content-between">
                                            <form method="POST" action="{{ route('bordereau.download') }}">
                                                @csrf
                                                <input name="bordereau_id" type="hidden" value="{{ $bordereau['parcel_number'] }}">
                                                <button type="submit" class="download_bordereau_button"><i class="bx bx-show-alt"></i>Bordereau n°{{ $bordereau['parcel_number'] }} <span class="label_created_at text-secondary">({{ $bordereau['label_date'] }})</span></button>
                                            </form>
                                            <div>
                                                <button title="Supprimer le bordereau" data-id="{{ $bordereau['parcel_number'] }}" type="submit" class="delete_bordereau download_label_button"><i class="bx bx-trash"></i></button>
                                            </div>
                                        </div>
                                    </td>
                                    <td data-label="ID">{{ $bordereau['bordereauId'] }}</td>
                                    <td data-label="Origine">{{ $bordereau['origin'] }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>


    	<!-- Modal supression -->
        <div class="modal modal_radius fade" id="deleteBordereauModalCenter" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-body">
                        <h2 class="text-center">Voulez-vous supprimer ce bordereau ?</h2>
                        <form method="POST" action="{{ route('bordereau.delete') }}">
                            @csrf
                            <input id="bordereau_parcel_number_to_delete" name="parcel_number" type="hidden" value="">
                            <div class="d-flex justify-content-center mt-3 w-100">
                                <button type="button" class="btn btn-dark px-5" data-bs-dismiss="modal">Annuler</button>
                                <button style="margin-left:15px" type="submit" class="btn btn-dark px-5">Oui</button>
                            </div>
                        </form>
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

        "use strict";

        const form1 = document.getElementById("form1");
        const form2 = document.getElementById("form2");
        const progressEl = document.getElementById("progress");
        const circles = document.querySelectorAll(".circle");
        let currectActive = 1;
        //============== Next Form===============
        function nextOne() {
            if($(".check_all").is(':checked')){
                $(".check_all").css('border', '1px solid black')
                form1.style.left = "-500px";
                form2.style.left = "0px";
                //next slide
                increamentNumber();
                // update progress bar
                update();
            } else {
                $(".check_all").css('border', '1px solid red')
            }
        }
        //=============== Back One==================
        function backOne() {
            form1.style.left = "0px";
            form2.style.left = "500px";
            // back slide
            decreametNumber();
            // update progress bar
            update();
        }
        //============= Progress update====================
        function update() {
            circles.forEach((circle, indx) => {
                if (indx < currectActive) {
                circle.classList.add("active_progress");
                } else {
                circle.classList.remove("active_progress");
                }
                // get all of active classes
                const active_progress = document.querySelectorAll(".active_progress");
                progressEl.style.width =
                ((active_progress.length - 1) / (circles.length - 1)) * 100 + "%";
            });
        }
        //================== Increament Number===============
        function increamentNumber() {
            // next progress number
            currectActive++;
                if (currectActive > circles.length) {
                    currectActive = circles.length;
                }
        }
        //================ Decreament Number=================
        function decreametNumber() {
            currectActive--;
                if (currectActive < 1) {
                    currectActive = 1;
                }
        }
        //================= btn Events===================
        const btnsEvents = () => {
            const next1 = document.getElementById("next1");
            const back1 = document.getElementById("back1");
            //next1
            next1.addEventListener("click", nextOne);
            // back1
            back1.addEventListener("click", backOne);
        };
        document.addEventListener("DOMContentLoaded", btnsEvents);

        $(document).ready(function() {
            $('#example').DataTable({
                "order": [[4, 'DESC']],
                "columnDefs": [
                    { "visible": false, "targets": 4 },
                    { "visible": false, "targets": 5 },
                ],
                "initComplete": function(settings, json) {
                    $(".loading").hide()
                    $("#example").removeClass('d-none')
                    $("#example_filter").parent().remove()
                    // $("#example_length select").css('margin-right', '10px')
                    $(".type_dropdown").appendTo('.dataTables_length')
                    $(".dataTables_length").css('display', 'flex')
                    $(".dataTables_length").addClass('select2_custom')
                    $(".type_dropdown").removeClass('d-none')

                    $(".type_dropdown").select2({
                        width: '175px', 
                    }); 

                    $(".select2-container").css('margin-left', '10px')
                    $(".custom_input").css('margin-left', '10px')
                }
            })
        })

        $('.type_dropdown').on('change', function(e){
			var type_dropdown = $(this).val();
			$('#example').DataTable()
			.column(5).search(type_dropdown, true, false)
			.draw();
		})

        $("#show_modal_bordereau").on('click', function(){
            $("#modalBordereau").modal('show')
        })

        $(".delete_bordereau").on('click', function(){
            $("#bordereau_parcel_number_to_delete").val($(this).attr('data-id'))
            $("#deleteBordereauModalCenter").modal('show')
        })


    </script>
@endsection


