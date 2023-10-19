<div class="modal-dialog modal-fullscreen">
    <div class="modal-content">
        <div class="modal-header">

            {{-- {{dd($value)}} --}}
            <h5 class="modal-title">Identifiant du réassort : {{$identifiant}}</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>


            <div class="modal-body">




                


                
                <div id="id_reassor1" class="card card_product_commande">
                    <div class="table-responsive p-3">
                        <table id="example6" class="table mb-0 dataTable">
                            <thead>
                                <tr>
                                    <th title="L'entrepôt qui va être décrémenté">ID</th>
                                    <th title="L'entrepôt qui va être décrémenté">Code barre</th>
                                    <th title="L'entrepôt qui va être décrémenté">Nom produit</th>
                                    <th title="L'entrepôt qui va être décrémenté">Entrepôt source</th>
                                    <th title="L'entrepôt qui va être alimenter">Entrepôt de destination</th>
                                    <th title="L'entrepôt qui va être alimenter">Quantité transféré</th>
                                </tr>
                            </thead>
                            <tbody id="">
                            
                                @foreach ($detail_reassort as $key => $line_reassort)
                                    <tr class="class_line2">

                                        <td data-key="product_id" data-value="" id="_product_id" style="text-align: left !important;">{{$line_reassort->product_id}}</td>
                                        <td data-key="barcode" data-value="" id="_barcode" style="text-align: left !important;">{{$line_reassort->barcode}}</td>
                                        <td data-key="nom_produit" data-value="" id="_nom_produit" style="text-align: left !important;">{{$line_reassort->label}}</td>
                                        <td data-key="entrepot_source" data-value="" id="_entrepot_source" style="text-align: left !important;">{{$entrepot_source}}</td>
                                        <td data-key="entrepot_destination" data-value="" id="_entrepot_destination" style="text-align: left !important;">{{$entrepot_destination}}</td>
                                        <td data-key="quantite" data-value="" id="_quantite" style="text-align: left !important;">{{$line_reassort->qty}}</td>

                                    </tr>                                         
                                @endforeach
                            </tbody>
    
                        
    
                        </table>
                    </div>                   
                </div>












            </div>


        {{-- <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">fermé</button>
        </div> --}}
    </div>
</div>