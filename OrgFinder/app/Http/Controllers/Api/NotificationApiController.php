<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Models\OrganizationAccess;
use Illuminate\Http\Request;

class NotificationApiController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        // IDs of orgs the user belongs to
        $myOrgIds = OrganizationAccess::where('user_id', $user->id)
            ->pluck('organization_id')
            ->toArray();

        $notifications = Notification::where('user_id', $user->id)
            ->orderByRaw('CASE WHEN organization_id IN (' . (empty($myOrgIds) ? '0' : implode(',', $myOrgIds)) . ') THEN 0 ELSE 1 END')
            ->orderByDesc('created_at')
            ->limit(100)
            ->get()
            ->map(fn($n) => $this->resource($n, $myOrgIds));

        return response()->json([
            'notifications' => $notifications,
            'unread_count'  => Notification::where('user_id', $user->id)->where('is_read', false)->count(),
        ]);
    }

    public function markRead(Request $request, int $id)
    {
        $notification = Notification::where('user_id', $request->user()->id)
            ->findOrFail($id);

        $notification->update(['is_read' => true]);

        return response()->json(['success' => true]);
    }

    public function markAllRead(Request $request)
    {
        Notification::where('user_id', $request->user()->id)
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return response()->json(['success' => true]);
    }

    public function unreadCount(Request $request)
    {
        $count = Notification::where('user_id', $request->user()->id)
            ->where('is_read', false)
            ->count();

        return response()->json(['unread_count' => $count]);
    }

    private function resource(Notification $n, array $myOrgIds): array
    {
        return [
            'id'              => $n->id,
            'type'            => $n->type,
            'title'           => $n->title,
            'body'            => $n->body,
            'data'            => $n->data,
            'is_read'         => $n->is_read,
            'is_my_org'       => $n->organization_id && in_array($n->organization_id, $myOrgIds),
            'organization_id' => $n->organization_id,
            'created_at'      => $n->created_at?->diffForHumans(),
        ];
    }
}
