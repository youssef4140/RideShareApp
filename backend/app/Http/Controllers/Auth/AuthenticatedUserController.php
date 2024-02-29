<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Notifications\ForgotPassword;
use Illuminate\Validation\Rules;
use Illuminate\Support\Facades\Hash;



class AuthenticatedUserController extends Controller
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

        return response()->json(["message"=>"incorrect credentials"],401);
    }

    public function forgotPassword(Request $request)
    {
        try {
        $request->validate([
            'phone'=>['required','regex:/^\+?[0-9\s]+$/'],
        ]);

        $user = User::where('phone',$request->phone)->first();

        if(!$user) return response()->json([
            "message"=>"This user is not registred"
        ],404);

        $loginCode = 111111;

        $user->update([
            'VerificationCode' => $loginCode
        ]);
        // $user->notify(new ForgotPassword());

        return response()->json([
            "message"=>'Please check your phone for password retrieval code'
        ],200);

    } catch (\Exception $e) {
        // Handle unexpected errors
        return response()->json(['error' => $e->getMessage()], 500);
    }

    }

    public function verifyPasswordChange(Request $request)
    {
        try {
            $request->validate([
                'phone'=>['required','regex:/^\+?[0-9\s]+$/'],
                'VerificationCode'=>'required'
            ]);

            $user = User::where('phone',$request->phone)
            ->where('VerificationCode',$request->VerificationCode)
            ->first();

        if(!$user) return response()->json(["message"=>'Incorrect Verification Code Or This number is not registered!'],404);

        $user->update([
            'isVerified'=>true,
            'VerificationCode'=>null
        ]);

        return response()->json([
            "message" => "Please change your password",
            "user" => $user,
            "token" => $user->createToken($user->id)->plainTextToken,
        ], 200);
        } catch (\Exception $e) {
            // Handle unexpected errors
            return response()->json(['error' => $e->getMessage()], 500);
        }
    
    }

    public function changePassword(Request $request)
    {
        try{

        $request->validate([
            'password'=> ['required','confirmed',Rules\Password::defaults()]
        ]);

        $user = $request->user();
        $user->update([
            'password'=>Hash::make($request->password)
        ]);

        return response()->json([
            "user" => $user,
        ], 200);
    } catch (\Exception $e) {
        // Handle unexpected errors
        return response()->json(['error' => $e->getMessage()], 500);
    }
    }
}
