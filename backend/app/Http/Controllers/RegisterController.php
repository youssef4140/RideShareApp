<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;

class RegisterController extends Controller
{
    public function register(Request $request):JsonResponse
    {

        //validate request
        $request->validate([
            'name'=>['required','string','max:255'],
            'phone'=>['required','regex:/^\+?[0-9\s]+$/','unique:'.User::class],
            'password'=> ['required','confirmed',Rules\Password::defaults()]
        ]);

        // create verificationCode
        $verificationCode = rand(111111,999999);

        $user = User::create([
            'name'=>$request->name,
            'phone'=>$request->phone,
            'password'=>Hash::make($request->password),
            'VerificationCode' => $verificationCode
        ]);


        return response()->json([
            'user'=>$user,
            'message'=>'Please check your phone for a verification code'
        ]);
        
    }

}
