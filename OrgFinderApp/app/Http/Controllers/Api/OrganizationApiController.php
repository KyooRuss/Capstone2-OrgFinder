<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Organization;
use Illuminate\Http\Request;

class OrganizationApiController extends Controller
{
    public function index(Request $request)
    {
        $query = Organization::with(['photos', 'reasons', 'testimonials'])
            ->whereNull('deleted_at');

        if ($search = $request->query('search')) {
            $query->where('org_name', 'like', "%{$search}%");
        }

        if ($category = $request->query('category')) {
            $query->where('category', $category);
        }

        $orgs = $query->orderBy('org_name')->get();

        return response()->json(['organizations' => $orgs->map(fn($o) => $this->orgResource($o))]);
    }

    public function show($id)
    {
        $org = Organization::with(['photos', 'reasons', 'testimonials', 'events' => function ($q) {
            $q->where('status', 'approved')
              ->where('date', '>=', now()->toDateString())
              ->orderBy('date')
              ->limit(6);
        }])->findOrFail($id);

        return response()->json(['organization' => $this->orgDetailResource($org)]);
    }

    private function orgResource(Organization $org): array
    {
        return [
            'id'       => $org->id,
            'org_name' => $org->org_name,
            'category' => $org->category,
            'president'=> $org->president,
            'mission'  => $org->mission,
            'logo'     => $org->logo ? asset('storage/' . $org->logo) : null,
        ];
    }

    private function orgDetailResource(Organization $org): array
    {
        return [
            'id'               => $org->id,
            'org_name'         => $org->org_name,
            'category'         => $org->category,
            'president'        => $org->president,
            'vision'           => $org->vision,
            'mission'          => $org->mission,
            'room_number'      => $org->room_number,
            'contact_telegram' => $org->contact_telegram,
            'contact_facebook' => $org->contact_facebook,
            'logo'             => $org->logo ? asset('storage/' . $org->logo) : null,
            'photos'           => $org->photos->map(fn($p) => asset('storage/' . $p->path))->values(),
            'reasons'          => $org->reasons->pluck('reason')->values(),
            'testimonials'     => $org->testimonials->pluck('testimonial')->values(),
            'upcoming_events'  => $org->events->map(fn($e) => [
                'id'       => $e->id,
                'title'    => $e->title,
                'date'     => $e->date->format('M j, Y'),
                'venue'     => $e->venue,
                'event_poster' => $e->event_poster ? asset('storage/' . $e->event_poster) : null,
            ])->values(),
        ];
    }
}
