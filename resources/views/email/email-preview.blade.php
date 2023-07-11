@extends('layouts.email')

@section('content')

<table style="font-family:math;color:#000000" width="100%" border="0" cellspacing="0" cellpadding="0">
    <tr>
        <td align="center">
            <table style="max-width:600px;border-style:solid; border-width:1px; border-color:#c9c9c9;" width="100%" border="0" cellspacing="0" cellpadding="25">
                <tr>
                    <td align="center">
                       
                        <a href="">
                            <img src="{{ asset('assets/images/elyamaje_logo_long_noir.png') }}" width="150px"; height="auto"; style="margin-top:10px;";>
                        </a>     

                        <img src="{{ asset('assets/images/bg-themes/bg-email.jpg') }}" width="100%"; height="auto" style="border-top: 1px solid #c9c9c9; margin-top:25px;";>
                        
                        <h1>Bonjour</h1>        
                        <p>Ceci est le modèle d'email</p>
                        <p></p>
                        <p style="margin-bottom:50px">Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut</p>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>

@endsection

