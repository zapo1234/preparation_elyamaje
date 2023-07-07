@extends('layouts.email')

@section('content')

<table style="color:#000000" width="100%" border="0" cellspacing="0" cellpadding="0">
    <tr>
        <td align="center">
            <table style="max-width:600px;border-style:solid; border-width:1px; border-color:#c9c9c9;" width="100%" border="0" cellspacing="0" cellpadding="0">
                <tr>
                    <td align="center">
                        <a href="">
                            <img src="assets{{ ('assets/images/elyamaje_logo_long_noir.png') }}" width="150px"; height="auto"; style="margin-top:35px;";>
                        </a>     

                        <img src="assets{{ ('assets/images/bg-themes/bg-email.jpg') }}" width="100%"; height="auto" style="border-top: 1px solid #c9c9c9; margin-top:25px;";>     
                        </br>  
                        <h1>Bonjour</h1>        
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
                        </br>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>

@endsection


