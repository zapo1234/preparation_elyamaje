$(document).ready(function() {

    $('#example').DataTable({
        "lengthMenu": [
            [10, 25, 50, 100, 250, -1],
            [10, 25, 50, 100, 250,'Tous']
        ],
        "info": false,
        "order": [[6, 'asc']],
        "columnDefs": [
            { "visible": false, "targets": 6 }
        ],
        "initComplete": function(settings, json) {
            $(".loading_div").addClass('d-none')
            $("#example").removeClass('d-none')
            $("#example_length select").css('margin-right', '10px')
            $(".category_dropdown").appendTo('.dataTables_length')
            $(".dataTables_length").css('display', 'flex')
            $(".dataTables_length").addClass('select2_custom')
            $(".category_dropdown").removeClass('d-none')
            $(".category_dropdown").select2({
                width: '250px',
            });

        }
    })
})

$('.category_dropdown').on('change', function(e){
    var category_dropdown = $(this).val();
    $('#example').DataTable()
    .column(3).search(category_dropdown, true, false)
    .draw();
})

$(".edit_product_location").on('click', function(){
    $(this).hide()
    id = $(this).attr('data-id')
    $("#input_"+id).attr('disabled', false)
    $("#save_"+id).removeClass('d-none')
})

function save_location(id_product){

    $("#input_"+id_product).attr('disabled', true)
    $("#edit_"+id_product).show()
    $("#save_"+id_product).addClass('d-none')

    $.ajax({
        url: "products",
        method: "POST",
        data : {_token: $('input[name=_token]').val(), id_product: id_product, location: $("#input_"+id_product).val()}
    }).done(function(data) {
        if(JSON.parse(data).success){
           
        } else {
            $("#input_"+id_product).attr('disabled', false)
            $("#edit_"+id_product).hide()
            $("#save_"+id_product).addClass('d-none')
            alert('Erreur enregistrement')
        }
    });
}
