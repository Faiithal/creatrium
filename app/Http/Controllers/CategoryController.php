<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index(){
        return $this->Ok(Category::all(), 'Retrieved all categories');    
    }
}
