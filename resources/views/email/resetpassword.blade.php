@extends('layouts.email')

@section('content')

<table style="color:#000000" width="100%" border="0" cellspacing="0" cellpadding="0">
    <tr>
        <td align="center">
            <table style="border-style:solid; border-width:1px; border-color:#edeff1;" width="auto" border="0" cellspacing="0" cellpadding="25">
                <tr>
                    <td align="center">
                        <h1>Bonjour</h1>
                        <a href="">
                            <img src="{{ asset('assets/images/Logo_elyamaje.png')}}" width="95px"; height="auto"; style="margin-top:20px;";>
                        </a>       
                        <p>Vous avez fait une demande de réinitialisation de mot de passe</p>
                        <p></p>
                        <p>Cliquez ci-dessous afin d'effectuer la modification :</p>
                        <p></p>
                        <button class="resetpassword" style="display:block:color:black"><a href="{{ route('auth.passwords.reset') }}?token={{ $token }}">Réinitialiser mot de passe !</a></button>
                        <p></p>
                        <p>L'équipe Elya Maje</p>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>

@endsection

