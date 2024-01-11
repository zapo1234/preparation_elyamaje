<!-- admin/bordereauChrono.blade.php -->

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bordereau Chrono</title>

    <style>
        @page {
            size: landscape;
        }
    </style>
</head>
<body>

    <table style="width: 100%;">
        <tr>
            <td style="text-align: left; font-family: 'Nunito', sans-serif; font-size: 9px;"><h1>BORDEREAU RECAPITULATIF</h1></td>
            <td style="text-align: right; font-family: 'Nunito', sans-serif; font-size: 9px"><h1>Date: {{ date('d/m/Y') }}</h1></td>
        </tr>
    </table>

    <table style="width: 100%; padding-left: 15px; padding-right: 15px; margin-bottom: 30px">
        <thead>
            <th style="text-align: left; text-transform: uppercase; font-weight: bold; font-size: 14px">EMETTEUR :</th>
        </thead>
        <tbody style="font-size: 15px">
            <tr style="text-align:left;">
                <td></td>
                <td>NUMERO DE COMPTE : {{ config('app.chronopost_accountNumber') }}</td>
                <td>NUMERO DE SOUS COMPTE : xxxx</td>
            </tr>
            <tr style="text-align:left;">
                <td></td>
                <td style="position: absolute;">ADRESSE : {{ config('app.line2') }}</td>
            </tr>
            <tr style="text-align:left;">
                <td></td>
                <td>VILLE : {{ config('app.city') }}</td>
            </tr>
            <tr style="text-align:left;">
                <td></td>
                <td>CODE POSTAL : {{ config('app.zipCode') }}</td>
            </tr>
            <tr style="text-align:left;">
                <td></td>
                <td>PAYS : {{ config('app.countryCode') }}</td>
            </tr>
            <tr style="text-align:left;">
                <td></td>
                <td>TELEPHONE : {{ config('app.companyPhone') }}</td>
            </tr>
        </tbody>
    </table>

    <img src="{{ asset('assets/images/icons/chronopost_logo.png') }}" style="width: 120px; position:absolute; right: 0; top: 100">


    <span style="text-transform: uppercase; font-weight: bold; font-size: 14px">Détail des envois nationaux</span>
    <table style="width: 100%; border-collapse: collapse; margin-top: 10px; margin-bottom: 30px;">
        <thead style="background-color: #EEECE1">
            <tr>
                <th style="border: 1px solid #000; padding: 8px; text-transform: uppercase; font-size: 10px; padding: 2">Numéro de LT</th>
                <th style="border: 1px solid #000; padding: 8px; text-transform: uppercase; font-size: 10px; padding: 2">Ref. Expéditeur</th>
                <th style="border: 1px solid #000; padding: 8px; text-transform: uppercase; font-size: 10px; padding: 2">Ref. Destinataire</th>
                <th style="border: 1px solid #000; padding: 8px; text-transform: uppercase; font-size: 10px; padding: 2">Produit</th>
                <th style="border: 1px solid #000; padding: 8px; text-transform: uppercase; font-size: 10px; padding: 2">Poid (Kg)</th>
                <th style="border: 1px solid #000; padding: 8px; text-transform: uppercase; font-size: 10px; padding: 2">Raison Sociale</th>
                <th style="border: 1px solid #000; padding: 8px; text-transform: uppercase; font-size: 10px; padding: 2">Nom et Prénom</th>
                <th style=" border: 1px solid #000; padding: 8px; text-transform: uppercase; font-size: 10px; padding: 2">C.P.</th>
                <th style="border: 1px solid #000; padding: 8px; text-transform: uppercase; font-size: 10px; padding: 2">Ville</th>
                <th style="border: 1px solid #000; padding: 8px; text-transform: uppercase; font-size: 10px; padding: 2">Pays</th>
                <th style="border: 1px solid #000; padding: 8px; text-transform: uppercase; font-size: 10px; padding: 2">ASSU.</th>
            </tr>
        </thead>
        <tbody>
            @foreach($order_detail['orders'][config('app.countryCode')]['orders'] as $order)
                <tr style="font-size:10px">
                    <td style="border: 1px solid #000; padding: 8px;">{{ $order['tracking_number'] }}</td>
                    <td style="border: 1px solid #000; padding: 8px;">{{ $order['order_id'] }}</td>
                    <td style="border: 1px solid #000; padding: 8px;">{{ $order['customer_id'] }}</td>
                    <td style="border: 1px solid #000; padding: 8px;">{{ $order['shipping_method'] }}</td>
                    <td style="border: 1px solid #000; padding: 8px;">{{ $order['weight'] }}</td>
                    <td style="border: 1px solid #000; padding: 8px;">{{ $order['billing_customer_company'] }}</td>
                    <td style="border: 1px solid #000; padding: 8px;">{{ $order['last_name'] }} {{ $order['first_name'] }}</td>
                    <td style="border: 1px solid #000; padding: 8px;">{{ $order['postcode'] }}</td>
                    <td style="border: 1px solid #000; padding: 8px;">{{ $order['city'] }}</td>
                    <td style="border: 1px solid #000; padding: 8px;">{{ $order['country'] }}</td>
                    <td style="border: 1px solid #000; padding: 8px;">{{ $order['insured'] }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    @if(count($order_detail['orders']) > 1)
        <span style="text-transform: uppercase; font-weight: bold; font-size: 14px">Détail des envois internationaux</span>
        <table style="width: 100%; border-collapse: collapse; margin-top: 10px; margin-bottom: 30px;">
            <thead style="background-color: #EEECE1">
                <tr>
                    <th style="border: 1px solid #000; padding: 8px; text-transform: uppercase; font-size: 10px; padding: 2">Numéro de LT</th>
                    <th style="border: 1px solid #000; padding: 8px; text-transform: uppercase; font-size: 10px; padding: 2">Ref. Expéditeur</th>
                    <th style="border: 1px solid #000; padding: 8px; text-transform: uppercase; font-size: 10px; padding: 2">Ref. Destinataire</th>
                    <th style="border: 1px solid #000; padding: 8px; text-transform: uppercase; font-size: 10px; padding: 2">Produit</th>
                    <th style="border: 1px solid #000; padding: 8px; text-transform: uppercase; font-size: 10px; padding: 2">Poid (Kg)</th>
                    <th style="border: 1px solid #000; padding: 8px; text-transform: uppercase; font-size: 10px; padding: 2">Raison Sociale</th>
                    <th style="border: 1px solid #000; padding: 8px; text-transform: uppercase; font-size: 10px; padding: 2">Nom et Prénom</th>
                    <th style=" border: 1px solid #000; padding: 8px; text-transform: uppercase; font-size: 10px; padding: 2">C.P.</th>
                    <th style="border: 1px solid #000; padding: 8px; text-transform: uppercase; font-size: 10px; padding: 2">Ville</th>
                    <th style="border: 1px solid #000; padding: 8px; text-transform: uppercase; font-size: 10px; padding: 2">Pays</th>
                    <th style="border: 1px solid #000; padding: 8px; text-transform: uppercase; font-size: 10px; padding: 2">ASSU.</th>
                </tr>
            </thead>
            <tbody>
                @foreach($order_detail['orders'] as $key => $orderOther)
                    @if($key != config('app.countryCode'))
                        @foreach($orderOther['orders'] as $ord)
                            <tr style="font-size:10px">
                                <td style="border: 1px solid #000; padding: 8px;">{{ $ord['tracking_number'] }}</td>
                                <td style="border: 1px solid #000; padding: 8px;">{{ $ord['order_id'] }}</td>
                                <td style="border: 1px solid #000; padding: 8px;">{{ $ord['customer_id'] }}</td>
                                <td style="border: 1px solid #000; padding: 8px;">{{ $ord['shipping_method'] }}</td>
                                <td style="border: 1px solid #000; padding: 8px;">{{ $ord['weight'] }}</td>
                                <td style="border: 1px solid #000; padding: 8px;">{{ $ord['billing_customer_company'] }}</td>
                                <td style="border: 1px solid #000; padding: 8px;">{{ $ord['last_name'] }} {{ $ord['first_name'] }}</td>
                                <td style="border: 1px solid #000; padding: 8px;">{{ $ord['postcode'] }}</td>
                                <td style="border: 1px solid #000; padding: 8px;">{{ $ord['city'] }}</td>
                                <td style="border: 1px solid #000; padding: 8px;">{{ $ord['country'] }}</td>
                                <td style="border: 1px solid #000; padding: 8px;">{{ $ord['insured'] }}</td>
                            </tr>
                        @endforeach
                    @endif
                @endforeach
            </tbody>
        </table>
    @endif


    <span style="text-transform: uppercase; font-weight: bold; font-size: 14px">Résumé</span>
    <table style="width: 100%; border-collapse: collapse; margin-top: 10px;">
        <thead style="background-color: #EEECE1">
            <tr>
                <th style="border: 1px solid #000; padding: 8px; text-transform: uppercase; font-size: 10px; padding: 2">Destination</th>
                <th style="border: 1px solid #000; padding: 8px; text-transform: uppercase; font-size: 10px; padding: 2">Nombre de colis</th>
                <th style="border: 1px solid #000; padding: 8px; text-transform: uppercase; font-size: 10px; padding: 2">Poid (Kg)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($order_detail['orders'] as $key => $detail)
                <tr style="font-size:10px">
                    <td style="border: 1px solid #000; padding: 8px;">{{ $key }}</td>
                    <td style="border: 1px solid #000; padding: 8px;">{{ $order_detail['orders'][$key]['total_order'] }}</td>
                    <td style="border: 1px solid #000; padding: 8px;">{{ $order_detail['orders'][$key]['total_weight'] }}</td>
                </tr>
            @endforeach
                <tr style="font-size:10px">
                    <td style="border: 1px solid #000; padding: 8px; text-transform: uppercase;">Total</td>
                    <td style="border: 1px solid #000; padding: 8px;">{{ $order_detail['total_order'] }}</td>
                    <td style="border: 1px solid #000; padding: 8px;">{{ $order_detail['total_weight'] }}</td>
                </tr>
        </tbody>
    </table>

    <table style="width: 100%; margin-top: 50px;">
        <tbody>
            <tr>
                <td style="font-weight:bold; text-align:center; padding: 8px;">Signature du client</td>
                <td style="font-weight:bold; text-align:center; padding: 8px;">Signature du messager Chronopost</td>
            </tr>
        </tbody>
    </table>
</body>
</html>
