<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Http\Request;

class EventController extends Controller
{
    public function upcoming(Request $request)
    {
        $query = Event::with('organization')
            ->whereIn('status', ['pending', 'approved'])
            ->where('date', '>=', now()->toDateString());

        if ($request->filled('search')) {
            $query->where('title', 'like', '%' . $request->search . '%');
        }

        if ($request->filled('filter') && in_array($request->filter, ['pending', 'approved'])) {
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
            ->where(function ($q) {
                $q->where('status', 'rejected')
                  ->orWhere(function ($q2) {
                      $q2->where('status', 'approved')
                         ->where('date', '<', now()->toDateString());
                  });
            });

        if ($request->filled('search')) {
            $query->where('title', 'like', '%' . $request->search . '%');
        }

        $events = $query->orderByDesc('updated_at')->get();

        return view('super-admin.events.past', compact('events'));
    }

    public function show(Event $event)
    {
        $event->load(['organization', 'benefits']);

        return response()->json([
            'id'           => $event->id,
            'title'        => $event->title,
            'organization' => $event->organization->org_name,
            'description'  => $event->description,
            'date'         => $event->date->format('D, F j, Y'),
            'time'         => $event->time ? date('g:i A', strtotime($event->time)): null,
            'venue'        => $event->venue,
            'poster'       => $event->event_poster ? asset('storage/' . $event->event_poster) : null,
            'status'       => $event->status,
            'benefits'     => $event->benefits->pluck('benefit'),
        ]);
    }

    public function approve(Event $event)
    {
        $event->update(['status' => 'approved']);

        // Notify every mobile user (student + prof) — org members see it under "Your Organization", others under "Other Organizations"
        $orgName = $event->organization->org_name ?? 'An organization';
        $allMobileUserIds = User::whereIn('role', ['student', 'prof'])
            ->where('status', 'active')
            ->pluck('id');

        foreach ($allMobileUserIds as $uid) {
            Notification::send(
                userId: $uid,
                type:   'event_posted',
                title:  $orgName . ' added a new event',
                body:   '"' . $event->title . '" is happening on ' . $event->date->format('M j, Y') . ($event->venue ? ' at ' . $event->venue : '') . '.',
                orgId:  $event->organization_id,
                data:   ['event_id' => $event->id],
            );
        }

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
