<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ad;
use App\Models\complaint;
use App\Models\delivery_service;
use App\Models\favourite;
use App\Models\form;
use App\Models\logo;
use App\Models\offer;
use App\Models\order;
use App\Models\product;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use App\Models\products_type;
use App\Models\requests_info;
use App\Models\sale;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function addProductType(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required',
        ]);

        products_type::create($validatedData);
        $var = products_type::get();

        return response([
            'status' => true,
            'message' => 'type Added Successfully',
            'types' => $var
        ]);
    }

    public function editProductType(Request $request)
    {
        $validatedData = $request->validate([
            'id' => 'required',
            'name' => 'required',
        ]);

        $productType = products_type::find($request->id);
        $productType->fill($validatedData);
        $productType->save();

        $var = products_type::get();

        return response([
            'status' => true,
            'message' => 'type Added Successfully',
            'types' => $var
        ]);
    }

    public function deleteProductType(Request $request)
    {
        $request->validate([
            'id' => 'required',
        ]);

        products_type::where('id', $request->id)->delete();
        $var = products_type::get();

        return response([
            'status' => true,
            'message' => 'type deleted Successfully',
            'types' => $var
        ], 200);
    }

    public function editProduct(Request $request)
    {
        $request->validate([
            'id' => 'required',
        ]);

        $product = product::find($request->id);

        if ($request->has('name')) $product->name = $request->name;
        if ($request->has('type_id')) $product->type_id = $request->type_id;
        if ($request->has('disc')) $product->disc = $request->disc;
        if ($request->has('long_disc')) $product->long_disc = $request->long_disc;
        if ($request->has('price')) {
            if ($request->price < 0)
                return response()->json([
                    'status' => false,
                    'message' => "price couldnt be negative value"
                ], 200);
            $product->price = $request->price;
        }
        if ($request->has('quantity')) {
            if ($request->quantity < 0)
                return response()->json([
                    'status' => false,
                    'message' => "quantitiy couldnt be negative value"
                ], 200);
            $product->quantity = $request->quantity;
        }
        if ($request->has('source_price')) {
            if ($request->source_price < 0)
                return response()->json([
                    'status' => false,
                    'message' => "price couldnt be negative value"
                ], 200);
            $product->source_price = $request->source_price;
        }
        if ($request->has('code')) $product->code = $request->code;
        if ($request->has('visibile')) $product->visibile = $request->visibile;
        if ($request->has('img_url')) {
            $image1 = Str::random(32) . "." . $request->img_url->getClientOriginalExtension();
            Storage::disk('public_htmlProducts')->put($image1, file_get_contents($request->img_url));
            $image1 = asset('products/' . $image1);
            $product->img_url = $image1;
        }

        $product->save();
        $var = product::join('products_types as pt', 'pt.id', 'products.type_id')
            ->get([
                'products.id', 'products.name', 'type_id', 'pt.name as type',
                'disc', 'long_disc', 'price', 'quantity', 'source_price', 'code', 'img_url', 'visible'
            ]);

        return response()->json([
            'status' => true,
            'message' => 'product edited Successfully',
            'products' => $var,
        ]);
    }

    public function addProduct(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required',
            'type_id' => 'required',
            'disc' => 'required',
            'price' => 'required',
            'quantity' => 'required',
            'source_price' => 'required',
            'code' => 'required',
            'img_url' => 'image|mimes:jpg,png,jpeg,gif,webg,svg|max:2048',
        ]);

        if ($request->quantity < 0) {
            return response()->json([
                'status' => false,
                'message' => "quantitiy couldnt be negative value"
            ], 200);
        }
        if (($request->price < 0) || ($request->source_price < 0)) {
            return response()->json([
                'status' => false,
                'message' => "price couldnt be negative value"
            ], 200);
        }

        if ($request->has('long_disc')) {
            $validatedData['long_disc'] = $request->long_disc;
        }
        if ($request->has('img_url')) {
            $image1 = Str::random(32) . "." . $request->img_url->getClientOriginalExtension();
            Storage::disk('public_htmlProducts')->put($image1, file_get_contents($request->img_url));
            $image1 = asset('products/' . $image1);
            $validatedData['img_url'] = $image1;
        }

        product::create($validatedData);

        $var = product::join('products_types as pt', 'pt.id', 'products.type_id')
            ->get([
                'products.id', 'products.name', 'type_id', 'pt.name as type',
                'disc', 'long_disc', 'price', 'quantity', 'source_price', 'code', 'img_url', 'visible'
            ]);

        return response()->json([
            'status' => true,
            'message' => 'product added Successfully',
            'products' => $var,
        ]);
    }

    public function editProductVis($id)
    {

        $var = product::find($id);
        if ($var->visible == 0) $var->visible = 1;
        else $var->visible = 0;
        $var->save();

        $var = product::join('products_types as pt', 'pt.id', 'products.type_id')
            ->get([
                'products.id', 'products.name', 'type_id', 'pt.name as type',
                'disc', 'long_disc', 'price', 'quantity', 'source_price', 'code', 'img_url', 'visible'
            ]);

        return response([
            'status' => true,
            'message' => 'done Successfully',
            'types' => $var
        ]);
    }

    public function deleteProduct($id)
    {
        product::where('id', $id)->delete();

        $var = product::join('products_types as pt', 'pt.id', 'products.type_id')
            ->get([
                'products.id', 'products.name', 'type_id', 'pt.name as type',
                'disc', 'long_disc', 'price', 'quantity', 'source_price', 'code', 'img_url', 'visible'
            ]);

        return response([
            'status' => true,
            'message' => 'deleted successfully',
            'products' => $var
        ], 200);
    }

    public function blockUser($id)
    {

        $var = User::find($id);
        if ($var->blocked == 0) $var->blocked = 1;
        else $var->blocked = 0;
        $var->save();

        return response([
            'status' => true,
            'message' => 'done Successfully',
        ]);
    }

    public function blockDelSer($id)
    {

        $ser = delivery_service::find($id);
        if ($ser->blocked == 0) $ser->blocked = 1;
        else $ser->blocked = 0;
        $ser->save();

        $var = delivery_service::join('cities', 'city_id', 'cities.id')
            ->get(['delivery_services.id', 'city_id', 'cities.name as city', 'price', 'blocked']);

        return response([
            'status' => true,
            'message' => 'done Successfully',
            'Data' => $var,
        ]);
    }

    public function showhiddenProducts()
    {
        $var = product::where('visible', 0)->get();

        return response([
            'status' => true,
            'products' => $var
        ], 200);
    }

    public function uploadProductImg(Request $request)
    {
        $request->validate([
            'id' => 'required',
            'img_url' => 'required|image|mimes:jpg,webp,png,jpeg,gif,svg|max:2048',
        ]);

        $product = Product::find($request->id);
        $image1 = Str::random(32) . "." . $request->img_url->getClientOriginalExtension();
        Storage::disk('public_htmlProducts')->put($image1, file_get_contents($request->img_url));
        $image1 = asset('products/' . $image1);
        $product->img_url = $image1;
        $product->save();

        return response([
            'status' => true,
            'message' => "done successfully"
        ], 200);
    }

    public function uploadLogo(Request $request)
    {
        $validatedData = $request->validate([
            'img_url' => 'required|image|mimes:jpg,webp,png,jpeg,gif,svg|max:2048',
        ]);

        $image1 = Str::random(32) . "." . $request->img_url->getClientOriginalExtension();
        Storage::disk('public_htmlLogos')->put($image1, file_get_contents($request->img_url));

        $image1 = asset('Logos/' . $image1);

        $validatedData['img_url'] = $image1;

        logo::create($validatedData);
        $var = logo::get();


        return response([
            'status' => true,
            'message' => "done successfully",
            'data' => $var,
            'image_path' => $image1,
        ], 200);
    }

    public function showLogos()
    {
        $var = logo::get();

        return response([
            'status' => true,
            'images' => $var
        ], 200);
    }

    public function selectLogo($id)
    {

        DB::table('logos')->update(['selected' => 0]);
        DB::table('logos')->where('id', $id)->update(['selected' => 1]);

        $var = logo::get();
        return response([
            'status' => true,
            'data' => $var,
            'message' => "done successfully"
        ], 200);
    }

    public function showComplaints()
    {
        $var = complaint::join('users', 'users.id', 'user_id')->get([
            'complaints.id as complaint_id',
            'user_id',
            'users.name as user_name',
            'phone_no', 'email',
            'complaints.name as complaint',
            'complaints.created_at',
            'complaints.updated_at'
        ]);

        return response([
            'status' => true,
            'message' => $var
        ], 200);
    }

    public function deleteComplaint($id)
    {
        complaint::where('id', $id)->delete();

        $var = complaint::join('users', 'users.id', 'user_id')->get([
            'complaints.id as complaint_id',
            'user_id',
            'users.name as user_name',
            'phone_no', 'email',
            'complaints.name as complaint',
            'complaints.created_at',
            'complaints.updated_at'
        ]);

        return response([
            'status' => true,
            'message' => 'deleted successfully',
            'complaints' => $var
        ], 200);
    }

    public function uploadAdImg(Request $request)
    {
        $validatedData = $request->validate([
            'img_url' => 'required|image|mimes:jpg,webp,png,jpeg,gif,svg|max:2048',
        ]);

        $image1 = Str::random(32) . "." . $request->img_url->getClientOriginalExtension();
        Storage::disk('public_htmlAds')->put($image1, file_get_contents($request->img_url));

        $image1 = asset('Ads/' . $image1);
        $validatedData['img_url'] = $image1;
        ad::create($validatedData);

        $var = ad::get();

        return response([
            'status' => true,
            'message' => "done successfully",
            'data' => $var,
            'image_path' => $image1,
        ], 200);
    }

    public function deleteAdImg($id)
    {
        ad::where('id', $id)->delete();
        $var = ad::get();

        return response([
            'status' => true,
            'message' => 'deleted successfully',
            'data' => $var,
        ], 200);
    }

    public function uploadOrderImg(Request $request)
    {
        $validatedData = $request->validate([
            'img_url' => 'required|image|mimes:jpg,webp,png,jpeg,gif,svg|max:2048',
        ]);

        $image1 = Str::random(32) . "." . $request->img_url->getClientOriginalExtension();
        Storage::disk('public_htmlOrders')->put($image1, file_get_contents($request->img_url));

        $image1 = asset('Orders/' . $image1);

        $validatedData['img_url'] = $image1;

        order::create($validatedData);
        $orders = order::get();

        return response([
            'status' => true,
            'message' => "done successfully",
            'image_path' => $image1,
            'data' => $orders,
        ], 200);
    }

    public function deleteOrderImg($id)
    {
        order::where('id', $id)->delete();
        $var = order::get();

        return response([
            'status' => true,
            'message' => 'deleted successfully',
            'Data' => $var,
        ], 200);
    }

    public function showAdsOrdersLogos()
    {
        $logos = logo::get();
        $orders = order::get();
        $ads = ad::get();

        return response([
            'status' => true,
            'Logos' => $logos,
            'Orders' => $orders,
            'Ads' => $ads,
        ], 200);
    }

    public function editDelSer(Request $request)
    {
        $request->validate([
            'id' => 'required',
        ]);

        $var = delivery_service::find($request->id);

        if ($request->has('city_id')) $var->city_id = $request->city_idprice;
        if ($request->has('price')) $var->price = $request->price;
        $var->save();
        $var = delivery_service::get();

        return response()->json([
            'status' => true,
            'message' => 'done Successfully',
            'products' => $var,
        ]);
    }

    public function updateProductImg(Request $request)
    {
        $request->validate([
            'id' => 'required',
            'img_url' => 'required|image|mimes:jpg,webp,png,jpeg,gif,svg|max:2048',
        ]);

        $var = product::find($request->id);
        $image1 = Str::random(32) . "." . $request->img_url->getClientOriginalExtension();
        Storage::disk('public_htmlProducts')->put($image1, file_get_contents($request->img_url));
        $image1 = asset('Products/' . $image1);
        $var->img_url = $image1;
        $var->save();

        return response([
            'status' => true,
            'message' => "done successfully",
            'img_path' => $image1
        ], 200);
    }

    public function addDelSer(Request $request)
    {
        $validatedData = $request->validate([
            'city_id' => 'required',
            'price' => 'required',
        ]);

        delivery_service::create($validatedData);

        $var = delivery_service::get();

        return response()->json([
            'status' => true,
            'message' => 'added Successfully',
            'services' => $var,
        ]);
    }

    public function addOffer(Request $request)
    {
        $validatedData = $request->validate([
            'product_id' => 'required',
            'quantity' => 'required',
            'percentage' => 'required',
        ]);

        if ($request->quantity < 0) {
            return response()->json([
                'status' => false,
                'message' => "quantitiy couldnt be negative value"
            ], 200);
        }
        if ($request->percentage < 0) {
            return response()->json([
                'status' => false,
                'message' => "percentage couldnt be negative value"
            ], 200);
        }

        $product = product::find($request->product_id);
        if ($product->quantity < $request->quantity) {
            return response()->json([
                'status' => false,
                'message' => 'wrong quantity , offer qty cannot be more than product qty',
            ]);
        } else {
            $validatedData['new_price'] = ($product->price * (100 - $request->percentage)) / 100;
            offer::create($validatedData);
            $product->quantity -= $request->quantity;
            $product->save();
        }

        $offers = product::join('offers as o', 'o.product_id', 'products.id')
            ->join('products_types as t', 't.id', 'products.type_id')
            ->where('visible', 1)->get([
                'o.id as offer_id', 'product_id', 'o.quantity as offer_quantity', 'products.quantity as product_quantity',
                'new_price', 'percentage', 'products.name', 'type_id', 't.name as type_name', 'disc',
                'long_disc', 'price as old_price', 'source_price', 'img_url', 'code as product_code', 'visible', 'created_at', 'updated_at'
            ]);

        $products = product::join('products_types', 'type_id', 'products_types.id')
            ->get([
                'products.id', 'products.name', 'type_id', 'products_types.name as type_name', 'disc',
                'long_disc', 'price', 'quantity', 'source_price', 'code', 'img_url', 'visible'
            ]);

        $types = products_type::get();

        return response()->json([
            'status' => true,
            'message' => 'added Successfully',
            'offers' => $offers,
            'products' => $products,
            'types' => $types,
        ]);
    }

    public function editOffer(Request $request)
    {
        $request->validate([
            'offer_id' => 'required',
            'quantity' => 'required',
            'percentage' => 'required',
        ]);

        $offer = offer::findOrFail($request->offer_id);
        $product = product::findOrFail($offer->product_id);
        if ($offer->quantity > $request->quantity) {
            $product->quantity += ($offer->quantity - $request->quantity);
            $offer->quantity = $request->quantity;
        } else if ($offer->quantity < $request->quantity) {
            if (($offer->quantity + $product->quantity) < $request->quantity)
                return response()->json([
                    'status' => false,
                    'message' => 'wrong quantity , offer qty cannot be more than product qty',
                ]);
            else {
                $product->quantity -= ($request->quantity - $offer->quantity);
                $offer->quantity = $request->quantity;
            }
        } else {
            $offer->quantity = $request->quantity;
        }

        if ($offer->percentage != $request->percentage) {
            $offer->percentage = $request->percentage;
            $offer->new_price = ($product->price * (100 - $request->percentage)) / 100;
        }
        $offer->save();
        $product->save();

        $offers = product::join('offers as o', 'o.product_id', 'products.id')
            ->join('products_types as t', 't.id', 'products.type_id')
            ->where('visible', 1)->get([
                'o.id as offer_id', 'product_id', 'o.quantity as offer_quantity', 'products.quantity as product_quantity',
                'new_price', 'percentage', 'products.name', 'type_id', 't.name as type_name', 'disc',
                'long_disc', 'price as old_price', 'source_price', 'img_url', 'code as product_code', 'visible', 'created_at', 'updated_at'
            ]);

        $products = product::join('products_types', 'type_id', 'products_types.id')
            ->get([
                'products.id', 'products.name', 'type_id', 'products_types.name as type_name', 'disc',
                'long_disc', 'price', 'quantity', 'source_price', 'code', 'img_url', 'visible'
            ]);

        $types = products_type::get();

        return response()->json([
            'status' => true,
            'message' => 'added Successfully',
            'offers' => $offers,
            'products' => $products,
            'types' => $types,
        ]);
    }

    public function showEmploymentRequests()
    {
        $forms = form::join('users', 'forms.user_id', 'users.id')
            ->join('cities as c', 'c.id', 'users.city_id')
            ->get([
                'forms.id as form_id', 'user_id', 'file_url',
                'forms.created_at as form_created_at', 'forms.updated_at as form_updated_at', 'users.name as user_name', 'city_id',
                'c.name as city', 'type_id', 'email', 'phone_no', 'img_url', 'blocked',
                'users.created_at as user_created_at', 'users.updated_at as user_updated_at',
            ]);

        return response()->json([
            'status' => true,
            'data' => $forms
        ]);
    }

    public function deleteEmploymentRequest($id)
    {
        form::where('id', $id)->delete();

        $forms = form::join('users', 'forms.user_id', 'users.id')
            ->join('cities as c', 'c.id', 'users.city_id')
            ->get([
                'forms.id as form_id', 'user_id', 'file_url',
                'forms.created_at as form_created_at', 'forms.updated_at as form_updated_at', 'users.name as user_name', 'city_id',
                'c.name as city', 'type_id', 'email', 'phone_no', 'img_url', 'blocked',
                'users.created_at as user_created_at', 'users.updated_at as user_updated_at',
            ]);

        return response()->json([
            'status' => true,
            'message' => 'deleted successfully',
            'data' => $forms
        ]);
    }

    public function showDelSer()
    {
        $services = delivery_service::where('blocked', 0)->join('cities', 'delivery_services.city_id', 'cities.id')
            ->get([
                'delivery_services.id', 'city_id', 'name as city', 'price', 'blocked'
            ]);

        return response()->json([
            'status' => true,
            'data' => $services
        ]);
    }

    public function showUserData()
    {
        $id = auth()->user()->id;

        if (!(User::where('id', $id)->exists())) {
            return response()->json([
                'status' => true,
                'message' => 'Wrong id, User dosent exist'
            ], 200);
        }

        $user = User::where('users.id', $id)
            ->join('cities as c', 'users.city_id', 'c.id')
            ->join('users_types as t', 'users.type_id', 't.id')
            ->get([
                'users.id', 'users.name', 'city_id', 'c.name as city', 'type_id',
                't.name as type', 'email', 'password', 'phone_no', 'img_url', 'badget', 'blocked',
                'created_at', 'updated_at'
            ]);

        $requests = DB::table('requests as r')->where('user_id', $id)
            ->join('delivery_services as d', 'delivery_ser_id', 'd.id')
            ->join('cities as c', 'd.city_id', 'c.id')
            ->join('users as u', 'u.id', 'r.employee_id')
            ->join('request_states as s', 's.id', 'state_id')
            ->get([
                'r.id', 'user_id', 'delivery_ser_id', 'd.city_id as delivery_ser_city_id',
                'c.name as delivery_ser_city_name', 'd.price as delivery_ser_price',
                'employee_id', 'u.name as employee_name', 'state_id', 's.name as request_state', 'r.created_at', 'r.updated_at'
            ]);

        $collection = collect();
        foreach ($requests as $req) {
            $col = collect();
            $col->push(['request_id' => $req->id]);
            $info = DB::table('requests_infos as r')->where('req_id', $req->id)
                ->join('products as p', 'p.id', 'r.product_id')
                ->join('products_types as t', 'p.type_id', 't.id')
                ->get([
                    'p.id as product_id', 'p.name', 'type_id', 't.name as type_name',
                    'disc', 'long_disc', 'price', 'p.quantity as product_quantity',
                    'code', 'img_url', 'visible', 'r.quantity as required_quantity'
                ]);

            foreach ($info as $var) {
                $col->push([$var][0]);
            }

            $collection->push($col);
        }

        $complaints = complaint::where('user_id', $id)->get();
        $favs = favourite::where('user_id', $id)
            ->join('products as p', 'p.id', 'favourites.product_id')
            ->join('products_types as t', 'p.type_id', 't.id')
            ->get([
                'p.id as product_id', 'p.name', 'type_id', 't.name as type_name',
                'disc', 'long_disc', 'price', 'quantity',
                'code', 'img_url', 'visible'
            ]);

        return response()->json([
            'status' => true,
            'user_data' => $user,
            'requests' => $requests,
            'requests_infos' => $collection,
            'complaints' => $complaints,
            'favourites' => $favs
        ], 200);
    }

    public function addSale(Request $request)
    {
        $validatedData = $request->validate([
            'quantity' => 'required',
            'product_id' => 'required',
            'price' => 'required',
        ]);

        if (!(product::where('id', $request->product_id)->exists())) {
            return response()->json([
                'status' => false,
                'message' => 'not found , Wrong Id'
            ]);
        }

        $product = product::findOrFail($request->product_id);

        if ($product->quantity < $request->quantity) {
            return response()->json([
                'status' => false,
                'message' => 'quantity not available'
            ]);
        }
        $product->quantity -= $request->quantity;
        $product->save();

        $validatedData['user_id'] = auth()->user()->id;
        $validatedData['is_offer'] = false;

        sale::create($validatedData);

        $sales = sale::join('products as p', 'p.id', 'sales.product_id')
            ->join('users as u', 'u.id', 'sales.user_id')
            ->join('products_types as pt', 'pt.id', 'p.type_id')
            ->get([
                'sales.id', 'user_id', 'u.name as user_name', 'product_id',
                'p.name as product_name', 'p.type_id', 'pt.name as type_name',
                'disc', 'long_disc', 'p.price as product_price', 'source_price',
                'p.quantity as product_quantity', 'code', 'p.img_url', 'visible', 'sales.price as sale_price',
                'sales.quantity as sale_quantity', 'sales.created_at', 'sales.updated_at'
            ]);

        return response()->json([
            'status' => true,
            'message' => 'added Successfully',
            'sales' => $sales
        ]);
    }

    public function editSale(Request $request)
    {
        $request->validate([
            'sale_id' => 'required',
        ]);

        if (!(sale::where('id', $request->sale_id)->exists())) {
            return response()->json([
                'status' => false,
                'message' => 'not found , Wrong Id'
            ]);
        }

        $sale = sale::findOrFail($request->sale_id);
        $product = product::findOrFail($sale->product_id);

        if ($request->has('quantity')) {
            if ($request->quantity < $sale->quantity) {
                $product->quantity += $sale->quantity - $request->quantity;
                $sale->quantity = $request->quantity;
            } else {
                if ($request->quantity > $product->quantity + $sale->quantity) {
                    return response()->json([
                        'status' => false,
                        'message' => 'quantity not available'
                    ]);
                } else {
                    $product->quantity -= $request->quantity - $sale->quantity;
                    $sale->quantity = $request->quantity;
                }
            }
        }

        if ($request->has('price')) {
            $sale->price = $request->price;
        }

        $product->save();
        $sale->save();

        $sales = sale::join('products as p', 'p.id', 'sales.product_id')
            ->join('users as u', 'u.id', 'sales.user_id')
            ->join('products_types as pt', 'pt.id', 'p.type_id')
            ->get([
                'sales.id', 'user_id', 'u.name as user_name', 'product_id',
                'p.name as product_name', 'p.type_id', 'pt.name as type_name',
                'disc', 'long_disc', 'p.price as product_price', 'source_price',
                'p.quantity as product_quantity', 'code', 'p.img_url', 'visible', 'sales.price as sale_price',
                'sales.quantity as sale_quantity', 'sales.created_at', 'sales.updated_at'
            ]);

        return response()->json([
            'status' => true,
            'message' => 'edited Successfully',
            'sales' => $sales
        ]);
    }

    public function showSales()
    {
        $sales = sale::join('products as p', 'p.id', 'sales.product_id')
            ->join('users as u', 'u.id', 'sales.user_id')
            ->join('products_types as pt', 'pt.id', 'p.type_id')
            ->get([
                'sales.id', 'user_id', 'u.name as user_name', 'product_id',
                'p.name as product_name', 'p.type_id', 'pt.name as type_name',
                'disc', 'long_disc', 'p.price as product_price', 'source_price',
                'p.quantity as product_quantity', 'code', 'p.img_url', 'visible', 'sales.price as sale_price',
                'sales.quantity as sale_quantity', 'sales.created_at', 'sales.updated_at'
            ]);

        return response()->json([
            'status' => true,
            'sales' => $sales
        ]);
    }

    public function editReqState(Request $request)
    {
        $request->validate([
            'request_id' => 'required',
            'state_id' => 'required',
        ]);

        if (!(DB::table('requests')->where('id', $request->request_id)->exists())) {
            return response()->json([
                'status' => false,
                'message' => 'not found , Wrong Id'
            ]);
        }
        if (($request->state_id != 2) && ($request->state_id != 3) && ($request->state_id != 4)) {
            return response()->json([
                'status' => false,
                'message' => 'wrong state value , should be 2,3,4'
            ]);
        }

        $req = DB::table('requests')->find($request->request_id);

        if ($request->state_id == 3) {
            $info = requests_info::where('req_id', $req->id)->get();
            foreach ($info as $in) {
                if (isset($in->offer_id)) {
                    $offer = offer::findOrFail($in['offer_id']);
                    $price = $offer->new_price;
                } else {
                    $p = product::findOrFail($in['product_id']);
                    $price = $p->price;
                }

                DB::table('sales')->insert([
                    'product_id' => $in['product_id'],
                    'user_id' => $req->user_id,
                    'quantity' => $in['quantity'],
                    'price' => $price,
                    'is_offer' => 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        } else if ($request->state_id == 4) {
            $info = requests_info::where('req_id', $req->id)->get();
            foreach ($info as $in) {
                if (isset($in->offer_id)) {
                    $offer = offer::findOrFail($in['offer_id']);
                    $offer->quantity += $in->quantity;
                    $offer->save();
                } else {
                    $p = product::findOrFail($in['product_id']);
                    $p->quantity += $in->quantity;
                    $p->save();
                }
            }
        }
        DB::table('requests')->where('id', $req->id)
            ->update([
                'state_id' => $request->state_id,
                'employee_id' => auth()->user()->id
            ]);

        return response()->json([
            'status' => true,
            'message' => 'edited Successfully',
        ]);
    }

    public function showAllRequests()
    {
        $requests = DB::table('requests as r')
            ->join('users as x', 'x.id', 'r.user_id')
            ->leftJoin('delivery_services as d', 'delivery_ser_id', 'd.id')
            ->leftJoin('cities as c', 'd.city_id', 'c.id')
            ->leftJoin('users as u', 'u.id', 'r.employee_id')
            ->join('request_states as s', 's.id', 'state_id')
            ->select([
                'r.id', 'user_id', 'x.name as user_name', 'delivery_ser_id', 'd.city_id as delivery_ser_city_id',
                'c.name as delivery_ser_city_name', 'd.price as delivery_ser_price',
                'employee_id', 'u.name as employee_name', 'state_id', 'discount_id', 'discount_app', 'payment',
                's.name as request_state', 'r.created_at', 'r.updated_at'
            ])->get();

        $requests = $requests->map(function ($request) {
            $info = DB::table('requests_infos as r')
                ->where('req_id', $request->id)
                ->join('products as p', 'p.id', 'r.product_id')
                ->join('products_types as t', 'p.type_id', 't.id')
                ->LeftJoin('offers as o', 'r.offer_id', 'o.id')
                ->select([
                    'p.id as product_id', 'p.name', 'type_id', 't.name as type_name',
                    'disc', 'long_disc', 'price', 'p.quantity as product_quantity',
                    'code', 'img_url', 'visible', 'r.quantity as required_quantity',
                    'o.id as offer_id', 'o.quantity as offer_quantity', 'o.percentage as offer_percentage',
                    'new_price as offer_price', 'o.created_at as offer_created_at', 'o.updated_at as offer_updated_at'
                ])
                ->get();

            $request->products = $info;

            return $request;
        });

        foreach ($requests as $req) {
            $sum_p = 0;
            $sum_q = 0;
            foreach ($req->products as $p) {
                if (isset($p->offer_price)) $sum_p += ($p->offer_price * $p->required_quantity);
                else $sum_p += ($p->price * $p->required_quantity);
                $sum_q += $p->required_quantity;
            }
            $req->total_price = $sum_p;
            $req->total_quantity = $sum_q;
        }


        return response()->json([
            'status' => true,
            'requests' => $requests,
        ], 200);
    }

    public function showStats(Request $request)
    {
        $request->validate([
            'date1' => 'required',
            'date2' => 'required',
        ]);

        $requests_count = DB::table('requests')
            ->whereDate('created_at', '>=', $request->date1)
            ->whereDate('created_at', '<=', $request->date2)
            ->count();

        $sales = DB::table('sales')
            ->whereDate('created_at', '>=', $request->date1)
            ->whereDate('created_at', '<=', $request->date2)
            ->get();

        $total_sales = 0;
        foreach ($sales as $t) {
            $total_sales += $t->price * $t->quantity;
        }

        $total_quantity_sales = DB::table('sales')
            ->whereDate('created_at', '>=', $request->date1)
            ->whereDate('created_at', '<=', $request->date2)
            ->sum('quantity');

        $users_count = DB::table('users')
            ->where('type_id', 1)
            ->whereDate('created_at', '>=', $request->date1)
            ->whereDate('created_at', '<=', $request->date2)
            ->count();

        $total_delivery = DB::table('requests')
            ->whereDate('created_at', '>=', $request->date1)
            ->whereDate('created_at', '<=', $request->date2)
            ->join('delivery_services as d', 'delivery_ser_id', 'd.id')
            ->sum('d.price');

        $sales = DB::table('sales as s')
            ->whereDate('created_at', '>=', $request->date1)
            ->whereDate('created_at', '<=', $request->date2)
            ->join('products as p', 'product_id', 'p.id')
            ->get(['s.price', 'p.source_price', 's.quantity']);

        $profits = 0;
        foreach ($sales as $t) {
            $profits += ($t->price - $t->source_price) * $t->quantity;
        }

        $emps = DB::table('users')
            ->where('type_id', 2)
            ->whereDate('created_at', '>=', $request->date1)
            ->whereDate('created_at', '<=', $request->date2)
            ->get();

        $productCounts = DB::table('sales')
            ->select('product_id', DB::raw('count(*) as times'))
            ->groupBy('product_id')
            ->orderBy('times', 'desc')
            ->get();

        $products = [];
        foreach ($productCounts as $p) {
            $p = DB::table('products')->where('id', $p->product_id)->get();
            array_push($products, $p[0]);
        }

        return response()->json([
            'status' => true,
            'requests_count' => $requests_count,
            'total_sales' => $total_sales,
            'total_quantity_sales' => $total_quantity_sales,
            'users_count' => $users_count,
            'total_delivery' => $total_delivery,
            'profits' => $profits,
            'most_selled_products' => $products,
            'employees' => $emps,
        ]);
    }
}
