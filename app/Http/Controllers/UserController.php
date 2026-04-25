<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Auth;

class UserController extends Controller
{
     public function createUser(Request $request){
            $request->validate([
                'name' => 'required|string',
                'email' => 'required|email|unique:users,email',
                'password' => 'required|string|min:4|max:20',
                'role' => 'sometimes|string'
            ]);
            
            $id = DB::table('users')->insertGetId([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role' => $request->role ?? 'customer',
                'created_at' => now(),
                'updated_at' => now(),
                'email_verified_at' => now(),
               
            ]);

           $user = User::find($id);
           $token = $user->createToken('remember-token')->plainTextToken;

           DB::table('users')->where('id', $id)->update([
                'remember_token' =>  $token
           ]);
         $userWithToken = DB::table('users')->where('id', $id)->first();

            return response()->json([
                'status' => 'success',
                'message' => 'user created successfully!',
                'token' => $token,
                'user' => $userWithToken
            ], 201);
    }
    public function updateUserDetails(Request $request, $id){
            $user = DB::table('users')->where('id', $id)->first();

            if(!$user){
                return response()->json(['message' => 'user not found']);
            }
           $fields = $request->validate([
                'name' => 'sometimes|string',
                'email' => 'sometimes|email',
                'password' => 'sometimes|string|min:4|max:24',
                'role' => 'sometimes|string'
            ]);
            DB::table('users')->where('id', $id)->update([
                'name' => $fields['name'],
                'email' => $fields['email'],
                'password' => Hash::make($fields['password']),
                'role' => $fields['role'],
                'updated_at' => now()
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'user updated successfully!',
                'updated field(s)' => array_keys($fields)
            ], 200);
    }

    public function deleteUser($id){
        $user = DB::table('users')->where('id', $id)->first();
        if(!$user){
            return response()->json(['message' => 'user not found']);
        }
        $deleted = DB::table('users')->where('id', $id)->delete();

        return response()->json([
            'status' => 'success',
            'message' => $deleted . ' user(s) deleted successfully!'
        ], 200);
    }

    public function usersList(){
        $users = DB::table('users')->get();

        if(!$users){
            return response()->json(['message' => 'no user data available']);
        }

        return response()->json($users);
    }
}
