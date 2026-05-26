<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\OrganizationAccess;
use Illuminate\Http\Request;

class EventApiController extends Controller
{
    public function upcoming(Request $request)
    {
        $user  = $request->user();
        $query = Event::with('organization')
            ->where('status', 'approved')
            ->where('date', '>=', now()->toDateString());

        if ($search = $request->query('search')) {
            $query->where('title', 'like', "%{$search}%");
        }

        if ($orgId = $request->query('org_id')) {
            $query->where('organization_id', $orgId);
        }

        // Profs can filter to only events explicitly targeting a specific department
        if ($user->isProf() && ($dept = $request->query('department'))) {
            $query->whereJsonContains('target_departments', $dept);
        }

        $events = $query->orderBy('date')->get();

        // For profs, bubble events that match their department to the top
        if ($user->isProf() && ($profDept = $user->profile?->department)) {
            $events = $events->sortByDesc(function ($e) use ($profDept) {
                $targets = $e->target_departments;
                return !empty($targets) && in_array($profDept, $targets) ? 1 : 0;
            })->values();
        }

        // Pre-load user's org memberships for visibility filtering and is_my_org flag
        $myOrgIds = OrganizationAccess::where('user_id', $user->id)
            ->pluck('organization_id')
            ->flip();

        // Hide members_only events from non-members
        $events = $events->filter(function ($e) use ($myOrgIds) {
            return $e->visibility !== 'members_only' || isset($myOrgIds[$e->organization_id]);
        })->values();

        return response()->json(['events' => $events->map(fn($e) => $this->eventResource($e, $myOrgIds))]);
    }

    public function show(int $id)
    {
        $event = Event::with(['organization', 'benefits'])->findOrFail($id);

        return response()->json(['event' => $this->eventDetailResource($event)]);
    }

    private function eventResource(Event $event, $myOrgIds = null): array
    {
        return [
            'id'                 => $event->id,
            'title'              => $event->title,
            'date'               => $event->date->format('D, M j, Y'),
            'time'               => $event->time,
            'venue'              => $event->venue,
            'poster'             => $event->event_poster ? url('storage/' . $event->event_poster) : null,
            'target_departments' => $event->target_departments ?? [],
            'is_my_org'          => $myOrgIds ? isset($myOrgIds[$event->organization_id]) : false,
            'organization'       => [
                'id'   => $event->organization->id,
                'name' => $event->organization->org_name,
            ],
        ];
    }

    private function eventDetailResource(Event $event): array
    {
        return [
            'id'                 => $event->id,
            'title'              => $event->title,
            'description'        => $event->description,
            'date'               => $event->date->format('D, M j, Y'),
            'time'               => $event->time,
            'venue'              => $event->venue,
            'poster'             => $event->event_poster ? url('storage/' . $event->event_poster) : null,
            'target_departments' => $event->target_departments ?? [],
            'benefits'           => $event->benefits->pluck('benefit')->values(),
            'organization'       => [
                'id'   => $event->organization->id,
                'name' => $event->organization->org_name,
                'logo' => $event->organization->logo
                    ? url('storage/' . $event->organization->logo)
                    : null,
            ],
        ];
    }
}
