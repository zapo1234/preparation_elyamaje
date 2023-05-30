@extends('layouts.email')

@section('content')

<table style="color:#000000" width="100%" border="0" cellspacing="0" cellpadding="0">
    <tr>
        <td align="center">
            <table style="border-style:solid; border-width:1px; border-color:#edeff1;" width="auto" border="0" cellspacing="0" cellpadding="25">
                <tr>
                    <td align="center">
                        <h1>Bonjour {{ $email}} </h1>
                        <a href="">
                            <img src="assets/images/Logo_elyamaje.png" width="95px"; height="auto"; style="margin-top:20px;";>
                        </a>       
                        <p>Votre compte à été activé !</p>
                        <p></p>
                        <p>Voici le lien et le mot de passe pour vous connecter :</p>
                        <p></p>
                        <p>Lien : https://preparation.elyamaje.com</p>
                        <p>Mot de passe : {{ $password }}</p>
                        <p>L'équipe Elya Maje</p>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>

@endsection

