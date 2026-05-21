<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\UserProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProfileApiController extends Controller
{
    public function complete(Request $request)
    {
        $data = $request->validate([
            'first_name'  => 'required|string|max:255',
            'last_name'   => 'required|string|max:255',
            'year_level'  => 'required|integer|min:1|max:5',
            'program'     => 'required|string|max:100',
            'interests'   => 'required|array|min:1|max:3',
            'skills'      => 'required|array|min:1|max:3',
            'activities'  => 'required|array|min:1|max:3',
        ]);

        $user = $request->user();
        $user->update([
            'year_level'        => $data['year_level'],
            'program'           => $data['program'],
            'interests'         => $data['interests'],
            'skills'            => $data['skills'],
            'activities'        => $data['activities'],
            'profile_completed' => true,
        ]);

        return response()->json(['user' => $this->userResource($user->fresh())]);
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'first_name'  => 'sometimes|string|max:255',
            'last_name'   => 'sometimes|string|max:255',
            'year_level'  => 'sometimes|integer|min:1|max:5',
            'program'     => 'sometimes|string|max:100',
            'interests'   => 'sometimes|array|min:1|max:3',
            'skills'      => 'sometimes|array|min:1|max:3',
            'activities'  => 'sometimes|array|min:1|max:3',
        ]);

        $request->user()->update($data);

        return response()->json(['user' => $this->userResource($request->user()->fresh())]);
    }

    public function uploadPhoto(Request $request)
    {
        $request->validate(['photo' => 'required|image|max:2048']);

        $user = $request->user();

        if ($user->profile_photo) {
            Storage::disk('public')->delete($user->profile_photo);
        }

        $path = $request->file('photo')->store('profile-photos', 'public');
        $user->update(['profile_photo' => $path]);

        return response()->json([
            'profile_photo' => asset('storage/' . $path),
        ]);
    }

    private function userResource($user): array
    {
        $profile = $user->profile;
        return [
            'id'                => $user->id,
            'first_name'        => $user->first_name,
            'last_name'         => $user->last_name,
            'email'             => $user->email,
            'year_level'        => $profile->year_level,
            'program'           => $profile->program,
            'interests'         => $profile->interest ?? [],
            'skills'            => $profile->skill_to_improve ?? [],
            'activities'        => $profile->preferred_activity ?? [],
            'profile_completed' => $profile->profile_completed ?? false,
            'profile_photo'     => $profile->profile_photo
                ? asset('storage/' . $profile->profile_photo)
                : null,
        ];
    }
}
