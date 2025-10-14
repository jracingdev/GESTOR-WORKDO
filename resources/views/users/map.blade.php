@extends("layouts.main")

@section("page-title")
    {{ __("Mapa de Clientes") }}
@endsection

@section("page-breadcrumb")
    {{ __("Clientes") }}, {{ __("Mapa") }}
@endsection

@push("css")
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
    <style>
        #map {
            height: 600px;
        }
    </style>
@endpush

@section("content")
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5>{{ __("Mapa de Clientes") }}</h5>
                </div>
                <div class="card-body">
                    <div id="map"></div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push("scripts")
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            var map = L.map("map").setView([-14.235004, -51.92528], 4);

            L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
            }).addTo(map);

            @foreach ($users as $user)
                L.marker([{{ $user->latitude }}, {{ $user->longitude }}])
                    .addTo(map)
                    .bindPopup("<b>{{ $user->name }}</b><br>{{ $user->endereco_completo }}");
            @endforeach
        });
    </script>
@endpush
@endpush
