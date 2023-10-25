

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
                <button type="submit" class="btn btn-danger">Syncroniser</button>
            </div>
        </form>





    </div>
</div>



