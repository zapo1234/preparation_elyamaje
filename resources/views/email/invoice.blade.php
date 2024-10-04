@extends('layouts.email')

@section('content')

<style>
    body {
        font-family: Helvetica, Arial, sans-serif;
    }

    body p{
        color: #000000;
        font-size: 14px;
    }
</style>

<table style="color:#00000 !important; font-size:15px;" width="100%" border="0" cellspacing="0" cellpadding="0">
    <tr>
        <td align="center">
        <table style="max-width:600px;border-style:solid; border-width:1px; border-color:#c9c9c9;" width="100%" border="0" cellspacing="0" cellpadding="25">
            <tr>
                <td class="header" style="text-align:center;">
                    <div style="text-align:center;padding: 0px;">
                        <img src="https://www.connect.elyamaje.com/admin/uploads/logo_caisse.png" style="width: 170px;">
                        <h1 style="color: #1A2028; text-align:center; margin-top: 40px; font-size: 22px; font-weight: bold; margin-bottom: 15px;">Bonjour {{ $name }},</h1>
                    </div>
                    <p style="text-align:center; line-height: 17px;">Toute l'Équipe Elyamaje vous remercie pour votre venue à son Gala de Marseille 2024.</p>
                    <p style="text-align:center; line-height: 17px;">Nous avons le plaisir de vous informer que votre commande est actuellement en cours de préparation. 
                    Elle vous sera livrée sous un délai de 72H.</p>

                    <p style="text-align:center; line-height: 17px;">Vous trouverez, ci-joint, votre facture N° {{ $ref_order }}</p>
                    <p style="text-align:center; line-height: 17px;"> Pour toutes questions, vous pouvez contacter notre support en cliquant <a style="color: #F07289; font-weight:bold;" href="https://elyamaje.zendesk.com/hc/fr">ici</a>.</p>
                    <p style="text-align:center; line-height: 17px;">À bientôt !</p>
                    <p style="text-align:center; line-height: 17px;">L'Équipe Elyamaje</p>
                </td>
            </tr>
            <tr style="background-color: #000000;">
                <td style="width: 100%; padding:0; margin:0; text-align:center;">
                    <img src="{{ asset('assets/images/elyamaje_logo_long_blanc.png') }}" style="margin-top: 40px; text-align: center; width: 130px;">
                </td>
            </tr>
            <tr>
                <td style="padding:0; margin:0; text-align: center; background-color: #000000;">
                    <a href="https://www.instagram.com/elya.maje/"><img src="{{ asset('assets/images/icons/instagram.png') }}" style="margin-top: 15px; text-align: center; width: 18px;"></a>
                    <a href="https://www.tiktok.com/@elyamaje?lang=fr"><img src="{{ asset('assets/images/icons/tiktok.png') }}" style="margin-left: 20px; text-align: center; width: 18px;"></a>
                    <a href="https://www.facebook.com/ElyaMaje/"><img src="{{ asset('assets/images/icons/facebook.png') }}" style="margin-left: 20px; text-align: center; width: 18px;"></a>
                </td>
            </tr>
            <tr>
                <td style="padding:0; margin:0; text-align: center; background-color: #000000;">
                    <a style="text-decoration: none;" href="https://www.elyamaje.com"><p style="color: white;">elyamaje.com</p></a>
                    <p style="color: white; font-size: 10px;">16 Boulevard Gueidon, 13013 Marseille</p>
                    <p style="color: white; font-size: 10px; margin-bottom: 30px;">© 2024 Elyamaje. Tous droits réservés</p>
                </td>
            </tr>
            </table>
        </td> 
    </tr>        
</table>

@endsection
