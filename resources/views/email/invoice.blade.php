@extends('layouts.email')

@section('content')

<table style="color:#00000 !important; font-size:15px;" width="100%" border="0" cellspacing="0" cellpadding="0">
    <tr>
        <td align="center">
            <tr>
                <td class="header" style="text-align:center;">
                    <div style="text-align:center;padding: 10px;">
                        <img src="https://www.connect.elyamaje.com/admin/uploads/logo_caisse.png" style="height: 50px;">
                        <h1 style="text-align:center;">Elyamaje vous remercie pour votre visite sur le Salon Beauty Prof 2024</h1>
                    </div>
                     
                    <p style="text-align:center;">L'équipe d'Elyamaje tient à vous remercier pour votre enthousiasme et la confiance que vous nous avez témoigné lors de l'événement Beauty Prof de Paris ce week-end. En raison du volume élevé de commandes, nous avons rencontré une erreur technique indépendante de notre volonté lors de la génération de votre facture initiale.</p>
                    <p style="text-align:center;">Nous vous prions de bien vouloir trouver ci-joint votre facture définitive. N° {{ $ref_order }}</p>
                    <div style="text-align:center;">
                        <p style="width:100%;"> 
                         <br><br><strong style="font-size:18px;"></strong></p> 
                        <p style="width:100%;">Nous nous excusons pour tout inconvénient que cela aurait pu causer et vous remercions pour votre compréhension.</p>
                        <p>Cordialement</p>
                        <p style="width:100%;">L'Équipe Elyamaje</p>

                        </div>
                </td>
             </tr>
        </td> 
    </tr>        
    <tr>
        <td class="footer" style="text-align:center;">
            <div class="conditions" style="padding:20px;background-color:#ededed;border-radius:20px; width:90%;margin:10px auto 50px auto;">
                <ul style="width:90%; text-align:left; margin:0 auto; font-size:14px">
                    <li>Les échanges, remboursements & modifications de commandes ne pourront être effectués sur le salon.</li>
                    <li>Pour toute réclamation concernant votre commande veuillez nous contacter par message via notre <a href="https://elyamaje.zendesk.com/hc/fr">espace SAV</a>.</li>
                    <li>Pour toute information supplémentaire, veuillez consulter nos <a href="https://www.elyamaje.com/c-g-v/">conditions générales de vente</a>.</li>
                </ul>        
                <p style="font-size:12px; text-align:center; width:100%;"></p>
            </div>
        </td>
    </tr>
            </table>
        </td>
    </tr>