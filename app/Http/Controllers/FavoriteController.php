<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\User;
use Illuminate\Http\Request;

class FavoriteController extends Controller
{
    public function index()
    {
        return $this->Ok(User::with(('favorited_projects'))->get());
    }

    public function show(User $user)
    {
        return $this->Ok($user->favorited_projects);
    }

    // public function checkUserFavorites(Request $request)
    // {
    //     if (!isset($request->user()->id)) {
    //         return $this->Unauthorized();
    //     }

    //     return $this->Ok(User::find($request->user()->id)->favorited_projects);
    // }

    public function store(Request $request, Project $project)
    {
        if (!isset($request->user()->id)) {
            return $this->Unauthorized();
        }
        $user_id = $request->user()->id;


        if ($project->user_favorites()->find($user_id) != null) {
            return $this->BadRequest(null, 'User has already favorited the project');
        } else {
            $user = User::find($request->user()->id);
            $user->favorited_projects()->attach($project);

            return $this->Created($project->user_favorites, 'Favorite Added Successfully');
        }
    }

    public function destroy(Request $request, Project $project)
    {
        if (!isset($request->user()->id)) {
            return $this->Unauthorized();
        }

        $user = User::find($request->user()->id);
        $user->favorited_projects()->detach($project);

        return $this->Ok($project, "The Book has successfully been deleted!");
    }
}
