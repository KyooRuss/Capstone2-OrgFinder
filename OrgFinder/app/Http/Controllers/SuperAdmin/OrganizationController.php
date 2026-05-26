<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Organization;
use App\Models\OrganizationAccess;
use App\Models\OrganizationPhoto;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class OrganizationController extends Controller
{
    public function index(Request $request)
    {
        $query = Organization::withCount(['accessUsers as members_count', 'events as events_count']);

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
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
            'org_name'          => ['required', 'string', 'max:255'],
            'categories'        => ['nullable', 'array', 'max:5'],
            'categories.*'      => ['string', 'max:255'],
            'vision'            => ['nullable', 'string'],
            'mission'           => ['nullable', 'string'],
            'room_number'       => ['nullable', 'string', 'max:100'],
            'contact_telegram'  => ['nullable', 'string', 'max:255'],
            'contact_facebook'  => ['nullable', 'string', 'max:255'],
            'logo'              => ['nullable', 'image', 'max:2048'],
            'photos'            => ['nullable', 'array'],
            'photos.*'          => ['image', 'max:2048'],
            'reasons'           => ['nullable', 'array'],
            'reasons.*'         => ['nullable', 'string'],
            'testimonials'      => ['nullable', 'array'],
            'testimonials.*'    => ['nullable', 'string'],
            'eligible_programs'   => ['nullable', 'array'],
            'eligible_programs.*' => ['string'],
            'department'          => ['nullable', 'string', 'max:255'],
        ]);

        $logoPath = null;
        if ($request->hasFile('logo')) {
            $logoPath = $request->file('logo')->store('organizations/logos', 'public');
        }

        $organization = Organization::create([
            'org_name'          => $validated['org_name'],
            'category'          => array_filter($validated['categories'] ?? []) ?: null,
            'vision'            => $validated['vision'] ?? null,
            'mission'           => $validated['mission'] ?? null,
            'room_number'       => $validated['room_number'] ?? null,
            'contact_telegram'  => $validated['contact_telegram'] ?? null,
            'contact_facebook'  => $validated['contact_facebook'] ?? null,
            'logo'              => $logoPath,
            'eligible_programs' => $validated['eligible_programs'] ?? null,
            'department'        => $validated['department'] ?? null,
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

        $authors = $request->input('testimonial_authors', []);
        foreach (($validated['testimonials'] ?? []) as $index => $testimonial) {
            if (!empty($testimonial)) {
                $organization->testimonials()->create([
                    'testimonial' => $testimonial,
                    'author'      => $authors[$index] ?? null,
                    'order_index' => $index,
                ]);
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
            'org_name'          => ['required', 'string', 'max:255'],
            'categories'        => ['nullable', 'array', 'max:5'],
            'categories.*'      => ['string', 'max:255'],
            'vision'            => ['nullable', 'string'],
            'mission'           => ['nullable', 'string'],
            'room_number'       => ['nullable', 'string', 'max:100'],
            'contact_telegram'  => ['nullable', 'string', 'max:255'],
            'contact_facebook'  => ['nullable', 'string', 'max:255'],
            'logo'              => ['nullable', 'image', 'max:2048'],
            'photos'            => ['nullable', 'array'],
            'photos.*'          => ['image', 'max:2048'],
            'reasons'           => ['nullable', 'array'],
            'reasons.*'         => ['nullable', 'string'],
            'testimonials'      => ['nullable', 'array'],
            'testimonials.*'    => ['nullable', 'string'],
            'eligible_programs'   => ['nullable', 'array'],
            'eligible_programs.*' => ['string'],
            'department'          => ['nullable', 'string', 'max:255'],
        ]);

        $logoPath = $organization->logo;
        if ($request->hasFile('logo')) {
            if ($logoPath) Storage::disk('public')->delete($logoPath);
            $logoPath = $request->file('logo')->store('organizations/logos', 'public');
        }

        $organization->update([
            'org_name'          => $validated['org_name'],
            'category'          => array_filter($validated['categories'] ?? []) ?: null,
            'vision'            => $validated['vision'] ?? null,
            'mission'           => $validated['mission'] ?? null,
            'room_number'       => $validated['room_number'] ?? null,
            'contact_telegram'  => $validated['contact_telegram'] ?? null,
            'contact_facebook'  => $validated['contact_facebook'] ?? null,
            'logo'              => $logoPath,
            'eligible_programs' => $validated['eligible_programs'] ?? null,
            'department'        => $validated['department'] ?? null,
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
        $authors = $request->input('testimonial_authors', []);
        foreach (($validated['testimonials'] ?? []) as $index => $testimonial) {
            if (!empty($testimonial)) {
                $organization->testimonials()->create([
                    'testimonial' => $testimonial,
                    'author'      => $authors[$index] ?? null,
                    'order_index' => $index,
                ]);
            }
        }

        // Delete photos marked for removal
        if ($request->filled('delete_photos')) {
            $photosToDelete = OrganizationPhoto::whereIn('id', $request->input('delete_photos'))
                ->where('organization_id', $organization->id)
                ->get();
            foreach ($photosToDelete as $photo) {
                Storage::disk('public')->delete($photo->photo_path);
                $photo->delete();
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
        $access = $organization->accessUsers()->with('user')->get()
            ->filter(fn($a) => $a->user !== null)
            ->map(fn($a) => [
                'id'       => $a->id,
                'user_id'  => $a->user_id,
                'name'     => $a->user->name,
                'email'    => $a->user->email,
                'position' => $a->position,
            ])->values();

        return response()->json([
            'organization' => $organization->org_name,
            'access'       => $access,
        ]);
    }

    public function addAccess(Request $request, Organization $organization)
    {
        $validated = $request->validate([
            'email'    => ['required', 'email', 'exists:users,email'],
            'last_name' => ['nullable', 'string'],
            'first_name' => ['nullable', 'string'],
            'position' => ['required', 'in:Organization Adviser,Organization President'],
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
        $hasOfficerAccess = $user?->organizationAccess()
            ->whereIn('position', ['Organization Adviser', 'Organization President'])
            ->exists();

        if ($user && !$hasOfficerAccess) {
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
