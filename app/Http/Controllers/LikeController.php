<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Http\Request;

class LikeController extends Controller
{
    /**
     * Retrieves the likes of all projects
     * @param \App\Models\Project $project
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function index(){
        return $this->Ok(Project::with("user_likes")->get());
        //Why does it still not work if I use pivot?? My Current Theory is that the whole table is a collection even if there is a single row that is why it 
        //is still considered as a single row
    }
    
    /**
     * Captures all user likes in a project.
     * @param \App\Models\Project $project
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function show(Project $project){
        return $this->Ok($project->user_likes, "Retrieved Likes Successfully!");
    }

    public function checkLike(Request $request, Project $project){
        if(!isset($request->user()->id)){
            return $this->Unauthorized();
        }

        return $this->Ok($request->user()->liked_projects()->find($project));
    }

    /**
     * Adds the user's like to the project
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Project $project
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function store(Request $request, Project $project){
        if(!isset($request->user()->id)){
            return $this->Unauthorized();
        }

        $user_id = $request->user()->id;
        

        if($project->user_likes()->find($user_id) != null){
            return $this->BadRequest(null, 'User has already Liked the project');
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
    public function destroy(Request $request, Project $project){
        if(!isset($request->user()->id)){
            return $this->Unauthorized();
        }
        $user = User::find($request->user()->id);
        $user->liked_projects()->detach($project);

        return $this->Ok($project->user_likes, 'Like Removed Successfully');
    }
}
