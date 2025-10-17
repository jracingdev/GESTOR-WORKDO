@extends('layouts.app')

@section('page-title')
    {{__('Asaas Settings')}}
@endsection

@section('content')
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-body">
                    <form method="post" action="{{ route('asaas.setting.store') }}">
                        @csrf
                        <h4 class="mb-4">{{__('Asaas API Configuration')}}</h4>
                        <div class="row">
                            <div class="form-group col-md-6">
                                <label class="form-label">{{__('Asaas API Key')}}</label>
                                <input type="text" name="asaas_api_key" class="form-control" value="{{ setting('asaas_api_key') }}" required>
                            </div>
                            <div class="form-group col-md-6">
                                <label class="form-label">{{__('Asaas Environment')}}</label>
                                <select name="asaas_environment" class="form-control">
                                    <option value="sandbox" {{ setting('asaas_environment') == 'sandbox' ? 'selected' : '' }}>{{__('Sandbox')}}</option>
                                    <option value="production" {{ setting('asaas_environment') == 'production' ? 'selected' : '' }}>{{__('Production')}}</option>
                                </select>
                            </div>
                            <div class="form-group col-md-12">
                                <label class="form-label">{{__('Webhook URL')}}</label>
                                <input type="text" class="form-control" value="{{ url('/api/asaas/webhook') }}" readonly>
                                <small class="form-text text-muted">{{__('Copy this URL and paste it into your Asaas account webhook configuration.')}}</small>
                            </div>
                        </div>
                        <div class="text-right">
                            <button type="submit" class="btn btn-primary">{{__('Save Changes')}}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
