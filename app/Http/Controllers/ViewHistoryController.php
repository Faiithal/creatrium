<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\User;
use Illuminate\Http\Request;

class ViewHistoryController extends Controller
{

    public function index(Request $request)
    {
        if (!isset($request->user)) {
            return $this->Unauthorized();
        }
        // I think this will not work but lets see
        // update: that did not work HAHAHAHA
        // return $this->Ok($request->user()->viewed_projects); 
        $user = User::find($request->user()->id);
        return $this->Ok($user->viewed_projects);
    }

    public function store(Request $request, Project $project)
    {
        /* The reason why we do this is that we want to get the account id of the current
            user logged in instead of using grabbing the user 
        */
        if (!isset($request->user)) {
            return $this->Unauthorized();
        }
        $user = User::find($request->user()->id);

        if ($user->viewed_projects()->find($project) == null) {
            $user->viewed_projects()->attach($project);
        }

        return $this->Created($user->viewed_projects);
    }

}
