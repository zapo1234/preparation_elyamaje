$(document).ready(function() {
    $(".pace").remove()

    $('#example').DataTable({
        "order": [[ 4, 'desc' ]],
        "ajax": {
            url: 'getAnalytics',
            dataSrc: function(json) {
                var data = []
                Object.keys(json.histories).forEach(function(k, v){
                    Object.keys(json.histories[k]).forEach(function(a, b){
                        data.push(json.histories[k][a])
                    })
                });
                return data;
            }
        },
        columns: [
            { 
            data: null, 
                render: function(data, type, row) {
                    return row.name
                }
            },
            {data: null, 
                render: function(data, type, row) {
                    return '<div class="prepared_column">'+row.prepared_count+'</div>'
                }
            },
            {data: null, 
                render: function(data, type, row) {
                    return '<div class="finished_column">'+row.finished_count+'</div>'
                }
            },
            {data: null, 
                render: function(data, type, row) {
                    return row.items_picked
                }
            },
            {data: null, 
                render: function(data, type, row) {
                    return row.date
                }
            }
        ],
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
                order_prepared = parseInt(order_prepared) + parseInt(data.prepared_count)
                order_finished = parseInt(order_finished) + parseInt(data.finished_count)
            });

            $('.order_prepared').text(order_prepared)
            $('.order_finished').text(order_finished)
            $(".loading_div").addClass('d-none')
            $(".number_order").removeClass('d-none')
            $("#example").removeClass('d-none')
        },
        "drawCallback": function( settings  ) {
            var order_prepared = 0
            var order_finished = 0
            var api = this.api();
            current_row = api.rows({search: 'applied'}).data();

            Object.keys(current_row).forEach(function(k, v){

                if(typeof current_row[k]['prepared_count'] != "undefined"){
                    order_prepared = order_prepared + current_row[k]['prepared_count']
                }

                if(typeof current_row[k]['finished_count'] != "undefined"){
                    order_finished = order_finished + current_row[k]['finished_count']
                }

            })

            $('.order_prepared').text(order_prepared)
            $('.order_finished').text(order_finished)
        }

    })


    // Affiche la moyenne de préparation de chaque users
    $.ajax({
        url: "getAverage",
        method: "GET"
    }).done(function(data) {
        if(JSON.parse(data).success){
            var data = JSON.parse(data)
            $(".loading_chart").remove()

            // Créer le chart js
            chartAverage(data.average_by_name)
        }
    });
})


$('.date_dropdown').on('change', function(e){
    $('#example').DataTable().ajax.url('getAnalytics?date=' + $(".date_dropdown").val()).load();
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

function chartAverage(average){
    var average = average
    var list_name = []
    var order_prepared = []
    var order_finished = []
    var items_picked = []
    
    Object.entries(average).forEach(([key, value]) => {
        list_name.push(key)
        order_prepared.push(value.avg_prepared)
        order_finished.push(value.avg_finished)
        items_picked.push(value.avg_items_picked)
    });
   
    // chart 6
    Highcharts.chart('chart6', {
        chart: {
            type: 'bar',
            styledMode: true
        },
        title: {
            text: 'Moyenne préparation / Jour'
        },
        xAxis: {
            categories: list_name
        },
        yAxis: {
            min: 0,
            title: {
                text: '',
                style: {
                    display: 'none',
                }
            }
        },
        legend: {
            reversed: false
        },
        colors: ['#4eda58', '#ff7300' , '#212529'],
        series: [{
            name: 'Commandes préparées',
            data: order_prepared
        },{
            name: 'Commandes emballées',
            data: order_finished
        }
        ,{
            name: 'Produits bippés',
            data: items_picked,
        }]
    });
}

