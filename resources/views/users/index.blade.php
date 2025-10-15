@extends("layouts.main")

@if (Auth::user()->type == "super admin")
    @section("page-title")
        {{ __("Customers") }}
    @endsection
    @section("page-breadcrumb")
        {{ __("Customers") }}
    @endsection
@else
    @section("page-title")
        {{ __("Users") }}
    @endsection
    @section("page-breadcrumb")
        {{ __("Users") }}
    @endsection
@endif

@section("page-action")
    <div class="d-flex">
        @permission("user logs history")
            <a href="{{ route("users.userlog.history") }}" class="btn btn-sm btn-primary me-2" data-bs-toggle="tooltip"
                data-bs-placement="top" title="{{ __("User Logs History") }}"><i class="ti ti-user-check"></i>
            </a>
        @endpermission
        @permission("user import")
            <a href="#" class="btn btn-sm btn-primary me-2" data-ajax-popup="true" data-title="{{ __("Import Customers") }}"
                data-url="{{ route("users.file.import") }}" data-bs-toggle="tooltip" title="{{ __("Import Customers") }}"><i
                    class="ti ti-file-import"></i>
            </a>
        @endpermission
        @permission("user manage")
            <a href="{{ route("users.list.view") }}" data-bs-toggle="tooltip" data-bs-original-title="{{ __("List View") }}"
                class="btn btn-sm btn-primary btn-icon me-2">
                <i class="ti ti-list"></i>
            </a>
            <a href="{{ route("users.map") }}" data-bs-toggle="tooltip" data-bs-original-title="{{ __("Ver clientes no mapa") }}"
                class="btn btn-sm btn-primary btn-icon me-2">
                <i class="ti ti-map"></i>
            </a>
        @endpermission
        @if (Auth::user()->type == "super admin")
            @permission("user create")
                <a href="#" class="btn btn-sm btn-primary" data-ajax-popup="true" data-size="md"
                    data-title="{{ __("Create New Customer") }}" data-url="{{ route("users.create") }}"
                    data-bs-toggle="tooltip" data-bs-original-title="{{ __("Create") }}">
                    <i class="ti ti-plus"></i>
                </a>
            @endpermission
        @else
            @permission("user create")
                <a href="#" class="btn btn-sm btn-primary" data-ajax-popup="true" data-size="md"
                    data-title="{{ __("Create New User") }}" data-url="{{ route("users.create") }}"
                    data-bs-toggle="tooltip" data-bs-original-title="{{ __("Create") }}">
                    <i class="ti ti-plus"></i>
                </a>
            @endpermission
        @endif
    </div>
@endsection

