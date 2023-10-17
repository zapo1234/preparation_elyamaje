
@extends("layouts.app")

@section("style")
    <link href="{{asset('assets/plugins/datatable/css/dataTables.bootstrap5.min.css')}}" rel="stylesheet" />
    <link href="assets/plugins/select2/css/select2.min.css" rel="stylesheet" />
    <link href="assets/plugins/select2/css/select2-bootstrap4.css" rel="stylesheet" />
    <link href="assets/css/style_reassort.css" rel="stylesheet" />

    <link href="{{asset('assets/plugins/datetimepicker/css/classic.css')}}" rel="stylesheet" />
    <link href="{{asset('assets/plugins/datetimepicker/css/classic.time.css')}}" rel="stylesheet" />
    <link href="{{asset('assets/plugins/datetimepicker/css/classic.date.css')}}" rel="stylesheet" />
    <link href="{{asset('assets/plugins/bootstrap-material-datetimepicker/css/bootstrap-material-datetimepicker.min.css')}}" rel="stylesheet" />


   
@endsection

@section("wrapper")
    <div class="page-wrapper">
        <div class="page-content">



        {{-- alert succes --}}
        <div class="alert alert-success border-0 bg-success alert-dismissible fade show alert-succes-calcul" style="display: none">

            <div class="text-white text_alert">Transfère envoyer pour préparation</div>

            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>

        </div>

        {{-- alert erreur --}}
        <div class="alert alert-danger border-0 bg-danger alert-dismissible fade show alert-danger-calcul" style="display: none">

            <div class="text-white text_alert">Erreur de transfère</div>

            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>

        </div>

        {{-- Modal de confirmation delete --}}

        <div class="modal fade" id="confirmationModal" tabindex="-1" aria-labelledby="confirmationModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="confirmationModalLabel">Confirmation de la suppression</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        Êtes-vous sûr de vouloir supprimer ce transfert ?
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                        <button type="button" class="btn btn-danger" id="confirmDelete">Confirmer la suppression</button>
                    </div>
                </div>
            </div>
        </div>

        {{-- Modal de confirmation annulation --}}

        <div class="modal fade" id="confirmationModal2" tabindex="-1" aria-labelledby="confirmationModal2Label" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="confirmationModal2Label">Confirmation de l'annulation</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        Êtes-vous sûr de vouloir annuler ce transfert ?
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                        <button type="button" class="btn btn-danger" id="confirmCancel">Confirmer l'annulation'</button>
                    </div>
                </div>
            </div>
        </div>


            
            <div class="card">
                <form method="POST" action="{{route('createReassort')}}">
                    @csrf
                    <div class="card-body row">
                        <div id="sender" class="col-md-4">

                            <label for="" class="form-label">Dépot d'éxpediteur</label>

                            <select id="entrepot_source" name="entrepot_source" class="form-select" aria-label="Default select example">
                                <option value="0" selected="">Selectionner l'entrepot à déstocker</option>
                                    @foreach ($listWarehouses as $listWarehouse)
                                        @if ($listWarehouse["id"] == "6")
                                            @if (isset($entrepot_source))
                                                @if ($listWarehouse["id"] == $entrepot_source)
                                                    <option value="{{$listWarehouse["id"]}}" selected>{{$listWarehouse["libelle"]}}</option>
                                                @else
                                                    <option value="{{$listWarehouse["id"]}}">{{$listWarehouse["libelle"]}}</option>
                                                @endif 
                                            @else  
                                                <option value="{{$listWarehouse["id"]}}">{{$listWarehouse["libelle"]}}</option>
                                            @endif
                                        @else  
                                            {{-- <option value="{{$listWarehouse["id"]}}" disabled>{{$listWarehouse["libelle"]}}</option> --}}
                                        @endif
                                    @endforeach
                            </select>
                        </div>

                        <div class="col-md-4 d-flex justify-content-center align-items-center">
                            <p class="mb-0">>>>>>>>>>>>>>>>>>>>>>></p>
                        </div>

                        <div id="recipient" class="col-md-4">
                            <label for="" class="form-label">Dépot de reception</label>

                            <select id="entrepot_destination" name="entrepot_destination" class="form-select" aria-label="Default select example">
                                <option value="0" selected="">Selectionner l'entrepot à approvisionner</option>
                                @foreach ($listWarehouses as $listWarehouse)
                                    @if ($listWarehouse["id"] == "1" || $listWarehouse["id"] == "11" || $listWarehouse["id"] == "15")
                                        @if (isset($entrepot_destination))
                                            @if ($listWarehouse["id"] == $entrepot_destination)
                                                <option value="{{$listWarehouse["id"]}}" selected>{{$listWarehouse["libelle"]}}</option>
                                            @else
                                                <option value="{{$listWarehouse["id"]}}">{{$listWarehouse["libelle"]}}</option>
                                            @endif
                                        @else  
                                            <option value="{{$listWarehouse["id"]}}">{{$listWarehouse["libelle"]}}</option>
                                        @endif
                                    @else  
                                        {{-- <option value="{{$listWarehouse["id"]}}" disabled>{{$listWarehouse["libelle"]}}</option> --}}
                                    @endif
                                    
                                @endforeach
                            </select>
                        </div>

                        <div class="col-12 d-flex justify-content-center">
                            <div class="form-check">
                                
                                @if (isset($vente_by_product) && $first_transfert)
                                    <input class="form-check-input" type="checkbox" id="first_transfert" name="first_transfert" checked>
                                @else
                                    <input class="form-check-input" type="checkbox" id="first_transfert" name="first_transfert">
                                @endif
                                
                                <label class="form-check-label" for="first_transfert">State de vente</label>
                            </div>
                        </div>

                        <div class="date_interval row">
                           
                            <div class="mb-3 col-md-3"></div>
                            
                            <div class="mb-3 col-md-2">
                                <label id="start_date" class="form-label">Date de début</label>
                                <input id="start_date_input" name="start_date" type="text" class="form-control datepicker picker__input" readonly="" aria-haspopup="true" aria-readonly="false" aria-owns="P1662151982_root"><div class="picker" id="P1662151982_root" aria-hidden="true"><div class="picker__holder" tabindex="-1"><div class="picker__frame"><div class="picker__wrap"><div class="picker__box"><div class="picker__header"><select class="picker__select--year" aria-controls="P1662151982_table" title="Select a year" disabled="disabled"><option value="2018">2018</option><option value="2019">2019</option><option value="2020">2020</option><option value="2021">2021</option><option value="2022">2022</option><option value="2023" selected="">2023</option><option value="2024">2024</option><option value="2025">2025</option><option value="2026">2026</option><option value="2027">2027</option><option value="2028">2028</option></select><select class="picker__select--month" aria-controls="P1662151982_table" title="Select a month" disabled="disabled"><option value="0">January</option><option value="1">February</option><option value="2">March</option><option value="3">April</option><option value="4">May</option><option value="5">June</option><option value="6">July</option><option value="7">August</option><option value="8">September</option><option value="9" selected="">October</option><option value="10">November</option><option value="11">December</option></select><div class="picker__nav--prev" data-nav="-1" tabindex="0" role="button" aria-controls="P1662151982_table" title="Previous month"> </div><div class="picker__nav--next" data-nav="1" tabindex="0" role="button" aria-controls="P1662151982_table" title="Next month"> </div></div><table class="picker__table" id="P1662151982_table" role="grid" aria-controls="P1662151982" aria-readonly="true"><thead><tr><th class="picker__weekday" scope="col" title="Sunday">Sun</th><th class="picker__weekday" scope="col" title="Monday">Mon</th><th class="picker__weekday" scope="col" title="Tuesday">Tue</th><th class="picker__weekday" scope="col" title="Wednesday">Wed</th><th class="picker__weekday" scope="col" title="Thursday">Thu</th><th class="picker__weekday" scope="col" title="Friday">Fri</th><th class="picker__weekday" scope="col" title="Saturday">Sat</th></tr></thead><tbody><tr><td><div class="picker__day picker__day--infocus" data-pick="1696111200000" id="P1662151982_1696111200000" tabindex="0" role="gridcell" aria-label="1 October, 2023">1</div></td><td><div class="picker__day picker__day--infocus" data-pick="1696197600000" id="P1662151982_1696197600000" tabindex="0" role="gridcell" aria-label="2 October, 2023">2</div></td><td><div class="picker__day picker__day--infocus" data-pick="1696284000000" id="P1662151982_1696284000000" tabindex="0" role="gridcell" aria-label="3 October, 2023">3</div></td><td><div class="picker__day picker__day--infocus" data-pick="1696370400000" id="P1662151982_1696370400000" tabindex="0" role="gridcell" aria-label="4 October, 2023">4</div></td><td><div class="picker__day picker__day--infocus" data-pick="1696456800000" id="P1662151982_1696456800000" tabindex="0" role="gridcell" aria-label="5 October, 2023">5</div></td><td><div class="picker__day picker__day--infocus" data-pick="1696543200000" id="P1662151982_1696543200000" tabindex="0" role="gridcell" aria-label="6 October, 2023">6</div></td><td><div class="picker__day picker__day--infocus" data-pick="1696629600000" id="P1662151982_1696629600000" tabindex="0" role="gridcell" aria-label="7 October, 2023">7</div></td></tr><tr><td><div class="picker__day picker__day--infocus" data-pick="1696716000000" id="P1662151982_1696716000000" tabindex="0" role="gridcell" aria-label="8 October, 2023">8</div></td><td><div class="picker__day picker__day--infocus" data-pick="1696802400000" id="P1662151982_1696802400000" tabindex="0" role="gridcell" aria-label="9 October, 2023">9</div></td><td><div class="picker__day picker__day--infocus" data-pick="1696888800000" id="P1662151982_1696888800000" tabindex="0" role="gridcell" aria-label="10 October, 2023">10</div></td><td><div class="picker__day picker__day--infocus" data-pick="1696975200000" id="P1662151982_1696975200000" tabindex="0" role="gridcell" aria-label="11 October, 2023">11</div></td><td><div class="picker__day picker__day--infocus" data-pick="1697061600000" id="P1662151982_1697061600000" tabindex="0" role="gridcell" aria-label="12 October, 2023">12</div></td><td><div class="picker__day picker__day--infocus picker__day--today" data-pick="1697148000000" id="P1662151982_1697148000000" tabindex="0" role="gridcell" aria-label="13 October, 2023">13</div></td><td><div class="picker__day picker__day--infocus" data-pick="1697234400000" id="P1662151982_1697234400000" tabindex="0" role="gridcell" aria-label="14 October, 2023">14</div></td></tr><tr><td><div class="picker__day picker__day--infocus" data-pick="1697320800000" id="P1662151982_1697320800000" tabindex="0" role="gridcell" aria-label="15 October, 2023">15</div></td><td><div class="picker__day picker__day--infocus" data-pick="1697407200000" id="P1662151982_1697407200000" tabindex="0" role="gridcell" aria-label="16 October, 2023">16</div></td><td><div class="picker__day picker__day--infocus" data-pick="1697493600000" id="P1662151982_1697493600000" tabindex="0" role="gridcell" aria-label="17 October, 2023">17</div></td><td><div class="picker__day picker__day--infocus" data-pick="1697580000000" id="P1662151982_1697580000000" tabindex="0" role="gridcell" aria-label="18 October, 2023">18</div></td><td><div class="picker__day picker__day--infocus" data-pick="1697666400000" id="P1662151982_1697666400000" tabindex="0" role="gridcell" aria-label="19 October, 2023">19</div></td><td><div class="picker__day picker__day--infocus picker__day--selected picker__day--highlighted" data-pick="1697752800000" id="P1662151982_1697752800000" tabindex="0" role="gridcell" aria-label="20 October, 2023" aria-selected="true" aria-activedescendant="1697752800000">20</div></td><td><div class="picker__day picker__day--infocus" data-pick="1697839200000" id="P1662151982_1697839200000" tabindex="0" role="gridcell" aria-label="21 October, 2023">21</div></td></tr><tr><td><div class="picker__day picker__day--infocus" data-pick="1697925600000" id="P1662151982_1697925600000" tabindex="0" role="gridcell" aria-label="22 October, 2023">22</div></td><td><div class="picker__day picker__day--infocus" data-pick="1698012000000" id="P1662151982_1698012000000" tabindex="0" role="gridcell" aria-label="23 October, 2023">23</div></td><td><div class="picker__day picker__day--infocus" data-pick="1698098400000" id="P1662151982_1698098400000" tabindex="0" role="gridcell" aria-label="24 October, 2023">24</div></td><td><div class="picker__day picker__day--infocus" data-pick="1698184800000" id="P1662151982_1698184800000" tabindex="0" role="gridcell" aria-label="25 October, 2023">25</div></td><td><div class="picker__day picker__day--infocus" data-pick="1698271200000" id="P1662151982_1698271200000" tabindex="0" role="gridcell" aria-label="26 October, 2023">26</div></td><td><div class="picker__day picker__day--infocus" data-pick="1698357600000" id="P1662151982_1698357600000" tabindex="0" role="gridcell" aria-label="27 October, 2023">27</div></td><td><div class="picker__day picker__day--infocus" data-pick="1698444000000" id="P1662151982_1698444000000" tabindex="0" role="gridcell" aria-label="28 October, 2023">28</div></td></tr><tr><td><div class="picker__day picker__day--infocus" data-pick="1698530400000" id="P1662151982_1698530400000" tabindex="0" role="gridcell" aria-label="29 October, 2023">29</div></td><td><div class="picker__day picker__day--infocus" data-pick="1698620400000" id="P1662151982_1698620400000" tabindex="0" role="gridcell" aria-label="30 October, 2023">30</div></td><td><div class="picker__day picker__day--infocus" data-pick="1698706800000" id="P1662151982_1698706800000" tabindex="0" role="gridcell" aria-label="31 October, 2023">31</div></td><td><div class="picker__day picker__day--outfocus" data-pick="1698793200000" id="P1662151982_1698793200000" tabindex="0" role="gridcell" aria-label="1 November, 2023">1</div></td><td><div class="picker__day picker__day--outfocus" data-pick="1698879600000" id="P1662151982_1698879600000" tabindex="0" role="gridcell" aria-label="2 November, 2023">2</div></td><td><div class="picker__day picker__day--outfocus" data-pick="1698966000000" id="P1662151982_1698966000000" tabindex="0" role="gridcell" aria-label="3 November, 2023">3</div></td><td><div class="picker__day picker__day--outfocus" data-pick="1699052400000" id="P1662151982_1699052400000" tabindex="0" role="gridcell" aria-label="4 November, 2023">4</div></td></tr><tr><td><div class="picker__day picker__day--outfocus" data-pick="1699138800000" id="P1662151982_1699138800000" tabindex="0" role="gridcell" aria-label="5 November, 2023">5</div></td><td><div class="picker__day picker__day--outfocus" data-pick="1699225200000" id="P1662151982_1699225200000" tabindex="0" role="gridcell" aria-label="6 November, 2023">6</div></td><td><div class="picker__day picker__day--outfocus" data-pick="1699311600000" id="P1662151982_1699311600000" tabindex="0" role="gridcell" aria-label="7 November, 2023">7</div></td><td><div class="picker__day picker__day--outfocus" data-pick="1699398000000" id="P1662151982_1699398000000" tabindex="0" role="gridcell" aria-label="8 November, 2023">8</div></td><td><div class="picker__day picker__day--outfocus" data-pick="1699484400000" id="P1662151982_1699484400000" tabindex="0" role="gridcell" aria-label="9 November, 2023">9</div></td><td><div class="picker__day picker__day--outfocus" data-pick="1699570800000" id="P1662151982_1699570800000" tabindex="0" role="gridcell" aria-label="10 November, 2023">10</div></td><td><div class="picker__day picker__day--outfocus" data-pick="1699657200000" id="P1662151982_1699657200000" tabindex="0" role="gridcell" aria-label="11 November, 2023">11</div></td></tr></tbody></table><div class="picker__footer"><button class="picker__button--today" type="button" data-pick="1697148000000" aria-controls="P1662151982" disabled="disabled">Today</button><button class="picker__button--clear" type="button" data-clear="1" aria-controls="P1662151982" disabled="disabled">Clear</button><button class="picker__button--close" type="button" data-close="true" aria-controls="P1662151982" disabled="disabled">Close</button></div></div></div></div></div></div>
                            </div>

                            <div class="mb-3 col-md-1"></div>

                            <div class="mb-3 col-md-1">
                                {{-- style="color:#fff" --}}
                                <label id="semaine" class="form-label" >Semaines</label>
                                <input id="semaine_input" name="semaine" type="text" class="form-control">
                            </div>
                            <div class="mb-3 col-md-1"></div>

                            <div class="mb-3 col-md-2">
                                <label id="end_date" class="form-label">Date de fin</label>
                                <input id="end_date_input" name="end_date" type="text" class="form-control datepicker picker__input" readonly="" aria-haspopup="true" aria-readonly="false" aria-owns="P1662151982_root"><div class="picker" id="P1662151982_root" aria-hidden="true"><div class="picker__holder" tabindex="-1"><div class="picker__frame"><div class="picker__wrap"><div class="picker__box"><div class="picker__header"><select class="picker__select--year" aria-controls="P1662151982_table" title="Select a year" disabled="disabled"><option value="2018">2018</option><option value="2019">2019</option><option value="2020">2020</option><option value="2021">2021</option><option value="2022">2022</option><option value="2023" selected="">2023</option><option value="2024">2024</option><option value="2025">2025</option><option value="2026">2026</option><option value="2027">2027</option><option value="2028">2028</option></select><select class="picker__select--month" aria-controls="P1662151982_table" title="Select a month" disabled="disabled"><option value="0">January</option><option value="1">February</option><option value="2">March</option><option value="3">April</option><option value="4">May</option><option value="5">June</option><option value="6">July</option><option value="7">August</option><option value="8">September</option><option value="9" selected="">October</option><option value="10">November</option><option value="11">December</option></select><div class="picker__nav--prev" data-nav="-1" tabindex="0" role="button" aria-controls="P1662151982_table" title="Previous month"> </div><div class="picker__nav--next" data-nav="1" tabindex="0" role="button" aria-controls="P1662151982_table" title="Next month"> </div></div><table class="picker__table" id="P1662151982_table" role="grid" aria-controls="P1662151982" aria-readonly="true"><thead><tr><th class="picker__weekday" scope="col" title="Sunday">Sun</th><th class="picker__weekday" scope="col" title="Monday">Mon</th><th class="picker__weekday" scope="col" title="Tuesday">Tue</th><th class="picker__weekday" scope="col" title="Wednesday">Wed</th><th class="picker__weekday" scope="col" title="Thursday">Thu</th><th class="picker__weekday" scope="col" title="Friday">Fri</th><th class="picker__weekday" scope="col" title="Saturday">Sat</th></tr></thead><tbody><tr><td><div class="picker__day picker__day--infocus" data-pick="1696111200000" id="P1662151982_1696111200000" tabindex="0" role="gridcell" aria-label="1 October, 2023">1</div></td><td><div class="picker__day picker__day--infocus" data-pick="1696197600000" id="P1662151982_1696197600000" tabindex="0" role="gridcell" aria-label="2 October, 2023">2</div></td><td><div class="picker__day picker__day--infocus" data-pick="1696284000000" id="P1662151982_1696284000000" tabindex="0" role="gridcell" aria-label="3 October, 2023">3</div></td><td><div class="picker__day picker__day--infocus" data-pick="1696370400000" id="P1662151982_1696370400000" tabindex="0" role="gridcell" aria-label="4 October, 2023">4</div></td><td><div class="picker__day picker__day--infocus" data-pick="1696456800000" id="P1662151982_1696456800000" tabindex="0" role="gridcell" aria-label="5 October, 2023">5</div></td><td><div class="picker__day picker__day--infocus" data-pick="1696543200000" id="P1662151982_1696543200000" tabindex="0" role="gridcell" aria-label="6 October, 2023">6</div></td><td><div class="picker__day picker__day--infocus" data-pick="1696629600000" id="P1662151982_1696629600000" tabindex="0" role="gridcell" aria-label="7 October, 2023">7</div></td></tr><tr><td><div class="picker__day picker__day--infocus" data-pick="1696716000000" id="P1662151982_1696716000000" tabindex="0" role="gridcell" aria-label="8 October, 2023">8</div></td><td><div class="picker__day picker__day--infocus" data-pick="1696802400000" id="P1662151982_1696802400000" tabindex="0" role="gridcell" aria-label="9 October, 2023">9</div></td><td><div class="picker__day picker__day--infocus" data-pick="1696888800000" id="P1662151982_1696888800000" tabindex="0" role="gridcell" aria-label="10 October, 2023">10</div></td><td><div class="picker__day picker__day--infocus" data-pick="1696975200000" id="P1662151982_1696975200000" tabindex="0" role="gridcell" aria-label="11 October, 2023">11</div></td><td><div class="picker__day picker__day--infocus" data-pick="1697061600000" id="P1662151982_1697061600000" tabindex="0" role="gridcell" aria-label="12 October, 2023">12</div></td><td><div class="picker__day picker__day--infocus picker__day--today" data-pick="1697148000000" id="P1662151982_1697148000000" tabindex="0" role="gridcell" aria-label="13 October, 2023">13</div></td><td><div class="picker__day picker__day--infocus" data-pick="1697234400000" id="P1662151982_1697234400000" tabindex="0" role="gridcell" aria-label="14 October, 2023">14</div></td></tr><tr><td><div class="picker__day picker__day--infocus" data-pick="1697320800000" id="P1662151982_1697320800000" tabindex="0" role="gridcell" aria-label="15 October, 2023">15</div></td><td><div class="picker__day picker__day--infocus" data-pick="1697407200000" id="P1662151982_1697407200000" tabindex="0" role="gridcell" aria-label="16 October, 2023">16</div></td><td><div class="picker__day picker__day--infocus" data-pick="1697493600000" id="P1662151982_1697493600000" tabindex="0" role="gridcell" aria-label="17 October, 2023">17</div></td><td><div class="picker__day picker__day--infocus" data-pick="1697580000000" id="P1662151982_1697580000000" tabindex="0" role="gridcell" aria-label="18 October, 2023">18</div></td><td><div class="picker__day picker__day--infocus" data-pick="1697666400000" id="P1662151982_1697666400000" tabindex="0" role="gridcell" aria-label="19 October, 2023">19</div></td><td><div class="picker__day picker__day--infocus picker__day--selected picker__day--highlighted" data-pick="1697752800000" id="P1662151982_1697752800000" tabindex="0" role="gridcell" aria-label="20 October, 2023" aria-selected="true" aria-activedescendant="1697752800000">20</div></td><td><div class="picker__day picker__day--infocus" data-pick="1697839200000" id="P1662151982_1697839200000" tabindex="0" role="gridcell" aria-label="21 October, 2023">21</div></td></tr><tr><td><div class="picker__day picker__day--infocus" data-pick="1697925600000" id="P1662151982_1697925600000" tabindex="0" role="gridcell" aria-label="22 October, 2023">22</div></td><td><div class="picker__day picker__day--infocus" data-pick="1698012000000" id="P1662151982_1698012000000" tabindex="0" role="gridcell" aria-label="23 October, 2023">23</div></td><td><div class="picker__day picker__day--infocus" data-pick="1698098400000" id="P1662151982_1698098400000" tabindex="0" role="gridcell" aria-label="24 October, 2023">24</div></td><td><div class="picker__day picker__day--infocus" data-pick="1698184800000" id="P1662151982_1698184800000" tabindex="0" role="gridcell" aria-label="25 October, 2023">25</div></td><td><div class="picker__day picker__day--infocus" data-pick="1698271200000" id="P1662151982_1698271200000" tabindex="0" role="gridcell" aria-label="26 October, 2023">26</div></td><td><div class="picker__day picker__day--infocus" data-pick="1698357600000" id="P1662151982_1698357600000" tabindex="0" role="gridcell" aria-label="27 October, 2023">27</div></td><td><div class="picker__day picker__day--infocus" data-pick="1698444000000" id="P1662151982_1698444000000" tabindex="0" role="gridcell" aria-label="28 October, 2023">28</div></td></tr><tr><td><div class="picker__day picker__day--infocus" data-pick="1698530400000" id="P1662151982_1698530400000" tabindex="0" role="gridcell" aria-label="29 October, 2023">29</div></td><td><div class="picker__day picker__day--infocus" data-pick="1698620400000" id="P1662151982_1698620400000" tabindex="0" role="gridcell" aria-label="30 October, 2023">30</div></td><td><div class="picker__day picker__day--infocus" data-pick="1698706800000" id="P1662151982_1698706800000" tabindex="0" role="gridcell" aria-label="31 October, 2023">31</div></td><td><div class="picker__day picker__day--outfocus" data-pick="1698793200000" id="P1662151982_1698793200000" tabindex="0" role="gridcell" aria-label="1 November, 2023">1</div></td><td><div class="picker__day picker__day--outfocus" data-pick="1698879600000" id="P1662151982_1698879600000" tabindex="0" role="gridcell" aria-label="2 November, 2023">2</div></td><td><div class="picker__day picker__day--outfocus" data-pick="1698966000000" id="P1662151982_1698966000000" tabindex="0" role="gridcell" aria-label="3 November, 2023">3</div></td><td><div class="picker__day picker__day--outfocus" data-pick="1699052400000" id="P1662151982_1699052400000" tabindex="0" role="gridcell" aria-label="4 November, 2023">4</div></td></tr><tr><td><div class="picker__day picker__day--outfocus" data-pick="1699138800000" id="P1662151982_1699138800000" tabindex="0" role="gridcell" aria-label="5 November, 2023">5</div></td><td><div class="picker__day picker__day--outfocus" data-pick="1699225200000" id="P1662151982_1699225200000" tabindex="0" role="gridcell" aria-label="6 November, 2023">6</div></td><td><div class="picker__day picker__day--outfocus" data-pick="1699311600000" id="P1662151982_1699311600000" tabindex="0" role="gridcell" aria-label="7 November, 2023">7</div></td><td><div class="picker__day picker__day--outfocus" data-pick="1699398000000" id="P1662151982_1699398000000" tabindex="0" role="gridcell" aria-label="8 November, 2023">8</div></td><td><div class="picker__day picker__day--outfocus" data-pick="1699484400000" id="P1662151982_1699484400000" tabindex="0" role="gridcell" aria-label="9 November, 2023">9</div></td><td><div class="picker__day picker__day--outfocus" data-pick="1699570800000" id="P1662151982_1699570800000" tabindex="0" role="gridcell" aria-label="10 November, 2023">10</div></td><td><div class="picker__day picker__day--outfocus" data-pick="1699657200000" id="P1662151982_1699657200000" tabindex="0" role="gridcell" aria-label="11 November, 2023">11</div></td></tr></tbody></table><div class="picker__footer"><button class="picker__button--today" type="button" data-pick="1697148000000" aria-controls="P1662151982" disabled="disabled">Today</button><button class="picker__button--clear" type="button" data-clear="1" aria-controls="P1662151982" disabled="disabled">Clear</button><button class="picker__button--close" type="button" data-close="true" aria-controls="P1662151982" disabled="disabled">Close</button></div></div></div></div></div></div>
                            </div>

                            <div class="mb-3 col-md-3"></div>

                        </div>

                        <div class="col-12 d-flex justify-content-center mt-5">
                            <button id="id_sub_calcul_reassort" onclick="this.disabled=true;this.form.submit();" class="btn btn-primary" type="submit">Générer le réassort</button>
                        </div>

                        

                    </div>

                </form>
               
            </div>


            @if (isset($vente_by_product) && $first_transfert)
               
                {{-- {{dd($first_transfert)}} --}}

                <div id="id_first_reassort" class="card card_product_commande">
                    <div class="table-responsive p-3">
                        <table id="example5" class="table mb-0 dataTable">
                            <thead>
                                <tr>
                                    <th title="L'entrepôt qui va être décrémenté">ID</th>
                                    <th title="L'entrepôt qui va être décrémenté">Libelle</th>
                                    <th title="L'entrepôt qui va être décrémenté">Quantité vendu</th>
                                    <th title="L'entrepôt qui va être décrémenté">Catégorie</th>
                                </tr>
                            </thead>
                            <tbody id="tbody_id_1">
                                @foreach ($vente_by_product as $id_product => $value)
                            
                                    <tr class="class_line1">
                                        
                                        
                                        <td data-key="product_id" data-value="{{$id_product}}" id="{{$id_product}}_product_id" style="text-align: left !important;">{{$id_product}}</td>
                                        <td data-key="libelle" data-value="{{$value["libelle"]}}" id="{{$id_product}}_libelle" style="text-align: left !important;">{{$value["libelle"]}}</td>
                                        <td data-key="qty" data-value="{{$value["qty"]}}" id="{{$id_product}}_qty" style="text-align: left !important;">{{$value["qty"]}}</td>
                                        <td data-key="label_cat" data-value="{{$value["label_cat"]}}" id="{{$id_product}}_label_cat" style="text-align: left !important;">{{$value["label_cat"]}}</td>
                                        
                                    </tr>
                                
                                @endforeach
    
                            </tbody>
    
                        
    
                        </table>
                    </div>
    
                </div>
            @endif


            @if (isset($products_reassort))


            <div id="id_reassor1" class="card card_product_commande">
                <div class="table-responsive p-3">
                    <table id="example2" class="table mb-0 dataTable">
                        <thead>
                            <tr>
                                <th title="L'entrepôt qui va être décrémenté">ID</th>
                                <th title="L'entrepôt qui va être décrémenté">Code barre</th>
                                <th title="L'entrepôt qui va être décrémenté">Nom produit</th>
                                <th title="L'entrepôt qui va être décrémenté">Prix d'achat unitaire</th>
                                <th title="L'entrepôt qui va être décrémenté">Entrepôt source (Qté)</th>
                                <th title="Points actuellement valide de l'utilisateur">Demande/sem</th>
                                <th title="L'entrepôt qui va être alimenter">Entrepôt de destination (Qté)</th>
                                <th title="Points actuellement valide de l'utilisateur">Qté souhaité</th>

                                <th title="Points actuellement valide de l'utilisateur">Qté a transférer</th>
                                <th title="Points actuellement valide de l'utilisateur">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="tbody_id_1">
                            @foreach ($products_reassort as $k1 => $value)
                        
                                <tr data_id_product="{{$value["product_id"]}}" id="{{$value["product_id"]}}_line" class="class_line1">
                                    <td data-key="product_id" data-value="{{$value["product_id"]}}" id="{{$value["product_id"]}}_product_id" style="text-align: left !important;">{{$value["product_id"]}}</td>
                                    <td data-key="barcode" data-value="{{$value["barcode"]}}" id="{{$value["barcode"]}}_product_id" style="text-align: left !important;">{{$value["barcode"]}}</td>

                                    <td data-key="libelle" data-value="{{$value["libelle"]}}" id="{{$value["product_id"]}}_libelle" style="text-align: left !important;">{{$value["libelle"]}}</td>
                                    <td data-key="price" data-value="{{$value["price"]}}" id="{{$value["price"]}}_price" style="text-align: left !important;">{{round($value["price"],2)}}</td>

                                    @if ($value["qte_en_stock_in_source"] <= 0)
                                        <td class="error_stock" title="Il semble y avoir une erreur dans ce stock" data-key="qte_en_stock_in_source" data-value="{{$value["qte_en_stock_in_source"]}}" id="{{$value["product_id"]}}_qte_en_stock_in_source" style="text-align: left !important;">{{$value["name_entrepot_a_destocker"]}} ({{$value["qte_en_stock_in_source"]}})</td>
                                    @else
                                        <td class="" data-key="qte_en_stock_in_source" data-value="{{$value["qte_en_stock_in_source"]}}" id="{{$value["product_id"]}}_qte_en_stock_in_source" style="text-align: left !important;">{{$value["name_entrepot_a_destocker"]}} ({{$value["qte_en_stock_in_source"]}})</td>
                                    @endif
                                    
                                    <td data-key="demande" data-value="{{$value["demande"]}}" id="{{$value["product_id"]}}_demande">{{$value["demande"]}}</td>
                                    

                                    @if ($value["qte_act"] < 0)
                                        <td class="error_stock" title="Il semble y avoir une erreur dans ce stock" data-key="qte_act" data-value="{{$value["qte_act"]}}" id="{{$value["product_id"]}}_qte_act">{{$value["entrepot_a_alimenter"]}} ({{$value["qte_act"]}})</td>
                                    @else
                                        <td data-key="qte_act" data-value="{{$value["qte_act"]}}" id="{{$value["product_id"]}}_qte_act">{{$value["entrepot_a_alimenter"]}} ({{$value["qte_act"]}})</td>
                                    @endif
                                    <td data-key="qte_optimale" data-value="{{$value["qte_optimale"]}}" id="{{$value["product_id"]}}_qte_optimale">{{$value["qte_optimale"]}}</td>




                                    @if ($value["qte_act"] < 0 || $value["qte_en_stock_in_source"] <= 0)
                                        <td data-key="qte_transfere" data-value="0" id="{{$value["product_id"]}}_qte_transfere"><input class="text-center" style="width: 50px" type="text" value="0" disabled></td>
                                    @else
                                        @if ((($value["qte_optimale"] - $value["qte_act"])/($value["qte_en_stock_in_source"]))>0.2)
                                        {{-- Quantité demandée trop elevée on donne juste 20% de la reserve --}}
                                        <td data-key="qte_transfere" data-value="{{($value["qte_en_stock_in_source"])*0.2}}" id="{{$value["product_id"]}}_qte_transfere"><input class="text-center" style="width: 50px" type="text" value="{{($value["qte_optimale"] - $value["qte_act"])*0.2}}" disabled></td>
                                        @else
                                        <td data-key="qte_transfere" data-value="{{$value["qte_en_stock_in_source"]}}" id="{{$value["product_id"]}}_qte_transfere"><input class="text-center" style="width: 50px" type="text" value="{{$value["qte_optimale"] - $value["qte_act"]}}" disabled></td>
                                        @endif

                                    @endif


                                    




                                    
                                    <td data-key="action" id="{{$value["product_id"]}}_action">
                                        <button onclick='delete_line({{$value["product_id"]}})' type="button" class="btn" title="Supprimer l'offre" style="margin: 0;padding: 0;" class="update_line">
                                            <a class="" title="Supprimer l'offre" href="javascript:void(0)">
                                                <i class="fadeIn animated bx bx-trash"></i>
                                            </a>
                                        </button>
                                        <button onclick='update_line({{$value["product_id"]}})' type="button" class="btn" title="Supprimer l'offre" style="margin: 0;padding: 0;" class="remove_line">
                                            <a class="" title="Supprimer l'offre" href="javascript:void(0)">
                                                <i class="fadeIn animated bx bxs-edit"></i>
                                            </a>
                                        </button>
                                    </td>

                                    
                                    
                                </tr>
                            
                            @endforeach

                        </tbody>

                    

                    </table>
                </div>

                <div class="row">
                    <div class="col-md-3"></div>

                    <div class="col-md-2 d-flex justify-content-center mt-5">
                        <button id="id_sub_validation_reassort" onclick="valide_reassort1()" class="btn btn-primary mb-4" type="submit">Valider le réassort</button>
                    </div>

                    <div class="col-md-4 d-flex justify-content-center" style="margin-top: 3.3rem!important;">
                        <select id="user_selected" class="js-states form-control" name="user_selected">
                            <option style="width:100%" value="" selected>Selectionnez l'utilisateur</option>
                            @foreach($users as $u => $user) 
                                <option value="{{$user["id"]}}"> {{$user["name"]}} </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-3"></div>

                </div>


                <div class="row g-3 mb-4">
                    <div class="col-md-2"></div>

                    <div class="col-md-6">

                        <select id="product_add" class="js-states form-control" name="email">
                            <option style="width:100%" value="" selected>Selectionnez le produit</option>
                            @foreach($entrepot_source_product as $k => $val) 
                                <option value="{{json_encode($val)}}"> {{$val["libelle"]}} </option>
                            @endforeach
                        </select>

                    </div>
                    <div class="col-md-2">
                        <input id="qte_id" class="qte_class" type="text" placeholder="Quantité">
                    </div>
                    
                    <div class="font-22 text-primary col-md-2">	
                        <i onclick="add_line()" class="lni lni-circle-plus"></i>
                    </div>
                </div>



            </div>

            <div id="id_reassor2" class="card card_product_no_commande d-none">
                <div class="table-responsive p-3">

                    <div>--------------------------------------------------------------------------------------</div>

                    <table id="example3" class="table mb-0 dataTable">
                        <thead>
                            <tr>
                                <th title="L'entrepôt qui va être décrémenté">ID</th>
                                <th title="L'entrepôt qui va être décrémenté">Code barre</th>
                                <th title="L'entrepôt qui va être décrémenté">Nom produit</th>
                                <th title="L'entrepôt qui va être décrémenté">Prix d'achat unitaire</th>
                                <th title="L'entrepôt qui va être décrémenté">Entrepôt source (Qté)</th>
                                <th title="Points actuellement valide de l'utilisateur">Demande/sem</th>
                                <th title="L'entrepôt qui va être alimenter">Entrepôt de destination (Qté)</th>
                                <th title="Points actuellement valide de l'utilisateur">Qté souhaité</th>

                                <th title="Points actuellement valide de l'utilisateur">Qté a transférer</th>
                                <th title="Points actuellement valide de l'utilisateur">Actions</th>
                            </tr>
                        </thead>  
                            @foreach ($products_non_vendu_in_last_month_inf_5 as $k2 => $value2)
                                <tr data_id_product="{{$value2["product_id"]}}" id="{{$value2["product_id"]}}_line"  class="class_line2">

                                    <td data-key="product_id" data-value="{{$value2["product_id"]}}" id="{{$value2["product_id"]}}_product_id" style="text-align: left !important;">{{$value2["product_id"]}}</td>
                                    <td data-key="barcode" data-value="{{$value2["barcode"]}}" id="{{$value2["barcode"]}}_product_id" style="text-align: left !important;">{{$value2["barcode"]}}</td>

                                    <td data-key="libelle" data-value="{{$value2["libelle"]}}" id="{{$value2["product_id"]}}_libelle" style="text-align: left !important;">{{$value2["libelle"]}}</td>
                                    <td data-key="price" data-value="{{$value2["price"]}}" id="{{$value2["price"]}}_price" style="text-align: left !important;">{{round($value2["price"],2)}}</td>
                                    <td data-key="qte_en_stock_in_source" data-value="{{$value2["qte_en_stock_in_source"]}}" id="{{$value2["product_id"]}}_qte_en_stock_in_source" style="text-align: left !important;">{{$value2["name_entrepot_a_destocker"]}} ({{$value2["qte_en_stock_in_source"]}})</td>
                                    <td data-key="demande" data-value="{{$value2["demande"]}}" id="{{$value2["product_id"]}}_demande">{{$value2["demande"]}}</td>
                                    <td data-key="qte_ac" data-value="{{$value2["qte_act"]}}" id="{{$value2["product_id"]}}_qte_act">{{$value2["entrepot_a_alimenter"]}} ({{$value2["qte_act"]}})</td>
                                    <td data-key="qte_optimale" data-value="{{$value2["qte_optimale"]}}" id="{{$value2["product_id"]}}_qte_optimale">{{$value2["qte_optimale"]}}</td>

                                    <td data-key="qte_transfere" data-value="0" id="{{$value2["product_id"]}}_qte_transfere"><input class="text-center" style="width: 70px" type="text" value="0" disabled></td>

                                    <td data-key="action" id="{{$value2["product_id"]}}_action">
                                    
                                        <button onclick='delete_line({{$value2["product_id"]}})' type="button" class="btn" title="Supprimer l'offre" style="margin: 0;padding: 0;">
                                            <a class="" title="Supprimer l'offre" href="javascript:void(0)">
                                                <i class="fadeIn animated bx bx-trash"></i>
                                            </a>
                                        </button>
                                        <button onclick='update_line({{$value2["product_id"]}})' type="button" class="btn" title="Supprimer l'offre" style="margin: 0;padding: 0;">
                                            <a class="" title="Supprimer l'offre" href="javascript:void(0)">
                                                <i class="fadeIn animated bx bxs-edit"></i>
                                            </a>
                                        </button>

                                    </td>
                                </tr>
                            @endforeach

                        </tbody>

                        
                    </table>

                    


                </div>

                <div class="col-12 d-flex justify-content-center mt-5">
                    <button onclick="valide_reassort2()" class="btn btn-primary mb-4" type="submit">Valider le reassort 2</button>
                </div>

            </div>

            @endif

            @if (isset($liste_reassort))

                <div id="id_reassor1" class="card card_product_commande">
                    <div class="table-responsive p-3">
                        <table id="example4" class="table mb-0 dataTable">
                            <thead>
                                <tr>
                                    <th title="L'entrepôt qui va être décrémenté">Identifiant</th>
                                    <th title="L'entrepôt qui va être décrémenté">Date</th>
                                    <th title="L'entrepôt qui va être décrémenté">Entrepot source</th>
                                    <th title="L'entrepôt qui va être décrémenté">Entrepôt de destination</th>
                                    <th title="Points actuellement valide de l'utilisateur">Etat</th>
                                    <th title="Points actuellement valide de l'utilisateur">Action</th>
                                    <th title="Points actuellement valide de l'utilisateur">Attribué à</th>
                                </tr>
                            </thead>
                            <tbody id="tbody_id_1">
                                @foreach ($liste_reassort as $li => $value)
                            
                                    <tr id="{{$value["identifiant"]}}_transfert">
                                        <td id="{{$value["identifiant"]}}_identifiant" style="text-align: left !important;">{{$value["identifiant"]}}</td>
                                        <td id="{{$value["identifiant"]}}_date" style="text-align: left !important;">{{$value["date"]}}</td>
                                        <td id="{{$value["identifiant"]}}_entrepot_source" style="text-align: left !important;">{{$value["entrepot_source"]}}</td>
                                        <td id="{{$value["identifiant"]}}_entrepot_destination" style="text-align: left !important;">{{$value["entrepot_destination"]}}</td>
                                        <td id="{{$value["identifiant"]}}_etat" style="text-align: left !important;">{!!$value["etat"]!!}</td>
                                       
                                        <td id="{{$value["identifiant"]}}_action" class="d-flex">
                                    
                                            @if (!$value["origin_id_reassort"])
                                                
                                            
                                                @if ($value["val_etat"] == 0)
                                                    {{-- a griser --}}
                                                    <button type="submit" class="btn" title="Annuler le transfère" style="margin: 0;padding: 0;color:gray">
                                                        <i class="fadeIn animated bx bx-transfer-alt"></i>
                                                    </button>

                                                    <form action="{{ route('delete_transfert', ['identifiant' => $value["identifiant"]]) }}" method="post" id="deleteForm">
                                                        @csrf
                                                        <button type="button" class="btn" title="Supprimer le transfère" style="margin: 0;padding: 0;" data-bs-toggle="modal" data-bs-target="#confirmationModal">
                                                            <i class="fadeIn animated bx bx-trash"></i>
                                                        </button>
                                                    </form>


                                                @elseif ($value["val_etat"] > 0)
                                                    <form action="{{ route('cancel_transfert', ['identifiant' => $value["identifiant"]]) }}" method="post" id="cancelForm">
                                                        @csrf
                                                        <button type="button" class="btn" title="Annuler le transfère" style="margin: 0;padding: 0;" data-bs-toggle="modal" data-bs-target="#confirmationModal2">
                                                            <i class="fadeIn animated bx bx-transfer-alt"></i>
                                                        </button>
                                                    </form>
                                                    {{-- a griser --}}
                                                    <button type="submit" class="btn" title="Supprimer le transfère" style="margin: 0;padding: 0;color:gray">
                                                        <i class="fadeIn animated bx bx-trash"></i>
                                                    </button>
                                                @elseif ($value["val_etat"] < 0)
                                                
                                                <div>
                                                    <button type="submit" class="btn" title="Annuler le transfère" style="margin: 0;padding: 0;color:gray"">
                                                        <i class="fadeIn animated bx bx-transfer-alt"></i>
                                                    </button>
                                                </div>
                                                {{-- a griser --}}
                                                <button type="submit" class="btn" title="Supprimer le transfère" style="margin: 0;padding: 0;color:gray">
                                                    <i class="fadeIn animated bx bx-trash"></i>
                                                </button>

                                                @endif
                                            @else
                                                <div>
                                                    <button type="submit" class="btn" title="Annuler le transfère" style="margin: 0;padding: 0;color:gray"">
                                                        <i class="fadeIn animated bx bx-transfer-alt"></i>
                                                    </button>
                                                </div>
                                                {{-- a griser --}}
                                                <button type="submit" class="btn" title="Supprimer le transfère" style="margin: 0;padding: 0;color:gray">
                                                    <i class="fadeIn animated bx bx-trash"></i>
                                                </button>

                                            @endif

                                        </td>

                                        {{-- {{dd($value)}} --}}

                                        <td id="{{$value["identifiant"]}}_attribue_a" style="text-align: left !important;">

                                            <div class="list-inline d-flex align-items-center customers-contacts">	
                                                <select class="select_userApprovisionnement">

                                                    @foreach($users as $key => $user)
                                                        @if ($user["id"] == $value["attribue_a"])
                                                            <option value="{{$user["id"]}}" selected>{{$user["name"]}}</option>
                                                        @else
                                                            <option value="{{$user["id"]}}">{{$user["name"]}}</option>
                                                        @endif
                                                        
                                                    @endforeach

                                                </select>
                                            </div>
                                        
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>   
                </div>

            @endif
       
        </div>
    </div>

