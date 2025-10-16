<div class="card" id="pdv-sidenav">
    {{ Form::open(['route' => 'pdv.setting.store']) }}
    <div class="card-header p-3">
        <h5 class="">{{ __('PDV Settings') }}</h5>
    </div>
    <div class="card-body p-3 pb-0">
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label for="low_product_stock_threshold"
                        class="form-label">{{ __('Low Product Stock Threshold') }}</label>
                    <input type="number" name="low_product_stock_threshold" class="form-control"
                        placeholder="{{ __('Low Product Stock Threshold') }}"
                        value="{{ !empty($settings['low_product_stock_threshold']) ? $settings['low_product_stock_threshold'] : '' }}"
                        id="low_product_stock_threshold">
                </div>
            </div>
        </div>
    </div>
    <div class="card-footer text-end p-3">
        <input class="btn btn-print-invoice  btn-primary" type="submit" value="{{ __('Save Changes') }}">
    </div>
    {{ Form::close() }}
</div>


<div id="pdv-print-sidenav" class="card">
    <div class="card-header p-3">
        <h5>{{ __('PDV Print Settings') }}</h5>
        <small class="text-muted">{{ __('Edit details about your Company Bill') }}</small>
    </div>
    <div class="bg-none">
        <div class="row company-setting">
            <form id="setting-form" method="post" action="{{ route('pdv.template.setting') }}"
                enctype="multipart/form-data">
                @csrf
                <div class="card-body border-bottom border-1 p-3 pb-0">
                    <div class="row">
                        <div class="col-sm-4">
                            <div class="form-group">
                                {{ Form::label('pdv_prefix', __('Prefix'), ['class' => 'form-label']) }}
                                {{ Form::text('pdv_prefix', isset($settings['pdv_prefix']) && !empty($settings['pdv_prefix']) ? $settings['pdv_prefix'] : '#PUR', ['class' => 'form-control', 'placeholder' => __('Enter PDV Prefix')]) }}
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="form-group">
                                {{ Form::label('pdv_footer_title', __('Footer Title'), ['class' => 'form-label']) }}
                                {{ Form::text('pdv_footer_title', isset($settings['pdv_footer_title']) && !empty($settings['pdv_footer_title']) ? $settings['pdv_footer_title'] : '', ['class' => 'form-control', 'placeholder' => __('Enter Footer Title')]) }}
                            </div>
                        </div>
                        <div class="col-xxl-8">
                            <div class="form-group">
                                {{ Form::label('pdv_footer_notes', __('Footer Notes'), ['class' => 'form-label']) }}
                                {{ Form::textarea('pdv_footer_notes', isset($settings['pdv_footer_notes']) && !empty($settings['pdv_footer_notes']) ? $settings['pdv_footer_notes'] : '', ['class' => 'form-control', 'rows' => '2', 'placeholder' => __('Enter PDV Footer Notes')]) }}
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body p-3">
                    <div class="row row-gap">
                        <div class="col-md-4">
                            <div class="card">
                                <div class="card-body p-2">
                                    <div class="form-group d-flex align-items-center justify-content-between mb-0">
                                        {{ Form::label('pdv_shipping_display', __('Shipping Display?'), ['class' => 'form-label mb-0']) }}
                                        <div class=" form-switch form-switch-left">
                                            <input type="checkbox" class="form-check-input" name="pdv_shipping_display"
                                                id="pdv_shipping_display"
                                                {{ isset($settings['pdv_shipping_display']) && $settings['pdv_shipping_display'] == 'on' ? 'checked' : '' }}>
                                            <label class="form-check-label" for="pdv_shipping_display"></label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="card">
                                <div class="card-body p-2">
                                    <div class="form-group d-flex flex-wrap align-items-center gap-2 mb-0">
                                        <label for="address" class="form-label mb-0">{{ __('PDV Template') }}</label>
                                        <select class="form-control flex-1" name="pdv_template">
                                            @foreach (Workdo\PDV\Entities\PDV::templateData()['templates'] as $key => $template)
                                                <option value="{{ $key }}"
                                                    {{ isset($settings['pdv_template']) && !empty($settings['pdv_template']) && $settings['pdv_template'] == $key ? 'selected' : '' }}>
                                                    {{ $template }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="card">
                                <div class="card-header p-2">
                                    <h6 class="mb-0">{{ __('Color Input') }}</h6>
                                </div>
                                <div class="card-body p-2">
                                    @foreach (Workdo\PDV\Entities\PDV::templateData()['colors'] as $key => $color)
                                        <label class="colorinput">
                                            <input name="pdv_color" type="radio" value="{{ $color }}"
                                                class="colorinput-input"
                                                {{ !empty($settings['pdv_color']) && $settings['pdv_color'] == $color ? 'checked' : '' }}>
                                            <span class="colorinput-color rounded-circle"
                                                style="background: #{{ $color }}"></span>
                                        </label>
                                @endforeach
                                </div>
                            </div>
                            <div class="card">
                                <div class="card-header p-2">
                                    <h3 class="h6 mb-0">{{ __('Logo')}}</h3>
                                </div>
                                <div class="card-body setting-card setting-logo-box p-3">
                                    <div class="logo-content img-fluid logo-set-bg  text-center">
                                        <img alt="image" src="{{ isset($settings['pdv_logo']) ? get_file($settings['pdv_logo']) : get_file('uploads/logo/logo_dark.png') }}" id="pre_pdv_logo">
                                    </div>
                                    <div class="choose-files text-center  mt-3">
                                        <label for="blah8">
                                            <div class="bg-primary"> <i class="ti ti-upload px-1"></i>{{ __('Choose file here')}}</div>
                                            <input type="file" class="form-control file" name="pdv_logo" id="blah8" data-filename="blah8" onchange="document.getElementById('pre_pdv_logo').src = window.URL.createObjectURL(this.files[0])">
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group pt-4 mb-0 text-left">
                                <input type="submit" value="{{ __('Save Changes') }}"
                                    class="btn btn-print-invoice  btn-primary">
                            </div>
                        </div>
                        <div class="col-md-8">
                            @if (isset($settings['pdv_template']) &&
                                    isset($settings['pdv_color']) &&
                                    !empty($settings['pdv_template']) &&
                                    !empty($settings['pdv_color']))
                                <iframe id="pdv_frame" class="w-100 h-100 rounded-1" frameborder="0"
                                    src="{{ route('pdv.preview', [$settings['pdv_template'], $settings['pdv_color']]) }}"></iframe>
                            @else
                                <iframe id="pdv_frame" class="w-100 h-100 rounded-1" frameborder="0"
                                    src="{{ route('pdv.preview', ['template1 ', 'fffff']) }}"></iframe>
                            @endif
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>


<script>
    $(document).on("change", "select[name='pdv_template'], input[name='pdv_color']", function() {

        var template = $("select[name='pdv_template']").val();
        var color = $("input[name='pdv_color']:checked").val();
        $('#pdv_frame').attr('src', '{{ url('/pdv/preview') }}/' + template + '/' + color);
    });

    $(document).on("change", "select[name='purchase_template'], input[name='purchase_color']", function() {
        var template = $("select[name='purchase_template']").val();
        var color = $("input[name='purchase_color']:checked").val();
        $('#purchase_frame').attr('src', '{{ url('/purchase/preview') }}/' + template + '/' + color);
    });

    document.getElementById('purchase_logo').onchange = function() {
        var src = URL.createObjectURL(this.files[0])
        document.getElementById('purchase_image').src = src
    }


    document.getElementById('pdv_logo').onchange = function() {
        var src = URL.createObjectURL(this.files[0])
        document.getElementById('pdv_image').src = src
    }
</script>
