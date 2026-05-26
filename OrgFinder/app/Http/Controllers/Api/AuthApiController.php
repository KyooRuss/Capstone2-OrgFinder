<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
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

        $user = User::with('profile')->where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        if ($user->status === 'blocked') {
            return response()->json(['message' => 'Your account has been blocked.'], 403);
        }

        if (!$user->isMobileUser()) {
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
        $user = $request->user()->load('profile');

        return response()->json(['user' => $this->userResource($user)]);
    }

    public static function userResource(User $user): array
    {
        $profile = $user->profile;

        return [
            'id'                => $user->id,
            'role'              => $user->role,
            'first_name'        => $user->first_name,
            'last_name'         => $user->last_name,
            'email'             => $user->email,
            'student_number'    => $user->student_number,
            'department'        => $profile?->department,
            'year_level'        => $profile?->year_level,
            'program'           => $profile?->program,
            'interests'         => $profile?->interest ?? [],
            'skills'            => $profile?->skill_to_improve ?? [],
            'activities'        => $profile?->preferred_activity ?? [],
            'profile_completed' => $profile?->profile_completed ?? false,
            'profile_photo'     => $profile?->profile_photo
                ? url('storage/' . $profile->profile_photo)
                : null,
        ];
    }
}
