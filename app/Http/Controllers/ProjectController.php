<?php

namespace App\Http\Controllers;

use App\Models\Project;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Log;

class ProjectController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return $this->Ok(Project::with('categories')->get(), "Projects have been retrieved");
    }

    /**
     * Store a newly created resource in storage.
     * @param \Illuminate\Http\Request $request
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {

        if (!isset($request->user()->id)) {
            return $this->Unauthorized();
        }

        $validator = validator($request->all(), [
            "name" => "required|string|max:255",
            // This code may add an additional unnecessary source if you provide 2
            "type" => ["required", Rule::in(['pdf', 'web', 'img'])],
            "file" => "required_if:type,pdf|file|mimes:pdf",
            "web_link" => "required_if:type,web|url",
            "description" => "sometimes|string",
            "file_icon" => "sometimes|image|mimes:jpg,bmp,png",
            "authors" => "required|array",
            "authors.*" => "string",
            "visibility" => "required|boolean",
            "thumbnails" => "sometimes|array|max:5",
            "thumbnails.*" => "image",
            "categories" => "sometimes|array",
            "categories.*" => "integer|exists:categories,id"
        ]);

        if ($validator->fails()) {
            return $this->BadRequest($validator);
        }

        $validated = $validator->safe()->except('categories', 'web_link', 'type');
        $web_link = $request->input('web_link');
        $type = $request->input('type');

        $validated['user_id'] = $request->user()->id;
        $validated['thumbnails'] = json_encode($request->file('thumbnails'));
        $validated['authors'] = json_encode($validated['authors']);

        // Temporary Solution HAHAHAHAH
        $validated['file'] = '';
        $validated['file_extension'] = '';

        // Creates the project which will then be used to grab its id for the location of the project
        $project = Project::create($validated);

        $project_categories = $request->input('categories');
        if ($project_categories) {
            $project->categories()->sync($project_categories);
        }


        // Path names
        $baseUserProjectPath = $project->user_id . '-' . $project->id;

        switch ($type) {
            case 'pdf':
                $validated['file_extension'] = $request->file('file')->extension();
                // dd($request->file('file'));
                $projectPath = $request->file('file')->storeAs('uploads', $baseUserProjectPath . "." . $validated['file_extension'], 'public');
                $validated['file'] = $projectPath;
                break;
            case 'web':
                $validated['file_extension'] = '';
                $validated['file'] = $web_link;
                break;
            case 'img':
                // Do nothing as it's already blank at line 64
                break;
        }

        if (isset($validated['file_icon'])) {
            $IconPath = $request->file('file_icon')->storeAs('uploads', $baseUserProjectPath . "-icon" . '.' . $request->file('file_icon')->extension(), 'public');
            $validated['file_icon'] = $IconPath;
        }

        if (isset($validated['thumbnails'])) {
            $thumbnail_count = 0;
            foreach ($request->file('thumbnails') as $thumbnail) {
                $ThumbnailPath = $thumbnail->storeAs('uploads', $baseUserProjectPath . "-thumbnail-" . $thumbnail_count . '.' . $thumbnail->extension(), 'public');
                $thumbnails[] = $ThumbnailPath;
                $thumbnail_count++;
            }
            $validated["thumbnails"] = json_encode($thumbnails);
        }

        // Use Case: when there is no other instance of the project, this also serves as a one step process of a 2 step process
        // Project $project = $project->create($validated); - two step as you assign an object first to the variable then create an instance 

        // $url = Storage::url("C:\Users\John\Downloads\Postman_storage/{$validated['file']}");

        $project->update($validated);

        return $this->Created($project->with('categories')->find($project->id), "Project has been updated");
    }

    /**
     * Display the specified project based on its ID. 
     * @param \App\Models\Project $project
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function show($project)
    {
        // Find out why pivot does not work
        return $this->Ok(Project::with('categories')->find($project), "Project has been retrieved!");
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

        if ($request->user()->id != $project->user_id || !isset($request->user()->id)) {
            return $this->Unauthorized();
        }

        $validator = validator($request->all(), [
            "type" => ["required", Rule::in(['pdf', 'web', 'img'])],
            "name" => "sometimes|string|max:255",
            "file" => "sometimes|file|mimes:pdf",
            // Just check if file is null and check web_link
            "web_link" => "sometimes|url",
            "authors" => "sometimes|array",
            "authors.*" => "string",
            "description" => "sometimes|string",
            "file_icon" => "sometimes|image|mimes:jpg,bmp,png",
            "visibility" => "sometimes|boolean",
            "thumbnails" => "sometimes|array|max:5",
            "thumbnails.*" => "image",
            "categories" => "sometimes|array",
            "categories.*" => "integer|exists:categories,id",
        ]);

        if ($validator->fails()) {
            return $this->BadRequest(validator: $validator);
        }

        $validated = $validator->safe()->except('categories', 'web_link', 'type');
        $web_link = $request->input('web_link');
        $type = $request->input('type');

        $baseUserProjectPath = $project->user_id . '-' . $project->id;

        $project_categories = $request->input('categories');
        if ($project_categories) {
            $project->categories()->sync($project_categories);
        }

        $validated['authors'] = json_encode($validated['authors']);

        switch ($type) {
            case 'pdf':
                if ($request->hasFile('file')) {
                    if (!$project->file_extension)
                        $validated['file_extension'] = $request->file(key: 'file')->extension();

                    $file_extension = $project->file_extension ? $project->file_extension : $validated['file_extension'];

                    Storage::disk('public')->delete($project->file);
                    $projectPath = $request->file('file')->storeAs('uploads', $baseUserProjectPath . "." . $file_extension, 'public');
                    $validated['file'] = $projectPath;
                } 
                else if(!$project->file_extension){
                    return $this->BadRequest(null, 'A PDF file is required when changing project types!');
                }
                break;

            case 'web':
                
                if ($project->file_extension) {
                    if ($web_link) {
                        Storage::disk('public')->delete($project->file);

                        $validated['file_extension'] = '';
                        $validated['file'] = $web_link;
                    } else {
                        return $this->BadRequest(null, 'Website Link is required when changing project types!');
                    }
                } else {
                    unset($validated['file']);
                    if ($web_link)
                        $validated['file'] = $web_link;
                }
                break;

            case 'img':
                if ($project->file_extension) {
                    Storage::disk('public')->delete($project->file);
                    $validated['file_extension'] = '';
                    $validated['file'] = '';

                } else {
                    unset($validated['file']);
                    $validated['file'] = '';
                }
                break;

        }

        // dd($validated['file']);

        if (isset($validated['file_icon'])) {
            $IconPath = $request->file('file_icon')->storeAs('uploads', $baseUserProjectPath . "-icon" . '.' . $request->file('file_icon')->extension(), 'public');
            $validated['file_icon'] = $IconPath;
        }

        if (isset($validated['thumbnails'])) {
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
        if ($request->user()->id != $project->user_id || !isset($request->user()->id)) {
            return $this->Unauthorized();
        }
        $project->delete();
        Storage::disk('public')->delete($project->file);
        if (isset($project->file_icon)) {
            Storage::disk('public')->delete($project->file_icon);
        }
        if (isset($project->thumbnails)) {
            foreach (json_decode($project->thumbnails) as $thumbnail) {
                Storage::disk('public')->delete($thumbnail);
            }
        }
        return $this->Ok($project, "The Project has successfully been deleted!");
    }
}
