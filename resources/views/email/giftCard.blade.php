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
                        </br>
                        <p style="border-top: 1px solid #c9c9c9;"></p>
                        <img src="{{ asset('assets/images/bg-themes/gift_card.png') }}" width="200px"; height="auto" style="margin-top:15px;";>     
                        </br>
                        <h1>Bonjour</h1>
                        <p>Voici votre carte cadeau Elyamaje ci-dessous :</p>
                        <p style="font-weight: bold;">{{ $gift_card }}</p>
                        <p></p>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>

@endsection

