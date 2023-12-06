<?php

namespace App\Http\Controllers\api;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Facades\Hash;

class UserController extends Authenticatable
{

    public function register(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required',
            'email' => 'email|required|unique:users',
            'password' => 'required|unique:users',
            'phone_no' => 'required',
            'city_id' => 'nullable',
            'type_id' => 'required',
            'badget' => ' ',
            'img_url' => ' ',
        ]);

        $validatedData['password'] = bcrypt($request->password);

        if ($request->has('img_url')) {
            $image1 = Str::random(32) . "." . $request->img_url->getClientOriginalExtension();
            $validatedData['img_url'] = $image1;
            Storage::disk('publicUsersPhotos')->put($image1, file_get_contents($request->img_url));
        }

        $user = User::create($validatedData);

        $accessToken = $user->createToken('authToken')->accessToken;

        return response()->json([
            'status' => true,
            'user' => $user,
            'message' => 'User Created Successfully',
            'access_token' => $accessToken
        ]);
    }

    public function login(Request $request)
    {
        $loginData = $request->validate([
            'name' => 'required',
            'password' => 'required'
        ]);

        if (!Auth::guard('web')->attempt(['name' => $loginData['name'], 'password' => $loginData['password']])) {
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
}
