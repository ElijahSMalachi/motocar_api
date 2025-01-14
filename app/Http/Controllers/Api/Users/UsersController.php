<?php

namespace App\Http\Controllers\Api\Users;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\UsersResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class UsersController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $per_page = 50;

        if (isset($_GET['per_page']) && $_GET['per_page'] != '') {
            $per_page = $_GET['per_page'];
        }
        $users = User::paginate($per_page);
        return UsersResource::collection($users);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {

            $request->validate([
                'first_name' => 'required|string',
                'last_name' => 'required|string',
                'gender' => 'required|string|in:Male,Female',
                'dob' => 'required|date',
                'phone_number' => 'required|string|unique:users',
                'email' => 'sometimes|email',
                'password' => 'required|confirmed',
            ]);

            $user = new User;
            $user->first_name = $request->first_name;
            $user->last_name = $request->last_name;
            $user->gender = $request->gender;
            $user->dob = $request->dob;
            $user->phone_number = $request->phone_number;
            $user->email = $request->email;
            $user->password = Hash::make($request->password);
            $user->save();
            $token = $user->createToken($request->first_name);

            return response()->json(['msg' => 'user created successfully!', 'user' => $user, 'token' => $token->plainTextToken]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'msg' => 'Validation error',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Throwable $e) {
            // Log the error for debugging purposes
            Log::error('User registration failed', ['error' => $e->getMessage()]);

            return response()->json([
                'msg' => 'An error occurred while creating the user.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        try {

            $request->validate([
                'first_name' => 'required|string',
                'last_name' => 'required|string',
                'gender' => 'required|string|in:Male,Female',
                'dob' => 'required|date',
                'phone_number' => ['required','string',
                    Rule::unique('users')->where(function ($query) use ($id) {
                        return $query->where('id', '!=', $id); 
                    })
                ],
                'email' => 'sometimes|email',
            ]);

            $user = User::find($id);
            $user->first_name = $request->first_name;
            $user->last_name = $request->last_name;
            $user->gender = $request->gender;
            $user->dob = $request->dob;
            $user->phone_number = $request->phone_number;
            $user->email = $request->email;
            $user->save();
            $token = $user->createToken($request->first_name);

            return response()->json(['msg' => 'user created successfully!', 'user' => $user, 'token' => $token->plainTextToken]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'msg' => 'Validation error',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Throwable $e) {
            // Log the error for debugging purposes
            Log::error('User registration failed', ['error' => $e->getMessage()]);

            return response()->json([
                'msg' => 'An error occurred while creating the user.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}