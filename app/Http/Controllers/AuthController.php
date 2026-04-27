<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{

    public function login(Request $request){
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string|min:4|max:20'
        ]);

        if(!Auth::attempt($request->only('email','password'))){
            return response()->json([
                'status' => 'failed',
                'message' => 'invalid credentials, please try again later'
            ]);
        }
        $user = auth()->user();
        $token = $user->createToken('login-token')->plainTextToken;

        return response()->json([
            'status' => 'success',
            'message' => 'login successfully!',
            'token' => $token
        ], 200);
    }

    public function resetPassword(Request $request){
        $request->validate([
            'email' => 'required|email|exists:users,email',
             'password' => 'required|string|min:4|max:20' ],

             [ 'email.exists' => 'email provided is not registered']);

      
        $user = DB::table('users')->where('email', $request->email)->first();

        if(Hash::check($request->password, $user->password)){
            return response()->json(['message' => 'nothing changed, password entered are the same as previous one']);
        }

         DB::table('users')->where('email', $request->email)->update([
            'password' => Hash::make($request->password)
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'password changed successfully!'
        ], 200);
    }

    public function logout(Request $request){
        $user = auth()->user();

        $user->currentAccessToken()->delete();


        return response()->json([
            'status' => 'success',
            'message' => 'logout successfully!'
        ], 200);
    }
}
