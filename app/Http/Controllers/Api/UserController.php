<?php

namespace App\Http\Controllers\api;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

use App\Http\Controllers\Controller;
use App\Models\ad;
use App\Models\complaint;
use App\Models\favourite;
use App\Models\order;
use App\Models\product;
use App\Models\products_type;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Auth\User as Authenticatable;

class UserController extends Authenticatable
{

    public function register(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required',
            'email' => 'email|required|unique:users',
            'password' => 'required|unique:users',
            'phone_no' => 'required',
            'city_id' => 'required',
            'badget' => ' ',
            'img_url' => ' ',
        ]);

        $validatedData['password'] = bcrypt($request->password);
        $validatedData['type_id'] = 1;

        if ($request->has('img_url')) {
            $image1 = Str::random(32) . "." . $request->img_url->getClientOriginalExtension();
            $validatedData['img_url'] = $image1;
            Storage::disk('publicUsersPhotos')->put($image1, file_get_contents($request->img_url));
        }

        $user = User::create($validatedData);

        $accessToken = $user->createToken('authToken')->accessToken;

        return response()->json([
            'status' => true,
            'access_token' => $accessToken
        ]);
    }

    public function registerEmp(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required',
            'email' => 'email|required|unique:users',
            'password' => 'required|unique:users',
            'phone_no' => 'required',
            'city_id' => 'required',
            'badget' => ' ',
            'img_url' => ' ',
        ]);

        $validatedData['password'] = bcrypt($request->password);

        if ($request->has('type_id'))
            $validatedData['type_id'] = $request->type_id;
        else $validatedData['type_id'] = 2;


        if ($request->has('img_url')) {
            $image1 = Str::random(32) . "." . $request->img_url->getClientOriginalExtension();
            $validatedData['img_url'] = $image1;
            Storage::disk('publicUsersPhotos')->put($image1, file_get_contents($request->img_url));
        }

        $user = User::create($validatedData);

        $accessToken = $user->createToken('authToken')->accessToken;

        return response()->json([
            'status' => true,
            'access_token' => $accessToken
        ]);
    }

    public function login(Request $request)
    {
        $loginData = $request->validate([
            'password' => 'required',
            'phone_no' => 'required'
        ]);

        if (!Auth::guard('web')->attempt(['password' => $loginData['password'], 'phone_no' => $loginData['phone_no']])) {
            return response()->json(['status' => false, 'message' => 'Invalid User'], 404);
        }

        $accessToken = auth()->user()->createToken('authToken')->accessToken;

        return response()->json([
            'status' => true,
            'access_token' => $accessToken
        ]);
    }

    public function logout(Request $request)
    {
        $token = $request->user()->token();
        $token->revoke();
        return response()->json([
            'status' => true,
            'message' => "User logged out successfully"
        ]);
    }

    public function searchProducts(Request $request)
    {
        if ($request->has('name')) {
            $var = product::where('name', 'like', '%' . $request->name . '%')->where('visible', 1)->get();
        } else if ($request->has('type_id')) {
            $var = product::where('type_id', $request->type_id)->where('visible', 1)->get();
        } else if ($request->has('best')) {
            $var = Product::withCount('favourites')
                ->orderByDesc('favourites_count')
                ->where('visible', 1)
                ->get();
        } else $var = [];

        return response()->json([
            'status' => true,
            'data' => $var,
        ]);
    }

    public function searchOffers(Request $request)
    {
        if ($request->has('name')) {
            $var = product::where('name', 'like', '%' . $request->name . '%')
                ->join('offers as o', 'o.product_id', 'products.id')
                ->where('visible', 1)->get([
                    'o.id as offer_id', 'product_id', 'o.quantity as offer_quantity',
                    'new_price', 'name', 'type_id', 'disc', 'price as old_price',
                    'source_price', 'img_url'
                ]);
        } else if ($request->has('type_id')) {
            $var = product::where('type_id', $request->type_id)
                ->join('offers as o', 'o.product_id', 'products.id')
                ->where('visible', 1)->get([
                    'o.id as offer_id', 'product_id', 'o.quantity as offer_quantity',
                    'new_price', 'name', 'type_id', 'disc', 'price as old_price',
                    'source_price', 'img_url'
                ]);
        } else if ($request->has('best')) {
            $var = Product::withCount('favourites')
                ->orderByDesc('favourites_count')
                ->join('offers as o', 'o.product_id', 'products.id')
                ->where('visible', 1)->get([
                    'o.id as offer_id', 'product_id', 'o.quantity as offer_quantity',
                    'new_price', 'name', 'type_id', 'disc', 'price as old_price',
                    'source_price', 'img_url'
                ]);
        } else $var = [];


        return response()->json([
            'status' => true,
            'data' => $var,
        ]);
    }

    public function searchProductsAndOffers(Request $request)
    {
        if ($request->has('name')) {
            $products = product::where('name', 'like', '%' . $request->name . '%')->where('visible', 1)->get();
            $offers = product::where('name', 'like', '%' . $request->name . '%')
                ->join('offers as o', 'o.product_id', 'products.id')
                ->where('visible', 1)->get([
                    'o.id as offer_id', 'product_id', 'o.quantity as offer_quantity',
                    'new_price', 'name', 'type_id', 'disc', 'price as old_price',
                    'source_price', 'img_url'
                ]);
        } else if ($request->has('type_id')) {
            $products = product::where('type_id', $request->type_id)->where('visible', 1)->get();
            $offers = product::where('type_id', $request->type_id)
                ->join('offers as o', 'o.product_id', 'products.id')
                ->where('visible', 1)->get([
                    'o.id as offer_id', 'product_id', 'o.quantity as offer_quantity',
                    'new_price', 'name', 'type_id', 'disc', 'price as old_price',
                    'source_price', 'img_url'
                ]);
        } else if ($request->has('best')) {
            $products = Product::withCount('favourites')
                ->orderByDesc('favourites_count')
                ->where('visible', 1)->get();
            $offers = Product::withCount('favourites')
                ->orderByDesc('favourites_count')
                ->join('offers as o', 'o.product_id', 'products.id')
                ->where('visible', 1)->get([
                    'o.id as offer_id', 'product_id', 'o.quantity as offer_quantity',
                    'new_price', 'name', 'type_id', 'disc', 'price as old_price',
                    'source_price', 'img_url'
                ]);
        } else {
            $products = [];
            $offers = [];
        }

        return response()->json([
            'status' => true,
            'products' => $products,
            'offers' => $offers,
        ]);
    }

    public function showProductTypes()
    {
        $var = products_type::get();
        return response([
            'status' => true,
            'types' => $var
        ], 200);
    }

    public function showProducts()
    {
        $products = product::where('visible', 1)
            ->join('products_types', 'type_id', 'products_types.id')
            ->get([
                'products.id', 'products.name', 'type_id', 'products_types.name as type_name', 'disc',
                'long_disc', 'price', 'quantity', 'source_price', 'code', 'img_url'
            ]);

        $types = products_type::get();

        return response([
            'status' => true,
            'products' => $products,
            'products_types' => $types
        ], 200);
    }

    public function updateImg(Request $request)
    {
        $request->validate([
            'img_url' => 'required|image|mimes:jpg,webp,png,jpeg,gif,svg|max:2048',
        ]);

        $user = User::find(auth()->user()->id);
        $image1 = Str::random(32) . "." . $request->img_url->getClientOriginalExtension();
        $user->img_url = $image1;
        $user->save();

        Storage::disk('publicUsersPhotos')->put($image1, file_get_contents($request->img_url));
        return response([
            'status' => true,
            'message' => "done successfully"
        ], 200);
    }

    public function uploadImg(Request $request)
    {
        $request->validate([
            'img_url' => 'required|image|mimes:jpg,webp,png,jpeg,gif,svg|max:2048',
        ]);

        $image1 = Str::random(32) . "." . $request->img_url->getClientOriginalExtension();
        Storage::disk('publicUsersPhotos')->put($image1, file_get_contents($request->img_url));

        $imagePath = asset('public/' . $image1);

        return response([
            'status' => true,
            'message' => "Image saved successfully",
            'image_path' => $imagePath,
        ], 200);
    }

    public function addComplaint(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required',
        ]);

        $validatedData['user_id'] = auth()->user()->id;

        complaint::create($validatedData);

        return response()->json([
            'status' => true,
            'message' => 'sent successfully'
        ]);
    }

    public function showAdsImgs()
    {
        $var = ad::get();

        return response([
            'status' => true,
            'ads' => $var
        ], 200);
    }

    public function showOrdersImgs()
    {
        $var = order::get();

        return response([
            'status' => true,
            'ads' => $var
        ], 200);
    }

    public function addToFavourite($id)
    {
        if (!(favourite::where('product_id', $id)->where('user_id', auth()->user()->id)->exists())) {
            $fav = new favourite([
                'user_id' => auth()->user()->id,
                'product_id' => $id
            ]);
            $fav->save();
        }

        $var = favourite::where('user_id', auth()->user()->id)
            ->join('products', 'products.id', 'product_id')
            ->join('products_types', 'products_types.id', 'type_id')
            ->get([
                'user_id', 'product_id', 'products.name', 'type_id', 'products_types.name as type',
                'disc', 'long_disc', 'price', 'source_price', 'img_url'
            ]);

        return response([
            'status' => true,
            'favourites' => $var
        ], 200);
    }

    public function showFavourites()
    {

        $var = favourite::where('user_id', auth()->user()->id)
            ->join('products', 'products.id', 'product_id')
            ->join('products_types', 'products_types.id', 'type_id')
            ->get([
                'user_id', 'product_id', 'products.name', 'type_id', 'products_types.name as type',
                'disc', 'long_disc', 'price', 'source_price', 'img_url'
            ]);

        return response([
            'status' => true,
            'favourites' => $var
        ], 200);
    }

    public function deleteFavourite($id)
    {

        if (!(favourite::where('product_id', $id)->where('user_id', auth()->user()->id)->exists())) {
            return response(['message' => 'unauthorized',]);
        }

        favourite::where('product_id', $id)->where('user_id', auth()->user()->id)->delete();

        $var = favourite::where('user_id', auth()->user()->id)
            ->join('products', 'products.id', 'product_id')
            ->join('products_types', 'products_types.id', 'type_id')
            ->get([
                'user_id', 'product_id', 'products.name', 'type_id', 'products_types.name as type',
                'disc', 'long_disc', 'price', 'source_price', 'img_url'
            ]);

        return response([
            'status' => true,
            'message' => 'deleted successfully',
            'Data' => $var,
        ], 200);
    }

    public function showOffers()
    {
        $var = product::join('offers as o', 'o.product_id', 'products.id')
            ->where('visible', 1)->get([
                'o.id as offer_id', 'product_id', 'o.quantity as offer_quantity',
                'new_price', 'name', 'type_id', 'disc', 'price as old_price',
                'source_price', 'img_url'
            ]);
        return response()->json([
            'status' => true,
            'data' => $var,
        ]);
    }

    public function home()
    {
        $ads = ad::get();
        $products_types = products_type::get();
        $top_products = Product::withCount('favourites')
            ->orderByDesc('favourites_count')
            ->where('visible', 1)
            ->get();
        $top_offers = Product::withCount('favourites')
            ->orderByDesc('favourites_count')
            ->join('offers as o', 'o.product_id', 'products.id')
            ->where('visible', 1)->get([
                'o.id as offer_id', 'product_id', 'o.quantity as offer_quantity',
                'new_price', 'name', 'type_id', 'disc', 'price as old_price',
                'source_price', 'img_url'
            ]);
        return response()->json([
            'status' => true,
            'ads' => $ads,
            'products_types' => $products_types,
            'top_products' => $top_products,
            'top_offers' => $top_offers,
        ]);
    }
}
