<?php

namespace App\Http\Controllers\AdminOfficer;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\EventBenefit;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;

class EventController extends Controller
{
    private function myOrganization()
    {
        return Auth::user()?->organizations()->first();
    }

    public function index(Request $request)
    {
        $org = $this->myOrganization();

        if (!$org) {
            return view('admin-officer.events.index', ['events' => collect(), 'org' => null]);
        }

        $query = Event::where('organization_id', $org->id);

        if ($request->filled('search')) {
            $query->where('title', 'like', '%' . $request->search . '%');
        }

        if ($request->filled('status') && in_array($request->status, ['pending', 'approved', 'rejected'])) {
            $query->where('status', $request->status);
        }

        $events = $query->orderByDesc('date')->get();

        return view('admin-officer.events.index', compact('events', 'org'));
    }

    public function show(Event $event)
    {
        $this->authorizeEvent($event);

        $event->load('benefits');

        return response()->json([
            'id'          => $event->id,
            'title'       => $event->title,
            'description' => $event->description,
            'date'        => $event->date?->format('D, F j, Y'),
            'date_raw'    => $event->date?->format('Y-m-d'),
            'time'        => $event->time ? date('g:i A', strtotime($event->time)) : null,
            'time_raw'    => $event->time,
            'venue'       => $event->venue,
            'status'      => $event->status,
            'visibility'  => $event->visibility ?? 'general',
            'image_url'   => $event->event_poster ? asset('storage/' . $event->event_poster) : null,
            'benefits'    => $event->benefits->pluck('benefit')->implode("\n"),
        ]);
    }

    public function store(Request $request)
    {
        $org = $this->myOrganization();

        if (!$org) {
            return response()->json(['success' => false, 'message' => 'You are not associated with any organization.'], 422);
        }

        $data = $request->validate([
            'title'       => ['required', 'string', 'max:255'],
            'date'        => ['required', 'date'],
            'time'        => ['nullable', 'string'],
            'venue'       => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'benefits'    => ['nullable', 'string'],
            'visibility'  => ['nullable', 'in:general,members_only'],
            'image'       => ['nullable', 'image', 'max:4096'],
        ]);

        $posterPath = null;
        if ($request->hasFile('image')) {
            $posterPath = $request->file('image')->store('events/posters', 'public');
        }

        $event = Event::create([
            'organization_id' => $org->id,
            'title'           => $data['title'],
            'date'            => $data['date'],
            'time'            => $data['time'] ?? null,
            'venue'           => $data['venue'] ?? null,
            'description'     => $data['description'] ?? null,
            'event_poster'    => $posterPath,
            'visibility'      => $data['visibility'] ?? 'general',
            'status'          => 'pending',
        ]);

        if (!empty($data['benefits'])) {
            foreach (array_filter(explode("\n", $data['benefits'])) as $i => $benefit) {
                EventBenefit::create([
                    'event_id'    => $event->id,
                    'benefit'     => trim($benefit),
                    'order_index' => $i,
                ]);
            }
        }

        return response()->json(['success' => true, 'message' => 'Event submitted.']);
    }

    public function update(Request $request, Event $event)
    {
        $this->authorizeEvent($event);

        $data = $request->validate([
            'title'       => ['required', 'string', 'max:255'],
            'date'        => ['required', 'date'],
            'time'        => ['nullable', 'string'],
            'venue'       => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'benefits'    => ['nullable', 'string'],
            'visibility'  => ['nullable', 'in:general,members_only'],
            'image'       => ['nullable', 'image', 'max:4096'],
        ]);

        $posterPath = $event->event_poster;
        if ($request->hasFile('image')) {
            if ($posterPath) Storage::disk('public')->delete($posterPath);
            $posterPath = $request->file('image')->store('events/posters', 'public');
        }

        $event->update([
            'title'        => $data['title'],
            'date'         => $data['date'],
            'time'         => $data['time'] ?? null,
            'venue'        => $data['venue'] ?? null,
            'description'  => $data['description'] ?? null,
            'event_poster' => $posterPath,
            'visibility'   => $data['visibility'] ?? 'general',
        ]);

        $event->benefits()->delete();
        if (!empty($data['benefits'])) {
            foreach (array_filter(explode("\n", $data['benefits'])) as $i => $benefit) {
                EventBenefit::create([
                    'event_id'    => $event->id,
                    'benefit'     => trim($benefit),
                    'order_index' => $i,
                ]);
            }
        }

        return response()->json(['success' => true, 'message' => 'Event updated.']);
    }

    public function destroy(Event $event)
    {
        $this->authorizeEvent($event);
        $event->delete();

        return response()->json(['message' => 'Event removed.']);
    }

    private function authorizeEvent(Event $event): void
    {
        $org = $this->myOrganization();
        abort_if(!$org || $event->organization_id !== $org->id, 403);
    }
}
