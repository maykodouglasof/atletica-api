<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

use App\Models\Area;

class AreaController extends Controller
{
    public function getAll(){
        $array = ['error' => ''];

        $areas = Area::all();
        
        $array['list'] = $areas;

        return $array;
    }
}
