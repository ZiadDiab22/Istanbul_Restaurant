<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\api\UserController;
use App\Http\Controllers\api\AdminController;


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

Route::group(["middleware" => ["auth:api"]], function () {
    Route::post("logout", [UserController::class, "logout"]);
    Route::post("updateImg", [UserController::class, "updateImg"]);
    Route::post("updateProductImg", [AdminController::class, "updateProductImg"])->middleware('checkId');
    Route::post("addComplaint", [UserController::class, "addComplaint"])->middleware('checkUserId');
    Route::get("showComplaints", [AdminController::class, "showComplaints"])->middleware('checkId');
    Route::get("deleteComplaint/{id}", [AdminController::class, "deleteComplaint"])->middleware('checkId');
    Route::post("uploadAdImg", [AdminController::class, "uploadAdImg"])->middleware('checkId');
    Route::get("deleteAdImg/{id}", [AdminController::class, "deleteAdImg"])->middleware('checkId');
    Route::post("uploadOrderImg", [AdminController::class, "uploadOrderImg"])->middleware('checkId');
    Route::get("deleteOrderImg/{id}", [AdminController::class, "deleteOrderImg"])->middleware('checkId');
    Route::post("uploadLogo", [AdminController::class, "uploadLogo"])->middleware('checkId');
    Route::get("showLogos", [AdminController::class, "showLogos"])->middleware('checkId');
    Route::get("selectLogo/{id}", [AdminController::class, "selectLogo"])->middleware('checkId');
    Route::post("uploadProductImg", [AdminController::class, "uploadProductImg"])->middleware('checkId');
    Route::post("registerEmp", [UserController::class, "registerEmp"])->middleware('checkAdminId');
    Route::post("addProductType", [AdminController::class, "addProductType"])->middleware('checkId');
    Route::post("deleteProductType", [AdminController::class, "deleteProductType"])->middleware('checkId');
    Route::post("editProductType", [AdminController::class, "editProductType"])->middleware('checkId');
    Route::post("addProduct", [AdminController::class, "addProduct"])->middleware('checkId');
    Route::post("editProduct", [AdminController::class, "editProduct"])->middleware('checkId');
    Route::get("editProductVis/{id}", [AdminController::class, "editProductVis"])->middleware('checkId');
    Route::get("showhiddenProducts", [AdminController::class, "showhiddenProducts"])->middleware('checkId');
    Route::get("blockUser/{id}", [AdminController::class, "blockUser"])->middleware('checkId');
    Route::get("blockDelSer/{id}", [AdminController::class, "blockDelSer"])->middleware('checkId');
    Route::post("addDelSer", [AdminController::class, "addDelSer"])->middleware('checkId');
    Route::post("editDelSer", [AdminController::class, "editDelSer"])->middleware('checkId');
    Route::get("showAdsOrdersLogos", [AdminController::class, "showAdsOrdersLogos"])->middleware('checkId');
    Route::get("addToFavourite/{id}", [UserController::class, "addToFavourite"])->middleware('checkUserId');
    Route::get("showFavourites", [UserController::class, "showFavourites"])->middleware('checkUserId');
    Route::get("deleteFavourite/{id}", [UserController::class, "deleteFavourite"])->middleware('checkUserId');
    Route::post("addOffer", [AdminController::class, "addOffer"])->middleware('checkId');
    Route::post("editOffer", [AdminController::class, "editOffer"])->middleware('checkId');
});
