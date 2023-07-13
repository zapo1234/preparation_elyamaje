$(document).ready(function() {

    
   



    $('#example').DataTable({
        "order": [[ 4, 'desc' ]],
        "initComplete": function(settings, json) {
            
            $("#example_length select").css('margin-right', '10px')
            $(".date_dropdown").appendTo('.dataTables_length')
            $(".dataTables_length").css('display', 'flex')
            $(".dataTables_length").addClass('select2_custom')
            $(".date_dropdown").removeClass('d-none')

            var order_prepared = 0
            var order_finished = 0

            // Calcul commandes préparées
            $('#example').DataTable().rows().eq(0).each( function ( index ) {
                var row = $('#example').DataTable().row( index );
                var data = row.data();
                order_prepared = parseInt(order_prepared) + parseInt(data[1])
                order_finished = parseInt(order_finished) + parseInt(data[2])
            });

            $('.order_prepared').text(order_prepared)
            $('.order_finished').text(order_finished)
            $(".loading_div").addClass('d-none')
            $(".number_order").removeClass('d-none')
            $("#example").removeClass('d-none')
        }
    })
})

$('.date_dropdown').on('change', function(e){
    var date_dropdown = $(this).val();

    if(date_dropdown == ""){
        date_dropdown = date_dropdown
    } else {
        date_dropdown = dateFormat(date_dropdown, 'dd/MM/yyyy')
    }

    $('#example').DataTable()
    .column(4).search(date_dropdown, true, false)
    .draw();

    var order_prepared = 0
    var order_finished = 0

    $(".prepare_column").each( function ( index ) {
        order_prepared = order_prepared + parseInt($(this).text())
    });

    $(".finished_column").each( function ( index ) {
        order_finished = order_finished + parseInt($(this).text())
    });

    $('.order_prepared').text(order_prepared)
    $('.order_finished').text(order_finished)
 })


 function dateFormat(inputDate, format) {
    //parse the input date
    const date = new Date(inputDate);

    //extract the parts of the date
    const day = date.getDate();
    const month = date.getMonth() + 1;
    const year = date.getFullYear();    

    //replace the month
    format = format.replace("MM", month.toString().padStart(2,"0"));        

    //replace the year
    if (format.indexOf("yyyy") > -1) {
        format = format.replace("yyyy", year.toString());
    } else if (format.indexOf("yy") > -1) {
        format = format.replace("yy", year.toString().substr(2,2));
    }

    //replace the day
    format = format.replace("dd", day.toString().padStart(2,"0"));

    return format;
}


