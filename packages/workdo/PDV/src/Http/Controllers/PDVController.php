<?php

namespace Workdo\PDV\Http\Controllers;

use Workdo\FiscalBR\Library\NFCeService;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Validator;
use Rawilk\Settings\Support\Context;
use App\Models\Purchase;
use App\Models\Warehouse;
use Workdo\PDV\Entities\PDV;
use App\Models\WarehouseProduct;
use Workdo\PDV\Entities\PDVProduct;
use Workdo\PDV\Entities\PDVPayment;
use Workdo\PDV\Entities\PDVUtility;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use App\Models\User;
use App\Models\Setting;
use App\Models\WorkSpace;
use Workdo\PDV\DataTables\PDVOrderDataTable;
use DB;
use Workdo\PDV\Events\CreatePaymentPos;
use Stripe\Product;
use Workdo\PDV\DataTables\BarcodeDataTable;
use Workdo\Quotation\Entities\Quotation;
use Workdo\Quotation\Entities\QuotationProduct;
use Workdo\Account\Entities\Customer;
use Workdo\FiscalBR\Entities\NFe;
class PDVController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function __construct()
    {
        if(module_is_active("GoogleAuthentication"))
        {
            $this->middleware("2fa");
        }
    }
    public function index(Request $request)
    {
        if (\Auth::user()->isAbleTo("pdv add manage"))
        {
            session()->forget("pdv");
            $customers=[];
            $customers      = User::where("type","client")->where("created_by", creatorId())->where("workspace_id",getActiveWorkSpace())->get()->pluck("name", "name");
            $customers->prepend("Walk-in-customer", "Walk-in-customer");
            $user = \Auth::user();

            $details = [
                "pdv_id" =>PDV::posNumberFormat($this->invoicePosNumber()),
                "customer" => $customers != null ? $customers->toArray() : [],
                "user" => $user != null ? $user->toArray() : [],
                "date" => date("Y-m-d"),
                "pay" => "show",
            ];
            $warehouses = warehouse::select("*", \DB::raw("CONCAT(name) AS name"))->where("created_by", creatorId())->where("workspace",getActiveWorkSpace())->get()->pluck("name", "id");

            $cart = session()->get("pdv");
            if (isset($cart) && count($cart) > 0)
            {
                if(module_is_active("ProductService"))
                {
                    $product = \Workdo\ProductService\Entities\ProductService::where("created_by", creatorId())->where("workspace_id",getActiveWorkSpace())->whereIn("id",array_keys($cart))->get();
                    if(count($product) == 0)
                    {
                        session()->forget("pdv");
                    }
                }
            }

            if (isset($request->quotation_id)) {
                $quotation = Quotation::find($request->quotation_id);
                $customer = "";
                $warehouseId = "";
                if($quotation)
                {
                    $customer= Customer::where("user_id",$quotation->customer_id)->first();
                    if(empty($customer))
                    {
                        $customer = User::find($quotation->customer_id);
                    }
                    $customer = $customer->name;

                    $warehouseId = $quotation->warehouse_id;

                    $quotationProduct = QuotationProduct::where("quotation_id", $request->quotation_id)->get();

                    foreach ($quotationProduct as $value) {
                        $products = Quotation::quotationProduct($value);
                    }
                }
            } else {
                $customer = "";
                $warehouseId = "";
            }

            $id = !empty($request->quotation_id) ? $request->quotation_id : "0";

            return view("pdv::pos.index",compact("customers","warehouses","details","customer", "warehouseId", "id"));        
        }
        else
        {
            return redirect()->back()->with("error", __("Permission denied."));
        }
    }

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create(Request $request)
    {
        $sess = session()->get("pdv");
            if (Auth::user()->isAbleTo("pdv add manage") && isset($sess) && !empty($sess) && count($sess) > 0) {
            $user = Auth::user();

            if(module_is_active("Account"))
            {
                $user =  User::where("name", "=", $request->vc_name)->where("type","client")->where("created_by", creatorId())->where("workspace_id",getActiveWorkSpace())->first();
                if(!empty($user->id))
                {

                    $customer = \Workdo\Account\Entities\Customer::where("user_id",$user->id)->where("name", "=", $request->vc_name)->where("created_by", creatorId())->where("workspace",getActiveWorkSpace())->first();
                    if(!empty($customer)){
                        $customer = $customer;
                    }
                    else{
                        $customer = $user;
                    }
                }
                else{
                    $customer = NULL;
                    $user = Auth::user();
                }
            }
            else
            {
                $customer      = User::where("name", "=", $request->vc_name)->where("type","client")->where("created_by", creatorId())->where("workspace_id",getActiveWorkSpace())->first();
            }
		    $user = \Auth::user();
            $warehouse = warehouse::where("id", "=", $request->warehouse_name)->where("created_by", creatorId())->where("created_by", creatorId())->where("workspace",getActiveWorkSpace())->first();

            $details = [
                "pdv_id" => PDV::posNumberFormat($this->invoicePosNumber()),
                "customer" => $customer != null ? $customer->toArray() : [],
                "warehouse" => $warehouse != null ? $warehouse->toArray() : [],
                "user" => $user != null ? $user->toArray() : [],
                "date" => date("Y-m-d"),
                "pay" => "show",
            ];
            if (!empty($details["customer"]["billing_state"]))
            {

                $warehousedetails = "<h7 class=\"text-dark\">" . ucfirst($details["warehouse"]["name"])  . "</p></h7>";
                $details["customer"]["billing_state"] = $details["customer"]["billing_state"] != "" ? ", " . $details["customer"]["billing_state"] : "";
                $details["customer"]["shipping_state"] = $details["customer"]["shipping_state"] != "" ? ", " . $details["customer"]["shipping_state"] : "";

                $customerdetails = "<h6 class=\"text-dark\">" . ucfirst($details["customer"]["name"]) . "<p class=\"m-0 h6 font-weight-normal\">" . $details["customer"]["billing_phone"] . "</p>" . "<p class=\"m-0 h6 font-weight-normal\">" . $details["customer"]["billing_address"] . "</p>" . "<p class=\"m-0 h6 font-weight-normal\">" . $details["customer"]["billing_city"] . $details["customer"]["billing_state"] . "</p>" . "<p class=\"m-0 h6 font-weight-normal\">" . $details["customer"]["billing_country"] . "</p>" . "<p class=\"m-0 h6 font-weight-normal\">" . $details["customer"]["billing_zip"] . "</p></h6>";

                $shippdetails = "<h6 class=\"text-dark\"><b>" . ucfirst($details["customer"]["name"]) . "</b>" . "<p class=\"m-0 h6 font-weight-normal\">" . $details["customer"]["shipping_phone"] . "</p>" . "<p class=\"m-0 h6 font-weight-normal\">" . $details["customer"]["shipping_address"] . "</p>" . "<p class=\"m-0 h6 font-weight-normal\">" . $details["customer"]["shipping_city"] . $details["customer"]["shipping_state"] . "</p>" . "<p class=\"m-0 h6 font-weight-normal\">" . $details["customer"]["shipping_country"] . "</p>" . "<p class=\"m-0 h6 font-weight-normal\">" . $details["customer"]["shipping_zip"] . "</p></h6>";

            }
            else {

                if (!empty($details["customer"]))
                {
                    $customerdetails = "<h6 class=\"text-dark\">" . ucfirst($details["customer"]["name"]) .  "</p></h6>";
                }
                else{

                    $customerdetails = "<h2 class=\"h6\"><b>" . __("Walk-in Customer") . "</b><h2>";
                }
                $warehousedetails = "<h7 class=\"text-dark\">" . ucfirst($details["warehouse"]["name"])  . "</p></h7>";
                $shippdetails = "-";

            }

            $settings["company_telephone"] = company_setting("company_telephone") != "" ? ", " . company_setting("company_telephone") : "";
            $settings["company_state"]     = company_setting("company_state") != "" ? ", " . company_setting("company_state") : "";

            $userdetails = "<h6 class=\"text-dark\"><b>" . ucfirst($details["user"]["name"]) . " </b> <h2  class=\"font-weight-normal\">" . "<p class=\"m-0 font-weight-normal\">" . company_setting("company_name") . "</p>" . "<p class=\"m-0 font-weight-normal\">" . company_setting("company_telephone") . "</p>" . "<p class=\"m-0 font-weight-normal\">" . company_setting("company_address") . "</p>" . "<p class=\"m-0 h6 font-weight-normal\">" . company_setting("company_city") . ", " . company_setting("company_state") . "</p>" . "<p class=\"m-0 font-weight-normal\">" . company_setting("company_country") . "</p>" . "<p class=\"m-0 font-weight-normal\">" . company_setting("company_zipcode") . "</p></h2>";

            $details["customer"]["details"] = $customerdetails;
            $details["warehouse"]["details"] = $warehousedetails;
            $details["customer"]["shippdetails"] = $shippdetails;

            $details["user"]["details"] = $userdetails;

            $mainsubtotal = 0;
            $sales        = [];
            foreach ($sess as $key => $value) {
                $subtotal = $value["price"] * $value["quantity"];
                $tax      = ($subtotal * $value["tax"]) / 100;
                $sales["data"][$key]["name"]       = $value["name"];
                $sales["data"][$key]["quantity"]   = $value["quantity"];
                $sales["data"][$key]["price"]      = currency_format_with_sym($value["price"]);
                $sales["data"][$key]["tax"]        = $value["tax"] . "%";
                $sales["data"][$key]["product_tax"] = $value["product_tax"];
                $sales["data"][$key]["tax_amount"] = currency_format_with_sym($tax);
                $sales["data"][$key]["subtotal"]   = currency_format_with_sym($value["subtotal"]);
                $mainsubtotal                      += $value["subtotal"];
            }

            if($request->discount <= $mainsubtotal){
                $discount=!empty($request->discount)?$request->discount:0;
            }
            else{
                $discount=$mainsubtotal;
            }

            $sales["sub_total"] = currency_format_with_sym($mainsubtotal);
            $total = $mainsubtotal - $discount;
            $sales["total"] = currency_format_with_sym($total);
            $sales["discount"] = currency_format_with_sym($discount);

            session()->forget("pdv");

            return view("pdv::pdv.create", compact("sales", "details"));
        }
        else {
            return redirect()->back()->with("error", __("Cart is empty!"));
        }
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Renderable
     */
    public function store(Request $request)
    {
        if (\Auth::user()->isAbleTo("pdv add manage"))
        {
            $user_id = \Auth::user()->id;
            $customer_name = $request->vc_name;
            $warehouse_name = $request->warehouse_name;
            $pos_id = $this->invoicePosNumber();
            $workspace_id = getActiveWorkSpace();
            $created_by = creatorId();

            $details = [
                "pos_id" => $pos_id,
                "customer_name" => $customer_name,
                "warehouse_name" => $warehouse_name,
                "user_id" => $user_id,
                "workspace_id" => $workspace_id,
                "created_by" => $created_by,
            ];

            $pos = PDV::create($details);

            $products = $request->items;

            for ($i = 0; $i < count($products); $i++)
            {
                $posProduct = new PDVProduct();
                $posProduct->pos_id = $pos->id;
                $posProduct->product_id = $products[$i]["id"];
                $posProduct->quantity = $products[$i]["quantity"];
                $posProduct->tax = $products[$i]["tax"];
                $posProduct->price = $products[$i]["price"];
                $posProduct->discount = $products[$i]["discount"];
                $posProduct->workspace = $workspace_id;
                $posProduct->save();

                if(module_is_active("ProductService"))
                {
                    PDV::updateProductStock($products[$i]["id"], $products[$i]["quantity"], "minus");
                }
            }

            $posPayment = new PDVPayment();
            $posPayment->pos_id = $pos->id;
            $posPayment->date = $request->date;
            $posPayment->amount = $request->total;
            $posPayment->discount = $request->discount;
            $posPayment->discount_amount = $request->discount_amount;
            $posPayment->workspace = $workspace_id;
            $posPayment->save();

            // Emissão da NFC-e
            try {
                $nfe = new NFe();
                $nfe->setVersao("4.00");
                $nfe->setInfNFeId("");
                $nfe->setInfNFeVersao("4.00");
                $nfe->setIdeCUF("35");
                $nfe->setIdeCNF("12345678");
                $nfe->setIdeNatOp("VENDA");
                $nfe->setIdeIndPag("0");
                $nfe->setIdeMod("65");
                $nfe->setIdeSerie("1");
                $nfe->setIdeNNF("12345");
                $nfe->setIdeDhEmi(date("Y-m-d\TH:i:sP"));
                $nfe->setIdeDhSaiEnt(date("Y-m-d\TH:i:sP"));
                $nfe->setIdeTpNF("1");
                $nfe->setIdeIdDest("1");
                $nfe->setIdeCMunFG("3550308");
                $nfe->setIdeTpImp("4");
                $nfe->setIdeTpEmis("1");
                $nfe->setIdeCDV("1");
                $nfe->setIdeTpAmb("2");
                $nfe->setIdeFinNFe("1");
                $nfe->setIdeIndFinal("1");
                $nfe->setIdeIndPres("1");
                $nfe->setIdeProcEmi("0");
                $nfe->setIdeVerProc("1.0");

                $nfe->setEmitCnpj("01234567890123");
                $nfe->setEmitXNome("Empresa Teste");
                $nfe->setEmitXFant("Empresa Teste");
                $nfe->setEmitIe("123456789012");
                $nfe->setEmitCrt("1");
                $nfe->setEnderEmitXLgr("Rua Teste");
                $nfe->setEnderEmitNro("123");
                $nfe->setEnderEmitXBairro("Bairro Teste");
                $nfe->setEnderEmitCMun("3550308");
                $nfe->setEnderEmitXMun("Sao Paulo");
                $nfe->setEnderEmitUF("SP");
                $nfe->setEnderEmitCEP("01234567");
                $nfe->setEnderEmitCPais("1058");
                $nfe->setEnderEmitXPais("BRASIL");
                $nfe->setEnderEmitFone("11999999999");

                $nfe->setDestCnpj("01234567890123");
                $nfe->setDestXNome("Cliente Teste");
                $nfe->setDestIe("123456789012");
                $nfe->setEnderDestXLgr("Rua Teste");
                $nfe->setEnderDestNro("123");
                $nfe->setEnderDestXBairro("Bairro Teste");
                $nfe->setEnderDestCMun("3550308");
                $nfe->setEnderDestXMun("Sao Paulo");
                $nfe->setEnderDestUF("SP");
                $nfe->setEnderDestCEP("01234567");
                $nfe->setEnderDestCPais("1058");
                $nfe->setEnderDestXPais("BRASIL");
                $nfe->setEnderDestFone("11999999999");

                $i = 0;
                foreach ($products as $product) {
                    $i++;
                    $nfe->setDetNItem($i);
                    $nfe->setDetCProd($product["id"]);
                    $nfe->setDetXProd($product["name"]);
                    $nfe->setDetNCM("00000000");
                    $nfe->setDetCFOP("5102");
                    $nfe->setDetUCom("UN");
                    $nfe->setDetQCom($product["quantity"]);
                    $nfe->setDetVUnCom($product["price"]);
                    $nfe->setDetVProd($product["price"] * $product["quantity"]);
                    $nfe->setDetUTrib("UN");
                    $nfe->setDetQTrib($product["quantity"]);
                    $nfe->setDetVUnTrib($product["price"]);
                    $nfe->setDetIndTot("1");

                    $nfe->setIcmsCSOSN("102");
                    $nfe->setIcmsOrig("0");

                    $nfe->setPisCST("99");
                    $nfe->setPisVBc("0");
                    $nfe->setPisPPis("0");
                    $nfe->setPisVPis("0");

                    $nfe->setCofinsCST("99");
                    $nfe->setCofinsVBc("0");
                    $nfe->setCofinsPCofins("0");
                    $nfe->setCofinsVCofins("0");
                }

                $nfe->setIcmstotVBc("0");
                $nfe->setIcmstotVIcms("0");
                $nfe->setIcmstotVIcmsDeson("0");
                $nfe->setIcmstotVFcp("0");
                $nfe->setIcmstotVBcst("0");
                $nfe->setIcmstotVSt("0");
                $nfe->setIcmstotVFcpst("0");
                $nfe->setIcmstotVFcpstRet("0");
                $nfe->setIcmstotVProd("0");
                $nfe->setIcmstotVFrete("0");
                $nfe->setIcmstotVSeg("0");
                $nfe->setIcmstotVDesc("0");
                $nfe->setIcmstotVIi("0");
                $nfe->setIcmstotVIpi("0");
                $nfe->setIcmstotVIpiDevol("0");
                $nfe->setIcmstotVPis("0");
                $nfe->setIcmstotVCofins("0");
                $nfe->setIcmstotVOutro("0");
                $nfe->setIcmstotVNF("0");

                $nfe->setTranspModFrete("9");

                $nfe->setPagDetPag("01");
                $nfe->setPagVPag($request->total);

                $nfeService = new NFCeService();
                $nfeService->gerarNFCe($nfe);

            } catch (\Exception $e) {
                \Log::error($e->getMessage());
            }

            event(new CreatePaymentPos($request,$pos));

            return redirect()->route("pos.index")->with("success", __("POS Added Successfully!"));
        }
        else
        {
            return redirect()->back()->with("error", __("Permission denied."));
        }
    }

    /**
     * Show the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function show($id)
    {
        if (\Auth::user()->isAbleTo("pdv add manage"))
        {
            $pos = PDV::find($id);
            if($pos && $pos->workspace == getActiveWorkSpace())
            {
                $pos_products = PDVProduct::where("pos_id", $id)->get();
                $pos_payments = PDVPayment::where("pos_id", $id)->get();

                $customer = User::find($pos->customer_id);
                $warehouse = Warehouse::find($pos->warehouse_id);

                return view("pdv::pos.show", compact("pos", "pos_products", "pos_payments", "customer", "warehouse"));
            }
            else
            {
                return redirect()->back()->with("error", __("Permission denied."));
            }
        }
        else
        {
            return redirect()->back()->with("error", __("Permission denied."));
        }
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function edit($id)
    {
        return view("pdv::edit");
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Renderable
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Renderable
     */
    public function destroy($id)
    {
        if (\Auth::user()->isAbleTo("pdv delete"))
        {
            $pos = PDV::find($id);
            if($pos && $pos->workspace == getActiveWorkSpace())
            {
                $pos_products = PDVProduct::where("pos_id", $id)->get();
                foreach ($pos_products as $pos_product)
                {
                    if(module_is_active("ProductService"))
                    {
                        PDV::updateProductStock($pos_product->product_id, $pos_product->quantity, "add");
                    }
                    $pos_product->delete();
                }
                $pos_payments = PDVPayment::where("pos_id", $id)->get();
                foreach ($pos_payments as $pos_payment)
                {
                    $pos_payment->delete();
                }
                $pos->delete();

                return redirect()->back()->with("success", __("POS Deleted Successfully!"));
            }
            else
            {
                return redirect()->back()->with("error", __("Permission denied."));
            }
        }
        else
        {
            return redirect()->back()->with("error", __("Permission denied."));
        }
    }

    public function invoicePosNumber()
    {
        if (Auth::user()->isAbleTo("pdv add manage"))
        {
            $latest = PDV::where("workspace_id", getActiveWorkSpace())->latest()->first();
            if (!$latest)
            {
                return 1;
            }

            return $latest->pos_id + 1;
        }
        else
        {
            return redirect()->back()->with("error", __("Permission denied."));
        }
    }

    public function cart(Request $request)
    {
        if (\Auth::user()->isAbleTo("pdv add manage"))
        {
            $id = $request->id;
            $quantity = $request->quantity;
            $discount = $request->discount;

            if(module_is_active("ProductService"))
            {
                $product = \Workdo\ProductService\Entities\ProductService::find($id);
            }
            else
            {
                $product = Purchase::find($id);
            }

            if (!$product)
            {
                return response()->json(
                    [
                        "code" => 404,
                        "status" => "Error",
                        "error" => __("This product is out of stock!"),
                    ], 404
                );
            }

            $productquantity = 0;
            if(module_is_active("ProductService"))
            {
                $productquantity = PDV::getProductStock($id);
            }
            else
            {
                $productquantity = !empty($product) ? $product->quantity : 0;
            }

            if ($productquantity < $quantity)
            {
                return response()->json(
                    [
                        "code" => 404,
                        "status" => "Error",
                        "error" => __("This product is out of stock!"),
                    ], 404
                );
            }

            $cart = session()->get("pdv");

            if (!$cart)
            {
                $cart = [
                    $id => [
                        "name" => $product->name,
                        "quantity" => $quantity,
                        "price" => $product->sale_price,
                        "id" => $id,
                        "tax" => $product->tax_id,
                        "discount" => $discount,
                        "product_tax" => $product->taxs(),
                        "subtotal" => $product->sale_price * $quantity,
                    ],
                ];

                session()->put("pdv", $cart);

                return response()->json(
                    [
                        "code" => 200,
                        "status" => "Success",
                        "success" => $product->name . " " . __("added to cart successfully!"),
                        "cart" => $cart,
                    ]
                );
            }

            if (isset($cart[$id]))
            {
                $cart[$id]["quantity"]++;

                $subtotal = $cart[$id]["price"] * $cart[$id]["quantity"];
                $tax = ($subtotal * $cart[$id]["tax"]) / 100;
                $cart[$id]["subtotal"] = $subtotal + $tax;

                session()->put("pdv", $cart);

                return response()->json(
                    [
                        "code" => 200,
                        "status" => "Success",
                        "success" => $product->name . " " . __("added to cart successfully!"),
                        "cart" => $cart,
                    ]
                );
            }

            $cart[$id] = [
                "name" => $product->name,
                "quantity" => $quantity,
                "price" => $product->sale_price,
                "id" => $id,
                "tax" => $product->tax_id,
                "discount" => $discount,
                "product_tax" => $product->taxs(),
                "subtotal" => $product->sale_price * $quantity,
            ];

            session()->put("pdv", $cart);

            return response()->json(
                [
                    "code" => 200,
                    "status" => "Success",
                    "success" => $product->name . " " . __("added to cart successfully!"),
                    "cart" => $cart,
                ]
            );
        }
        else
        {
            return response()->json(
                [
                    "code" => 404,
                    "status" => "Error",
                    "error" => __("This product is out of stock!"),
                ], 404
            );
        }
    }

    public function cartremove(Request $request)
    {
        if (\Auth::user()->isAbleTo("pdv add manage"))
        {
            if ($request->id)
            {
                $cart = session()->get("pdv");
                if (isset($cart[$request->id]))
                {
                    unset($cart[$request->id]);
                    session()->put("pdv", $cart);
                }

                return response()->json(
                    [
                        "code" => 200,
                        "status" => "Success",
                        "success" => __("Product removed from cart!"),
                        "cart" => $cart,
                    ]
                );
            }
        }
        else
        {
            return response()->json(
                [
                    "code" => 404,
                    "status" => "Error",
                    "error" => __("This product is out of stock!"),
                ], 404
            );
        }
    }

    public function cartempty(Request $request)
    {
        session()->forget("pdv");

        return response()->json(
            [
                "code" => 200,
                "status" => "Success",
                "success" => __("Cart is empty!"),
            ]
        );
    }

    public function change(Request $request)
    {
        if (\Auth::user()->isAbleTo("pdv add manage"))
        {
            $id = $request->id;
            $quantity = $request->quantity;
            $price = $request->price;
            $tax = $request->tax;
            $cart = session()->get("pdv");

            if (isset($cart[$id]))
            {
                $cart[$id]["quantity"] = $quantity;
                $cart[$id]["price"] = $price;
                $cart[$id]["tax"] = $tax;

                $subtotal = $cart[$id]["price"] * $cart[$id]["quantity"];
                $tax = ($subtotal * $cart[$id]["tax"]) / 100;
                $cart[$id]["subtotal"] = $subtotal + $tax;

                session()->put("pdv", $cart);

                return response()->json(
                    [
                        "code" => 200,
                        "status" => "Success",
                        "success" => __("Cart updated successfully!"),
                        "cart" => $cart,
                    ]
                );
            }
        }
        else
        {
            return response()->json(
                [
                    "code" => 404,
                    "status" => "Error",
                    "error" => __("This product is out of stock!"),
                ], 404
            );
        }
    }

    public function order(PDVOrderDataTable $dataTable)
    {
        if (\Auth::user()->isAbleTo("pdv order manage"))
        {
            return $dataTable->render("pdv::pos.report");
        }
        else
        {
            return redirect()->back()->with("error", __("Permission denied."));
        }
    }

    public function setting(Request $request)
    {
        if(Auth::user()->isAbleTo("pdv setting manage"))
        {
            $user = \Auth::user();
            $context = new Context($user);
            if($request->has("pdv_prefix"))
            {
                $validator = \Validator::make($request->all(), [
                    "pdv_prefix" => "required",
                ]);
                if ($validator->fails())
                {
                    $messages = $validator->getMessageBag();

                    return redirect()->back()->with("error", $messages->first());
                }
                else
                {
                    $user->settings()->context($context)->set([
                        "pdv_prefix" => $request->pdv_prefix,
                    ]);

                    return redirect()->back()->with("success", __("POS Setting successfully updated."));
                }
            }
            if($request->has("pdv_footer_title") || $request->has("pdv_footer_notes"))
            {
                $validator = \Validator::make($request->all(), [
                    "pdv_footer_title" => "required",
                    "pdv_footer_notes" => "required",
                ]);
                if ($validator->fails())
                {
                    $messages = $validator->getMessageBag();

                    return redirect()->back()->with("error", $messages->first());
                }
                else
                {
                    $user->settings()->context($context)->set([
                        "pdv_footer_title" => $request->pdv_footer_title,
                        "pdv_footer_notes" => $request->pdv_footer_notes,
                    ]);

                    return redirect()->back()->with("success", __("POS Setting successfully updated."));
                }
            }
        }
        else
        {
            return redirect()->back()->with("error", __("Permission denied."));
        }
    }

    public function settingStore(Request $request)
    {
        if(Auth::user()->isAbleTo("pdv setting manage"))
        {
            if($request->has("pdv_template") && $request->has("pdv_color"))
            {
                $validator = \Validator::make($request->all(), [
                    "pdv_template" => "required",
                    "pdv_color" => "required",
                ]);
                if ($validator->fails())
                {
                    $messages = $validator->getMessageBag();

                    return redirect()->back()->with("error", $messages->first());
                }
                else
                {
                    $post = $request->all();
                    unset($post["_token"]);
                    foreach ($post as $key => $value) {
                        // Define the data to be updated or created
                        $data = ["key" => $key, "workspace_id" => getActiveWorkSpace(), "created_by" => creatorId()];

                        // Check if the record exists, and update or create it
                        Setting::updateOrCreate($data, ["value" => $value]);
                    }
                }
                return redirect()->back()->with("success", __("POS Setting successfully updated."));
            }
        }
        else
        {
            return redirect()->back()->with("error", __("Permission denied."));
        }
    }

    public function barcode(BarcodeDataTable $dataTable)
    {
        if (\Auth::user()->isAbleTo("pdv barcode manage"))
        {
            return $dataTable->render("pdv::barcode.barcode");
        }
        else
        {
            return redirect()->back()->with("error", __("Permission denied."));
        }
    }

    public function printBarcode(Request $request)
    {
        if (\Auth::user()->isAbleTo("pdv barcode manage"))
        {
            $product_id = $request->product_id;
            $product_id = explode(",", $product_id);
            if(!empty($product_id))
            {
                if(module_is_active("ProductService"))
                {
                    $product = \Workdo\ProductService\Entities\ProductService::whereIn("id", $product_id)->get();
                }
                else
                {
                    $product = Purchase::whereIn("id", $product_id)->get();
                }
                return view("pdv::barcode.print", compact("product"));
            }
            else
            {
                return redirect()->back()->with("error", __("Please select product to print barcode."));
            }
        }
        else
        {
            return redirect()->back()->with("error", __("Permission denied."));
        }
    }

    public function receipt(Request $request)
    {
        if (\Auth::user()->isAbleTo("pdv barcode manage"))
        {
            $data = $request->all();
            $product_id = $request->product_id;
            $product_id = explode(",", $product_id);
            if(!empty($product_id))
            {
                if(module_is_active("ProductService"))
                {
                    $product = \Workdo\ProductService\Entities\ProductService::whereIn("id", $product_id)->get();
                }
                else
                {
                    $product = Purchase::whereIn("id", $product_id)->get();
                }
                return view("pdv::barcode.receipt", compact("product", "data"));
            }
            else
            {
                return redirect()->back()->with("error", __("Please select product to print barcode."));
            }
        }
        else
        {
            return redirect()->back()->with("error", __("Permission denied."));
        }
    }

    public function warehouseemptyCart(Request $request)
    {
        $warehouse_id = $request->warehouse_id;
        $cart = session()->get("pdv");
        if(isset($cart) && count($cart) > 0)
        {
            if(module_is_active("ProductService"))
            {
                $product = \Workdo\ProductService\Entities\ProductService::where("created_by", creatorId())->where("workspace_id",getActiveWorkSpace())->whereIn("id",array_keys($cart))->get();
                if(count($product) == 0)
                {
                    session()->forget("pdv");
                }
            }
        }
        session()->forget("pdv");

        return response()->json(
            [
                "code" => 200,
                "status" => "Success",
                "success" => __("Cart is empty!"),
            ]
        );
    }

    public function quotation(Request $request)
    {
        $quotation = Quotation::find($request->quotation_id);
        $warehouseId = $quotation->warehouse_id;
        $customer = Customer::where("user_id",$quotation->customer_id)->first();
        if(empty($customer))
        {
            $customer = User::find($quotation->customer_id);
        }
        $customer = $customer->name;

        $quotationProduct = QuotationProduct::where("quotation_id", $request->quotation_id)->get();

        $cart = [];
        foreach ($quotationProduct as $value) {
            $products = Quotation::quotationProduct($value);
            $cart[$products->id] = [
                "name" => $products->name,
                "quantity" => $value->quantity,
                "price" => $products->sale_price,
                "id" => $products->id,
                "tax" => $products->tax_id,
                "discount" => $value->discount,
                "product_tax" => $products->taxs(),
                "subtotal" => $products->sale_price * $value->quantity,
            ];
        }

        session()->put("pdv", $cart);

        return response()->json(
            [
                "code" => 200,
                "status" => "Success",
                "success" => __("Cart is empty!"),
                "customer" => $customer,
                "warehouse" => $warehouseId,
            ]
        );
    }
}

