{{Form::model($user,array('route' => array('users.update', $user->id), 'method' => 'PUT','class'=>'needs-validation','novalidate','enctype'=>'multipart/form-data')) }}
    <div class="modal-body">
        <div class="row">
            @if(Auth::user()->type == 'super admin')
                <div class="col-md-12">
                    <div class="form-group">
                        {{Form::label('name',__('Name'),['class'=>'form-label']) }}<x-required></x-required>
                        {{Form::text('name',null,array('class'=>'form-control','placeholder'=>__('Enter Customer Name'),'required'=>'required'))}}
                    </div>
                </div>
                <div class="col-md-12">
                    <div class="form-group">
                        {{Form::label('email',__('Email'),['class'=>'form-label'])}}<x-required></x-required>
                        {{Form::email('email',null,array('class'=>'form-control','placeholder'=>__('Enter Customer Email'),'required'=>'required'))}}
                    </div>
                </div>

                <!-- CNPJ Field with Auto-fill -->
                <div class="col-md-12">
                    <div class="form-group">
                        {{Form::label('cnpj',__('CNPJ'),['class'=>'form-label'])}}
                        <div class="input-group">
                            {{Form::text('cnpj',null,array('class'=>'form-control','placeholder'=>__('Enter CNPJ'),'id'=>'cnpj_input'))}}
                            <button type="button" class="btn btn-secondary" id="btn_buscar_cnpj">
                                <i class="ti ti-search"></i> {{__('Search')}}
                            </button>
                        </div>
                        <small class="form-text text-muted">{{__('Format: 00.000.000/0000-00')}}</small>
                    </div>
                </div>

                <!-- State Registration -->
                <div class="col-md-12">
                    <div class="form-group">
                        {{Form::label('inscricao_estadual',__('State Registration'),['class'=>'form-label'])}}
                        {{Form::text('inscricao_estadual',null,array('class'=>'form-control','placeholder'=>__('Enter State Registration'),'id'=>'inscricao_estadual'))}}
                    </div>
                </div>

                <!-- Mobile Phone -->
                <div class="col-md-12">
                    <div class="form-group">
                        {{Form::label('celular',__('Mobile Phone'),['class'=>'form-label'])}}
                        {{Form::text('celular',null,array('class'=>'form-control','placeholder'=>__('(00) 00000-0000'),'id'=>'celular'))}}
                    </div>
                </div>

                <!-- Credit Information -->
                <div class="col-md-12">
                    <div class="form-group">
                        {{Form::label('informacoes_credito',__('Credit Information'),['class'=>'form-label'])}}
                        {{Form::textarea('informacoes_credito',null,array('class'=>'form-control','placeholder'=>__('Enter credit information, limits, payment terms, etc.'),'rows'=>'3'))}}
                    </div>
                </div>

                <!-- Customer Photo -->
                <div class="col-md-12">
                    <div class="form-group">
                        {{Form::label('foto',__('Customer Photo'),['class'=>'form-label'])}}
                        @if(!empty($user->caminho_foto))
                            <div class="mb-2">
                                <img src="{{ asset('storage/'.$user->caminho_foto) }}" alt="Customer Photo" style="max-width: 150px; max-height: 150px; border-radius: 5px;">
                            </div>
                        @endif
                        {{Form::file('foto',array('class'=>'form-control','accept'=>'image/*'))}}
                        <small class="form-text text-muted">{{__('Accepted formats: JPG, PNG, GIF (Max: 2MB)')}}</small>
                    </div>
                </div>

                <!-- Documents -->
                <div class="col-md-12">
                    <div class="form-group">
                        {{Form::label('documentos',__('Documents'),['class'=>'form-label'])}}
                        @if(!empty($user->caminho_documentos))
                            <div class="mb-2">
                                <small class="text-muted">{{__('Current documents:')}}</small>
                                <ul class="list-unstyled">
                                    @php
                                        $docs = json_decode($user->caminho_documentos, true);
                                        if(is_array($docs)) {
                                            foreach($docs as $doc) {
                                                echo '<li><a href="'.asset('storage/'.$doc).'" target="_blank">'.basename($doc).'</a></li>';
                                            }
                                        }
                                    @endphp
                                </ul>
                            </div>
                        @endif
                        {{Form::file('documentos[]',array('class'=>'form-control','multiple'=>'multiple','accept'=>'.pdf,.doc,.docx,.jpg,.jpeg,.png'))}}
                        <small class="form-text text-muted">{{__('You can select multiple files. Accepted formats: PDF, DOC, DOCX, JPG, PNG')}}</small>
                    </div>
                </div>

                <!-- Address Section for Map Integration -->
                <div class="col-md-12">
                    <hr>
                    <h5>{{__('Address Information')}}</h5>
                </div>

                <div class="col-md-8">
                    <div class="form-group">
                        {{Form::label('endereco_completo',__('Full Address'),['class'=>'form-label'])}}
                        {{Form::text('endereco_completo',null,array('class'=>'form-control','placeholder'=>__('Street, Number, Neighborhood, City, State'),'id'=>'endereco_completo'))}}
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="form-group">
                        {{Form::label('cep',__('ZIP Code'),['class'=>'form-label'])}}
                        {{Form::text('cep',null,array('class'=>'form-control','placeholder'=>__('00000-000'),'id'=>'cep'))}}
                    </div>
                </div>

                <!-- Map Display -->
                <div class="col-md-12">
                    <div class="form-group">
                        <div id="map_container" style="height: 300px; border: 1px solid #ddd; border-radius: 5px; display: {{ !empty($user->latitude) && !empty($user->longitude) ? 'block' : 'none' }};">
                            <div id="customer_map" style="height: 100%; width: 100%;"></div>
                        </div>
                        <button type="button" class="btn btn-sm btn-info mt-2" id="btn_show_map">
                            <i class="ti ti-map"></i> {{__('Show on Map')}}
                        </button>
                    </div>
                </div>

                <!-- Hidden fields for coordinates -->
                {{Form::hidden('latitude',null,array('id'=>'latitude'))}}
                {{Form::hidden('longitude',null,array('id'=>'longitude'))}}

            @else
                <div class="col-md-12">
                    <div class="form-group">
                        {{Form::label('name',__('Name'),['class'=>'form-label']) }}<x-required></x-required>
                        {{Form::text('name',null,array('class'=>'form-control','placeholder'=>__('Enter User Name'),'required'=>'required'))}}
                    </div>
                </div>
                <div class="col-md-12">
                    <div class="form-group">
                        {{Form::label('email',__('Email'),['class'=>'form-label'])}}<x-required></x-required>
                        {{Form::email('email',null,array('class'=>'form-control','placeholder'=>__('Enter User Email'),'required'=>'required'))}}
                    </div>
                </div>

                <!-- Mobile Phone for non-super admin -->
                <div class="col-md-12">
                    <div class="form-group">
                        {{Form::label('celular',__('Mobile Phone'),['class'=>'form-label'])}}
                        {{Form::text('celular',null,array('class'=>'form-control','placeholder'=>__('(00) 00000-0000'),'id'=>'celular'))}}
                    </div>
                </div>
            @endif
            <x-mobile value="{{ !empty($user->mobile_no) ? $user->mobile_no : null }}"></x-mobile>
        </div>
    </div>
    <div class="modal-footer">
        <button type="button" class="btn  btn-light" data-bs-dismiss="modal">{{__('Cancel')}}</button>
        {{Form::submit(__('Update'),array('class'=>'btn  btn-primary'))}}
    </div>
{{Form::close()}}

