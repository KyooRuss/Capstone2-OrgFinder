<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Organization;
use App\Models\OrganizationAccess;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class OrganizationController extends Controller
{
    public function index(Request $request)
    {
        $query = Organization::withCount(['accessUsers as members_count', 'events as events_count']);

        if ($request->filled('search')) {
            $query->where('org_name', 'like', '%' . $request->search . '%');
        }

        $organizations = $query->latest()->get();

        return view('super-admin.organizations.index', compact('organizations'));
    }

    public function create()
    {
        return view('super-admin.organizations.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'             => ['required', 'string', 'max:255'],
            'vision'           => ['nullable', 'string'],
            'mission'          => ['nullable', 'string'],
            'room_number'      => ['nullable', 'string', 'max:100'],
            'contact_telegram' => ['nullable', 'string', 'max:255'],
            'contact_facebook' => ['nullable', 'string', 'max:255'],
            'logo'             => ['nullable', 'image', 'max:2048'],
            'photos'           => ['nullable', 'array'],
            'photos.*'         => ['image', 'max:2048'],
            'reasons'          => ['nullable', 'array'],
            'reasons.*'        => ['nullable', 'string'],
            'testimonials'     => ['nullable', 'array'],
            'testimonials.*'   => ['nullable', 'string'],
        ]);

        $logoPath = null;
        if ($request->hasFile('logo')) {
            $logoPath = $request->file('logo')->store('organizations/logos', 'public');
        }

        $organization = Organization::create([
            'org_name'         => $validated['org_name'],
            'vision'           => $validated['vision'] ?? null,
            'mission'          => $validated['mission'] ?? null,
            'room_number'      => $validated['room_number'] ?? null,
            'contact_telegram' => $validated['contact_telegram'] ?? null,
            'contact_facebook' => $validated['contact_facebook'] ?? null,
            'logo'             => $logoPath,
        ]);

        if ($request->hasFile('photos')) {
            foreach ($request->file('photos') as $index => $photo) {
                $path = $photo->store('organizations/photos', 'public');
                $organization->photos()->create(['photo_path' => $path, 'order_index' => $index]);
            }
        }

        foreach (($validated['reasons'] ?? []) as $index => $reason) {
            if (!empty($reason)) {
                $organization->reasons()->create(['reason' => $reason, 'order_index' => $index]);
            }
        }

        foreach (($validated['testimonials'] ?? []) as $index => $testimonial) {
            if (!empty($testimonial)) {
                $organization->testimonials()->create(['testimonial' => $testimonial, 'order_index' => $index]);
            }
        }

        return redirect()->route('super-admin.organizations.index')
            ->with('success', 'Organization created successfully.');
    }

    public function edit(Organization $organization)
    {
        $organization->load(['reasons', 'testimonials', 'photos']);

        return view('super-admin.organizations.edit', compact('organization'));
    }

    public function update(Request $request, Organization $organization)
    {
        $validated = $request->validate([
            'org_name'         => ['required', 'string', 'max:255'],
            'vision'           => ['nullable', 'string'],
            'mission'          => ['nullable', 'string'],
            'room_number'      => ['nullable', 'string', 'max:100'],
            'contact_telegram' => ['nullable', 'string', 'max:255'],
            'contact_facebook' => ['nullable', 'string', 'max:255'],
            'logo'             => ['nullable', 'image', 'max:2048'],
            'photos'           => ['nullable', 'array'],
            'photos.*'         => ['image', 'max:2048'],
            'reasons'          => ['nullable', 'array'],
            'reasons.*'        => ['nullable', 'string'],
            'testimonials'     => ['nullable', 'array'],
            'testimonials.*'   => ['nullable', 'string'],
        ]);

        $logoPath = $organization->logo;
        if ($request->hasFile('logo')) {
            if ($logoPath) Storage::disk('public')->delete($logoPath);
            $logoPath = $request->file('logo')->store('organizations/logos', 'public');
        }

        $organization->update([
            'org_name'         => $validated['org_name'],
            'vision'           => $validated['vision'] ?? null,
            'mission'          => $validated['mission'] ?? null,
            'room_number'      => $validated['room_number'] ?? null,
            'contact_telegram' => $validated['contact_telegram'] ?? null,
            'contact_facebook' => $validated['contact_facebook'] ?? null,
            'logo'             => $logoPath,
        ]);

        // Replace reasons
        $organization->reasons()->delete();
        foreach (($validated['reasons'] ?? []) as $index => $reason) {
            if (!empty($reason)) {
                $organization->reasons()->create(['reason' => $reason, 'order_index' => $index]);
            }
        }

        // Replace testimonials
        $organization->testimonials()->delete();
        foreach (($validated['testimonials'] ?? []) as $index => $testimonial) {
            if (!empty($testimonial)) {
                $organization->testimonials()->create(['testimonial' => $testimonial, 'order_index' => $index]);
            }
        }

        // Add new photos (keep existing)
        if ($request->hasFile('photos')) {
            $existingCount = $organization->photos()->count();
            foreach ($request->file('photos') as $index => $photo) {
                $path = $photo->store('organizations/photos', 'public');
                $organization->photos()->create(['photo_path' => $path, 'order_index' => $existingCount + $index]);
            }
        }

        return redirect()->route('super-admin.organizations.index')
            ->with('success', 'Organization updated successfully.');
    }

    public function getAccess(Organization $organization)
    {
        $access = $organization->accessUsers()->with('user')->get()->map(function ($a) {
            return [
                'id'        => $a->id,
                'user_id'   => $a->user_id,
                'last_name' => $a->user->last_name,
                'first_name'=> $a->user->first_name,
                'email'     => $a->user->email,
                'position'  => $a->position,
                'avatar'    => $a->user->name,
            ];
        });

        return response()->json([
            'org_name'     => $organization->org_name,
            'access'       => $access,
        ]);
    }

    public function addAccess(Request $request, Organization $organization)
    {
        $validated = $request->validate([
            'email'      => ['required', 'email', 'exists:users,email'],
            'last_name'  => ['nullable', 'string'],
            'first_name' => ['nullable', 'string'],
            'position'   => ['required', 'string', 'max:100'],
        ]);

        $user = User::where('email', $validated['email'])->firstOrFail();

        $existing = OrganizationAccess::where('organization_id', $organization->id)
            ->where('user_id', $user->id)->exists();

        if ($existing) {
            return response()->json(['message' => 'User already has access to this organization.'], 422);
        }

        OrganizationAccess::create([
            'organization_id' => $organization->id,
            'user_id'         => $user->id,
            'position'        => $validated['position'],
        ]);

        if (!$user->isAdminOfficer()) {
            $user->update(['role' => 'admin_officer']);
        }

        return response()->json(['message' => 'Access granted successfully.']);
    }

    public function removeAccess(Organization $organization, OrganizationAccess $access)
    {
        $access->delete();

        $user = User::find($access->user_id);
        if ($user && $user->organizationAccess()->count() === 0) {
            $user->update(['role' => 'student']);
        }

        return response()->json(['message' => 'Access removed successfully.']);
    }

    public function destroy(Organization $organization)
    {
        $organization->delete();

        return response()->json(['message' => 'Organization moved to trash.']);
    }
}
