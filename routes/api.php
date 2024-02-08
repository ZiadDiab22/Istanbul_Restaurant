<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\AdminController;


Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post("register", [UserController::class, "register"]);
Route::post("login", [UserController::class, "login"]);
Route::post("searchProducts", [UserController::class, "searchProducts"]);
Route::post("searchOffers", [UserController::class, "searchOffers"]);
Route::post("searchProductsAndOffers", [UserController::class, "searchProductsAndOffers"]);
Route::get("showProductTypes", [UserController::class, "showProductTypes"]);
Route::get("showProducts", [UserController::class, "showProducts"]);
Route::post("uploadImg", [UserController::class, "uploadImg"]);
Route::get("showAdsImgs", [UserController::class, "showAdsImgs"]);
Route::get("showOrdersImgs", [UserController::class, "showOrdersImgs"]);
Route::get("showOffers", [UserController::class, "showOffers"]);
Route::get("home", [UserController::class, "home"]);

/**********/
Route::post("updateImg", [UserController::class, "updateImg"]);
Route::post("updateProductImg", [AdminController::class, "updateProductImg"]);
Route::get("showComplaints", [AdminController::class, "showComplaints"]);
Route::get("deleteComplaint/{id}", [AdminController::class, "deleteComplaint"]);
Route::post("uploadAdImg", [AdminController::class, "uploadAdImg"]);
Route::get("deleteAdImg/{id}", [AdminController::class, "deleteAdImg"]);
Route::post("uploadOrderImg", [AdminController::class, "uploadOrderImg"]);
Route::get("deleteOrderImg/{id}", [AdminController::class, "deleteOrderImg"]);
Route::post("uploadLogo", [AdminController::class, "uploadLogo"]);
Route::get("showLogos", [AdminController::class, "showLogos"]);
Route::get("selectLogo/{id}", [AdminController::class, "selectLogo"]);
Route::post("uploadProductImg", [AdminController::class, "uploadProductImg"]);
Route::post("registerEmp", [UserController::class, "registerEmp"]);
Route::post("addProductType", [AdminController::class, "addProductType"]);
Route::post("deleteProductType", [AdminController::class, "deleteProductType"]);
Route::post("editProductType", [AdminController::class, "editProductType"]);
Route::post("addProduct", [AdminController::class, "addProduct"]);
Route::post("editProduct", [AdminController::class, "editProduct"]);
Route::get("editProductVis/{id}", [AdminController::class, "editProductVis"]);
Route::get("showhiddenProducts", [AdminController::class, "showhiddenProducts"]);
Route::get("blockUser/{id}", [AdminController::class, "blockUser"]);
Route::get("blockDelSer/{id}", [AdminController::class, "blockDelSer"]);
Route::post("addDelSer", [AdminController::class, "addDelSer"]);
Route::post("editDelSer", [AdminController::class, "editDelSer"]);
Route::get("showAdsOrdersLogos", [AdminController::class, "showAdsOrdersLogos"]);
Route::post("addOffer", [AdminController::class, "addOffer"]);
Route::post("editOffer", [AdminController::class, "editOffer"]);
/**********/

Route::group(["middleware" => ["auth:api"]], function () {
    Route::post("logout", [UserController::class, "logout"]);
    Route::post("addComplaint", [UserController::class, "addComplaint"])->middleware('checkUserId');
    Route::get("showFavourites", [UserController::class, "showFavourites"])->middleware('checkUserId');
    Route::get("deleteFavourite/{id}", [UserController::class, "deleteFavourite"])->middleware('checkUserId');
    Route::get("addToFavourite/{id}", [UserController::class, "addToFavourite"])->middleware('checkUserId');
});
