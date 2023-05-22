<?php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\FormController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\SocialiteController;
use App\Http\Controllers\applicationController;


/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/
Route::controller(AuthController::class)->group(function(){
    Route::post('register','register');
    Route::post('login','login')->name('connection');
    Route::post('logout', 'logout')->name('user.logout');
    Route::get('login', 'index')->name('login');
    Route::get('home', 'home')->name('home');

});
Route::controller(UserController::class)->group(function(){
    Route::get('utilisateurs','index');
    Route::get('modifier-utilisateur/{id}','editUser');
    Route::put('updateProfile','updateProfile');
    Route::delete('deleteProfile','deleteProfile');
    Route::delete('utilisateurs','deleteUser')->name('delete.user');
    Route::put('utilisateurs','updateUser')->name('update.user');
    Route::post('utilisateurs','createUser')->name('create.user');
});
Route::controller(FormController::class)->group(function(){
   Route::post('/formulaire','storeInformation')->name('send.Information');
   Route::get('/formulaire','index');
});

Route::controller(RoleController::class)->group(function(){
   Route::get('roles','getRoles');
   Route::post('roles','createRole')->name('create.role');
   Route::put('roles','updateRole')->name('update.role');
   Route::get('modifier-role/{id}','showRole');
   Route::delete('roles','deleteRole')->name('delete.role');
});

Route::controller(applicationController::class)->group(function(){
   Route::get('demandes','index')->name('demande');
   Route::get('show-files/{id}','showFiles');
   Route::put('demandes','updateStatus')->name('update.status');
   Route::get('edit-application/{id}','showEditform');
   Route::delete('demandes','deleteApplication')->name('delete.application');
});
Route::get('/register', function () {
    return view('register');
});

Route::get('/', function () {
    return view('home');
});

/* anl detail route */
Route::get('anl-detail',function () {
    return view('anl-detail');
});



Route::get('auth/google', [SocialiteController::class, 'redirectToGoogle']);
Route::get('auth/google/callback', [SocialiteController::class, 'handleGoogleCallback']);


// Route::get('auth/facebook', [SocialiteController::class, 'redirectToFacebook']);
// Route::get('auth/facebook/callback', [SocialiteController::class, 'handleFacebookCallback']);



