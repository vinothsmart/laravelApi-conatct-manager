<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    protected $user;

    public function __construct()
    {
        $this->user = new User;
    }

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(),
            [
                "firstname" => "required|string",
                "lastname" => "required|string",
                "email" => "required|string",
                "password" => "required|string|min:6",
            ]);

        if ($validator->fails()) {
            return response()->json([
                "success" => false,
                "message" => $validator->messages()->toArray(),
            ], 400);
        }

        $checkEmail = $this->user->where("email", $request->email)->count();
        if ($checkEmail > 0) {
            return response()->json([
                "success" => false,
                "message" => "this email already exists please try another email",
            ], 422);
        }

        $registerComplete = $this->user::create([
            "firstname" => $request->firstname,
            "lastname" => $request->lastname,
            "email" => $request->email,
            "password" => Hash::make($request->password),
        ]);

        if ($registerComplete) {
            $this->login($request);
        }
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->only("email", "password"),
            [
                "email" => "required|string",
                "password" => "required|string|min:6",
            ]);

        if ($validator->fails()) {
            return response()->json([
                "success" => false,
                "message" => $validator->messages()->toArray(),
            ], 400);
        }

        $jwtToken = null;

        $input = $request->only("email", "password");

        if (!$jwtToken = auth("users")->attempt($input)) {
            return response()->json([
                "success" => false,
                "message" => "invalid email or password",
            ], 400);
        }

        return response()->json([
            "success" => true,
            "token" => $jwtToken,
        ], 400);
    }
}
