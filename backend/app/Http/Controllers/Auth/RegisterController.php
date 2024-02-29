<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Notifications\UserVerification;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;

class RegisterController extends Controller
{
    public function submit(Request $request):JsonResponse
    {

        try{
        //validate request
        $request->validate([
            'name'=>['required','string','max:255'],
            'phone'=>['required','regex:/^\+?[0-9\s]+$/','unique:'.User::class],
            'password'=> ['required','confirmed',Rules\Password::defaults()]
        ]);

        //create user
        $user = User::create([
            'name'=>$request->name,
            'phone'=>$request->phone,
            'password'=>Hash::make($request->password),
            'isVerified'=>false,
        ]);

        // send verification code to user
        $user->notify(new UserVerification());
        // return 
        return response()->json([
            'user'=>$user,
            "token" => $user->createToken($user->id)->plainTextToken,
            'message'=>'Please check your phone for a verification code'
        ]);
    } catch (\Exception $e) {
        // Handle unexpected errors
        return response()->json(['error' => $e->getMessage()], 500);
    }
        
    }

    public function verify(Request $request):JsonResponse
    {
        try {
            //validate
        $request->validate([
            'phone'=>['required','regex:/^\+?[0-9\s]+$/'],
            'VerificationCode'=>'required'
        ]);

        //find user
        $user = User::where('phone',$request->phone)
                    ->where('VerificationCode',$request->VerificationCode)
                    ->first();

        // handling if no user exists
        if(!$user) return response()->json(["message"=>'Incorrect Verification Code!'],404);

        // updating user 
        $user->update([
            'isVerified'=>true,
            'VerificationCode'=>null
        ]);

        //return user and token
        return response()->json([
            "user" => $user,
        ], 200);
    } catch (\Exception $e) {
        // Handle unexpected errors
        return response()->json(['error' => $e->getMessage()], 500);
    }
    }

}
