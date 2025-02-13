<?php

namespace App\Http\Controllers;

use App\Models\Profile;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProfilesController extends Controller
{
    public function index() {
        return $this->Ok(Profile::all(), 'Retrieved all profiles successfully!');
    }

    public function show(User $user){
        return $this->Ok(['user' => $user, 'profile' => $user->profile], 'Retrieve profile successfully!');
    }

    public function update(Request $request){

        if(!isset($request->user()->id)){
            return $this->Unauthorized();
        }

        $validator = validator($request->all(), [
            'name' => 'sometimes|string|min:
            4|alpha_dash|unique:users|max:255',
            'password' => 'sometimes|string|confirmed|min: 8|max: 255',
            'first_name' => 'sometimes|string|max: 64',
            'middle_name' => 'sometimes|string|max: 64',
            'last_name' => 'sometimes|string|max: 64',
            'affix' => 'sometimes|string|max: 10',
            'birth_date' => 'sometimes|date|before:tomorrow|after:1.1.1900',
            'section' => 'sometimes|string|max: 64',
            'course' => 'sometimes|integer',
            'campus' => ['sometimes', Rule::in(['Pasig', 'Pasay', 'Jala-Jala'])],
            'academic_year' => 'sometimes|integer|digits: 4|min: 1900|max:' . (date('Y') + 1),
            'image' => 'sometimes|image',
            'gender' => 'sometimes|integer|min:1|max:3'
        ]);

        if($validator->fails()){
            return $this->BadRequest($validator->errors());
        }

        $user = $request->user(); 
        // Based on my assumptions, this does in fact work as it grabs the user model using the request method
        // and all functions pertaining to the user model still works in which you can use relationships


        $user->profile()->update($validator->safe()->except('course'));
        $user->course()->sync($validator->safe()->only('course'));

        return $this->Ok(User::with('profile', 'course')->find($user->id));
    }

    public function getProjects(User $user){
        return $this->Ok($user->projects);
    }
}
