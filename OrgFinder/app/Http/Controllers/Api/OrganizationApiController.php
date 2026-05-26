<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Organization;
use App\Models\OrganizationAccess;
use Illuminate\Http\Request;

class OrganizationApiController extends Controller
{
    private const INTEREST_MAP = [
        'Technology'              => ['Technology', 'Information Technology', 'Programming', 'Software Development', 'Systems & Networking', 'Information Systems', 'Business & Technology Integration', 'Research', 'Innovation', 'Academic Organization'],
        'Programming'             => ['Programming', 'Software Development', 'Technology', 'Information Technology', 'Systems & Networking', 'Academic Organization'],
        'Networking'              => ['Systems & Networking', 'Information Technology', 'Technology', 'Information Systems'],
        'Arts'                    => ['Arts & Design', 'Creative', 'Creative Services', 'Multimedia', 'Performing Arts', 'Photography', 'Photo & Video Editing', 'Media Production', 'Entertainment'],
        'Gaming'                  => ['Gaming', 'E-Sports', 'Competition', 'Team Strategy', 'Entertainment'],
        'Design'                  => ['Arts & Design', 'Creative', 'Creative Services', 'Multimedia', 'Photography', 'Photo & Video Editing'],
        'Animation'               => ['Multimedia', 'Creative Services', 'Arts & Design', 'Creative', 'Media Production', 'Recording & Production', 'Audio & Audiovisual Media'],
        'Music'                   => ['Music Publishing', 'Singing', 'Music Collaboration', 'Recording & Production', 'Performing Arts', 'Audio & Audiovisual Media', 'Entertainment', 'Dancing', 'Choreography', 'Media Production'],
        'Cyber Security'          => ['Information Technology', 'Systems & Networking', 'Technology', 'Information Systems'],
        'Artificial Intelligence' => ['Technology', 'Research', 'Information Technology', 'Academic Organization', 'Innovation'],
        'Analytics'               => ['Research', 'Academic Organization', 'Technology', 'Information Technology'],
        'Innovation'              => ['Innovation', 'Research', 'Technology', 'Business & Technology Integration', 'Academic Organization'],
        'Leadership'              => ['Leadership', 'Communication', 'Service', 'Community', 'Discipline', 'Academic Organization', 'Educational'],
        'Sports'                  => ['Competition', 'Team Strategy', 'E-Sports', 'Gaming', 'Service', 'Discipline'],
    ];

    public function myOrganizations(Request $request)
    {
        $user = $request->user();

        $orgs = Organization::whereHas('accessUsers', fn($q) => $q->where('user_id', $user->id))
            ->whereNull('deleted_at')
            ->orderBy('org_name')
            ->get();

        return response()->json([
            'organizations' => $orgs->map(fn($o) => [
                'id'       => $o->id,
                'name'     => $o->org_name,
                'category' => $o->category,
                'logo'     => $o->logo ? url('storage/' . $o->logo) : null,
                'position' => OrganizationAccess::where('organization_id', $o->id)
                                ->where('user_id', $user->id)
                                ->value('position'),
            ]),
        ]);
    }

    public function recruiting(Request $request)
    {
        $user        = $request->user();
        $userProgram = $user->profile?->program;

        // Pre-load the user's actual memberships for fast lookup
        $memberOrgIds = OrganizationAccess::where('user_id', $user->id)
            ->pluck('organization_id')
            ->flip();

        $userHasOrg = $memberOrgIds->isNotEmpty();

        $orgs = Organization::with(['photos', 'membershipRequests'])
            ->whereNull('deleted_at')
            ->where('is_recruiting', true)
            ->orderBy('org_name')
            ->get()
            ->filter(function ($org) use ($userProgram) {
                $eligible = $org->eligible_programs;
                return empty($eligible) || in_array($userProgram, $eligible);
            })->values();

        return response()->json([
            'user_has_org' => $userHasOrg,
            'recruiting'   => $orgs->map(fn($o) => [
                'id'        => $o->id,
                'name'      => $o->org_name,
                'category'  => $o->category,
                'logo'      => $o->logo ? url('storage/' . $o->logo) : null,
                'is_member' => isset($memberOrgIds[$o->id]),
                'my_status' => $o->membershipRequests
                    ->where('user_id', $user->id)
                    ->first()?->status,
            ]),
        ]);
    }

