<?php

namespace App\Http\Controllers;

use App\Models\Project;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Log;

class ProjectController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return $this->Ok(Project::all(), "Projects have been retrieved");
    }

    /**
     * Store a newly created resource in storage.
     * @param \Illuminate\Http\Request $request
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function store(Request $request, Project $project)
    {

        $validator = validator($request->all(), [
            "name" => "required|string|max:255",
            "file" => "required|file",
            "description" => "sometimes|string",
            "file_icon" => "sometimes|image|mimes:jpg,bmp,png",
            "visibility" => "required|boolean",
            "thumbnails" => "sometimes|array",
            "thumbnails.*" => "image"
        ]);

        if ($validator->fails()) {
            return $this->BadRequest($validator);
        }

        $validated = $validator->validated();
        $validated['user_id'] = $request->user()->id;

        $validated['file_extension'] = $request->file('file')->extension();

        $validated['thumbnails'] = json_encode($validated['thumbnails']);
        // Use Case: when there is no other instance of the project, this also serves as a one step process of a 2 step process
        $project = Project::create($validated);
        // Project $project = $project->create($validated); - two step as you assign an object first to the variable then create an instance 


        $baseUserProjectPath = $project->user_id . '-' . $project->id;

        $projectPath = $request->file('file')->storeas('uploads', $baseUserProjectPath . "." . $project->file_extension, 'public');
        $validated['file'] = $projectPath;

        if (isset($validated['file_icon'])) {
            $IconPath = $request->file('file_icon')->storeAs('uploads', $baseUserProjectPath . "-icon" . '.' . $request->file('file_icon')->extension(), 'public');
            $validated['file_icon'] = $IconPath;
        }

        if(isset($validated['thumbnails'])){
            $thumbnail_count = 0;
            foreach ($request->file('thumbnails') as $thumbnail) {
                $ThumbnailPath = $thumbnail->storeAs('uploads', $baseUserProjectPath . "-thumbnail-" . $thumbnail_count . '.' . $request->file('file_icon')->extension(), 'public');
                $thumbnails[] = $ThumbnailPath;
                $thumbnail_count++;
            }
            
            $validated["thumbnails"] = json_encode($thumbnails);
        }

        $project->update($validated);

        // $url = Storage::url("C:\Users\John\Downloads\Postman_storage/{$validated['file']}");
        return $this->Created($project, "Project has been updated");
    }

    /**
     * Display the specified project based on its ID. 
     * @param \App\Models\Project $project
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function show(Project $project)
    {
        return $this->Ok($project, "Project has been retrieved!");
    }

    /**
     * Searches a project using keywords
     * @param \Illuminate\Http\Request $request
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function search(Request $request)
    {
        $validator = validator(
            $request->all(),
            ['search_input' => 'required']
        );

        if ($validator->fails()) {
            return $this->BadRequest($validator);
        }
        $validated = $validator->validated();
        $search = explode(' ', $validated['search_input']);

        $project = DB::table('projects');
        foreach ($search as $keyword) {
            $project->orWhere('name', 'like', '%' . $keyword . '%');
        }
        ;

        return $this->Ok($project->get());
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Project $project)
    {
        //Checks if the user accessing the project is the creator

        if ($request->user()->id != $project->user_id) {
            return $this->Unauthorized();
        }

        $validator = validator($request->all(), [
            "name" => "sometimes|string|max:255",
            "file" => "sometimes|file",
            "description" => "sometimes|string",
            "file_icon" => "sometimes|image|mimes:jpg,bmp,png",
            "visibility" => "sometimes|boolean",
            "thumbnails" => "sometimes|array",
            "thumbnails.*" => "image"
        ]);

        if ($validator->fails()) {
            return $this->BadRequest($validator);
        }

        $validated = $validator->validated();
        $baseUserProjectPath = $project->user_id . '-' . $project->id;

        if(isset($validated['file'])){            
            $validated['file_extension'] = $request->file('file')->extension();
            $projectPath = $request->file('file')->storeas('uploads', $baseUserProjectPath . "." . $project->file_extension, 'public');
        $validated['file'] = $projectPath;

        }


        if (isset($validated['file_icon'])) {
            Storage::disk('public')->delete($project->file);
            $IconPath = $request->file('file_icon')->storeAs('uploads', $baseUserProjectPath . "-icon" . '.' . $request->file('file_icon')->extension(), 'public');
            $validated['file_icon'] = $IconPath;
        }

        if(isset($validated['thumbnails'])){
            $thumbnail_count = 0;
            $thumbss = json_decode($project->thumbnails, true);
            Storage::disk('public')->delete($thumbss);
            foreach ($request->file('thumbnails') as $thumbnail) {
                $ThumbnailPath = $thumbnail->storeAs('uploads', $baseUserProjectPath . "-thumbnail-" . $thumbnail_count . '.' . $thumbnail->extension(), 'public');
                $thumbnails[] = $ThumbnailPath;
                $thumbnail_count++;
            }
            
            $validated['thumbnails'] = json_encode($thumbnails);
        }

        // Use Case: when there is no other instance of the project, this also serves as a one step process of a 2 step process
        $project->update($validated);
        // $project = $project->create($validated); - two step
        // Project $project = $project->create($validated); - two step as it you assign an object first to the variable then create an instance 
        return $this->Ok($project, "Project has been updated");
    }

    /**
     * Remove the specified resource from storage.
     * @param \App\Models\Project $project
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function destroy(Request $request, Project $project)
    {
        if ($request->user()->id != $project->user_id) {
            return $this->Unauthorized();
        }
        $project->delete();
        Storage::disk('public')->delete($project->file);
        if(isset($project->file_icon)){
            Storage::disk('public')->delete($project->file_icon);
        }
        if(isset($project->thumbnails)){
            foreach(json_decode($project->thumbnails) as $thumbnail){
                Storage::disk('public')->delete($thumbnail);
            }
        }
        return $this->Ok($project, "The Project has successfully been deleted!");
    }
}
