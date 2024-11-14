<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Auth\Events\Failed;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthController extends Controller
{

    // Register
    /**
     * Creates User and Profile Data
     * @param Request
     * @return JsonResponse
     */
    public function register(Request $request)
    {

        //validator is variable that will verify the contents if it follows the rules needed, it avoids the use of many if and else codes
        $validator = validator($request->all(), [
            'name' => 'required|string|min:4|alpha_dash|unique:users|max:255',
            'password' => 'required|string|confirmed|min: 8|max: 255',
            'first_name' => 'required|string|max: 64',
            'middle_name' => 'required|string|max: 64',
            'last_name' => 'required|string|max: 64',
            'birth_date' => 'required|date|before:tomorrow|after:1.1.1900',
            'section' => 'required|string|max: 64',
            'course' => 'required|integer',
            'campus' => 'required|integer',
            'academic_year' => 'required|integer|digits: 4|min: 1900|max:' . (date('Y') + 1),
            //possible question: try to find a way to make the whole academic year
            // 'image' => 'required|string',
            //possible problem: I want this to only act when you upload an pfp image
            // solution: gawing
            'gender' => 'required|integer|min:1|max:3'
        ]);

        if ($validator->fails()) {
            return $this->BadRequest($validator);
            //Question: why "$this"
        }

        $validator->validated();

        $user = User::create($validator->safe()->only('name', 'password'));


        /* 
        User::create([
            "name" => $validated["name"],
            "password" => $validated["password"]
        ])
        */

        $user->profile()->create($validator->safe()->except('name', 'password'));

        return $this->Created($user);
    }
    // Login

    /**
     * Attempts to authenticate username and password
     * @param \Illuminate\Http\Request $request
     * @return void
     */
    public function login(Request $request)
    {
        $validator = validator(request()->all(), [
            'name' => "required",
            'password' => 'required'
        ]);

        if($validator->fails()) {
            return $this->BadRequest($validator, "All fields are required!");
        }

        // Used to check if value is an email

        // filter_var("abc@gmail.com", FILTER_VALIDATE_EMAIL);

        $validated = $validator->validated();

        if (
            !auth()->attempt([
                "name" => $validated["name"],
                "password" => $validated["password"]
            ])
        ) {
            return $this->Unauthorized("Invalid Credentials");
        }
        //Other possible method:

        // auth()->attempt([$validator->safe()->only("name", "password")]);

        $user = auth()->user();
        $user->profile;

        $token = $user->createToken("api")->accessToken;

        return $this->Ok([
            "user" => $user, //Question: Why cant you also add another item for profile, like why is every info both profile and user is in user?
            "token" => $token
        ], "Logged in Success!"); 
    }

    public function checkToken(Request $request){
        $user = $request->user(); //Question: Does this mean we're just getting user data?
        $user->profile;

        return $this->Ok($user, "Valid Token");


    }
    // Check Token

}
