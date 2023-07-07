@extends('layouts.email')

@section('content')

<table style="color:#000000" width="100%" border="0" cellspacing="0" cellpadding="0">
    <tr>
        <td align="center">
            <table style="max-width:600px;border-style:solid; border-width:1px; border-color:#c9c9c9;" width="100%" border="0" cellspacing="0" cellpadding="0">
                <tr>
                    <td align="center">
                       
                        <a href="">
                            <img src="assets{{ ('/images/elyamaje_logo_long_noir.png') }}" width="150px"; height="auto"; style="margin-top:35px;";>
                        </a>     

                        <img src="assets{{ ('/images/bg-themes/bg-email.jpg') }}" width="100%"; height="auto" style="border-top: 1px solid #c9c9c9; margin-top:25px;";>
                        
                        <h1>Bonjour </h1>
                        <p>Votre compte à été activé !</p>
                        <p></p>
                        <p>Voici le lien et le mot de passe pour vous connecter :</p>
                        <p></p>
                        <p>Lien : https://preparation.elyamaje.com</p>
                        <p>Identifiant :  </p>
                        <p>Mot de passe : </p>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>

@endsection

