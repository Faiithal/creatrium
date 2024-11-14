<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Http\Request;

class LikeController extends Controller
{
    /**
     * Captures all user likes in a project.
     * @param \App\Models\Project $project
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function index(){
        return $this->Ok(Project::with("user_likes")->get());
        //Why does it still not work if I use pivot?? My Current Theory is that the whole table is a collection even if there is a single row that is why it 
        //is still considered as a single row
    }
    
    /**
     * Captures a specific user who liked the project.
     * @param \App\Models\Project $project
     * @param \App\Models\User $user
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function show(Project $project, User $user){
        return $this->Ok($project->user_likes, "Retrieved Likes Successfully!");
    }

    /**
     * Adds the user's like to the project
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Project $project
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function store(Request $request, Project $project){

        $user_id = $request->user()->id;
        

        if($project->user_likes()->find($user_id) != null){
            return response()->json([
                "ok" => false,
                "message" => "User Has Already Liked the Project"
            ], 400);
        }
        else{
            $user = User::find($request->user()->id);
            $user->liked_projects()->attach($project);
    
            return $this->Created($project->user_likes, 'Like Added Successfully');
        }
    }

    /**
     * Removes the user's like to the project
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Project $project
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function remove(Request $request, Project $project){
        $user = User::find($request->user()->id);
        $user->liked_projects()->detach($project);

        return $this->Ok($project->user_likes, 'Like Removed Successfully');
    }
}