@section("content")
    <!-- [ Main Content ] start -->
    <div class="row row-gap-2 mb-4">
        @if (\Auth::user()->type == "super admin")
            <div class="" id="multiCollapseExample1">
                <div class="card mb-0">
                    <div class="card-body">
                        {{ Form::open(["route" => ["users.index"], "method" => "GET", "id" => "user_submit"]) }}
                        <div class="row d-flex align-items-center justify-content-end">
                            <div class="col-xl-2 col-lg-3 col-md-6 col-sm-12 col-12 mr-2 mb-3">
                                <div class="btn-box">
                                    {{ Form::label("name", __("Name"), ["class" => "form-label"]) }}
                                    {{ Form::text("name", isset($_GET["name"]) ? $_GET["name"] : null, ["class" => "form-control", "placeholder" => __("Enter Name")]) }}
                                </div>
                            </div>
                            <div class="col-xl-2 col-lg-3 col-md-6 col-sm-12 col-12 mr-2 mb-3">
                                <div class="btn-box">
                                    {{ Form::label("email", __("Email"), ["class" => "form-label"]) }}
                                    {{ Form::text("email", isset($_GET["email"]) ? $_GET["email"] : null, ["class" => "form-control", "placeholder" => __("Enter Email")]) }}
                                </div>
                            </div>
                            <div class="col-xl-2 col-lg-3 col-md-6 col-sm-12 col-12 mr-2 mb-3">
                                <div class="btn-box">
                                    {{ Form::label("role", __("Role"), ["class" => "form-label"]) }}
                                    {{ Form::select("role", $roles, isset($_GET["role"]) ? $_GET["role"] : "", ["class" => "form-control select text-capitalize", "placeholder" => __("All")]) }}
                                </div>
                            </div>
                            <div class="col-xl-2 col-lg-3 col-md-6 col-sm-12 col-12 mr-2 mb-3">
                                <div class="btn-box">
                                    {{ Form::label("cnpj", __("CNPJ"), ["class" => "form-label"]) }}
                                    {{ Form::text("cnpj", isset($_GET["cnpj"]) ? $_GET["cnpj"] : null, ["class" => "form-control", "placeholder" => __("Enter CNPJ")]) }}
                                </div>
                            </div>
                            <div class="col-xl-2 col-lg-3 col-md-6 col-sm-12 col-12 mr-2 mb-3">
                                <div class="btn-box">
                                    {{ Form::label("celular", __("Mobile Phone"), ["class" => "form-label"]) }}
                                    {{ Form::text("celular", isset($_GET["celular"]) ? $_GET["celular"] : null, ["class" => "form-control", "placeholder" => __("Enter Phone")]) }}
                                </div>
                            </div>
                            <div class="col-xl-2 col-lg-3 col-md-6 col-sm-12 col-12 mr-2 mb-3">
                                <div class="btn-box">
                                    {{ Form::label("cidade", __("City"), ["class" => "form-label"]) }}
                                    {{ Form::text("cidade", isset($_GET["cidade"]) ? $_GET["cidade"] : null, ["class" => "form-control", "placeholder" => __("Enter City")]) }}
                                </div>
                            </div>
                            <div class="col-xl-2 col-lg-3 col-md-6 col-sm-12 col-12 mr-2 mb-3">
                                <div class="btn-box">
                                    {{ Form::label("estado", __("State"), ["class" => "form-label"]) }}
                                    {{ Form::select("estado", [""=>__("All"), "AC"=>"AC", "AL"=>"AL", "AP"=>"AP", "AM"=>"AM", "BA"=>"BA", "CE"=>"CE", "DF"=>"DF", "ES"=>"ES", "GO"=>"GO", "MA"=>"MA", "MT"=>"MT", "MS"=>"MS", "MG"=>"MG", "PA"=>"PA", "PB"=>"PB", "PR"=>"PR", "PE"=>"PE", "PI"=>"PI", "RJ"=>"RJ", "RN"=>"RN", "RS"=>"RS", "RO"=>"RO", "RR"=>"RR", "SC"=>"SC", "SP"=>"SP", "SE"=>"SE", "TO"=>"TO"], isset($_GET["estado"]) ? $_GET["estado"] : "", ["class" => "form-control select"]) }}
                                </div>
                            </div>
                            <div class="col-xl-2 col-lg-3 col-md-6 col-sm-12 col-12 mr-2 mb-3">
                                <div class="btn-box">
                                    {{ Form::label("status", __("Status"), ["class" => "form-label"]) }}
                                    {{ Form::select("status", [""=>__("All"), "1"=>__("Active"), "0"=>__("Inactive")], isset($_GET["status"]) ? $_GET["status"] : "", ["class" => "form-control select"]) }}
                                </div>
                            </div>
                            <div class="col-auto float-end mt-4 d-flex">
                                <a href="#" class="btn btn-sm btn-primary me-2"
                                    onclick="document.getElementById("user_submit").submit(); return false;"
                                    data-bs-toggle="tooltip" title="{{ __("Apply") }}"
                                    data-original-title="{{ __("apply") }}">
                                    <span class="btn-inner--icon"><i class="ti ti-search"></i></span>
                                </a>
                                <a href="{{ route("users.index") }}" id="clearfilter" class="btn btn-sm btn-danger"
                                    data-bs-toggle="tooltip" title="{{ __("Reset") }}"
                                    data-original-title="{{ __("Reset") }}">
                                    <span class="btn-inner--icon"><i class="ti ti-trash-off text-white-off"></i></span>
                                </a>
                            </div>
                        </div>
                        {{ Form::close() }}
                    </div>
                </div>
            </div>
        @endif
        <div id="loading-bar-spinner" class="spinner">
            <div class="spinner-icon"></div>
        </div>
        @foreach ($users as $user)
            <div class="col-xxl-3 col-xl-4 col-md-6">
                <div class="card user-card">
                    <div class="card-header p-3 border border-bottom h-100">
                        @if (Auth::user()->type == "super admin")
                            <div class="d-flex align-items-center">
                                <span class="badge bg-primary p-2 px-3">{{ ucfirst($user->type) }}</span>
                            </div>
                            <div class="card-header-right">
                                @permission("user manage")
                                    <div class="btn-group card-option">
                                        <button type="button" class="btn dropdown-toggle" data-bs-toggle="dropdown"
                                            aria-haspopup="true" aria-expanded="true">
                                            <i class="feather icon-more-vertical"></i>
                                        </button>
                                        <div class="dropdown-menu dropdown-menu-end" data-popper-placement="bottom-end">
                                            @permission("user edit")
                                                <a href="#!" data-url="{{ route("users.edit", $user->id) }}"
                                                    data-ajax-popup="true" data-size="md" class="dropdown-item"
                                                    data-title="{{ __("Update Customer") }}"
                                                    data-bs-original-title="{{ __("Edit") }}">
                                                    <i class="ti ti-pencil"></i>
                                                    <span class="ms-2">{{ __("Edit") }}</span>
                                                </a>
                                            @endpermission
                                            @permission("user show")
                                                <a href="#!" data-url="{{ route("users.show", $user->id) }}"
                                                    data-ajax-popup="true" data-size="md" class="dropdown-item"
                                                    data-title="{{ __("View Customer") }}"
                                                    data-bs-original-title="{{ __("View") }}">
                                                    <i class="ti ti-eye"></i>
                                                    <span class="ms-2">{{ __("View") }}</span>
                                                </a>
                                            @endpermission
                                            @permission("user reset password")
                                                <a href="#!" data-url="{{ route("users.reset", \Crypt::encrypt($user->id)) }}"
                                                    data-ajax-popup="true" data-size="md" class="dropdown-item"
                                                    data-title="{{ __("Reset Password") }}"
                                                    data-bs-original-title="{{ __("Reset Password") }}">
                                                    <i class="ti ti-key"></i>
                                                    <span class="ms-2">{{ __("Reset Password") }}</span>
                                                </a>
                                            @endpermission
                                            @permission("user login manage")
                                                @if ($user->is_enable_login == 1)
                                                    <a href="{{ route("users.login", \Crypt::encrypt($user->id)) }}"
                                                        class="dropdown-item">
                                                        <i class="ti ti-road-sign"></i>
                                                        <span class="text-danger"> {{ __("Login Disable") }}</span>
                                                    </a>
                                                @elseif ($user->is_enable_login == 0 && $user->password == null)
                                                    <a href="#" data-url="{{ route("users.reset", \Crypt::encrypt($user->id)) }}"
                                                        data-ajax-popup="true" data-size="md" class="dropdown-item login_enable"
                                                        data-title="{{ __("New Password") }}" class="dropdown-item">
                                                        <i class="ti ti-road-sign"></i>
                                                        <span class="text-success"> {{ __("Login Enable") }}</span>
                                                    </a>
                                                @else
                                                    <a href="{{ route("users.login", \Crypt::encrypt($user->id)) }}"
                                                        class="dropdown-item">
                                                        <i class="ti ti-road-sign"></i>
                                                        <span class="text-success"> {{ __("Login Enable") }}</span>
                                                    </a>
                                                @endif
                                            @endpermission
                                            @permission("user delete")
                                                {!! Form::open(["method" => "DELETE", "route" => ["users.destroy", $user->id], "id" => "delete-form-" . $user->id]) !!}
                                                <a href="#!" class="dropdown-item bs-pass-para" data-id="{{ $user->id }}">
                                                    <i class="ti ti-trash"></i>
                                                    <span class="ms-2">{{ __("Delete") }}</span>
                                                </a>
                                                {!! Form::close() !!}
                                            @endpermission
                                        </div>
                                    </div>
                                @endpermission
                            </div>
                        @endif
                        <div class="text-center">
                            <div class="position-relative">
                                <img src="{{ check_file($user->avatar) ? get_file($user->avatar) : get_file("uploads/users-avatar/avatar.png") }}"
                                    alt="user-image" class="img-fluid rounded-circle" style="width: 100px; height: 100px;">
                            </div>
                            <h5 class="text-primary mt-3">{{ $user->name }}</h5>
                            <p class="text-muted">{{ $user->email }}</p>
                            <p class="mb-0">{{ $user->celular }}</p>
                            <div class="mt-3">
                                <span class="text-dark">{{ $user->plan_name }}</span>
                            </div>
                            <div class="mt-2">
                                <div class="d-flex justify-content-center">
                                    <p class="text-muted me-2">{{ __("Plan Expired : ") }}</p>
                                    <span
                                        class="text-dark">{{ !empty($user->plan_expire_date) ? company_date_formate($user->plan_expire_date) : "Unlimited" }}</span>
                                </div>
                            </div>
                            <div class="row mt-3">
                                <div class="col-6 text-center">
                                    <span class="text-dark">{{ $user->total_workspace }}</span>
                                    <p class="text-muted mb-0">{{ __("Workspaces") }}</p>
                                </div>
                                <div class="col-6 text-center">
                                    <span class="text-dark">{{ $user->total_user }}</span>
                                    <p class="text-muted mb-0">{{ __("Users") }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
        <div class="col-xl-3 col-lg-4 col-sm-6">
            @if (Auth::user()->type == "super admin")
                @permission("user create")
                    <a href="#" class="btn-addnew-project" data-ajax-popup="true" data-size="md"
                        data-title="{{ __("Create New Customer") }}" data-url="{{ route("users.create") }}">
                        <div class="bg-primary proj-add-icon">
                            <i class="ti ti-plus"></i>
                        </div>
                        <h6 class="mt-4 mb-2">{{ __("New Customer") }}</h6>
                        <p class="text-muted text-center">{{ __("Click here to add new customer") }}</p>
                    </a>
                @endpermission
            @else
                @permission("user create")
                    <a href="#" class="btn-addnew-project" data-ajax-popup="true" data-size="md"
                        data-title="{{ __("Create New User") }}" data-url="{{ route("users.create") }}">
                        <div class="bg-primary proj-add-icon">
                            <i class="ti ti-plus"></i>
                        </div>
                        <h6 class="mt-4 mb-2">{{ __("New User") }}</h6>
                        <p class="text-muted text-center">{{ __("Click here to add new user") }}</p>
                    </a>
                @endpermission
            @endif
        </div>
    </div>
    <!-- [ Main Content ] end -->
@endsection

