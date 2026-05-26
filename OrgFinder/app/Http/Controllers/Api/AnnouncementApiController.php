<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Models\OrganizationAccess;
use Illuminate\Http\Request;

class AnnouncementApiController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $myOrgIds = OrganizationAccess::where('user_id', $user->id)
            ->pluck('organization_id')
            ->toArray();

        $announcements = Announcement::with(['organization', 'event'])
            ->latest()
            ->limit(50)
            ->get()
            ->filter(fn($a) => $a->visibility !== 'members_only' || in_array($a->organization_id, $myOrgIds))
            ->values()
            ->map(fn($a) => [
                'id'           => $a->id,
                'title'        => $a->title,
                'body'         => $a->body,
                'is_my_org'    => in_array($a->organization_id, $myOrgIds),
                'organization' => ['id' => $a->organization_id, 'name' => $a->organization->org_name],
                'event'        => $a->event ? ['id' => $a->event->id, 'title' => $a->event->title] : null,
                'created_at'   => $a->created_at->diffForHumans(),
            ]);

        return response()->json(['announcements' => $announcements]);
    }
}
