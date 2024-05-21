@extends("layouts.app")

@section("style")
    <link href="{{asset('assets/plugins/datatable/css/dataTables.bootstrap5.min.css')}}" rel="stylesheet" />
@endsection

@section("wrapper")
    <div class="page-wrapper page-preparateur-order">
        <div class="page-content comming_soon">
            <div class="page-breadcrumb d-sm-flex align-items-center mb-2">
                <div class="breadcrumb-title pe-3">Préparation</div>
                <div class="ps-3">kits</div>
                @csrf
            </div>

        </div>
    </div>


@endsection


@section("script")


@endsection