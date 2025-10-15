<?php

namespace App\Http\Controllers;

use App\Events\CreateUser;
use App\Events\DefaultData;
use App\Events\DestroyUser;
use App\Events\EditProfileUser;
use App\Events\UpdateUser;
use App\Models\EmailTemplate;
use App\Models\LoginDetail;
use App\Models\Plan;
use App\Models\ReferralTransaction;
use App\Models\Role;
use App\Models\User;
use App\Models\WorkSpace;
use Illuminate\Http\Request;
use DataTables;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
use Illuminate\Auth\Events\Registered;
use Lab404\Impersonate\Impersonate;
use App\DataTables\UsersDataTable;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {

        if(Auth::user()->isAbleTo("user manage"))
        {
            if(Auth::user()->type == "super admin")
            {
                $roles =[];
                $users = User::where("type","company");

                if($request->name)
                {
                    $users->where("name", "like", "%" . $request->name . "%");
                }
                if($request->email)
                {
                    $users->where("email", "like", "%" . $request->email . "%");
                }
                if($request->cnpj)
                {
                    $users->where("cnpj", "like", "%" . $request->cnpj . "%");
                }
                if($request->celular)
                {
                    $users->where("celular", "like", "%" . $request->celular . "%");
                }
                if($request->cidade)
                {
                    $users->where("cidade", "like", "%" . $request->cidade . "%");
                }
                if($request->estado)
                {
                    $users->where("estado", "like", "%" . $request->estado . "%");
                }
                if($request->status !== null && $request->status !== "")
                {
                    $users->where("is_disable", $request->status == "1" ? 0 : 1);
                }
                $users = $users->paginate(11);
            }
            else
            {
                $roles = Role::where("created_by",creatorId())->pluck("name","id")->map(function ($name) {
                    return ucfirst($name);
                });
                if(Auth::user()->isAbleTo("workspace manage"))
                {
                    $users = User::where("created_by",creatorId())->where("workspace_id",getActiveWorkSpace());
                }
                else
                {
                    $users = User::where("created_by",creatorId());
                }

                if($request->name)
                {
                    $users->where("name", "like", "%" . $request->name . "%");
                }
                if($request->email)
                {
                    $users->where("email", "like", "%" . $request->email . "%");
                }
                if($request->role)
                {
                    $role = Role::find($request->role);
                    $users = $users->where("type",$role->name);
                }
                $users = $users->paginate(11);
            }
            return view("users.index",compact("users","roles"));
        }
        else
        {
            return redirect()->back()->with("error", __("Permission denied."));
        }
    }

    public function List(UsersDataTable $dataTable)
    {
        if(Auth::user()->isAbleTo("user manage"))
        {
            $roles = [];
            if(Auth::user()->type != "super admin")
            {
                $roles = Role::where("created_by",creatorId())->pluck("name","id")->map(function ($name) {
                    return ucfirst($name);
                });
            }
            return $dataTable->render("users.list",compact("roles"));
        }
        else
        {
            return redirect()->back()->with("error", __("Permission denied."));
        }
    }
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        if(Auth::user()->isAbleTo("user create"))
        {
            $roles = Role::where("created_by",creatorId())->pluck("name","id");
            return view("users.create",compact("roles"));
        }
        else
        {
            return response()->json(["error" => __("Permission denied.")], 401);
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if(Auth::user()->isAbleTo("user create"))
        {
            if(Auth::user()->type != "super admin"){
                $canUse=  PlanCheck("User",Auth::user()->id);
                if($canUse == false)
                {
                    return redirect()->back()->with("error", __("You have maxed out the total number of User allowed on your current plan"));
                }
            }
            if (Auth::user()->type == "super admin") {
                $validatorArray = [
                    "name" => "required|max:120",
                    "email" => ["required", "email",
                        Rule::unique("users")->where(function ($query) {
                            return $query->where("created_by", creatorId());
                        })
                    ],
                    "cnpj" => "nullable|string|max:18",
                    "inscricao_estadual" => "nullable|string|max:20",
                    "celular" => "nullable|string|max:15",
                    "cidade" => "nullable|string|max:255",
                    "estado" => "nullable|string|max:2",
                ];
            } else {
                $validatorArray = [
                    "name" => "required|max:120",
                    "roles" => "required|exists:roles,id",
                    "email" => ["required", "email",
                        Rule::unique("users")->where(function ($query) {
                            return $query->where("created_by", creatorId())
                                         ->where("workspace_id", getActiveWorkSpace());
                        })
                    ],
                ];
            }

            $validator = Validator::make($request->all(), $validatorArray);

            if($validator->fails())
            {
                return redirect()->back()->with("error", $validator->errors()->first());
            }
            $user["is_enable_login"]       = 0;
            if(!empty($request->password_switch) && $request->password_switch == "on")
            {
                $user["is_enable_login"]   = 1;
                $validator = Validator::make(
                    $request->all(), ["password" => "required|min:6"]
                );

                if($validator->fails())
                {
                    return redirect()->back()->with("error", $validator->errors()->first());
                }

                $userpassword               = $request->input("password");
            }

            if(Auth::user()->type == "super admin")
            {
                $roles = Role::where("name","company")->first();
            }
            else
            {
                $roles = Role::find($request->input("roles"));
            }
            $company_settings = getCompanyAllSetting();


            $user["name"]               = $request->input("name");
            $user["email"]              = $request->input("email");
            $user["password"]           = !empty($userpassword) ? \Hash::make($userpassword) : null;
            $user["lang"]               = !empty($company_settings["defult_language"]) ? $company_settings["defult_language"] : "en";
            $user["type"]               = $roles->name;
            $user["created_by"]         = creatorId();
            $user["workspace_id"]       = getActiveWorkSpace();
            $user["active_workspace"]   = getActiveWorkSpace();
            
            // New customer fields
            $user["cnpj"]               = $request->input("cnpj");
            $user["inscricao_estadual"] = $request->input("inscricao_estadual");
            $user["celular"]            = $request->input("celular");
            $user["informacoes_credito"] = $request->input("informacoes_credito");
            $user["latitude"]           = $request->input("latitude");
            $user["longitude"]          = $request->input("longitude");
            $user["cidade"]             = $request->input("cidade");
            $user["estado"]             = $request->input("estado");
            
            // Handle photo upload
            if($request->hasFile("foto")) {
                $foto = $request->file("foto");
                $fotoName = time() . "_" . $foto->getClientOriginalName();
                $fotoPath = $foto->storeAs("customers/photos", $fotoName, "public");
                $user["caminho_foto"] = $fotoPath;
            }
            
            // Handle documents upload
            if($request->hasFile("documentos")) {
                $documentos = [];
                foreach($request->file("documentos") as $documento) {
                    $docName = time() . "_" . $documento->getClientOriginalName();
                    $docPath = $documento->storeAs("customers/documents", $docName, "public");
                    $documentos[] = $docPath;
                }
                $user["caminho_documentos"] = json_encode($documentos);
            }
            
            $user = User::create($user);
            if(Auth::user()->type == "super admin")
            {
                    do {
                        $code = rand(100000, 999999);
                    } while (User::where("referral_code", $code)->exists());

                $company = User::find($user->id);

                 // create  WorkSpace
                $workspace = new WorkSpace();
                $workspace->name       = !empty($request->workSpace_name) ? $request->workSpace_name : $request->name;
                $workspace->created_by = $company->id;
                $workspace->save();

                $company->referral_code  = $code;
                $company->active_workspace = $workspace->id;
                $company->workspace_id = $workspace->id;
                $company->save();

                // make entry in user_workspaces table
                // User::AddUserToWorkspaces($company->id,$workspace->id,$roles->name); // Removido: método não existe

                // User Default Data
                event(new DefaultData($company->id,$workspace->id));

                // send email
                try
                {
                    $role_r = Role::find($roles->id);
                    $check_user_for_email_template = User::where("email",$user->email)->first();
                    $company_settings = getCompanyAllSetting();
                    $workspace_setting = getCompanyAllSetting($user->id,$workspace->id);

                    $uArr = [
                        "email" => $user->email,
                        "password" => $userpassword,
                        "company_name" => $user->name,
                        "workspace_name" => $workspace->name,
                        "app_name" => !empty($company_settings["app_name"]) ? $company_settings["app_name"] : env("APP_NAME"),
                        "app_url" => env("APP_URL"),
                    ];

                    $resp = EmailTemplate::sendEmailTemplate("New Company", [$check_user_for_email_template->id => $check_user_for_email_template->email], $uArr, $user->id,$workspace->id);
                }
                catch(\Exception $e)
                {
                    $resp["error"] = $e->getMessage();
                }
                return redirect()->route("users.index")->with("success", __("Company successfully created.") . ((isset($resp) && $resp["is_success"] == false && !empty($resp["error"])) ? "<br> <span class=\"text-danger\">" . $resp["error"] . "</span>" : ""));
            }
            else
            {
                // make entry in user_workspaces table
                // User::AddUserToWorkspaces($user->id,getActiveWorkSpace(),$roles->name); // Removido: método não existe

                try
                {
                    event(new CreateUser($user,$request));
                }
                catch(\Exception $e)
                {
                    $resp["error"] = $e->getMessage();
                }
                return redirect()->route("users.index")->with("success", __("User successfully created.") . ((isset($resp) && $resp["is_success"] == false && !empty($resp["error"])) ? "<br> <span class=\"text-danger\">" . $resp["error"] . "</span>" : ""));
            }
        }
        else
        {
            return redirect()->back()->with("error", __("Permission denied."));
        }
    }


    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        if(Auth::user()->isAbleTo("user show"))
        {
            $user = User::find($id);
            $roles = Role::where("created_by",creatorId())->pluck("name","id");
            return view("users.show",compact("user","roles"));
        }
        else
        {
            return redirect()->back()->with("error", __("Permission denied."));
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        if(Auth::user()->isAbleTo("user edit"))
        {
            $user = User::find($id);
            $roles = Role::where("created_by",creatorId())->pluck("name","id");
            return view("users.edit",compact("user","roles"));
        }
        else
        {
            return response()->json(["error" => __("Permission denied.")], 401);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        if(Auth::user()->isAbleTo("user edit"))
        {
            $user = User::find($id);
            if (Auth::user()->type == "super admin") {
                $validatorArray = [
                    "name" => "required|max:120",
                    "email" => ["required", "email",
                        Rule::unique("users")->where(function ($query) use ($id) {
                            return $query->where("created_by", creatorId())->where("id", "!=", $id);
                        })
                    ],
                    "cnpj" => "nullable|string|max:18",
                    "inscricao_estadual" => "nullable|string|max:20",
                    "celular" => "nullable|string|max:15",
                    "cidade" => "nullable|string|max:255",
                    "estado" => "nullable|string|max:2",
                ];
            } else {
                $validatorArray = [
                    "name" => "required|max:120",
                    "roles" => "required|exists:roles,id",
                    "email" => ["required", "email",
                        Rule::unique("users")->where(function ($query) use ($id) {
                            return $query->where("created_by", creatorId())
                                         ->where("workspace_id", getActiveWorkSpace())
                                         ->where("id", "!=", $id);
                        })
                    ],
                ];
            }

            $validator = Validator::make($request->all(), $validatorArray);

            if($validator->fails())
            {
                return redirect()->back()->with("error", $validator->errors()->first());
            }

            if(Auth::user()->type == "super admin")
            {
                $roles = Role::where("name","company")->first();
            }
            else
            {
                $roles = Role::find($request->input("roles"));
            }

            $user->name               = $request->input("name");
            $user->email              = $request->input("email");
            $user->type               = $roles->name;

            // New customer fields
            $user->cnpj               = $request->input("cnpj");
            $user->inscricao_estadual = $request->input("inscricao_estadual");
            $user->celular            = $request->input("celular");
            $user->informacoes_credito = $request->input("informacoes_credito");
            $user->latitude           = $request->input("latitude");
            $user->longitude          = $request->input("longitude");
            $user->cidade             = $request->input("cidade");
            $user->estado             = $request->input("estado");

            // Handle photo upload
            if($request->hasFile("foto")) {
                $foto = $request->file("foto");
                $fotoName = time() . "_" . $foto->getClientOriginalName();
                $fotoPath = $foto->storeAs("customers/photos", $fotoName, "public");
                $user->caminho_foto = $fotoPath;
            } elseif ($request->has("remove_foto") && $request->input("remove_foto") == "1") {
                // Remove existing photo if requested
                if ($user->caminho_foto) {
                    delete_file($user->caminho_foto);
                    $user->caminho_foto = null;
                }
            }
            
            // Handle documents upload
            if($request->hasFile("documentos")) {
                $documentos = $user->caminho_documentos ? json_decode($user->caminho_documentos, true) : [];
                foreach($request->file("documentos") as $documento) {
                    $docName = time() . "_" . $documento->getClientOriginalName();
                    $docPath = $documento->storeAs("customers/documents", $docName, "public");
                    $documentos[] = $docPath;
                }
                $user->caminho_documentos = json_encode($documentos);
            } elseif ($request->has("remove_documentos")) {
                // Remove selected documents if requested
                $documentos_to_keep = [];
                $existing_documents = $user->caminho_documentos ? json_decode($user->caminho_documentos, true) : [];
                foreach ($existing_documents as $doc_path) {
                    if (!in_array($doc_path, $request->input("remove_documentos"))) {
                        $documentos_to_keep[] = $doc_path;
                    } else {
                        delete_file($doc_path);
                    }
                }
                $user->caminho_documentos = json_encode($documentos_to_keep);
            }

            $user->save();

            event(new UpdateUser($user,$request));

            return redirect()->back()->with("success", __("User successfully updated."));
        }
        else
        {
            return redirect()->back()->with("error", __("Permission denied."));
        }
    }


    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        if(Auth::user()->isAbleTo("user delete"))
        {
            $user = User::find($id);
            if(Auth::user()->type == "super admin")
            {
                $workspaces = WorkSpace::where("created_by",$id)->get();
                foreach($workspaces as $workspace)
                {
                    $workspace->delete();
                }
            }
            $user->delete();
            event(new DestroyUser($user));

            return redirect()->back()->with("success", __("User successfully deleted."));
        }
        else
        {
            return redirect()->back()->with("error", __("Permission denied."));
        }
    }

    public function changePassword($id)
    {
        $user = User::find($id);
        if(Auth::user()->isAbleTo("user change password"))
        {
            return view("users.changepassword",compact("user"));
        }
        else
        {
            return response()->json(["error" => __("Permission denied.")], 401);
        }
    }

    public function changePasswordStore(Request $request,$id)
    {
        if(Auth::user()->isAbleTo("user change password"))
        {
            $validator = Validator::make($request->all(), [
                "password" => "required|confirmed|min:6",
            ]);

            if($validator->fails())
            {
                return redirect()->back()->with("error", $validator->errors()->first());
            }

            $user = User::find($id);
            $user->password = Hash::make($request->input("password"));
            $user->save();

            return redirect()->back()->with("success", __("Password successfully changed."));
        }
        else
        {
            return redirect()->back()->with("error", __("Permission denied."));
        }
    }

    public function LoginManage($id)
    {
        if(Auth::user()->isAbleTo("user login manage"))
        {
            $user = User::find($id);
            if($user->is_enable_login == 1)
            {
                $user->is_enable_login = 0;
                $user->save();
                $msg = __("User login disable successfully.");
            }
            else
            {
                $user->is_enable_login = 1;
                $user->save();
                $msg = __("User login enable successfully.");
            }
            return redirect()->back()->with("success", $msg);
        }
        else
        {
            return redirect()->back()->with("error", __("Permission denied."));
        }
    }

    public function UserUnable($id)
    {
        $user = User::find($id);
        if($user->is_disable == 1)
        {
            $user->is_disable = 0;
            $user->save();
            $msg = __("User successfully unable.");
        }
        else
        {
            $user->is_disable = 1;
            $user->save();
            $msg = __("User successfully disable.");
        }
        if(Auth::user()->type == "super admin"){
            $msg =  __("The customer has been verifed successfully.");
        }
        else{
            $msg =  __("The user has been verifed successfully.");
        }

        return redirect()->back()->with("success", $msg);
    }

    public function Counter($id)
    {
        $response = [];
        if(!empty($id))
        {
            $workspces= WorkSpace::where("created_by", $id)
            ->selectRaw("COUNT(*) as total_workspace, SUM(CASE WHEN is_disable = 0 THEN 1 ELSE 0 END) as disable_workspace, SUM(CASE WHEN is_disable = 1 THEN 1 ELSE 0 END) as active_workspace")
            ->first();
            $workspaces = WorkSpace::where("created_by",$id)->get();
            $users_data = [];
            foreach($workspaces as $workspce)
            {
                $users = User::where("created_by",$id)->where("workspace_id",$workspce->id)->selectRaw("COUNT(*) as total_users, SUM(CASE WHEN is_disable = 0 THEN 1 ELSE 0 END) as disable_users, SUM(CASE WHEN is_disable = 1 THEN 1 ELSE 0 END) as active_users")->first();

                $users_data[$workspce->name] = [
                    "workspace_id" => $workspce->id,
                    "total_users" => !empty($users->total_users) ? $users->total_users : 0,
                    "disable_users" => !empty($users->disable_users) ? $users->disable_users : 0,
                    "active_users" => !empty($users->active_users) ? $users->active_users : 0,
                ];
            }
            $workspce_data =[
                "total_workspace" =>  $workspces->total_workspace,
                "disable_workspace" => $workspces->disable_workspace,
                "active_workspace" => $workspces->active_workspace,
            ];
            $response["users_data"] = $users_data;
            $response["workspce_data"] = $workspce_data;

            return [
                "is_success" => true,
                "response" => $response,
            ];
        }
        return [
            "is_success" => false,
            "error" => __("Plan is deleted."),
        ];
    }

    public function verifeduser($id)
    {
        $user                    = User::find($id);
        $user->email_verified_at = date("Y-m-d h:i:s");
        $user->save();

        if(Auth::user()->type == "super admin"){
            $msg =  __("The customer has been verifed successfully.");
        }
        else{
            $msg =  __("The user has been verifed successfully.");
        }

        return redirect()->back()->with("success", $msg);
    }

    public function mapView()
    {
        if (\Auth::user()->isAbleTo("user manage")) {
            $users = User::whereNotNull("latitude")->whereNotNull("longitude")->get();
            return view("users.map", compact("users"));
        } else {
            return redirect()->back()->with("error", __("Permission denied."));
        }
    }
}

