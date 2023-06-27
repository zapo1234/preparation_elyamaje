@extends('layouts.email')

@section('content')

<table style="color:#000000" width="100%" border="0" cellspacing="0" cellpadding="0">
    <tr>
        <td align="center">
            <table style="border-style:solid; border-width:1px; border-color:#edeff1;" width="auto" border="0" cellspacing="0" cellpadding="25">
                <tr>
                    <td align="center">
                        <h1>Bonjour {{ $name }}</h1>
                        <a href="">
                            <img src="{{ asset('assets/images/Logo_elyamaje.png')}}" width="95px"; height="auto"; style="margin-top:20px;";>
                        </a>       
                        <p>La commande #{{ $order_id }} n'a pas pu être complétée,</p>
                        <p>Note : {{ $note_partial_order ?? 'Aucune' }}</p>
                        <p>merci de consulter son état sur votre tableaux de bord</p>
                        <p></p>
                        <p>L'équipe Elya Maje</p>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>

@endsection

