<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return $this->Ok(Project::all(), "Books have been retrieved");
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     * @param \Illuminate\Http\Request $request
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function store(Request $request, Project $project)
    {   
        

        $validator = validator($request->all(), [
            "file_link" => "required|string",
            "file_extension" => "required|string",
            "description" => "sometimes|string",
            "file_thumbnail" => "sometimes|string"
        ]);

        if($validator->fails()){
            return $this->BadRequest($validator);
        }
        
        $validated = $validator->validated();
        $validated['user_id'] = $request->user()->id;

        // Use Case: when there is no other instance of the project, this also serves as a one step process of a 2 step process
        $project = Project::create($validated);
        // $project = $project->create($validated); - two step
        // Project $project = $project->create($validated); - two step as it you assign an object first to the variable then create an instance 
        return $this->Created($project, "Project has been updated");
    }

    /**
     * Display the specified project based on its ID. 
     * @param \App\Models\Project $project
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function show(Project $project)
    {
        return $this->Ok($project, "Book has been retrieved!");
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Project $project)
    {
        $validator = validator($request->all(), [
            "file_link" => "required|string",
            "file_extension" => "required|string",
            "description" => "sometimes|string",
            "file_thumbnail" => "sometimes|string"
        ]);

        if($validator->fails()){
            return $this->BadRequest($validator);
        }

        // Use Case: when there is no other instance of the project, this also serves as a one step process of a 2 step process
        $project->update($validator->validated());
        // $project = $project->create($validated); - two step
        // Project $project = $project->create($validated); - two step as it you assign an object first to the variable then create an instance 
        return $this->Ok($project, "Project has been updated");
    }

    /**
     * Remove the specified resource from storage.
     * @param \App\Models\Project $project
     * @return mixed|\Illuminate\Http\JsonResponse
     */ 
    public function destroy()
    {
        $project = Project::delete();

        return $this->Ok($project, "The Book has successfully been deleted!");   
    }
}
