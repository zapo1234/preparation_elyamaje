$(document).ready(function() {

    // Affiche la moyenne de préparation de chaque users
    // $.ajax({
    //     url: "getAverage",
    //     method: "GET",
    //     async: true,
    // }).done(function(data) {
    //     if(JSON.parse(data).success){
    //         var data = JSON.parse(data)
    //         $(".loading_chart").remove()

    //         // Créer le chart js
    //         chartAverage(data.average_by_name)
    //     }
    // });

    $('#example').DataTable({
        "order": [[ 4, 'desc' ]],
        "ajax": {
            url: 'getAnalytics',
            async: true,
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
        "language": {
            loadingRecords: ""
        },
        "initComplete": function(settings, json) {
            $(".loading_table").remove()
            $("#example_length select").css('margin-right', '10px')
            $(".date_dropdown").appendTo('.dataTables_length')
            $(".dataTables_length").css('display', 'flex')
            $(".dataTables_length").addClass('select2_custom')
            $(".date_dropdown").removeClass('d-none')
            $(".dataTables_paginate").css('margin-right', '10px')
            $(".dataTables_paginate").css('margin-bottom', '10px')

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
            $(".number_order").removeClass('d-none')
        },
        "fnRowCallback": function(nRow, aData, iDisplayIndex, iDisplayIndexFull) {
            $('td:nth-child(1)', nRow).attr('data-label', 'Nom');
            $('td:nth-child(2)', nRow).attr('data-label', 'Commandes Préparées');
            $('td:nth-child(3)', nRow).attr('data-label', 'Commandes Emballées');
            $('td:nth-child(4)', nRow).attr('data-label', 'Prouits Bippés');
            $('td:nth-child(5)', nRow).attr('data-label', 'Date');

            return nRow;
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

            $(".data_number").removeClass('d-none')
            $('.order_prepared').text(order_prepared)
            $('.order_finished').text(order_finished)
        }

    })

    $("thead").remove()
})


$('.date_dropdown').on('change', function(e){
    $(".data_number").addClass('d-none')
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
            text: ''
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

