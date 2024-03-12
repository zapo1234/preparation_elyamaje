<div class="modal-dialog modal-fullscreen">
    <div class="modal-content">
        <div class="modal-header">

            {{-- {{dd($value)}} --}}
            <h5 class="modal-title">Alerte du : {{$date}}</h5>

            <h5 class="modal-title" style="margin-left: 5%;">
                
                <a href="{{$url}}" download="{{$name_alerte}}" style="color: #8833ff !important;">
                    Télécharger le csv
                    <i class="bx bx-cloud-download mr-1"></i>
                </a>
            
            </h5>

            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>


            <div class="modal-body">
                <div id="id_reassor1" class="card card_product_commande">
                    <div class="table-responsive p-3">
                        <table class="table mb-0 dataTable example6">
                            <thead>
                                <tr>
                                    <th title="L'entrepôt qui va être décrémenté">ID</th>
                                    <th title="L'entrepôt qui va être décrémenté">Libelle</th>
                                    <th title="L'entrepôt qui va être décrémenté">Nombre de pièces</th>
                                </tr>
                            </thead>
                            <tbody id="">
                            
                                @foreach ($contenu as $key => $line)
                                    <tr class="class_line2">

                                        <td data-key="product_id" data-value="" id="_ID" style="text-align: left !important;">{{$line["ID"]}}</td>
                                        <td data-key="barcode" data-value="" id="_Libelle" style="text-align: left !important;">{{$line["Libelle"]}}</td>
                                        <td data-key="nom_produit" data-value="" id="_nbrPiece" style="text-align: left !important;">{{$line["Quantité vendu"]}}</td>
                                    </tr>                                         
                                @endforeach
                            </tbody>
    
                        
    
                        </table>
                    </div>                   
                </div>



            </div>
    </div>
</div>
