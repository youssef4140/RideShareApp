<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class LoginController extends Controller
{
    public function submit(Request $request)
    {
        $request->validate([
            'phone' => 'required',
            'password' => 'required',
        ]);


        $credentials = $request->only('phone', 'password');

        if (Auth::attempt($credentials)) {
            $token = $request->user()->createToken($request->user()->id);
            return response()->json(["user"=>$request->user(),
                                    "token"=>$token->plainTextToken,
        ],200);
        };

    }
}
