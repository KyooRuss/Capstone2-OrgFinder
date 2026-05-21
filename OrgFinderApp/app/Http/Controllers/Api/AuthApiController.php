<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthApiController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        if ($user->status === 'blocked') {
            return response()->json(['message' => 'Your account has been blocked.'], 403);
        }

        if (!$user->isStudent()) {
            return response()->json(['message' => 'Access denied.'], 403);
        }

        $token = $user->createToken('mobile')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user'  => $this->userResource($user),
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Logged out.']);
    }

    public function user(Request $request)
    {
        return response()->json(['user' => $this->userResource($request->user())]);
    }

    private function userResource(User $user): array
    {
        return [
            'id'                => $user->id,
            'name'              => $user->name,
            'email'             => $user->email,
            'student_number'    => $user->student_number,
            'year_level'        => $user->year_level,
            'program'           => $user->program,
            'interests'         => $user->interests ?? [],
            'skills'            => $user->skills ?? [],
            'activities'        => $user->activities ?? [],
            'profile_completed' => $user->profile_completed,
            'profile_photo'     => $user->profile_photo
                ? asset('storage/' . $user->profile_photo)
                : null,
        ];
    }
}
