<?php

namespace App\Http\Controllers;

abstract class Controller
{
    /**
     * Returns a BadRequest Status Code and Appropriate errors.
     * @param mixed $validator
     * @return mixed|\Illuminate\Http\JsonResponse
     */

    public function BadRequest($validator, $message = "Request Didn't pass the validation")
    {
        if($validator != null){
        return response()->json([
            "ok" => false,
            "data" => $validator->errors(),
            "message" => $message
        ], 400);
    }
        
    else{
        return response()->json([
                "ok" => false,
                "message" => $message
            ], 400);
    }
    }

    /**
     * Returns a Created Status Code and appropriate message.
     * @param mixed $data
     * @param mixed $message
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function Created($data, $message = "Account has been created")
    {
        return response()->json([
            "ok" => true,
            "data" => $data,
            "message" => $message
        ], 201);
    }

    /**
     * Returns Unauthorized Status code and appropriate message
     * @param mixed $message
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function Unauthorized($message = "Unauthorized Access")
    {
        return response()->json([
            "ok" => false,
            "message" => $message
        ], 401);
    }
    /**
     * Returns Ok Status Code and appropriate message
     * @param mixed $user
     * @param mixed $token
     * @param mixed $message
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function Ok($data = [], $message = "Logged in Success!")
    {
        return response()->json([
            "ok" => true,
            "data" => $data,
            "message" => $message
        ], 200);
    }

}
