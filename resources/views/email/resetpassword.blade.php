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
                        <a 
                            style="letter-spacing: .5px;color: #fff;background-color: #212529;
                                border-color: #212529;border-radius: 30px;
                                padding-right: 10%;padding-left: 10%;padding-top: 2%;padding-bottom: 2%;text-decoration:none" 
                            type="button" 
                            class="btn btn-dark px-5 radius-30"
                            href="{{ route('auth.passwords.reset') }}?token={{ $token }}"
                        >
                            Réinitialiser mot de passe !
                        </a>
                        <p></p>
                        <p>L'équipe Elya Maje</p>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>

@endsection

