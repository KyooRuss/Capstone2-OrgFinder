<?php

namespace App\Http\Controllers\AdminOfficer;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\EventGain;
use Illuminate\Http\Request;

class EventController extends Controller
{
    private function myOrganization()
    {
        return auth()->user()->organizations()->first();
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

        $event->load('gains');

        return response()->json([
            'id'          => $event->id,
            'title'       => $event->title,
            'description' => $event->description,
            'date'        => $event->date?->format('D, F j, Y'),
            'time'        => $event->start_time
                ? date('g:i A', strtotime($event->start_time)) .
                  ($event->end_time ? ' - ' . date('g:i A', strtotime($event->end_time)) : '')
                : null,
            'venue'       => $event->location,
            'status'      => $event->status,
            'image_url'   => $event->poster ? asset('storage/' . $event->poster) : null,
            'gains'       => $event->gains->pluck('gain')->implode("\n"),
        ]);
    }

    public function store(Request $request)
    {
        $org = $this->myOrganization();

        $data = $request->validate([
            'title'       => ['required', 'string', 'max:255'],
            'date'        => ['required', 'date'],
            'time'        => ['nullable', 'string'],
            'venue'       => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'gains'       => ['nullable', 'string'],
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
            'start_time'      => $data['time'] ?? null,
            'location'        => $data['venue'] ?? null,
            'description'     => $data['description'] ?? null,
            'poster'          => $posterPath,
            'status'          => 'pending',
        ]);

        if (!empty($data['gains'])) {
            foreach (array_filter(explode("\n", $data['gains'])) as $i => $gain) {
                EventGain::create([
                    'event_id'    => $event->id,
                    'gain'        => trim($gain),
                    'order_index' => $i,
                ]);
            }
        }

        return response()->json(['success' => true, 'message' => 'Event submitted.']);
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
        abort_if($event->organization_id !== $org->id, 403);
    }
}
