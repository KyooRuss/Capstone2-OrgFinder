<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\OrgChatMessage;
use App\Models\OrganizationAccess;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ChatApiController extends Controller
{
    public function myChats(Request $request)
    {
        $userId  = Auth::id();
        $reads   = $request->input('reads', []);
        $accesses = OrganizationAccess::with('organization')
            ->where('user_id', $userId)
            ->get();

        $chats = $accesses->map(function ($access) use ($userId, $reads) {
            $org      = $access->organization;
            $lastRead = (int) ($reads[$org->id] ?? 0);
            $unread   = OrgChatMessage::where('org_id', $org->id)
                ->where('id', '>', $lastRead)
                ->where('user_id', '!=', $userId)
                ->count();
            $latest   = OrgChatMessage::where('org_id', $org->id)
                ->orderBy('id', 'desc')
                ->first();

            return [
                'org_id'         => $org->id,
                'org_name'       => $org->org_name,
                'org_logo'       => $org->logo ? url('storage/' . $org->logo) : null,
                'unread'         => $unread,
                'last_message'   => $latest?->message,
                'last_time'      => $latest?->created_at->setTimezone('Asia/Manila')->format('h:i A'),
            ];
        });

        return response()->json(['chats' => $chats]);
    }

    public function unreadCount(Request $request)
    {
        $userId = Auth::id();
        $orgIds = OrganizationAccess::where('user_id', $userId)->pluck('organization_id');
        $reads  = $request->input('reads', []);

        $total = 0;
        foreach ($orgIds as $orgId) {
            $lastRead = (int) ($reads[$orgId] ?? 0);
            $total += OrgChatMessage::where('org_id', $orgId)
                ->where('id', '>', $lastRead)
                ->where('user_id', '!=', $userId)
                ->count();
        }

        return response()->json(['unread' => $total]);
    }

    private function isMember(int $orgId): bool
    {
        return OrganizationAccess::where('organization_id', $orgId)
            ->where('user_id', Auth::id())
            ->exists();
    }

    public function messages(Request $request, int $orgId)
    {
        if (!$this->isMember($orgId)) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        $query = OrgChatMessage::with('user')->where('org_id', $orgId);

        if ($request->filled('after')) {
            $messages = $query->where('id', '>', (int) $request->after)
                ->orderBy('id')
                ->get();
        } else {
            $messages = $query->orderBy('id', 'desc')->limit(50)->get()->reverse()->values();
        }

        return response()->json([
            'messages' => $messages->map(fn($m) => [
                'id'          => $m->id,
                'user_id'     => $m->user_id,
                'sender_name' => $m->user?->name ?? 'Unknown',
                'message'     => $m->message,
                'time'        => $m->created_at->setTimezone('Asia/Manila')->format('h:i A'),
            ]),
        ]);
    }

    public function send(Request $request, int $orgId)
    {
        if (!$this->isMember($orgId)) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        $request->validate(['message' => 'required|string|max:1000']);

        $msg = OrgChatMessage::create([
            'org_id'  => $orgId,
            'user_id' => Auth::id(),
            'message' => $request->message,
        ]);

        $msg->load('user');

        return response()->json([
            'message' => [
                'id'          => $msg->id,
                'user_id'     => $msg->user_id,
                'sender_name' => $msg->user?->name ?? 'Unknown',
                'message'     => $msg->message,
                'time'        => $msg->created_at->setTimezone('Asia/Manila')->format('h:i A'),
            ],
        ]);
    }
}
