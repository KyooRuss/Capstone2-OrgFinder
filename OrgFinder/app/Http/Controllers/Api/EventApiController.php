<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Event;
use Illuminate\Http\Request;

class EventApiController extends Controller
{
    public function upcoming(Request $request)
    {
        $query = Event::with('organization')
            ->where('status', 'approved')
            ->where('date', '>=', now()->toDateString());

        if ($search = $request->query('search')) {
            $query->where('title', 'like', "%{$search}%");
        }

        if ($orgId = $request->query('org_id')) {
            $query->where('organization_id', $orgId);
        }

        $events = $query->orderBy('date')->get();

        return response()->json(['events' => $events->map(fn($e) => $this->eventResource($e))]);
    }

    public function show(int $id)
    {
        $event = Event::with(['organization', 'benefits'])->findOrFail($id);

        return response()->json(['event' => $this->eventDetailResource($event)]);
    }

    private function eventResource(Event $event): array
    {
        return [
            'id'           => $event->id,
            'title'        => $event->title,
            'date'         => $event->date->format('D, M j, Y'),
            'time'         => $event->time,
            'venue'        => $event->venue,
            'poster'       => $event->event_poster ? url('storage/' . $event->event_poster) : null,
            'organization' => [
                'id'   => $event->organization->id,
                'name' => $event->organization->org_name,
            ],
        ];
    }

    private function eventDetailResource(Event $event): array
    {
        return [
            'id'           => $event->id,
            'title'        => $event->title,
            'description'  => $event->description,
            'date'         => $event->date->format('D, M j, Y'),
            'time'         => $event->time,
            'venue'        => $event->venue,
            'poster'       => $event->event_poster ? url('storage/' . $event->event_poster) : null,
            'benefits'     => $event->benefits->pluck('benefit')->values(),
            'organization' => [
                'id'       => $event->organization->id,
                'name'     => $event->organization->org_name,
                'logo'     => $event->organization->logo
                    ? url('storage/' . $event->organization->logo)
                    : null,
            ],
        ];
    }
}
