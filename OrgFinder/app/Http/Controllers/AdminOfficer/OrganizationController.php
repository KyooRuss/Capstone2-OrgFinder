<?php

namespace App\Http\Controllers\AdminOfficer;

use App\Http\Controllers\Controller;
use App\Models\OrganizationAccess;
use App\Models\OrganizationPhoto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class OrganizationController extends Controller
{
    private const POSITION_MAP = [
        'organization president' => 'president',
        'organization adviser'   => 'adviser',
        'president'              => 'president',
        'vice president'         => 'vice_president',
        'vice_president'         => 'vice_president',
        'secretary'              => 'secretary',
        'adviser'                => 'adviser',
        'officer'                => 'officer',
        'member'                 => 'member',
    ];

    private const RESPONSIBILITIES = [
        'president'      => 'Lead with integrity — set the vision, guide your team, and represent the organization with pride.',
        'vice_president' => 'Support the President, coordinate officers, and step up whenever the organization needs you.',
        'secretary'      => 'Keep records accurate, manage communications, and make sure nothing important slips through.',
        'adviser'        => 'Guide and mentor members with wisdom, uphold standards, and bridge students and administration.',
        'officer'        => 'Own your tasks, coordinate with your team, and deliver results that reflect well on the organization.',
        'member'         => 'Participate actively, follow your leaders\' guidance, and uphold the values of your organization.',
    ];

    private const QUOTES = [
        'Great things are built by people who show up every single day.',
        'Your contribution matters — even the smallest effort drives the team forward.',
        'Leadership is not a position. It is a choice to make a difference.',
        'One step at a time. Consistency beats perfection every time.',
        'The strength of the team is each individual member. The strength of each member is the team.',
        'Discipline is the bridge between goals and accomplishment.',
        'Show up, work hard, and inspire others to do the same.',
        'Excellence is not a skill — it is an attitude.',
        'Together, we can achieve what none of us could alone.',
        'Your role in this organization is valuable. Never underestimate it.',
        'Serve with purpose. Lead with heart. Act with integrity.',
        'Be the reason your organization succeeds today.',
        'Small acts of dedication create big moments of success.',
        'Your organization needs the best version of you — give it freely.',
        'True leaders lift others up as they rise.',
    ];

    private function myOrganization()
    {
        return Auth::user()?->organizations()->first();
    }

    public function index()
    {
        $user         = Auth::user();
        $organization = $this->myOrganization();

        $eventPosters = $organization
            ? $organization->events()
                ->whereNotNull('event_poster')
                ->where('status', 'approved')
                ->orderByDesc('date')
                ->get(['id', 'title', 'date', 'event_poster'])
            : collect();

        $testimonials = $organization ? $organization->testimonials : collect();
        $photos       = $organization ? $organization->photos       : collect();
        $reasons      = $organization ? $organization->reasons      : collect();

        $rawPosition = null;
        $reminder    = null;
        $quote       = null;
        if ($organization) {
            $rawPosition = OrganizationAccess::where('organization_id', $organization->id)
                ->where('user_id', $user->id)
                ->value('position');
            $normalized = strtolower(trim($rawPosition ?? 'member'));
            $posKey     = self::POSITION_MAP[$normalized] ?? 'member';
            $reminder = self::RESPONSIBILITIES[$posKey];
            $quote    = self::QUOTES[(int) date('j') % count(self::QUOTES)];
        }

        return view('admin-officer.organization.index', compact(
            'organization', 'eventPosters', 'testimonials', 'photos', 'reasons',
            'reminder', 'quote'
        ));
    }

    public function edit()
    {
        $organization = $this->myOrganization();

        if (!$organization) {
            return redirect()->route('admin-officer.organization.index');
        }

        $organization->load(['photos', 'testimonials', 'reasons']);

        return view('admin-officer.organization.edit', compact('organization'));
    }

    public function update(Request $request)
    {
        $organization = $this->myOrganization();

        if (!$organization) {
            return redirect()->route('admin-officer.organization.index');
        }

        $validated = $request->validate([
            'categories'       => ['nullable', 'array', 'max:5'],
            'categories.*'     => ['string', 'max:255'],
            'vision'           => ['nullable', 'string'],
            'mission'          => ['nullable', 'string'],
            'room_number'      => ['nullable', 'string', 'max:100'],
            'contact_telegram' => ['nullable', 'string', 'max:255'],
            'contact_facebook' => ['nullable', 'string', 'max:255'],
            'president'        => ['nullable', 'string', 'max:255'],
            'logo'             => ['nullable', 'image', 'max:2048'],
            'photos'           => ['nullable', 'array'],
            'photos.*'         => ['image', 'max:2048'],
            'testimonials'     => ['nullable', 'array'],
            'testimonials.*'   => ['nullable', 'string'],
            'reasons'          => ['nullable', 'array'],
            'reasons.*'        => ['nullable', 'string'],
        ]);

        $logoPath = $organization->logo;
        if ($request->hasFile('logo')) {
            if ($logoPath) Storage::disk('public')->delete($logoPath);
            $logoPath = $request->file('logo')->store('organizations/logos', 'public');
        }

        $organization->update([
            'category'         => array_filter($validated['categories'] ?? []) ?: null,
            'vision'           => $validated['vision'] ?? null,
            'mission'          => $validated['mission'] ?? null,
            'room_number'      => $validated['room_number'] ?? null,
            'contact_telegram' => $validated['contact_telegram'] ?? null,
            'contact_facebook' => $validated['contact_facebook'] ?? null,
            'president'        => $validated['president'] ?? null,
            'logo'             => $logoPath,
        ]);

        // Replace reasons
        $organization->reasons()->delete();
        foreach (($validated['reasons'] ?? []) as $index => $reason) {
            if (!empty(trim($reason))) {
                $organization->reasons()->create([
                    'reason'      => $reason,
                    'order_index' => $index,
                ]);
            }
        }

        // Replace testimonials
        $organization->testimonials()->delete();
        $authors = $request->input('testimonial_authors', []);
        foreach (($validated['testimonials'] ?? []) as $index => $testimonial) {
            if (!empty(trim($testimonial))) {
                $organization->testimonials()->create([
                    'testimonial' => $testimonial,
                    'author'      => $authors[$index] ?? null,
                    'order_index' => $index,
                ]);
            }
        }

        // Add new photos
        if ($request->hasFile('photos')) {
            $existingCount = $organization->photos()->count();
            foreach ($request->file('photos') as $index => $photo) {
                $path = $photo->store('organizations/photos', 'public');
                $organization->photos()->create(['photo_path' => $path, 'order_index' => $existingCount + $index]);
            }
        }

        return redirect()->route('admin-officer.organization.index')
            ->with('success', 'Organization profile updated successfully.');
    }

    public function deletePhoto(OrganizationPhoto $photo)
    {
        $organization = $this->myOrganization();

        if (!$organization || $photo->organization_id !== $organization->id) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        Storage::disk('public')->delete($photo->photo_path);
        $photo->delete();

        return response()->json(['message' => 'Photo deleted.']);
    }
}
