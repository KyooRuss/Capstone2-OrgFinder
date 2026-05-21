<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use Illuminate\Http\Request;

class EventController extends Controller
{
    public function upcoming(Request $request)
    {
        $query = Event::with('organization')
            ->whereIn('status', ['pending', 'approved', 'rejected'])
            ->where('date', '>=', now()->toDateString());

        if ($request->filled('search')) {
            $query->where('title', 'like', '%' . $request->search . '%');
        }

        if ($request->filled('filter') && in_array($request->filter, ['pending', 'approved', 'rejected'])) {
            $query->where('status', $request->filter);
        }

        $events = $query->orderBy('date')->get();
        $pendingCount = Event::where('status', 'pending')
            ->where('date', '>=', now()->toDateString())
            ->count();

        return view('super-admin.events.upcoming', compact('events', 'pendingCount'));
    }

    public function past(Request $request)
    {
        $query = Event::with('organization')
            ->where('date', '<', now()->toDateString());

        if ($request->filled('search')) {
            $query->where('title', 'like', '%' . $request->search . '%');
        }

        $events = $query->orderByDesc('date')->get();

        return view('super-admin.events.past', compact('events'));
    }

    public function show(Event $event)
    {
        $event->load(['organization', 'benefits']);

        return response()->json([
            'id'           => $event->id,
            'title'        => $event->title,
            'org_name'     => $event->organization->org_name,
            'description'  => $event->description,
            'date'         => $event->date->format('D, F j, Y'),
            'time'         => $event->time ? date('g:i A', strtotime($event->time)): null,
            'venue'        => $event->venue,
            'event_poster' => $event->poster ? asset('storage/' . $event->poster) : null,
            'status'       => $event->status,
            'benefits'     => $event->benefits->pluck('benefit'),
        ]);
    }

    public function approve(Event $event)
    {
        $event->update(['status' => 'approved']);

        return response()->json(['message' => 'Event approved successfully.']);
    }

    public function reject(Event $event)
    {
        $event->update(['status' => 'rejected']);

        return response()->json(['message' => 'Event rejected successfully.']);
    }

    public function destroy(Event $event)
    {
        $event->delete();

        return response()->json(['message' => 'Event moved to trash.']);
    }
}
