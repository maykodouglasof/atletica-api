<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Course;

class CourseController extends Controller
{
    public function getAll(){
        $array = ['error' => ''];

        $courses = Course::all();
        
        $array['list'] = $courses;

        return $array;
    }
}
