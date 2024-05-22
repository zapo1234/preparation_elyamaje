@extends("layouts.app")

@section("style")
    <link href="{{asset('assets/plugins/datatable/css/dataTables.bootstrap5.min.css')}}" rel="stylesheet" />
@endsection

@section("wrapper")
    <div class="page-wrapper page-preparateur-order">
        <div class="page-content">
            <div class="page-breadcrumb d-sm-flex align-items-center mb-2">
                <div class="breadcrumb-title pe-3">Préparation</div>
                <div class="ps-3">kits</div>
                @csrf
            </div>

            <div class="wrapper_kit">
                @foreach($kits as $key => $value)
                    @if($value['image'])
                        <div class="box card">
                            <span class="text-center">{{ $value['name'] }}</span>
                            <img src="{{ asset('assets/images/products/' . $value['image']) }}"/>
                        </div>
                    @else
                        <div class="box card">
                            <span class="text-center">{{ $value['name'] }}</span>
                            <img src="{{ asset('assets/images/products/default_product.png') }}"/>
                        </div>
                    @endif
                @endforeach
            </div>

        </div>
    </div>


@endsection


@section("script")


@endsection