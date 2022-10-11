<?php
use App\Http\Controllers\MpesaController;
use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('access/token', [MpesaController::class, 'mpesaAccessToken']);
Route::post('password/generate', [MpesaController::class, 'lipaNaMpesaPassword']);
Route::post('ctc/payment/confirmation', [MpesaController::class, 'confirmationUrl']);
Route::post('ctc/validation', [MpesaController::class, 'mpesaValidation']);
Route::post('ctc/register/urls', [MpesaController::class, 'mpesaRegisterUrls']);
Route::post('ctc/stkpush', [MpesaController::class, 'stkPush']);
 
 
 Route::get('/payment/password','MpesaController@lipaNaMpesaPassword');
Route::post('/payment/new/access/token','MpesaController@newAccessToken');
Route::post('/payment/stk/push','MpesaController@stkPush')->name('lipa');
Route::post('/stk/push/callback/url', 'MpesaController@MpesaRes');
 










