<?php

namespace App\Http\Controllers\API;

use Auth;
use App\User;
use Exception;
use Carbon\Carbon;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    /**
     * Login user and create token
     *
     * @param  [string] email
     * @param  [string] password
     * @return [json]
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string'
        ]);

        $credentials = $request->only(['email', 'password']);

        if(!Auth::attempt($credentials)) {
            return response()->json([
                'meta' => [
                    'status' => 0,
                    'code' => 403,
                    'message' => 'Email or password is incorrect.'
                ]
            ], 403);
        }
            
        $user = $request->user();

        $tokenResult = $user->createToken('Personal Access Token');
        $tokenResult->token->save();

        return response()->json([
            'meta' => [
                'status' => 1,
                'code' => 200,
                'message' => 'User logged in successfully'
            ],
            'data' => [
                'user' => $user,
                'session' => [
                    'access_token' => $tokenResult->accessToken,
                    'token_type' => 'Bearer',
                    'expires_at' => Carbon::parse(
                        $tokenResult->token->expires_at
                    )->toDateTimeString()
                ]
            ]
        ]);
    }

    /**
     * Create user
     *
     * @param  [string] name
     * @param  [string] email
     * @param  [string] phone_number
     * @param  [string] password
     * @param  [string] gender
     * @param  [date] date_of_birth
     * @return [json]
     */
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'email' => 'required|string|email|unique:users',
            'phone_number' => 'required|regex:/[0-9]{10}/|unique:users',
            'password' => 'required|string',
            'gender' => 'in:male,female,other',
            
        ]);

        try {
            $user = User::create($request->only(['name', 'email', 'phone_number', 'password', 'gender', 'date_of_birth']));
            throw_if(!$user, new Exception('Unknown error occoured.'));

            return response()->json([
                'meta' => [
                    'status' => 1,
                    'code' => 201,
                    'message' => 'Successfully created user!']
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'meta' => [
                    'status' => 0,
                    'code' => 409,
                    'message' => $e->getMessage()
                ]
            ], 409);
        }
    }

    /**
     * Logout user (Revoke the token)
     *
     * @return [json]
     */
    public function logout(Request $request)
    {
        $request->user()->token()->revoke();
        return response()->json([
            'meta' => [
                'status' => 1,
                'code' => 200,
                'message' => 'Successfully logged out'
            ]
        ], 200);
    }
}
