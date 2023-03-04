<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ApiController;
use App\Models\Section;
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

function findAngles(array $map): array{
  $angles= [];
  $total = array_sum($map);
  foreach($map as  $key => $value){
    $angle = round(($value / $total) * 360, 2);
    $angles[$key] = $angle;
  }
  return $angles;
}

Route::post('/angles', function (Request $request){
  $json = $request->getContent();
  $data = json_decode($json, true);
  $items = [];
  foreach($data as $key => $value){
    $items[$key] = $value;
  }
  foreach($items as $key => $value){
    $section = Section::create([
      'name'=> $key, 'value' => $value
    ]);
  }
  $response = findAngles($items);
  return response()->json($response, 200);
});