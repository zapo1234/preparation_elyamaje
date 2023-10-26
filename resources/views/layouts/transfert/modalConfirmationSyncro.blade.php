

<div class="modal-dialog">
    <div class="modal-content">

        <form action="{{ route('updateStockWoocommerce', ['identifiant' => $value["identifiant"]]) }}" method="post">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title">Confirmation de syncronisation</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Êtes-vous sûr de vouloir diminuer ces quantitées sur le site ?
            </div>
            <div class="modal-footer">
                <div class="d-flex justify-content-center parent_compteur d-none">
                   <strong>Temps estimé : </strong> <div id="id_compteur_{{$identifiant}}"></div>
                </div>
                <div class="btn">
                    <button id="btn_{{$identifiant}}" type="submit" class="btn btn-danger" onclick='demarrerCompteARebours({{$totalSecondes}},{{$id_div}},{{$btnElement}})'>Synchronisation</button>
                </div>
                
            </div>

        </form>

    </div>
</div>



