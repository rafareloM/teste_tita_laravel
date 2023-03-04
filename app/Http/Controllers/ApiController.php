<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ApiController extends Controller
{
    
  public function getAngles(Request $request){
        $data = $request->json_decode();
        echo $data;
        return response()->json(['message' => 'passou', 201]);
    }
}
