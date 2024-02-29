$(document).ready(function() {

    // Affiche la moyenne de préparation de chaque users
    $.ajax({
        url: "analyticsSellerTotal",
        method: "GET",
        async: true,
    }).done(function(data) {
        if(JSON.parse(data).success){
            var data = JSON.parse(data)
            $(".loading_chart").remove()

            // Créer le chart js
            chartAverage(data.average)
        }
    });


    $('#example').DataTable({
        "order": [[ 3, 'desc' ]],
        "ajax": {
            url: 'analyticsSeller',
            async: true,
            dataSrc: function(json) {
                var data = []
                Object.keys(json.histories).forEach(function(k, v){
                    Object.keys(json.histories[k]).forEach(function(a, b){
                        data.push(json.histories[k][a])
                    })
                });
                
                return data
            }
        },
        columns: [
            {data: null, 
                render: function(data, type, row) {
                    return row.name
                }
            },
            {data: null, 
                render: function(data, type, row) {
                    return '<div class="number_order">'+row.number_order+'</div>'
                }
            },
            {data: null, 
                render: function(data, type, row) {
                    return '<div class="average">'+parseFloat(row.average).toFixed(2)+'</div>'
                }
            },
            {data: null, 
                render: function(data, type, row) {
                    return '<div class="total_amount">'+parseFloat(row.total_amount).toFixed(2)+'</div>'
                }
            }
        ],
        "language": {
            loadingRecords: ""
        },
        "initComplete": function(settings, json) {

            $(".total_amount").text(parseFloat(json.total_amount_order).toFixed(2)+' €')
            $(".loading_table_analytics").hide()
            $(".load_spinner").hide()

            $(".order_pending").text(json.status.pending ?? 0)
            $(".order_paid").text(json.status.paid ?? 0)

            $("#example_length select").css('margin-right', '10px')
            $(".date_dropdown").appendTo('.dataTables_length')
            $(".dataTables_length").css('display', 'flex')
            $(".dataTables_length").addClass('select2_custom')
            $(".date_dropdown").removeClass('d-none')
            $(".dataTables_paginate").css('margin-right', '10px')
            $(".dataTables_paginate").css('margin-bottom', '10px')
        },
        "fnRowCallback": function(nRow, aData, iDisplayIndex, iDisplayIndexFull) {
            $('td:nth-child(1)', nRow).attr('data-label', 'Nom');
            $('td:nth-child(2)', nRow).attr('data-label', 'Commandes Prises');
            $('td:nth-child(3)', nRow).attr('data-label', 'Panier Emballées');
            $('td:nth-child(4)', nRow).attr('data-label', 'Prouits Moyen');
            $('td:nth-child(5)', nRow).attr('data-label', 'Total Vente');

            return nRow;
        },
        "drawCallback": function( settings  ) {
            if(settings.json){
                $(".total_amount").text(parseFloat(settings.json.total_amount_order).toFixed(2)+' €')
                $(".order_pending").text(settings.json.status.pending ?? 0)
                $(".order_paid").text(settings.json.status.paid ?? 0)
                $(".data_number").show()
                $(".load_spinner").hide()
            }
        }
    })

    $("thead").remove()

    $('.date_dropdown').on('change', function(e){
        $(".load_spinner").show()
        $(".data_number").hide()
        $(".loading_table_analytics").show()
        $('#example').DataTable().ajax.url('analyticsSeller?date=' + $(".date_dropdown").val()).load();
     })
});


function chartAverage(average){
    var list_name = [];
    var total_amount = [];
    var number_order = [];

    Object.entries(average).forEach(([key, value]) => {
        list_name.push(value.name)
        total_amount.push(value.total_amount)
        number_order.push(value.total_order)
    });
   
    // chartBP
    Highcharts.chart('chartBP', {
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
        series: [{
            name: 'Commandes Prises',
            data: number_order,
        },{
            name: 'Total Vente',
            data: total_amount,
            visible: false,
        }]
    });
}
