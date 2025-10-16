@extends('layouts.main')
@section('page-title')
    {{__('Manage Product Barcode')}}
@endsection
@section('page-breadcrumb')
    {{__('PDV Product Barcode')}}
@endsection
@push('css')
    @include('layouts.includes.datatable-css')
@endpush

@section('page-action')
    <div class="d-flex">
        @permission('print_barcode manage')
            <a href="{{ route('pdv.print') }}" class="btn btn-sm btn-primary me-2" data-bs-toggle="tooltip" title="{{__('Print Barcode')}}">
                <i class="ti ti-scan text-white"></i>
            </a>
            <a data-url="{{ route('pdv.setting') }}" data-ajax-popup="true" data-bs-toggle="tooltip" data-title="{{__('Barcode Setting')}}" title="{{__('Barcode Setting')}}" class="btn btn-sm btn-primary">
                <i class="ti ti-settings text-white"></i>
            </a>
        @endpermission
    </div>
@endsection

@section('content')
    <div class="row mt-3">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body table-border-style">
                    <div class="table-responsive ">
                        {{ $dataTable->table(['width' => '100%']) }}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@push('scripts')
@include('layouts.includes.datatable-js')
{{ $dataTable->scripts() }}

    <script src="{{ asset('packages/workdo/PDV/src/Resources/assets/js/jquery-barcode.min.js') }}"></script>
    <script src="{{ asset('packages/workdo/PDV/src/Resources/assets/js/jquery-barcode.js') }}"></script>
    <script>

        function generateBarcode(val, id) {
            var value = val;
            var btype = '{{ $barcode['barcodeType'] }}';
            var renderer = '{{ $barcode['barcodeFormat'] }}';
            var settings = {
                output: renderer,
                bgColor: '#FFFFFF',
                color: '#000000',
                barWidth: '1',
                barHeight: '50',
                moduleSize: '5',
                pdvX: '10',
                pdvY: '20',
                addQuietZone: '1'
            };
            $('#' + id).html("").show().barcode(value, btype, settings);

        }


    </script>

@endpush
