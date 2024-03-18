<div class="modal-dialog modal-fullscreen">
    <div class="modal-content">
        <div class="modal-header">

            {{-- {{dd($value)}} --}}
            <h5 class="modal-title">Référence de la commande : {{$ref_commande}}</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>


            <div class="modal-body">
                <div id="id_reassor1" class="card card_product_commande">
                    <div class="table-responsive p-3">
                        <table class="table mb-0 dataTable example6">
                            <thead>
                                <tr>
                                    <th title="L'entrepôt qui va être décrémenté">Ref fournisseur</th>
                                    <th title="L'entrepôt qui va être décrémenté">Ref produit</th>
                                    <th title="L'entrepôt qui va être décrémenté">Libelle</th>
                                    <th title="L'entrepôt qui va être décrémenté">Quantité</th>
                                    <th title="L'entrepôt qui va être alimenter">Total TTC</th>
                                </tr>
                            </thead>
                            <tbody id="">
                            
                                @foreach ($lines as $key => $line)

                                    <tr class="class_line2">
                                        <td data-key="ref_fornisseur" data-value="" id="_ref_fornisseur" style="text-align: left !important;">{{$line["ref_fornisseur"]}}</td>
                                        <td data-key="ref_product" data-value="" id="_ref_product" style="text-align: left !important;">{{$line["ref_product"]}}</td>
                                        <td data-key="libelle_product" data-value="" id="_libelle_productt" style="text-align: left !important;">{{$line["libelle_product"]}}</td>
                                        <td data-key="qty" data-value="" id="_qty" style="text-align: left !important;">{{$line["qty"]}}</td>
                                        <td data-key="total_ttc" data-value="" id="_total_ttc" style="text-align: left !important;">{{$line["total_ttc"]}}</td>

                                    </tr>                                         
                                @endforeach
                            </tbody>
    
                        
    
                        </table>
                    </div>                   
                </div>



            </div>
    </div>
</div>
