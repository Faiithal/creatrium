<?php

namespace App\Http\Controllers;

use App\Models\Course;
use Illuminate\Http\Request;

class CourseController extends Controller
{
    public function index(){
        return $this->Ok(Course::all(), 'Retrieved all courses');    
    }
}
