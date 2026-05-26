<?php

namespace App\Http\Controllers\AdminOfficer;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Models\Event;
use App\Models\Notification;
use App\Models\OrganizationAccess;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AnnouncementController extends Controller
{
    private function myOrganization()
    {
        return Auth::user()?->organizations()->first();
    }

    public function index()
    {
        $org = $this->myOrganization();

        if (!$org) {
            return view('admin-officer.announcements.index', ['announcements' => collect(), 'events' => collect(), 'org' => null]);
        }

        $announcements = Announcement::with(['event', 'author'])
            ->where('organization_id', $org->id)
            ->latest()
            ->get();

        $events = Event::where('organization_id', $org->id)
            ->whereIn('status', ['approved', 'pending'])
            ->orderByDesc('date')
            ->get(['id', 'title', 'date']);

        return view('admin-officer.announcements.index', compact('announcements', 'events', 'org'));
    }

    public function store(Request $request)
    {
        $org = $this->myOrganization();

        if (!$org) {
            return response()->json(['message' => 'No organization found.'], 422);
        }

        $data = $request->validate([
            'title'      => ['required', 'string', 'max:255'],
            'body'       => ['required', 'string'],
            'event_id'   => ['nullable', 'exists:events,id'],
            'visibility' => ['nullable', 'in:general,members_only'],
        ]);

        $announcement = Announcement::create([
            'organization_id' => $org->id,
            'event_id'        => $data['event_id'] ?? null,
            'created_by'      => Auth::id(),
            'title'           => $data['title'],
            'body'            => $data['body'],
            'visibility'      => $data['visibility'] ?? 'general',
        ]);

        // General → notify all active mobile users; Org Only → notify org members only
        if ($announcement->visibility === 'general') {
            $recipients = \App\Models\User::whereIn('role', ['student', 'prof'])
                ->where('status', 'active')
                ->pluck('id');
        } else {
            $recipients = OrganizationAccess::where('organization_id', $org->id)->pluck('user_id');
        }

        foreach ($recipients as $uid) {
            Notification::send(
                userId: $uid,
                type:   'announcement',
                title:  $org->org_name . ': ' . $announcement->title,
                body:   $announcement->body,
                orgId:  $org->id,
                data:   ['announcement_id' => $announcement->id],
            );
        }

        return response()->json(['success' => true, 'message' => 'Announcement posted.']);
    }

    public function destroy(Announcement $announcement)
    {
        $org = $this->myOrganization();

        if (!$org || $announcement->organization_id !== $org->id) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        $announcement->delete();

        return response()->json(['message' => 'Announcement deleted.']);
    }
}
