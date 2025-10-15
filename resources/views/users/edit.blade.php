
{{Form::model($user,array(
    'route' => array('users.update', $user->id),
    'method' => 'PUT',
    'class'=>'needs-validation',
    'novalidate',
    'enctype'=>'multipart/form-data'
)) }}
    <div class="modal-body">
        <div class="row">
            @if(Auth::user()->type == 'super admin')
                <div class="col-md-12">
                    <div class="form-group">
                        {{Form::label('name',__('Name'),['class'=>'form-label']) }}<x-required></x-required>
                        {{Form::text('name',$user->name,array('class'=>'form-control','placeholder'=>__('Enter Customer Name'),'required'=>'required'))}}
                    </div>
                </div>
                <div class="col-md-12">
                    <div class="form-group">
                        {{Form::label('email',__('Email'),['class'=>'form-label'])}}<x-required></x-required>
                        {{Form::email('email',$user->email,array('class'=>'form-control','placeholder'=>__('Enter Customer Email'),'required'=>'required'))}}
                    </div>
                </div>

                <!-- CNPJ Field with Auto-fill -->
                <div class="col-md-12">
                    <div class="form-group">
                        {{Form::label('cnpj',__('CNPJ'),['class'=>'form-label'])}}
                        <div class="input-group">
                            {{Form::text('cnpj',$user->cnpj,array('class'=>'form-control','placeholder'=>__('Enter CNPJ'),'id'=>'cnpj_input'))}}
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
                        {{Form::text('inscricao_estadual',$user->inscricao_estadual,array('class'=>'form-control','placeholder'=>__('Enter State Registration'),'id'=>'inscricao_estadual'))}}
                    </div>
                </div>

                <!-- Credit Information -->
                <div class="col-md-12">
                    <div class="form-group">
                        {{Form::label('informacoes_credito',__('Credit Information'),['class'=>'form-label'])}}
                        {{Form::textarea('informacoes_credito',$user->informacoes_credito,array('class'=>'form-control','placeholder'=>__('Enter credit information, limits, payment terms, etc.'),'rows'=>'3'))}}
                    </div>
                </div>

                <!-- Customer Photo -->
                <div class="col-md-12">
                    <div class="form-group">
                        {{Form::label('foto',__('Customer Photo'),['class'=>'form-label'])}}
                        @if(!empty($user->caminho_foto))
                            <div class="mb-2">
                                <img src="{{ asset('storage/'.$user->caminho_foto) }}" alt="Customer Photo" style="max-width: 150px; max-height: 150px; border-radius: 5px;">
                                <div class="form-check mt-2">
                                    <input class="form-check-input" type="checkbox" name="remove_foto" id="remove_foto" value="1">
                                    <label class="form-check-label" for="remove_foto">{{__('Remove current photo')}}</label>
                                </div>
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
                                    @endphp
                                    @if(is_array($docs))
                                        @foreach($docs as $index => $doc)
                                            <li>
                                                <a href="{{ asset('storage/'.$doc) }}" target="_blank">{{ basename($doc) }}</a>
                                                <div class="form-check form-check-inline">
                                                    <input class="form-check-input" type="checkbox" name="remove_documentos[]" id="remove_doc_{{ $index }}" value="{{ $doc }}">
                                                    <label class="form-check-label" for="remove_doc_{{ $index }}">{{__('Remove')}}</label>
                                                </div>
                                            </li>
                                        @endforeach
                                    @endif
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

                <div class="col-md-4">
                    <div class="form-group">
                        {{Form::label('cep',__('ZIP Code'),['class'=>'form-label'])}}
                        {{Form::text('cep',$user->cep,array('class'=>'form-control','placeholder'=>__('00000-000'),'id'=>'cep'))}}
                    </div>
                </div>
                <div class="col-md-8">
                    <div class="form-group">
                        {{Form::label('endereco_completo',__('Address'),['class'=>'form-label'])}}
                        {{Form::text('endereco_completo',$user->endereco_completo,array('class'=>'form-control','placeholder'=>__('Street, Number, Neighborhood'),'id'=>'endereco_completo'))}}
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        {{Form::label('cidade',__('City'),['class'=>'form-label'])}}
                        {{Form::text('cidade',$user->cidade,array('class'=>'form-control','placeholder'=>__('Enter City'),'id'=>'cidade'))}}
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        {{Form::label('estado',__('State'),['class'=>'form-label'])}}
                        {{Form::text('estado',$user->estado,array('class'=>'form-control','placeholder'=>__('Enter State'),'id'=>'estado'))}}
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
                {{Form::hidden('latitude',$user->latitude,array('id'=>'latitude'))}}
                {{Form::hidden('longitude',$user->longitude,array('id'=>'longitude'))}}

            @else
                <div class="col-md-12">
                    <div class="form-group">
                        {{Form::label('name',__('Name'),['class'=>'form-label']) }}<x-required></x-required>
                        {{Form::text('name',$user->name,array('class'=>'form-control','placeholder'=>__('Enter User Name'),'required'=>'required'))}}
                    </div>
                </div>
                <div class="col-md-12">
                    <div class="form-group">
                        {{Form::label('email',__('Email'),['class'=>'form-label'])}}<x-required></x-required>
                        {{Form::email('email',$user->email,array('class'=>'form-control','placeholder'=>__('Enter User Email'),'required'=>'required'))}}
                    </div>
                </div>
            @endif

            <div class="col-md-12">
                <div class="form-group">
                    {{ Form::label('roles', __('Roles'),['class'=>'form-label']) }}<x-required></x-required>
                    {{ Form::select('roles',$roles, null, ['class' => 'form-control','placeholder'=>'Select Role', 'id' => 'user_id','required'=>'required']) }}
                    <div class=" text-xs mt-1">
                        <span class="text-danger text-xs">{{ __('Unable to modify this user`s role. Please ensure that the correct role has been assigned to this user.') }}</span><br>
                        {{ __('Create role here. ') }}
                        <a href="{{ route('roles.index') }}"><b>{{ __('Create role') }}</b></a>
                    </div>
                </div>
            </div>

            <div class="col-md-12">
                <div class="form-group">
                    {{Form::label('celular',__('Mobile Phone'),['class'=>'form-label'])}}
                    {{Form::text('celular',$user->celular,array('class'=>'form-control','placeholder'=>__('(00) 00000-0000'),'id'=>'celular_input'))}}
                </div>
            </div>
            <x-mobile value="{{ !empty($user->mobile_no) ? $user->mobile_no : null }}"></x-mobile>

            <div class="col-md-5 mb-3">
                <label for="password_switch">{{ __('Login is enable') }}</label>
                <div class="form-check form-switch custom-switch-v1 float-end">
                    <input type="checkbox" name="password_switch" class="form-check-input input-primary pointer" value="on" id="password_switch" {{ company_setting('password_switch')=='on'?' checked ':'' }}>
                    <label class="form-check-label" for="password_switch"></label>
                </div>
            </div>
            <div class="col-md-12 ps_div d-none">
                <div class="form-group">
                    {{Form::label('password',__('Password'),['class'=>'form-label'])}}
                    {{Form::password('password',array('class'=>'form-control','placeholder'=>__('Enter User Password'),'minlength'=>"6"))}}
                </div>
            </div>
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
    const celularInput = document.getElementById('celular_input');
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

    // CEP Mask and Auto-fill
    const cepInput = document.getElementById('cep');
    if (cepInput) {
        cepInput.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length <= 8) {
                value = value.replace(/^(\d{5})(\d)/, '$1-$2');
            }
            e.target.value = value;

            if (value.replace('-', '').length === 8) {
                fetch(`https://viacep.com.br/ws/${value.replace('-', '')}/json/`)
                    .then(response => response.json())
                    .then(data => {
                        if (!data.erro) {
                            const enderecoInput = document.getElementById('endereco_completo');
                            if (enderecoInput && !enderecoInput.value) {
                                enderecoInput.value = `${data.logradouro || ''}, ${data.bairro || ''}`;
                            }
                            const cidadeInput = document.getElementById('cidade');
                            if (cidadeInput && !cidadeInput.value) {
                                cidadeInput.value = data.localidade;
                            }
                            const estadoInput = document.getElementById('estado');
                            if (estadoInput && !estadoInput.value) {
                                estadoInput.value = data.uf;
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

                        const celularInputFill = document.getElementById('celular_input');
                        if (celularInputFill && data.ddd_telefone_1) {
                             celularInputFill.value = `(${data.ddd_telefone_1}) ${data.telefone_1 || ''}`.replace(/\D/g, '').replace(/^(\d{2})(\d)/, '($1) $2').replace(/(\d{5})(\d)/, '$1-$2');
                        }

                        const enderecoInput = document.getElementById('endereco_completo');
                        if (enderecoInput) {
                            enderecoInput.value = `${data.logradouro || ''}, ${data.numero || 'S/N'}, ${data.bairro || ''}`;
                        }
                        
                        const cidadeInputFill = document.getElementById('cidade');
                        if (cidadeInputFill) {
                            cidadeInputFill.value = data.municipio || '';
                        }

                        const estadoInputFill = document.getElementById('estado');
                        if (estadoInputFill) {
                            estadoInputFill.value = data.uf || '';
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
            const mapContainer = document.getElementById('map_container');
            mapContainer.style.display = 'block';
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
            const cidade = document.getElementById('cidade').value;
            const estado = document.getElementById('estado').value;
            const fullAddress = `${endereco}, ${cidade}, ${estado}`;

            if (!endereco || !cidade || !estado) {
                alert('Por favor, insira um endereço, cidade e estado para visualizar no mapa.');
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
            fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(fullAddress)}&countrycodes=br`)
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

