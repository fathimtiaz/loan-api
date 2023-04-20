<?php

namespace App\Http\Controllers;

use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function authenticate(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);
        
        try {
            $user = User::where('email', $request->email)->first();
        } catch (\Exception $e) {
            report($e);
            return response()->json([
                'status' => 'error',
                'message' => 'not found'
            ], 404);
        }
     
        if (! $user || ! Hash::check($request->password, $user->password)) {
            return response()->json([
                'status' => 'error',
                'message' => 'invalid credentials'
            ], 400);
        }
        
        $permissions = $user->roles->first()->permissions;
        $permission_names = [];

        foreach($permissions as $permission) {
            $permisison_names[] = $permission->name;
        }

        $token = $user->createToken('API Token', $permisison_names)->plainTextToken;


        return response()->json([
            'status' => 'success',
            'token' => $token
        ], 200);
    }
}
