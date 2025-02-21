<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\User;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    /**
     * Retrieves the comments of all projects
     * @param \App\Models\Project $project
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function index(){
        return $this->Ok(Project::with("user_comments")->get());
        //Why does it still not work if I use pivot?? My Current Theory is that the whole table is a collection even if there is a single row that is why it 
        //is still considered as a single row
    }
    
    /**
     * Retrieves the comments of a specific project
     * @param \App\Models\Project $project
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function show(Project $project){
        return $this->Ok($project->user_comments, "Retrieved Comments Successfully!");
    }

    /**
     * Adds the user's like to the project
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Project $project
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function store(Request $request, Project $project){

        $validator = validator($request->all(), [
            'content' => "required|string"
        ]);

        if($validator->fails()){
            return $this->BadRequest($validator, 'Try to add a comment first, comment field is empty');
        }
        
            $validated = $validator->validated();

            $user = User::find($request->user()->id);
            $user->commented_projects()->attach(Project::find($project), $validated);
    
            return $this->Created(($project->user_comments), 'Comment Added Successfully');
    }

    /**
     * Removes the user's like to the project
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Project $project
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function destroy(Request $request, Project $project){
        $user = User::find($request->user()->id);
        $user->favorited_projects()->detach($project);

        return $this->Ok($project->user_favorites, 'Favorite Removed Successfully');
    }
}
