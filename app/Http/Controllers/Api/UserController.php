<?php

namespace App\Http\Controllers\api;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

use App\Http\Controllers\Controller;
use App\Models\ad;
use App\Models\city;
use App\Models\complaint;
use App\Models\favourite;
use App\Models\logo;
use App\Models\offer;
use App\Models\order;
use App\Models\product;
use App\Models\products_type;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Auth\User as Authenticatable;

class UserController extends Authenticatable
{

    public function register(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required',
            'email' => 'email|required',
            'password' => 'required',
            'phone_no' => 'required',
            'city_id' => 'required',
            'badget' => ' ',
            'img_url' => ' ',
        ]);

        if (User::where('email', $request->email)->exists()) {
            return response()->json([
                'status' => false,
                'message' => "email is taken"
            ], 200);
        }

        if (User::where('phone_no', $request->phone_no)->exists()) {
            return response()->json([
                'status' => false,
                'message' => "phone number is taken"
            ], 200);
        }

        $validatedData['password'] = bcrypt($request->password);
        $validatedData['type_id'] = 1;

        if ($request->has('img_url')) {
            $image1 = Str::random(32) . "." . $request->img_url->getClientOriginalExtension();
            Storage::disk('public_htmlUsersPhotos')->put($image1, file_get_contents($request->img_url));
            $image1 = asset('UsersPhotos/' . $image1);
            $validatedData['img_url'] = $image1;
        }

        $user = User::create($validatedData);

        $accessToken = $user->createToken('authToken')->accessToken;

        $user_data = User::where('id', $user->id)->first();

        return response()->json([
            'status' => true,
            'access_token' => $accessToken,
            'user_data' => $user_data
        ]);
    }

    public function registerEmp(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required',
            'email' => 'email|required',
            'password' => 'required',
            'phone_no' => 'required',
            'city_id' => 'required',
            'badget' => ' ',
            'img_url' => ' ',
        ]);

        if (User::where('email', $request->email)->exists()) {
            return response()->json([
                'status' => false,
                'message' => "email is taken"
            ], 200);
        }

        if (User::where('phone_no', $request->phone_no)->exists()) {
            return response()->json([
                'status' => false,
                'message' => "phone number is taken"
            ], 200);
        }

        $validatedData['password'] = bcrypt($request->password);

        if ($request->has('type_id'))
            $validatedData['type_id'] = $request->type_id;
        else $validatedData['type_id'] = 2;


        if ($request->has('img_url')) {
            $image1 = Str::random(32) . "." . $request->img_url->getClientOriginalExtension();
            Storage::disk('public_htmlUsersPhotos')->put($image1, file_get_contents($request->img_url));
            $image1 = asset('UsersPhotos/' . $image1);
            $validatedData['img_url'] = $image1;
        }

        $user = User::create($validatedData);

        $accessToken = $user->createToken('authToken')->accessToken;

        $user_data = User::where('id', $user->id)->first();

        return response()->json([
            'status' => true,
            'access_token' => $accessToken,
            'user_data' => $user_data
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

        $user_data = User::where('phone_no', $request->phone_no)->first();

        return response()->json([
            'status' => true,
            'access_token' => $accessToken,
            'user_data' => $user_data
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
            $var = product::where('products.name', 'like', '%' . $request->name . '%')->where('visible', 1)->where('quantity', '>', 0)
                ->join('products_types as pt', 'products.type_id', 'pt.id')
                ->get([
                    'products.id', 'products.name', 'type_id', 'pt.name as type', 'disc', 'long_disc',
                    'price', 'quantity', 'code', 'img_url', 'visible'
                ]);
        } else if ($request->has('type_id')) {
            $var = product::where('type_id', $request->type_id)->where('visible', 1)->where('quantity', '>', 0)
                ->join('products_types as pt', 'products.type_id', 'pt.id')
                ->get([
                    'products.id', 'products.name', 'type_id', 'pt.name as type', 'disc', 'long_disc',
                    'price', 'quantity', 'code', 'img_url', 'visible'
                ]);
        } else if ($request->has('best')) {
            $var = DB::table('products as p')
                ->where('quantity', '>', 0)
                ->join('favourites', 'p.id', 'favourites.product_id')
                ->join('products_types as pt', 'p.type_id', 'pt.id')
                ->select(
                    'p.id',
                    'p.name',
                    'p.type_id',
                    'pt.name as type',
                    'p.img_url',
                    'p.disc',
                    'p.long_disc',
                    'p.price',
                    'p.quantity',
                    'p.code',
                    'p.visible',
                    DB::raw('count(favourites.id) as favourite_count')
                )
                ->groupBy(
                    'p.id',
                    'p.name',
                    'p.img_url',
                    'p.type_id',
                    'p.disc',
                    'p.long_disc',
                    'p.price',
                    'p.quantity',
                    'p.code',
                    'pt.name',
                    'p.visible'
                )
                ->orderBy('favourite_count', 'desc')
                ->where('visible', 1)
                ->get();


            if (count($var) == 0) {
                $var = product::where('visible', 1)->where('quantity', '>', 0)
                    ->join('products_types as pt', 'products.type_id', 'pt.id')
                    ->get([
                        'products.id', 'products.name', 'type_id', 'pt.name as type', 'disc', 'long_disc',
                        'price', 'quantity', 'code', 'img_url', 'visible'
                    ]);
            }
        } else $var = [];

        return response()->json([
            'status' => true,
            'data' => $var,
        ]);
    }

    public function searchOffers(Request $request)
    {
        if ($request->has('name')) {
            $var = product::where('products.name', 'like', '%' . $request->name . '%')
                ->where('visible', 1)->where('products.quantity', '>', 0)
                ->join('offers as o', 'o.product_id', 'products.id')
                ->where('o.quantity', '>', 0)
                ->join('products_types as pt', 'products.type_id', 'pt.id')
                ->orderBy('percentage', 'desc')->get([
                    'o.id as offer_id', 'product_id', 'o.quantity as offer_quantity',
                    'new_price', 'products.name as product_name', 'pt.name as type',
                    'type_id', 'disc', 'long_disc', 'price as old_price',
                    'percentage', 'img_url', 'o.created_at', 'o.updated_at', 'code', 'visible'
                ]);
        } else if ($request->has('type_id')) {
            $var = product::where('type_id', $request->type_id)
                ->where('visible', 1)->where('products.quantity', '>', 0)
                ->join('offers as o', 'o.product_id', 'products.id')
                ->where('o.quantity', '>', 0)
                ->join('products_types as pt', 'products.type_id', 'pt.id')
                ->orderBy('percentage', 'desc')->get([
                    'o.id as offer_id', 'product_id', 'o.quantity as offer_quantity',
                    'new_price', 'products.name as product_name', 'pt.name as type',
                    'type_id', 'disc', 'long_disc', 'price as old_price',
                    'percentage', 'img_url', 'o.created_at', 'o.updated_at', 'code', 'visible'
                ]);
        } else if ($request->has('best')) {
            $var = DB::table('products as p')
                ->where('visible', 1)
                ->where('p.quantity', '>', 0)
                ->join('favourites', 'p.id', 'favourites.product_id')
                ->join('offers as o', 'o.product_id', 'p.id')
                ->where('o.quantity', '>', 0)
                ->join('products_types as pt', 'p.type_id', 'pt.id')
                ->select(
                    'p.id as product_id',
                    'o.id as offer_id',
                    'p.name as product_name',
                    'p.type_id',
                    'pt.name as type',
                    'p.disc',
                    'p.long_disc',
                    'p.price as old_price',
                    'p.code',
                    'p.img_url',
                    'p.visible',
                    'o.quantity as offer_quantity',
                    'o.percentage',
                    'o.new_price',
                    'o.created_at',
                    'o.updated_at',
                    DB::raw('count(favourites.id) as favourite_count')
                )
                ->groupBy(
                    'p.id',
                    'o.id',
                    'p.type_id',
                    'p.img_url',
                    'p.disc',
                    'p.long_disc',
                    'p.price',
                    'p.code',
                    'p.visible',
                    'o.quantity',
                    'o.percentage',
                    'pt.name',
                    'o.new_price',
                    'o.created_at',
                    'o.updated_at',
                    'p.name',
                )
                ->orderBy('favourite_count', 'desc')
                ->orderBy('percentage', 'desc')
                ->get();

            if (count($var) == 0) {
                $var = product::where('visible', 1)->where('products.quantity', '>', 0)
                    ->join('offers as o', 'o.product_id', 'products.id')
                    ->where('o.quantity', '>', 0)
                    ->join('products_types as pt', 'products.type_id', 'pt.id')
                    ->orderBy('percentage', 'desc')->get([
                        'o.id as offer_id', 'product_id', 'o.quantity as offer_quantity',
                        'new_price', 'products.name as product_name', 'pt.name as type',
                        'type_id', 'disc', 'long_disc', 'price as old_price',
                        'percentage', 'img_url', 'o.created_at', 'o.updated_at', 'code', 'visible'
                    ]);
            }
        } else $var = [];

        return response()->json([
            'status' => true,
            'data' => $var,
        ]);
    }

    public function searchProductsAndOffers(Request $request)
    {
        if ($request->has('name')) {
            $products = product::where('products.name', 'like', '%' . $request->name . '%')->where('visible', 1)->where('quantity', '>', 0)
                ->join('products_types as pt', 'products.type_id', 'pt.id')
                ->get([
                    'products.id', 'products.name', 'type_id', 'pt.name as type', 'disc', 'long_disc',
                    'price', 'quantity', 'code', 'img_url', 'visible'
                ]);
            $offers = product::where('products.name', 'like', '%' . $request->name . '%')
                ->where('visible', 1)->where('products.quantity', '>', 0)
                ->join('offers as o', 'o.product_id', 'products.id')
                ->where('o.quantity', '>', 0)
                ->join('products_types as pt', 'products.type_id', 'pt.id')
                ->orderBy('percentage', 'desc')->get([
                    'o.id as offer_id', 'product_id', 'o.quantity as offer_quantity',
                    'new_price', 'products.name as product_name', 'pt.name as type',
                    'type_id', 'disc', 'long_disc', 'price as old_price',
                    'percentage', 'img_url', 'o.created_at', 'o.updated_at', 'code', 'visible'
                ]);
        } else if ($request->has('type_id')) {
            $products = product::where('type_id', $request->type_id)->where('visible', 1)->where('quantity', '>', 0)
                ->join('products_types as pt', 'products.type_id', 'pt.id')
                ->get([
                    'products.id', 'products.name', 'type_id', 'pt.name as type', 'disc', 'long_disc',
                    'price', 'quantity', 'code', 'img_url', 'visible'
                ]);
            $offers = product::where('type_id', $request->type_id)
                ->where('visible', 1)->where('products.quantity', '>', 0)
                ->join('offers as o', 'o.product_id', 'products.id')
                ->where('o.quantity', '>', 0)
                ->join('products_types as pt', 'products.type_id', 'pt.id')
                ->orderBy('percentage', 'desc')->get([
                    'o.id as offer_id', 'product_id', 'o.quantity as offer_quantity',
                    'new_price', 'products.name as product_name', 'pt.name as type',
                    'type_id', 'disc', 'long_disc', 'price as old_price',
                    'percentage', 'img_url', 'o.created_at', 'o.updated_at', 'code', 'visible'
                ]);
        } else if ($request->has('best')) {
            $products = DB::table('products as p')
                ->where('quantity', '>', 0)
                ->join('favourites', 'p.id', 'favourites.product_id')
                ->join('products_types as pt', 'p.type_id', 'pt.id')
                ->select(
                    'p.id',
                    'p.name',
                    'p.type_id',
                    'pt.name as type',
                    'p.img_url',
                    'p.disc',
                    'p.long_disc',
                    'p.price',
                    'p.quantity',
                    'p.code',
                    'p.visible',
                    DB::raw('count(favourites.id) as favourite_count')
                )
                ->groupBy(
                    'p.id',
                    'p.name',
                    'p.img_url',
                    'p.type_id',
                    'p.disc',
                    'p.long_disc',
                    'p.price',
                    'p.quantity',
                    'p.code',
                    'pt.name',
                    'p.visible'
                )
                ->orderBy('favourite_count', 'desc')
                ->where('visible', 1)
                ->get();


            if (count($products) == 0) {
                $products = product::where('visible', 1)->where('quantity', '>', 0)
                    ->join('products_types as pt', 'products.type_id', 'pt.id')
                    ->get([
                        'products.id', 'products.name', 'type_id', 'pt.name as type', 'disc', 'long_disc',
                        'price', 'quantity', 'code', 'img_url', 'visible'
                    ]);
            }

            $offers = DB::table('products as p')
                ->where('visible', 1)
                ->where('p.quantity', '>', 0)
                ->join('favourites', 'p.id', 'favourites.product_id')
                ->join('offers as o', 'o.product_id', 'p.id')
                ->where('o.quantity', '>', 0)
                ->join('products_types as pt', 'p.type_id', 'pt.id')
                ->select(
                    'p.id as product_id',
                    'o.id as offer_id',
                    'p.name as product_name',
                    'p.type_id',
                    'pt.name as type',
                    'p.disc',
                    'p.long_disc',
                    'p.price as old_price',
                    'p.code',
                    'p.img_url',
                    'p.visible',
                    'o.quantity as offer_quantity',
                    'o.percentage',
                    'o.new_price',
                    'o.created_at',
                    'o.updated_at',
                    DB::raw('count(favourites.id) as favourite_count')
                )
                ->groupBy(
                    'p.id',
                    'o.id',
                    'p.type_id',
                    'p.img_url',
                    'p.disc',
                    'p.long_disc',
                    'p.price',
                    'p.code',
                    'p.visible',
                    'o.quantity',
                    'o.percentage',
                    'pt.name',
                    'o.new_price',
                    'o.created_at',
                    'o.updated_at',
                    'p.name',
                )
                ->orderBy('favourite_count', 'desc')
                ->orderBy('percentage', 'desc')
                ->get();

            if (count($offers) == 0) {
                $offers = product::where('visible', 1)->where('products.quantity', '>', 0)
                    ->join('offers as o', 'o.product_id', 'products.id')
                    ->where('o.quantity', '>', 0)
                    ->join('products_types as pt', 'products.type_id', 'pt.id')
                    ->orderBy('percentage', 'desc')->get([
                        'o.id as offer_id', 'product_id', 'o.quantity as offer_quantity',
                        'new_price', 'products.name as product_name', 'pt.name as type',
                        'type_id', 'disc', 'long_disc', 'price as old_price',
                        'percentage', 'img_url', 'o.created_at', 'o.updated_at', 'code', 'visible'
                    ]);
            }
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
        $products = product::join('products_types', 'type_id', 'products_types.id')
            ->get([
                'products.id', 'products.name', 'type_id', 'products_types.name as type_name', 'disc',
                'long_disc', 'price', 'quantity', 'source_price', 'code', 'img_url', 'visible'
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
        Storage::disk('public_htmlUsersPhotos')->put($image1, file_get_contents($request->img_url));
        $image1 = asset('UsersPhotos/' . $image1);
        $validatedData['img_url'] = $image1;
        $user->save();

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
        Storage::disk('public_htmlUsersPhotos')->put($image1, file_get_contents($request->img_url));

        $imagePath = asset('UsersPhotos/' . $image1);

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

    public function toggleFavourite($id)
    {
        if (!(product::where('id', $id)->exists())) {
            return response([
                'status' => false,
                'message' => 'product not found, wrong product id'
            ], 200);
        }

        if (!(favourite::where('product_id', $id)->where('user_id', auth()->user()->id)->exists())) {
            $fav = new favourite([
                'user_id' => auth()->user()->id,
                'product_id' => $id
            ]);
            $fav->save();
        } else favourite::where('product_id', $id)->where('user_id', auth()->user()->id)->delete();

        $var = favourite::where('user_id', auth()->user()->id)
            ->join('products', 'products.id', 'product_id')
            ->join('products_types', 'products_types.id', 'type_id')
            ->get([
                'favourites.id as favourite_id', 'user_id', 'product_id', 'products.name', 'type_id', 'products_types.name as type',
                'disc', 'long_disc', 'price', 'img_url', 'code', 'quantity', 'visible'
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
            'offers' => $offers,
            'products' => $products,
            'products_types' => $types
        ]);
    }

    public function home()
    {
        $ads = ad::get();
        $products_types = products_type::get();
        $top_products = DB::table('products as p')
            ->join('favourites', 'p.id', 'favourites.product_id')
            ->join('products_types as pt', 'p.type_id', 'pt.id')
            ->select(
                'p.id',
                'p.name',
                'p.img_url',
                'p.type_id',
                'pt.name as type',
                'p.img_url',
                'p.disc',
                'p.long_disc',
                'p.price',
                'p.quantity',
                'p.code',
                'p.img_url',
                'p.visible',
                DB::raw('count(favourites.id) as favourite_count')
            )
            ->groupBy(
                'p.id',
                'p.name',
                'p.img_url',
                'p.type_id',
                'p.img_url',
                'p.disc',
                'p.long_disc',
                'p.price',
                'p.quantity',
                'p.code',
                'p.img_url',
                'pt.name',
                'p.visible'
            )
            ->orderBy('favourite_count', 'desc')
            ->where('visible', 1)
            ->where('p.quantity', '>', 0)
            ->get();


        if (count($top_products) == 0) {
            $top_products = product::where('visible', 1)->where('quantity', '>', 0)
                ->join('products_types as pt', 'products.type_id', 'pt.id')

                ->get([
                    'products.id',
                    'products.name',
                    'img_url',
                    'type_id',
                    'pt.name as type',
                    'img_url',
                    'disc',
                    'long_disc',
                    'price',
                    'quantity',
                    'code',
                    'img_url',
                    'visible'
                ]);
        }

        $top_offers = DB::table('products as p')
            ->join('favourites', 'p.id', 'favourites.product_id')
            ->join('offers as o', 'o.product_id', 'p.id')
            ->join('products_types as pt', 'p.type_id', 'pt.id')
            ->where('p.quantity', '>', 0)
            ->where('o.quantity', '>', 0)
            ->where('visible', 1)
            ->select(
                'p.id as product_id',
                'o.id as offer_id',
                'p.name as product_name',
                'p.img_url',
                'p.type_id',
                'pt.name as type',
                'p.disc',
                'p.long_disc',
                'p.price',
                'p.code',
                'p.visible',
                'o.quantity as offer_quantity',
                'o.percentage',
                'o.new_price',
                'o.created_at',
                'o.updated_at',
                DB::raw('count(favourites.id) as favourite_count')
            )
            ->groupBy(
                'p.id',
                'o.id',
                'p.name',
                'p.type_id',
                'p.img_url',
                'p.disc',
                'p.long_disc',
                'p.price',
                'p.code',
                'p.visible',
                'o.quantity',
                'o.percentage',
                'pt.name',
                'o.new_price',
                'o.created_at',
                'o.updated_at',
            )
            ->orderBy('favourite_count', 'desc')
            ->orderBy('percentage', 'desc')
            ->get();


        if (count($top_offers) == 0) {
            $top_offers = product::where('visible', 1)->where('products.quantity', '>', 0)
                ->join('offers as o', 'o.product_id', 'products.id')
                ->join('products_types as pt', 'products.type_id', 'pt.id')
                ->where('o.quantity', '>', 0)
                ->orderBy('percentage', 'desc')
                ->get([
                    'products.id as product_id',
                    'products.name as product_name',
                    'o.id as offer_id',
                    'type_id',
                    'pt.name as type',
                    'disc',
                    'long_disc',
                    'code',
                    'img_url',
                    'o.quantity as offer_quantity',
                    'o.percentage',
                    'o.created_at',
                    'o.updated_at',
                    'products.price',
                    'o.new_price', 'visible'
                ]);
        }

        return response()->json([
            'status' => true,
            'ads' => $ads,
            'products_types' => $products_types,
            'top_products' => $top_products,
            'top_offers' => $top_offers,
        ]);
    }

    public function getLogoImg()
    {

        $name = logo::where('selected', 1)->get('img_url');

        if (count($name) == 0)
            return response()->json([
                'status' => false,
                'msg' => 'no photo selected',
            ]);

        $parsedUrl = parse_url($name[0]['img_url']);

        if ($parsedUrl !== false && isset($parsedUrl['path'])) {
            $relativePath = Str::replaceFirst('Logos', 'public_html/Logos', $parsedUrl['path']);
        }

        return response()->file(base_path($relativePath));
    }

    public function add()
    {

        $cities = [
            "Абакан",
            "Ачинск",
            "Альметьевск",
            "Анадырь",
            "Ангарск",
            "Архангельск",
            "Армавир",
            "Артем",
            "Арзамас",
            "Астрахань",
            "Баксан",
            "Балаково",
            "Балашиха",
            "Барнаул",
            "Батайск",
            "Белая Речка",
            "Белгород",
            "Бердск",
            "Березники",
            "Биробиджан",
            "Бийск",
            "Благовещенск",
            "Братск",
            "Брянск",
            "Буйнакск",
            "Бузулук",
            "Чебоксары",
            "Челябинск",
            "Череповец",
            "Черкесск",
            "Черногорск",
            "Чита",
            "Дагестанские Огни",
            "Дербент",
            "Десногорск",
            "Димитровград",
            "Долгопрудный",
            "Домодедово",
            "Дзержинск",
            "Дзержинский",
            "Дзержинское",
            "Электросталь",
            "Элиста",
            "Энгельс",
            "Фрязино",
            "Георгиевск",
            "Горно-Алтайск",
            "Грозный",
            "Гуково",
            "Иркутск",
            "Ишим",
            "Искитим",
            "Иваново",
            "Ивантеевка",
            "Ижевск",
            "Калининград",
            "Калуга",
            "Каменск-Уральский",
            "Камышин",
            "Канаш",
            "Карачаевск",
            "Касимов",
            "Каспийск",
            "Казань",
            "Кемерово",
            "Кенже",
            "Хабаровск",
            "Ханты-Мансийск",
            "Хасанья",
            "Хасавюрт",
            "Химки",
            "Кинешма",
            "Киров",
            "Кирово-Чепецк",
            "Кирсанов",
            "Кисловодск",
            "Кизляр",
            "Климовск",
            "Кохма",
            "Коломна",
            "Колпино",
            "Комсомольск-на-Амуре",
            "Копейск",
            "Королев",
            "Кострома",
            "Котельники",
            "Котовск",
            "Ковров",
            "Козьмодемьянск",
            "Красково",
            "Краснодар",
            "Красногорск",
            "Красноярск",
            "Краснознаменск",
            "Кудрово",
            "Курган",
            "Курск",
            "Кузнецк",
            "Кызыл",
            "Липецк",
            "Лобня",
            "Лосино-Петровский",
            "Лыткарино",
            "Люберцы",
            "Магадан",
            "Магас",
            "Магнитогорск",
            "Махачкала",
            "Майкоп",
            "Миасс",
            "Молочное",
            "Моршанск",
            "Москва",
            "Московский",
            "Можга",
            "Мценск",
            "Мурманск",
            "Муром",
            "Мытищи",
            "Набережные Челны",
            "Находка",
            "Нальчик",
            "Нарьян-Мар",
            "Назрань",
            "Нефтекамск",
            "Нефтеюганск",
            "Невинномысск",
            "Нижнекамск",
            "Нижневартовск",
            "Нижний Новгород",
            "Нижний Тагил",
            "Ногинск",
            "Ногинск",
            "Норильск",
            "Новочебоксарск",
            "Новочеркасск",
            "Новокуйбышевск",
            "Новокузнецк",
            "Новомосковск",
            "Новороссийск",
            "Новошахтинск",
            "Новосибирск",
            "Нововятск",
            "Новый Уренгой",
            "Ноябрьск",
            "Обнинск",
            "Одинцово",
            "Октябрьский",
            "Октябрьский",
            "Омск",
            "Орехово-Борисово Южное",
            "Орехово-Зуево",
            "Оренбург",
            "Орск",
            "Орел",
            "Пенза",
            "Переславль-Залесский",
            "Пермь",
            "Первоуральск",
            "Петропавловск-Камчатский",
            "Петрозаводск",
            "Пионерский",
            "Подольск",
            "Прохладный",
            "Прокопьевск",
            "Протвино",
            "Псков",
            "Пушкино",
            "Пятигорск",
            "Раменское",
            "Реутов",
            "Ростов",
            "Рубцовск",
            "Рязань",
            "Рыбинск",
            "Санкт-Петербург",
            "Салават",
            "Салехард",
            "Самара",
            "Саранск",
            "Саратов",
            "Сергиев Посад",
            "Серпухов",
            "Северодвинск",
            "Северск",
            "Шахты",
            "Щелково",
            "Шумерля",
            "Шуя",
            "Смоленск",
            "Сочи",
            "Солянка",
            "Старый Оскол",
            "Ставрополь",
            "Стерлитамак",
            "Сургут",
            "Сыктывкар",
            "Сызрань",
            "Таганрог",
            "Тамбов",
            "Тимофеевка",
            "Тольятти",
            "Томск",
            "Тула",
            "Тверь",
            "Тюмень",
            "Удельная",
            "Удомля",
            "Уфа",
            "Улан-Удэ",
            "Ульяновск",
            "Уссурийск",
            "Великий Новгород",
            "Великие Луки",
            "Владикавказ",
            "Владимир",
            "Владивосток",
            "Волгодонск",
            "Волгоград",
            "Вологда",
            "Волжск",
            "Волжский",
            "Воронеж",
            "Якутск",
            "Ярославль",
            "Екатеринбург",
            "Елец",
            "Ессентуки",
            "Йошкар-Ола",
            "Юрга",
            "Южно-Сахалинск",
            "Зеленодольск",
            "Железногорск",
            "Жуковский",
            "Златоуст"
        ];

        $id = 1;
        foreach ($cities as $city) {
            DB::table('cities')
                ->where('id', $id)
                ->update(['name' => $city]);
            $id++;
        }

        return response()->json([
            'status' => true,
            'msg' => "Added Successfully"
        ]);
    }

    public function showCities()
    {

        $cities = city::get();

        return response()->json([
            'status' => true,
            'data' => $cities
        ]);
    }

    public function AddEmploymentRequest(Request $request)
    {
        $request->validate([
            'cv' => 'required|mimes:pdf',
        ]);

        $file = $request->file('cv');
        $path = Storage::disk('public_htmlCVs')->put('', $file);
        $path = asset('CVs/' . $path);

        DB::table('forms')->insert([
            'user_id' => auth()->user()->id,
            'file_url' => $path,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json([
            'status' => true,
            'message' => 'sent successfully',
            'path' => $path
        ]);
    }

    public function showUserRequests()
    {
        $id = auth()->user()->id;
        $requests = DB::table('requests as r')
            ->where('r.user_id', $id)
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

    public function addRequestFromweb(Request $request)
    {
        $request->validate([
            'products' => 'required',
        ]);

        $state_id = auth()->user()->type_id == 1 ? 1 : 2;
        $emp_id = auth()->user()->type_id == 1 ? null : auth()->user()->id;

        $req_id = DB::table('requests')->insertGetId([
            'user_id' => auth()->user()->id,
            'delivery_ser_id' => isset($request->del_ser_id) ? $request->del_ser_id : null,
            'discount_id' => isset($request->discount_id) ? $request->discount_id : null,
            'payment' => isset($request->cash_payment) ? $request->cash_payment : "cash",
            'employee_id' => $emp_id,
            'state_id' => $state_id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        foreach ($request->products as $product) {
            if (!(isset($product[0]['offer_id']))) {
                $p = product::findOrFail($product[0]['id']);
                if ($p->quantity < $product[0]['quantity']) {
                    $infos = DB::table('requests_infos')->where('req_id', $req_id)->get();
                    foreach ($infos as $info) {
                        if (isset($info->offer_id)) {
                            $off = offer::findOrFail($info->offer_id);
                            $off->quantity += $info->quantity;
                            $off->save();
                        } else {
                            $pr = product::findOrFail($info->product_id);
                            $pr->quantity += $info->quantity;
                            $pr->save();
                        }
                    }
                    DB::table('requests')->where('id', $req_id)->delete();
                    return response()->json([
                        'status' => false,
                        'message' => 'quantity not available , in product with id = ' . $product[0]['id'],
                    ], 200);
                }
                $p->quantity -= $product[0]['quantity'];
                $p->save();
            } else {
                $offer = offer::findOrFail($product[0]['offer_id']);
                if ($offer->quantity < $product[0]['quantity']) {
                    $infos = DB::table('requests_infos')->where('req_id', $req_id)->get();
                    foreach ($infos as $info) {
                        if (isset($info->offer_id)) {
                            $off = offer::findOrFail($info->offer_id);
                            $off->quantity += $info->quantity;
                            $off->save();
                        } else {
                            $pr = product::findOrFail($info->product_id);
                            $pr->quantity += $info->quantity;
                            $pr->save();
                        }
                    }
                    DB::table('requests')->where('id', $req_id)->delete();
                    return response()->json([
                        'status' => false,
                        'message' => 'quantity not available , in product with id = ' . $product[0]['id'],
                    ], 200);
                }
                $offer->quantity -= $product[0]['quantity'];
                $offer->save();
            }

            DB::table('requests_infos')->insert([
                'req_id' => $req_id,
                'product_id' => $product[0]['id'],
                'offer_id' => isset($product[0]['offer_id']) ? $product[0]['offer_id'] : null,
                'quantity' => $product[0]['quantity'],
            ]);
        }
        if (auth()->user()->type_id == 1) {
            return response()->json([
                'status' => true,
                'message' => 'created successfully',
            ], 200);
        } else {
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
    }

    public function addRequestFromApp(Request $request)
    {
        $data = $request->input('products');

        $req_id = DB::table('requests')->insertGetId([
            'user_id' => auth()->user()->id,
            'state_id' => 1,
            'delivery_ser_id' => isset($request->del_ser_id) ? $request->del_ser_id : null,
            'discount_id' => isset($request->discount_id) ? $request->discount_id : null,
            'payment' => isset($request->payment) ? $request->payment : "cash",
            'discount_app' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        foreach ($data as $p) {

            if (!(isset($p['offer_id']))) {
                $pro = product::findOrFail($p['product_id']);
                if ($pro->quantity < $p['quantity']) {
                    $infos = DB::table('requests_infos')->where('req_id', $req_id)->get();
                    foreach ($infos as $info) {
                        if (isset($info->offer_id)) {
                            $off = offer::findOrFail($info->offer_id);
                            $off->quantity += $info->quantity;
                            $off->save();
                        } else {
                            $pr = product::findOrFail($info->product_id);
                            $pr->quantity += $info->quantity;
                            $pr->save();
                        }
                    }
                    DB::table('requests')->where('id', $req_id)->delete();
                    return response()->json([
                        'status' => false,
                        'message' => 'quantity not available , in product with id = ' . $p['product_id'],
                    ], 200);
                }
                $pro->quantity -= $p['quantity'];
                $pro->save();
            } else {
                $offer = offer::findOrFail($p['offer_id']);
                if ($offer->quantity < $p['quantity']) {
                    $infos = DB::table('requests_infos')->where('req_id', $req_id)->get();
                    foreach ($infos as $info) {
                        if (isset($info->offer_id)) {
                            $off = offer::findOrFail($info->offer_id);
                            $off->quantity += $info->quantity;
                            $off->save();
                        } else {
                            $pr = product::findOrFail($info->product_id);
                            $pr->quantity += $info->quantity;
                            $pr->save();
                        }
                    }
                    DB::table('requests')->where('id', $req_id)->delete();
                    return response()->json([
                        'status' => false,
                        'message' => 'quantity not available , in product with id = ' . $p['product_id'],
                    ], 200);
                }
                $offer->quantity -= $p['quantity'];
                $offer->save();
            }
            DB::table('requests_infos')->insert([
                'req_id' => $req_id,
                'product_id' => $p['product_id'],
                'offer_id' => isset($p['offer_id']) ? $p['offer_id'] : null,
                'quantity' => $p['quantity'],
            ]);
        }

        return response()->json([
            'status' => true,
            'message' => 'created successfully',
        ], 200);
    }
}
