<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ad;
use App\Models\complaint;
use App\Models\delivery_service;
use App\Models\logo;
use App\Models\offer;
use App\Models\order;
use App\Models\product;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use App\Models\products_type;
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
        if ($request->has('price')) $product->price = $request->price;
        if ($request->has('quantity')) $product->quantity = $request->quantity;
        if ($request->has('source_price')) $product->source_price = $request->source_price;
        if ($request->has('code')) $product->code = $request->code;
        if ($request->has('visibile')) $product->visibile = $request->visibile;
        if ($request->has('img_url')) {
            $image1 = Str::random(32) . "." . $request->img_url->getClientOriginalExtension();
            $product->img_url = $image1;
            Storage::disk('publicProducts')->put($image1, file_get_contents($request->img_url));
        }
        $product->save();
        $var = product::where('visible', 1)->get();

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
            'img_url' => 'image|mimes:jpg,png,jpeg,gif,svg|max:2048',
        ]);

        if ($request->has('long_disc')) {
            $validatedData['long_disc'] = $request->long_disc;
        }
        if ($request->has('img_url')) {
            $image1 = Str::random(32) . "." . $request->img_url->getClientOriginalExtension();
            Storage::disk('publicProducts')->put($image1, file_get_contents($request->img_url));
            $image1 = asset('public/Products/' . $image1);
            $validatedData['img_url'] = $image1;
        }

        product::create($validatedData);

        $var = product::where('visible', 1)->get();

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

        $var = product::where('visible', 1)->get();

        return response([
            'status' => true,
            'message' => 'done Successfully',
            'types' => $var
        ]);
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
        $product->img_url = $image1;
        $product->save();

        Storage::disk('publicProducts')->put($image1, file_get_contents($request->img_url));
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
        Storage::disk('publicLogos')->put($image1, file_get_contents($request->img_url));

        $image1 = asset('public/Logos/' . $image1);

        $validatedData['img_url'] = $image1;

        logo::create($validatedData);

        return response([
            'status' => true,
            'message' => "done successfully",
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

        return response([
            'status' => true,
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

        return response([
            'status' => true,
            'message' => 'deleted successfully'
        ], 200);
    }

    public function uploadAdImg(Request $request)
    {
        $validatedData = $request->validate([
            'img_url' => 'required|image|mimes:jpg,webp,png,jpeg,gif,svg|max:2048',
        ]);

        $image1 = Str::random(32) . "." . $request->img_url->getClientOriginalExtension();
        Storage::disk('publicAds')->put($image1, file_get_contents($request->img_url));

        $image1 = asset('public/Ads/' . $image1);
        $validatedData['img_url'] = $image1;
        ad::create($validatedData);

        return response([
            'status' => true,
            'message' => "done successfully",
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
            'Data' => $var,
        ], 200);
    }

    public function uploadOrderImg(Request $request)
    {
        $validatedData = $request->validate([
            'img_url' => 'required|image|mimes:jpg,webp,png,jpeg,gif,svg|max:2048',
        ]);

        $image1 = Str::random(32) . "." . $request->img_url->getClientOriginalExtension();
        Storage::disk('publicOrders')->put($image1, file_get_contents($request->img_url));

        $image1 = asset('public/Orders/' . $image1);

        $validatedData['img_url'] = $image1;

        order::create($validatedData);

        return response([
            'status' => true,
            'message' => "done successfully",
            'image_path' => $image1,
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
        Storage::disk('publicProducts')->put($image1, file_get_contents($request->img_url));
        $image1 = asset('public/Products/' . $image1);
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

        $product = product::find($request->product_id);
        if ($product->quantity < $request->quantity) {
            return response()->json([
                'status' => false,
                'message' => 'wrong quantity , offer qty cannot be more than product qty',
            ]);
        } else {
            $validatedData['new_price'] = ($product->price * (100 - $request->percentage)) / 100;
            offer::create($validatedData);
            $var = offer::get();
            $product->quantity -= $request->quantity;
            $product->save();
        }
        return response()->json([
            'status' => true,
            'message' => 'added Successfully',
            'offers' => $var,
        ]);
    }

    public function editOffer(Request $request)
    {
        $validatedData = $request->validate([
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

        $var = offer::get();

        return response()->json([
            'status' => true,
            'message' => 'added Successfully',
            'offers' => $var,
        ]);
    }
}