<!-- JavaScript for CNPJ, Phone Mask, and Map Integration -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // CNPJ Mask
    const cnpjInput = document.getElementById('cnpj_input');
    if (cnpjInput) {
        cnpjInput.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length <= 14) {
                value = value.replace(/^(\d{2})(\d)/, '$1.$2');
                value = value.replace(/^(\d{2})\.(\d{3})(\d)/, '$1.$2.$3');
                value = value.replace(/\.(\d{3})(\d)/, '.$1/$2');
                value = value.replace(/(\d{4})(\d)/, '$1-$2');
            }
            e.target.value = value;
        });
    }

    // Mobile Phone Mask (DDD + Number)
    const celularInput = document.getElementById('celular');
    if (celularInput) {
        celularInput.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length <= 11) {
                value = value.replace(/^(\d{2})(\d)/, '($1) $2');
                value = value.replace(/(\d{5})(\d)/, '$1-$2');
            }
            e.target.value = value;
        });
    }

    // CEP Mask
    const cepInput = document.getElementById('cep');
    if (cepInput) {
        cepInput.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length <= 8) {
                value = value.replace(/^(\d{5})(\d)/, '$1-$2');
            }
            e.target.value = value;
        });

        // CEP Auto-fill
        cepInput.addEventListener('blur', function(e) {
            const cep = e.target.value.replace(/\D/g, '');
            if (cep.length === 8) {
                fetch(`https://viacep.com.br/ws/${cep}/json/`)
                    .then(response => response.json())
                    .then(data => {
                        if (!data.erro) {
                            const enderecoInput = document.getElementById('endereco_completo');
                            if (enderecoInput && !enderecoInput.value) {
                                enderecoInput.value = `${data.logradouro}, ${data.bairro}, ${data.localidade} - ${data.uf}`;
                            }
                        }
                    })
                    .catch(error => console.error('Error fetching CEP:', error));
            }
        });
    }

    // CNPJ Search Button
    const btnBuscarCnpj = document.getElementById('btn_buscar_cnpj');
    if (btnBuscarCnpj) {
        btnBuscarCnpj.addEventListener('click', function() {
            const cnpj = cnpjInput.value.replace(/\D/g, '');
            if (cnpj.length === 14) {
                // Show loading state
                btnBuscarCnpj.disabled = true;
                btnBuscarCnpj.innerHTML = '<i class="ti ti-loader"></i> Buscando...';

                // Call CNPJ API (using BrasilAPI)
                fetch(`https://brasilapi.com.br/api/cnpj/v1/${cnpj}`)
                    .then(response => response.json())
                    .then(data => {
                        // Fill form fields with API data
                        const nameInput = document.querySelector('input[name="name"]');
                        if(nameInput && !nameInput.value) {
                            nameInput.value = data.razao_social || data.nome_fantasia || '';
                        }
                        
                        const emailInput = document.querySelector('input[name="email"]');
                        if(emailInput && !emailInput.value && data.email) {
                            emailInput.value = data.email;
                        }
                        
                        const inscricaoInput = document.getElementById('inscricao_estadual');
                        if (inscricaoInput) {
                            inscricaoInput.value = data.inscricao_estadual || '';
                        }

                        const celularInputFill = document.getElementById('celular');
                        if (celularInputFill && data.ddd_telefone_1) {
                            celularInputFill.value = data.ddd_telefone_1;
                        }

                        const enderecoInput = document.getElementById('endereco_completo');
                        if (enderecoInput) {
                            const endereco = `${data.logradouro || ''}, ${data.numero || ''}, ${data.bairro || ''}, ${data.municipio || ''} - ${data.uf || ''}`;
                            enderecoInput.value = endereco;
                        }

                        const cepInputFill = document.getElementById('cep');
                        if (cepInputFill && data.cep) {
                            const cepFormatted = data.cep.replace(/^(\d{5})(\d)/, '$1-$2');
                            cepInputFill.value = cepFormatted;
                        }

                        // Show success message
                        alert('Dados do CNPJ carregados com sucesso!');
                    })
                    .catch(error => {
                        console.error('Error fetching CNPJ:', error);
                        alert('Erro ao buscar dados do CNPJ. Verifique o número e tente novamente.');
                    })
                    .finally(() => {
                        btnBuscarCnpj.disabled = false;
                        btnBuscarCnpj.innerHTML = '<i class="ti ti-search"></i> Buscar';
                    });
            } else {
                alert('Por favor, insira um CNPJ válido com 14 dígitos.');
            }
        });
    }

    // Map Integration
    let map = null;
    let marker = null;

    // Initialize map if coordinates exist
    @if(!empty($user->latitude) && !empty($user->longitude))
        if (typeof L !== 'undefined') {
            map = L.map('customer_map').setView([{{ $user->latitude }}, {{ $user->longitude }}], 15);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© OpenStreetMap contributors'
            }).addTo(map);
            marker = L.marker([{{ $user->latitude }}, {{ $user->longitude }}]).addTo(map);
        }
    @endif

    const btnShowMap = document.getElementById('btn_show_map');
    if (btnShowMap) {
        btnShowMap.addEventListener('click', function() {
            const endereco = document.getElementById('endereco_completo').value;
            if (!endereco) {
                alert('Por favor, insira um endereço para visualizar no mapa.');
                return;
            }

            const mapContainer = document.getElementById('map_container');
            mapContainer.style.display = 'block';

            // Initialize map if not already initialized
            if (!map) {
                // Using OpenStreetMap with Leaflet
                if (typeof L !== 'undefined') {
                    map = L.map('customer_map').setView([-23.5505, -46.6333], 13); // Default: São Paulo
                    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                        attribution: '© OpenStreetMap contributors'
                    }).addTo(map);
                }
            }

            // Geocode address
            fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(endereco)}&countrycodes=br`)
                .then(response => response.json())
                .then(data => {
                    if (data && data.length > 0) {
                        const lat = parseFloat(data[0].lat);
                        const lon = parseFloat(data[0].lon);

                        // Update map view
                        if (map) {
                            map.setView([lat, lon], 15);
                            
                            // Remove old marker
                            if (marker) {
                                map.removeLayer(marker);
                            }
                            
                            // Add new marker
                            marker = L.marker([lat, lon]).addTo(map);
                        }

                        // Update hidden fields
                        document.getElementById('latitude').value = lat;
                        document.getElementById('longitude').value = lon;
                    } else {
                        alert('Endereço não encontrado. Por favor, verifique o endereço informado.');
                    }
                })
                .catch(error => {
                    console.error('Error geocoding address:', error);
                    alert('Erro ao buscar localização. Tente novamente.');
                });
        });
    }
});
</script>

<!-- Leaflet CSS and JS for Map -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

