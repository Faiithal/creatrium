<?php

use App\Http\Controllers\authcontroller;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\Controller;
use App\Http\Controllers\FavoriteController;
use App\Http\Controllers\LikeController;
use App\Http\Controllers\ProjectController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
    GET - Data Retrieval
    POST - Data Creation
    PATCH/PUT - Edit
    DELETE - Data Deletion
*/

// For AuthController
route::post("/register", [Authcontroller::class, 'register']);
route::post('/login', [Authcontroller::class, 'login']);
route::get('/checkToken', [Authcontroller::class, 'checkToken'])->middleware("auth:api");
route::post('/logout', [Authcontroller::class, 'logout'])->middleware("auth:api");

// For ProjectController
route::prefix("/projects")->group(
    function() {
        route::get("/", [ProjectController::class, 'index']);
        route::post("/", [ProjectController::class, 'store'])->middleware("auth:api");
        //{[id]} -> Specifies what id the function will perform it on
        route::get("/search", [ProjectController::class, 'search']);
        route::get("/{project}", [ProjectController::class, 'show']);
        route::delete("/{project}", [ProjectController::class, 'destroy'])->middleware("auth:api"); // Question: So as long you have that certain key, does it also 
        route::patch("/{project}", [ProjectController::class, 'update'])->middleware("auth:api"); 
        //Note: when using the update function, instead of specifying patch in postman, just make it a post request and
        //add the method "?_method=PATCH" because php doesnt have a concept of patch in their documentation
        //Also do note that the value that you will put there will serve as a variable that will be used for the function
        
    }
);


// LikeController

route::prefix("/likes")->group(
    function (){
        route::get("/", [LikeController::class, 'index']);
        route::get("/{project}", [LikeController::class, 'show']);
        route::post("/{project}", [LikeController::class, 'store'])->middleware('auth:api');
        route::delete("/{project}", action: [LikeController::class, 'destroy'])->middleware('auth:api');
    }
);

route::prefix("/comments")->group(
    function (){
        route::get("/", [CommentController::class, 'index']);
        route::get("/{project}", [CommentController::class, 'show']);
        route::post("/{project}", [CommentController::class, 'store'])->middleware('auth:api');
        route::delete("/{project}", action: [CommentController::class, 'destroy'])->middleware('auth:api');
    }
);

route::prefix("/favorites")->group(
    function (){
        route::get("/", [FavoriteController::class, 'index']);
        route::get("/{user}", [FavoriteController::class, 'show']);
        route::post("/{project}", [FavoriteController::class, 'store'])->middleware('auth:api');
        route::delete("/{project}", action: [FavoriteController::class, 'destroy'])->middleware('auth:api');
    }
);