    public function index(Request $request)
    {
        $user  = $request->user();
        $query = Organization::with(['photos', 'reasons', 'testimonials', 'membershipRequests'])
            ->whereNull('deleted_at');

        if ($search = $request->query('search')) {
            $query->where('org_name', 'like', "%{$search}%");
        }

        if ($category = $request->query('category')) {
            $query->whereJsonContains('category', $category);
        }

        $userProgram = $user->profile?->program;
        $isProf      = $user->isProf();
        $deptFilter  = $request->query('department');

        $orgs = $query->orderBy('org_name')->get()->filter(function ($org) use ($userProgram, $isProf, $deptFilter) {
            if ($isProf) {
                if ($deptFilter) {
                    return $org->department === $deptFilter;
                }
                return true;
            }
            $eligible = $org->eligible_programs;
            return empty($eligible) || in_array($userProgram, $eligible);
        })->values();

        return response()->json(['organizations' => $orgs->map(fn($o) => $this->orgResource($o))]);
    }

    public function show(Request $request, int $id)
    {
        $org = Organization::with(['photos', 'reasons', 'testimonials', 'membershipRequests', 'events' => function ($q) {
            $q->where('status', 'approved')
              ->orderByDesc('date')
              ->limit(12);
        }])->findOrFail($id);

        $myRequest = $org->membershipRequests
            ->where('user_id', $request->user()->id)
            ->first();

        $isMember = $org->accessUsers()
            ->where('user_id', $request->user()->id)
            ->exists();

        return response()->json(['organization' => $this->orgDetailResource($org, $myRequest?->status, $isMember)]);
    }

    private function orgResource(Organization $org): array
    {
        return [
            'id'           => $org->id,
            'name'         => $org->org_name,
            'category'     => $org->category,
            'president'    => $org->president,
            'mission'      => $org->mission,
            'logo'         => $org->logo ? url('storage/' .$org->logo) : null,
            'is_recruiting'=> $org->is_recruiting,
        ];
    }

    private function orgDetailResource(Organization $org, ?string $myRequestStatus = null, bool $isMember = false): array
    {
        return [
            'id'               => $org->id,
            'is_recruiting'    => $org->is_recruiting,
            'my_request_status'=> $myRequestStatus,
            'is_member'        => $isMember,
            'name'             => $org->org_name,
            'category'         => $org->category,
            'president'        => $org->president,
            'vision'           => $org->vision,
            'mission'          => $org->mission,
            'room_number'      => $org->room_number,
            'contact_telegram' => $org->contact_telegram,
            'contact_facebook' => $org->contact_facebook,
            'logo'             => $org->logo ? url('storage/' .$org->logo) : null,
            'photos'           => $org->photos->map(fn($p) => url('storage/' .$p->photo_path))->values(),
            'event_photos'     => $org->events->filter(fn($e) => $e->event_poster)->map(fn($e) => url('storage/' .$e->event_poster))->values(),
            'reasons'          => $org->reasons->pluck('reason')->values(),
            'testimonials'     => $org->testimonials->map(fn($t) => [
                'text'   => $t->testimonial,
                'author' => $t->author,
            ])->values(),
            'upcoming_events'  => $org->events->filter(fn($e) => $e->date >= now()->toDateString())->sortBy('date')->map(fn($e) => [
                'id'     => $e->id,
                'title'  => $e->title,
                'date'   => $e->date instanceof \Carbon\Carbon ? $e->date->format('M j, Y') : \Carbon\Carbon::parse($e->date)->format('M j, Y'),
                'venue'  => $e->venue,
                'poster' => $e->event_poster ? url('storage/' .$e->event_poster) : null,
            ])->values(),
        ];
    }
}
