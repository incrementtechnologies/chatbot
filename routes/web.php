<?php

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

Route::get('/', function () {
    return "heel";//view('welcome');
});
/*
  Accessing uploaded files
*/
Route::get('file/icon/{filename}', function ($filename)
{
  $path = storage_path('/icons/' . $filename);
  if (!File::exists($path)) {
      abort(404);
  }
  $file = File::get($path);
  $type = File::mimeType($path);
  $response = Response::make($file, 200);
  $response->header("Content-Type", $type);
  return $response;
});
Route::get('file/company/{filename}', function ($filename)
{
  $path = storage_path('/company/' . $filename);
  if (!File::exists($path)) {
      abort(404);
  }
  $file = File::get($path);
  $type = File::mimeType($path);
  $response = Response::make($file, 200);
  $response->header("Content-Type", $type);
  return $response;
});
Route::get('file/q_card/{filename}', function ($filename)
{
  $path = storage_path('/qcards/' . $filename);
  if (!File::exists($path)) {
      abort(404);
  }
  $file = File::get($path);
  $type = File::mimeType($path);
  $response = Response::make($file, 200);
  $response->header("Content-Type", $type);
  return $response;
});
Route::get('file/account_profiles/{filename}', function ($filename)
{
  $path = storage_path('/account_profiles/' . $filename);
  if (!File::exists($path)) {
      abort(404);
  }
  $file = File::get($path);
  $type = File::mimeType($path);
  $response = Response::make($file, 200);
  $response->header("Content-Type", $type);
  return $response;
});
Route::get('/cache', function () {
    $exitCode = Artisan::call('config:cache');
    return 'hey'.$exitCode;

    //
});
Route::get('/clear', function () {
    $exitCode = Artisan::call('config:cache');
    return 'hey'.$exitCode;

    //
});
Route::get('/migrate', function () {
    $exitCode = Artisan::call('migrate');
    return 'hey'.$exitCode;

    //
});


/*
  @Bot Routes
*/
Route::get("/bot/hook","IlinyaController@hook")->middleware("verify");
Route::post("/bot/hook","IlinyaController@hook");
Route::get("/bot/broadcast/{message}","IlinyaController@broadcast");
Route::get("/bot/paging/{recipientId}/{message}/{surveyMode}","IlinyaController@paging");
Route::get("/bot/reminder/{recipientId}/{message}/{surveyMode}","IlinyaController@reminder");
Route::get("/bot/image","IlinyaController@createImage");
Route::get("/bot/test/{size}","IlinyaController@test");