<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ContactUsController;
use App\Http\Controllers\ProductController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('home.index');
})->name('home.index');
Route::get('/about-us', function () {
    return view('about-us');
})->name('about.index');

Route::group(['prefix' => '/contact-us'],function () {
Route::get('/',[ContactUsController::class,'index'])->name('contact.index');
Route::post('/',[ContactUsController::class,'store'])->name('contact.store');
});



Route::get('/products/{product:slug}',[ProductController::class,'show'])->name('product.show');
Route::get('/menu',[ProductController::class,'menu'])->name('product.menu');

Route::middleware('guest')->group(function() {
Route::get('/login',[AuthController::class,'loginForm'])->name('auth.login.form');
Route::post('/login',[AuthController::class,'login'])->name('auth.login');
Route::post('/check-otp',[AuthController::class,'checkOtp'])->name('auth.check-Otp');
Route::post('/resend-otp',[AuthController::class,'resendOtp'])->name('auth.resendOtp');
});