@endsection


@section("script")

<script src="{{asset('assets/plugins/datatable/js/jquery.dataTables.min.js')}}"></script>
<script src="{{asset('assets/plugins/datatable/js/dataTables.bootstrap5.min.js')}}"></script>
<script src="assets/plugins/select2/js/select2.min.js"></script>

<script src="{{asset('assets/plugins/datetimepicker/js/legacy.js')}}"></script>
<script src="{{asset('assets/plugins/datetimepicker/js/picker.js')}}"></script>

<script src="{{asset('assets/plugins/datetimepicker/js/picker.time.js')}}"></script>
<script src="{{asset('assets/plugins/datetimepicker/js/picker.date.js')}}"></script>

<script src="{{asset('assets/plugins/bootstrap-material-datetimepicker/js/moment.min.js')}}"></script>
<script src="{{asset('assets/plugins/bootstrap-material-datetimepicker/js/bootstrap-material-datetimepicker.min.js')}}"></script>


<script>

    $('#product_add').select2({
        width: '100%'
    });
    $('#user_selected').select2({
        width: '100%'
    });

    csrfToken = $('input[name=_token]').val();
    
    $('#example2, #example3, #example5').DataTable({
    language: {
        info: "_TOTAL_ lignes",
        infoEmpty: "Aucun utlisateur à afficher",
        infoFiltered: "(filtrés sur un total de _MAX_ éléments)",
        lengthMenu: "_MENU_",
        search: "",
        paginate: {
            first: ">>",
            last: "<<",
            next: ">",
            previous: "<"
        }
    },


    order: [[2, 'desc']], // Tri par défaut sur la première colonne en ordre décroissant
    pageLength: 1000,

    dom: 'Bfrtip',

    buttons: [
        'copy',
        'excel',
        'csv',
        'pdf',
        'print'
    ],

    lengthMenu: [
        [5,10, 25, 50, -1],
        ['5','10', '25', '50', 'Tout']
    ],

    "drawCallback": function(settings) {
        $('#entrepot_source, #entrepot_destination').prop('disabled', true);
    }

    });

    // 

    $('#example4').DataTable({
    language: {
        info: "_TOTAL_ lignes",
        infoEmpty: "Aucun utlisateur à afficher",
        infoFiltered: "(filtrés sur un total de _MAX_ éléments)",
        lengthMenu: "_MENU_",
        search: "",
        paginate: {
            first: ">>",
            last: "<<",
            next: ">",
            previous: "<"
        }
    },


    order: [[2, 'desc']], // Tri par défaut sur la première colonne en ordre décroissant
    pageLength: 1000,

    dom: 'Bfrtip',

    buttons: [
        'copy',
        'excel',
        'csv',
        'pdf',
        'print'
    ],

    lengthMenu: [
        [5,10, 25, 50, -1],
        ['5','10', '25', '50', 'Tout']
    ],

    });

    // function de suppression et de modifications

    function delete_line(id_line){

        var line =  '#'+id_line+'_line';
        console.log(line);
        $(line).fadeOut(600, function() {
            $(this).remove();
        });

    }
    
    function update_line(id_line){
        var line =  '#'+id_line+'_qte_transfere';
        $(line).find('input').prop("disabled", false);

        $(line).on('change', function(){
            val =  $(line).find('input').val();
            $(line).find('input').prop("disabled", true);
            // updater la data_value du tr
            $(line).attr('data-value', val);

        })

    }

    // validation du reassort 1 et 2 



    urlCreateReassort = "{{route('postReassort')}}";
    console.log(urlCreateReassort);
    function valide_reassort1(){



        var tabProduitReassort1 = [];
        $(".class_line1").each(function(index, row) {
            
            var rowAssociatif = {};
            $(row).find("td").each(function(index, cell) {
                if($(cell).attr("data-value")){
                    var key = $(cell).attr("data-key");
                    var value = $(cell).attr("data-value");
                    rowAssociatif[key] = value;
                }
            });
            tabProduitReassort1.push(rowAssociatif);
        });

      //  console.log(tabProduitReassort1);

        var urlCreateReassort = "{{route('postReassort')}}";
        var urlRedirection = "{{route('getVieuxSplay')}}";
        var entrepot_source = $("#entrepot_source").val();
        var entrepot_destination = $("#entrepot_destination").val();
        var user = $("#user_selected").val();


        $("#id_sub_validation_reassort").addClass("disabled-link");
        spinner = `<span id="id_spaner_transfere" class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>Transfère en coours...`;
        $("#id_sub_validation_reassort").html(spinner);



        $.ajax({
            url: urlCreateReassort,
            method: 'POST',
            data : {
                tabProduitReassort1:tabProduitReassort1,
                entrepot_source:entrepot_source,
                entrepot_destination:entrepot_destination,
                user:user
                    
            },

            headers: {
                'X-CSRF-TOKEN': csrfToken
            },                       
            success: function(response) {

                console.log(response);

                if (response.response == true) {

                    $(".alert-succes-calcul").show();
                    setTimeout(() => {
                        $(".alert-succes-calcul").hide();
                        window.location.href = urlRedirection;
                    }, 3000);   

                    $('#example2 tbody').empty();

                    $("#id_sub_validation_reassort").removeClass("disabled-link");
                    $("#id_sub_validation_reassort").html("Valider le réassort");
                    $("#id_spaner_transfere").remove();

                }else{
					$(".alert-danger-calcul").show();
					setTimeout(() => {
						$(".alert-danger-calcul").hide();
                        window.location.href = urlRedirection;
					}, 10000);
                    console.log(response.error);
                }

            },
            error: function(xhr, status, error) {
                
                $(".alert-danger-calcul").show();
					setTimeout(() => {
						$(".alert-danger-calcul").hide();
                        window.location.href = urlRedirection;
					}, 3000);
					
					console.error(error);

            }
        });
        

    }


    function valide_reassort2(){
        var tabProduitReassort2 = [];
        $(".class_line2").each(function(index, row) {
            
            var rowAssociatif = {};
            $(row).find("td").each(function(index, cell) {
                if($(cell).attr("data-value")){
                    var key = $(cell).attr("data-key");
                    var value = $(cell).attr("data-value");
                    rowAssociatif[key] = value;
                }
            });
            tabProduitReassort2.push(rowAssociatif);
        });
        console.log(tabProduitReassort2);
    }

    // ajout d'un produit quantite


    function add_line(){

        // supprimer le tr qui affiche quand on a pas de donnée
        if ($('.dataTables_empty').length > 0) {
            $('.dataTables_empty').closest('tr').remove();
        }

        
        var data_product = JSON.parse($("#product_add").val())
        qte =$("#qte_id").val() ;
        product_id = data_product['product_id'];
        barcode = data_product['barcode'];
        price = (parseFloat(data_product['price'])).toFixed(2);
        stock = data_product['stock'];
        libelle = data_product['libelle'];
        var entrepot_source = $("#entrepot_source").val();
        var entrepot_destination = $("#entrepot_destination").val();
        qte_in_destination = data_product['qte_in_destination'];

        
      
        name_entrepot_a_alimenter = "{{$name_entrepot_a_alimenter}}";
        name_entrepot_a_destocker = "{{$name_entrepot_a_destocker}}";



        // console.log(data_product);
        

        var line_add = `
        <tr data_id_product="${product_id}" id="${product_id}_line" class="class_line1 odd" role="row">
            <td data-key="product_id" data-value="${product_id}" id="${product_id}_product_id" style="text-align: left !important;">${product_id}</td>
            <td data-key="barcode" data-value="${barcode}" id="${barcode}_product_id" style="text-align: left !important;">${barcode}</td>
            <td data-key="libelle" data-value="${libelle}" id="${product_id}_libelle" style="text-align: left !important;">${libelle}</td>
            <td data-key="price" data-value="${price}" id="${product_id}_price" style="text-align: left !important;" class="sorting_1">${price}</td>

            <td data-key="qte_en_stock_in_source" data-value="${stock}" id="${product_id}_qte_en_stock_in_source" style="text-align: left !important;">${name_entrepot_a_destocker} (${stock})</td>
            <td data-key="demande" data-value="/" id="${product_id}_demande">/</td>
            <td data-key="qte_act" data-value="/" id="${product_id}_qte_act">${name_entrepot_a_alimenter} (${qte_in_destination})</td>
            <td data-key="qte_optimale" data-value="/" id="${product_id}_qte_optimale">/</td>
            <td data-key="qte_transfere" data-value="${qte}" id="${product_id}_qte_transfere"><input class="text-center" style="width: 50px" type="text" value="${qte}" disabled=""></td>
            <td data-key="action" id="${product_id}_action">
                <button onclick="delete_line(${product_id})" type="button" class="btn" title="Supprimer l'offre" style="margin: 0;padding: 0;">
                    <a class="" title="Supprimer l'offre" href="javascript:void(0)">
                        <i class="fadeIn animated bx bx-trash"></i>
                    </a>
                </button>
                <button onclick="update_line(${product_id})" type="button" class="btn" title="Supprimer l'offre" style="margin: 0;padding: 0;">
                    <a class="" title="Supprimer l'offre" href="javascript:void(0)">
                        <i class="fadeIn animated bx bxs-edit"></i>
                    </a>
                </button>
            </td>
        </tr>
        `
        $("#tbody_id_1").append(line_add);

        $('#product_add').val([""]).trigger('change');
        $("#qte_id").val("");


    }

    $("#id_sub_calcul_reassort").on("click", function(){

        $(this).addClass("disabled-link");
        spinner = `<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>Calcul en cours...`;
        $(this).html(spinner);

    })

    // supprimer un transfère (uniquement si la preparation n'a pas commencé)

    $('#confirmDelete').on('click', function() {
        $('#deleteForm').submit();
    });
    $('#confirmCancel').on('click', function() {
        $('#cancelForm').submit();
    });

    if ($("#first_transfert").is(':checked')) {
        $(".date_interval").removeClass('d-none');
    } else {

        $(".date_interval").addClass('d-none');
    }

    $("#first_transfert").on('change', function(){

        if ($(this).is(':checked')) {
            $(".date_interval").removeClass('d-none');
        } else {

            $(".date_interval").addClass('d-none');
        }
    });

    $("#start_date_input, #end_date_input").on('change', function(){

        var start_date = $("#start_date_input").val();
        var end_date = $("#end_date_input").val();

        if (start_date && end_date) {

            var date1 = new Date(start_date);
            var date2 = new Date(end_date);
            var differenceMs = date2 - date1;

            var differenceDays = differenceMs / (1000 * 60 * 60 * 24);
            var differenceWeeks = differenceMs / (1000 * 60 * 60 * 24 * 7);


            $("#semaine_input").attr("disabled", "false");
            $("#semaine_input").val(differenceWeeks.toFixed(1));

        }else{
            $("#semaine_input").val(0);
            $("#semaine_input").attr("disabled", "true");
        }

    });


    var start_date_origin = '{{$start_date_origin}}';
    var end_date_origin = '{{$end_date_origin}}';

    if (start_date_origin && end_date_origin) {

        $("#start_date_input").val(start_date_origin);
        $("#end_date_input").val(end_date_origin);

        var start_date = $("#start_date_input").val();
        var end_date = $("#end_date_input").val();

        if (start_date && end_date) {

            var date1 = new Date(start_date);
            var date2 = new Date(end_date);
            var differenceMs = date2 - date1;

            var differenceDays = differenceMs / (1000 * 60 * 60 * 24);
            var differenceWeeks = differenceMs / (1000 * 60 * 60 * 24 * 7);


            $("#semaine_input").attr("disabled", "false");
            $("#semaine_input").val(differenceWeeks.toFixed(1));

        }else{
            $("#semaine_input").val(0);
            $("#semaine_input").attr("disabled", "true");
        }

        

       
        $("#start_date_input").attr("disabled", "true");
        $("#end_date_input").attr("disabled", "true");
        setTimeout(() => {
            $("#start_date_input, #end_date_input").removeClass('picker__input');
        }, 1000);
        

        
    }

   





   


    
    


</script>


<script>
    $('.datepicker').pickadate({
        selectMonths: true,
        selectYears: true
    })
    // ,
    // $('.timepicker').pickatime()
</script>


{{-- <script>
    $(function () {
        $('#date-time').bootstrapMaterialDatePicker({
            format: 'YYYY-MM-DD HH:mm'
        });
        $('#date').bootstrapMaterialDatePicker({
            time: false
        });
        $('#time').bootstrapMaterialDatePicker({
            date: false,
            format: 'HH:mm'
        });
    });
</script> --}}


@endsection


