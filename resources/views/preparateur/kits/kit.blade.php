@extends("layouts.app")

@section("style")
    <link href="{{asset('assets/plugins/datatable/css/dataTables.bootstrap5.min.css')}}" rel="stylesheet" />
    <link href="{{('assets/plugins/select2/css/select2.min.css')}}" rel="stylesheet" />
    <link href="assets/plugins/select2/css/select2-bootstrap4.css" rel="stylesheet" />
@endsection 

@section("wrapper")
    <div class="page-wrapper page-preparateur-order">
        <div class="page-content">
            <div class="page-breadcrumb d-sm-flex align-items-center mb-2">
                <div class="breadcrumb-title pe-3">Préparation</div>
                <div class="ps-3">kits</div>
                <input type="hidden" value="" id="barcode">
                <input type="hidden" value="" id="barcode_verif">
                @csrf
            </div>

            <div class="wrapper_kit">
                @foreach($kits as $key => $value)
                    <div data-id="{{ $key }}" class="box card">
                        <span class="text-center">{{ $value['name'] }}</span>
                        @if($value['image'])
                            <img src="{{ asset('assets/images/products/' . $value['image']) }}"/> 
                        @else  
                            <img src="{{ asset('assets/images/products/default_product.png') }}"/>
                        @endif
                    </div>

                    <div class="modal fade modal_radius modal_kit" id="kit_category_{{ $key }}" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered modal-lg">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="exampleModalLabel">{{ $value['name'] }}</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="d-flex flex-column h-100">
                                        @if(isset($value['kits']))
                                            <div class="container_multy_step">
                                                <div class="step_form form1">
                                                    <div class="title_step_kit">Sélection du kit à préparer</div>
                                                    <table id="example" class="table_kit d-none  w-100 table table-striped table-bordered">
                                                        <thead>
                                                            <tr>
                                                                <th class="col-md-1">Kit</th>
                                                                <th class="col-md-1"></th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            @foreach($value['kits'] as $kit)
                                                                <tr class="select_kit">
                                                                    <td><span id="kit_name_{{ $kit['id'] }}" >{{ $kit['name'] }}<span></td>
                                                                    <td>
                                                                        <div class="d-flex justify-content-end w-100">
                                                                            <input data-cat="{{ $key }}" data-id="{{ $kit['id'] }}" class="form-check-input checkbox_label" type="checkbox" value="" aria-label="Checkbox for product">
                                                                        </div>
                                                                    </td>
                                                                </tr>
                                                            @endforeach
                                                        </tbody>
                                                    </table>
                                                    
                                                </div>
                                                <div class="step_form form2">
                                                    <div class="mb-4 d-flex justify-content-center align-items-center gap-4">
                                                        <div class="title_step2 title_step_kit">Sélection du kit à préparer</div>
                                                        <div class="restart_kit"><i class="lni lni-spinner-arrow"></i></div>
                                                    </div>
                                                   
                                                    @foreach($value['kits'] as $kit)
                                                        @foreach($kit['children'] as $children)
                                                            <div class="barcode_{{ $children['barcode'] }} d-none product_in_kit parent_{{ $children['parent_id'] }} d-flex justify-content-between w-100">
                                                                <div class="d-flex flex-column">
                                                                    <div class="product_name">{{ $children['label'] }}</div>
                                                                    <div style="font-size:13px" class="product_barcode">{{ $children['barcode'] }}</div>

                                                                    @if($children['barcode'])
																		<div class="d-flex">
																			<span onclick="enter_manually_barcode({{ $children['barcode']}}, {{ $children['parent_id']}})" class="manually_barcode"><i class="lni lni-keyboard"></i></span>
																			<span class="remove_{{ $children['barcode'] }}_{{ $children['parent_id'] }} remove_product" onclick="remove_product({{ $children['barcode']}} , {{ $children['parent_id'] }})"><i class="lni lni-spinner-arrow"></i></span>
																		</div>
																	@endif
                                                                </div>
                                                                <div>
                                                                    <span class="quantity_pick_in">0</span>
                                                                    <span>/</span>
                                                                    <span class="quantity_to_pick_in">{{ $children['quantity'] }}</span>
                                                                </div>
                                                            </div>
                                                        @endforeach
                                                    @endforeach
                                                </div>
                                                <div class="step_form form3">
                                                    <div class="d-flex justify-content-center mt-4 printableDiv" id="prepare_by"></div>
                                                    <div class="d-flex justify-content-center no-print col">
                                                        <!-- Détails imprimante -->
                                                        <input type="hidden" class="printer_ip"  value="{{ $printer->address_ip ?? '' }}">
                                                        <input type="hidden" class="printer_port" value="{{ $printer->port ?? ''}}">

                                                        <button type="button" class="impression_code mt-5 btn btn-dark px-5 radius-20">
                                                            <i class="bx bx-printer"></i>
                                                            <span>Imprimer</span>
                                                            <div class="d-none spinner-border spinner-border-sm" role="status"> <span class="visually-hidden">Loading...</span></div>
                                                        </button>
                                                    </div>
                                                </div>
                                                <div class="progress_container no-print">
                                                    <div class="progress"></div>
                                                    <div class="circle active_progress">1</div>
                                                    <div class="circle">2</div>
                                                    <div class="circle">3</div>

                                                </div>
                                            </div>
                                        @else 
                                            <div class="no_kit h-100 w-100 d-flex align-items-center justify-content-center">Aucun kit</div>
                                        @endif
                                    </div>
                                </div>
                                <div class="no-print modal-footer">
                                    <div class="btn_box">
                                        <button class="d-none btn btn-dark px-5 back1" type="button">Retour</button>
                                        <button class="btn btn-dark px-5 next1" type="button">Suivant</button>
                                    </div>
                                </div>
                                </form>
                            </div>
                        </div>
                    </div>
                @endforeach


                <!-- Modal info produit déjà bippé ou inexistant-->
                <div class="modal fade modal_reset_order" id="infoMessageModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered" role="document">
                        <div class="modal-content">
                            <div class="modal-body">
                                <h3 class="text-center info_message"></h3>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Fermer</button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Modal vérification quantité -->
                <div class="modal_reset_order modal_verif_order modal fade" data-order="" id="modalverification" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered" role="document">
                        <div class="modal-content">
                            <div class="modal_body_reset modal-body d-flex flex-column justify-content-center">
                                <h2 class="text-center">Attention, ce kit contient <span class="quantity_product"></span> <span class="name_quantity_product"></span></h2>
                                <span style="font-size:25px" class="mb-3 text-center">Produit(s) restant(s) à bipper : <span class="text-danger" style="font-size:30px" id="quantity_product_to_verif"></span></span>
                                <input type="hidden" value="" id="product_to_verif">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Modal vérification quantité -->
                <div class="modal_reset_order modal_verif_order modal fade" data-order="" id="modalverification2" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered" role="document">
                        <div class="modal-content">
                            <div class="modal_body_reset modal-body d-flex flex-column justify-content-center">
                                <h2 class="text-center">Attention, cette commande contient <span class="quantity_product"></span> <span class="name_quantity_product"></span></h2>
                            </div>
                            <div class="w-100 d-flex justify-content-center p-2">
                                <button type="button" class="btn btn-dark px-5" data-bs-dismiss="modal">Valider</button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Modal entrée manuelle de code barre -->
                <div class="modal fade modal_reset_order" id="modalManuallyBarcode" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered" role="document">
                        <div class="modal-content">
                            <div class="modal_body_reset modal-body d-flex flex-column justify-content-center">
                                <h2 class="text-center">Code barre</h2>
                                <input class="mt-2 mb-2 custom_input" type="text" id="barcode_manually" name="barcode_manually" value="">
                                <input type="hidden" id="product_barcode" value="">
                                <input type="hidden" id="parent_id" value="">
                                <div class="mt-3 w-100 d-flex justify-content-center">
                                    <button type="button" class="btn btn-dark px-5" data-bs-dismiss="modal">Annuler</button>
                                    <button style="margin-left:10px;" type="button" class="valid_manually_barcode btn btn-dark px-5">Valider</button>
                                </div>
                            </div>
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
    <script src="{{asset('assets/plugins/select2/js/select2.min.js')}}"></script>
    <script src="{{asset('assets/js/kit.js')}}"></script>
    <script src="{{asset('assets/js/epos-2.24.0.js')}}"></script>
@endsection