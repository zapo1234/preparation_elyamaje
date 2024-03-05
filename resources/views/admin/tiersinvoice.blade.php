<!DOCTYPE html>
<html>
<head>
    <title>Merci pour votre commande</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@200;300;400;500;600&display=swap');
        body {
            font-family: 'Poppins', Arial, sans-serif;
            font-size: 13px;
            margin: 0;
            padding: 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            border:none !important;
        }
        th, td {
            padding: 8px;
            border: 1px solid #fff;
            text-align: left;
            font-size:13px;
        }
        th {
            background-color: #f2f2f2;
        }

        

        #total{margin-left:60%;}
        
    </style>
</head>
<body>
    <div style="width: 100%; margin: 0 auto; font-family: 'Poppins', sans-serif;">
        <!-- Header Section -->
        <div style="text-align:left;padding: 10px 10px 10px 0px;">
            <img src="https://www.connect.elyamaje.com/admin/uploads/logo_caisse.png" style="height: 50px;">
        </div>

        <!-- Invoice Address and Client Info -->
        <table style="margin-bottom:50px;">
            <tr>
                <td style="width: 50%;">
                    <h2 style="margin-bottom:0!important;"><strong>Elyamaje</strong></h2><br>
                    16 Boulevard Gueidon<br>
                    13013 Marseille<br>
                    Tél : 04 91 84 77 50<br>
                    Email: contact@elyamaje.com<br>
                    Site web: <a href="elyamaje.com" style="color:black;">www.elyamaje.com</a>
                </td>
                <td style="width: 48%; text-align: left;">
                    <h3 style="margin-bottom:0!important;"><strong>Adressé à</strong></h3><br>
                    {{ $tiers['name'] }}<br>
                    {{  $tiers['adresse'] }}<br>
                    {{ $tiers['code_postal'] }} {{ $tiers['city'] }}<br>
                    Téléphone : {{  $tiers['phone']  }}
                </td>
            </tr>
        </table>

        <!-- Invoice Details Table -->
        <table style="margin-bottom:50px;">
            <thead>
                <tr>
                    <th>Produit</th>
                    <th>Quantité</th>
                    <th>Prix (H.T)</th>
                    <th>Total (T.T.C)</th>
                    <th>Prix après remise (-30%)</th>
                </tr>
            </thead>
            <tbody>
                @foreach($data_line_order as $resultat)
                <tr>
                        <td style="padding: 2px; border: 1px solid #ddd;">{{ $resultat['libelle'] }}</td>
                        <td style="padding: 2px; border: 1px solid #ddd;">{{ $resultat['qte'] }}</td>
                        <td style="padding: 2px; border: 1px solid #ddd;">{{ $resultat['price'] }}</td>
                        <td style="padding: 2px; border: 1px solid #ddd;">{{ $resultat['total_ttc'] }}</td>
                        <td style="padding: 2px; border: 1px solid #ddd;">{{ $resultat['prix_remise'] }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

         <!-- Total de la facture -->
         <div style="margin-top: 20px; width:70%;" id="total">
            <p><strong>HT:  </strong>{{ number_format($total_ttc-$total_ttc*20/100, 2, ',', '') }} €</p>
            <p><strong>TVA:    </strong> 20%</p>
            
            <p><strong>TTC (+ Frais de port - réduction):  </strong>{{ number_format($total_ttc, 2, ',', '') }} €</p>
            {{-- <p><strong>Remise {{$remise }} %    </strong>:  {{ number_format($total_ttc*$remise/100, 2, ',', '') }} €</p>
            <p><strong>Total T.T.C après remise :   </strong>{{ number_format($total_ttc-$total_ttc*$remise/100, 2, ',', '') }} €</p> --}}
        </div>
                


        <!-- Footer Section -->
        <div style="border-top: 1px solid #eee; padding-top: 10px; text-align: center; font-size: 12px; color: #333; margin-top: 70px;">
            Société par actions simplifiée unipersonnelle (SASU) - Capital de 75 000 €<br>
            SIRET: 803 752 609 00039 - NAF-APE: 9602B - Numéro TVA: FR23803752609
        </div>
    </div>
</body>
</html>
