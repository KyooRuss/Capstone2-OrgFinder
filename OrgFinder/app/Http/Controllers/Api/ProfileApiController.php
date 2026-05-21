<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
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
            'first_name' => $data['first_name'],
            'last_name'  => $data['last_name'],
        ]);

        $user->profile()->updateOrCreate(
            ['user_id' => $user->id],
            [
                'year_level'         => $data['year_level'],
                'program'            => $data['program'],
                'interest'           => $data['interests'],
                'skill_to_improve'   => $data['skills'],
                'preferred_activity' => $data['activities'],
                'profile_completed'  => true,
            ]
        );

        $user->load('profile');

        return response()->json(['user' => $this->userResource($user)]);
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

        $user = $request->user();

        $userFields = array_filter([
            'first_name' => $data['first_name'] ?? null,
            'last_name'  => $data['last_name'] ?? null,
        ], fn($v) => $v !== null);

        if ($userFields) {
            $user->update($userFields);
        }

        $profileFields = [];
        if (isset($data['year_level']))  $profileFields['year_level']         = $data['year_level'];
        if (isset($data['program']))     $profileFields['program']             = $data['program'];
        if (isset($data['interests']))   $profileFields['interest']            = $data['interests'];
        if (isset($data['skills']))      $profileFields['skill_to_improve']    = $data['skills'];
        if (isset($data['activities']))  $profileFields['preferred_activity']  = $data['activities'];

        if ($profileFields) {
            $user->profile()->updateOrCreate(['user_id' => $user->id], $profileFields);
        }

        $user->load('profile');

        return response()->json(['user' => $this->userResource($user)]);
    }

    public function uploadPhoto(Request $request)
    {
        $request->validate(['photo' => 'required|image|max:2048']);

        $user    = $request->user();
        $profile = $user->profile;

        if ($profile?->profile_photo) {
            Storage::disk('public')->delete($profile->profile_photo);
        }

        $path = $request->file('photo')->store('profile-photos', 'public');

        $user->profile()->updateOrCreate(
            ['user_id' => $user->id],
            ['profile_photo' => $path]
        );

        return response()->json([
            'profile_photo' => url('storage/' . $path),
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
