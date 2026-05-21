<?php

namespace App\Http\Controllers\AdminOfficer;

use App\Http\Controllers\Controller;
use App\Models\OrgChatMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ChatController extends Controller
{
    private function myOrganization()
    {
        return Auth::user()?->organizations()->first();
    }

    public function index()
    {
        $org = $this->myOrganization();
        if (!$org) return redirect()->route('admin-officer.organization.index');

        $messages = OrgChatMessage::with('user')
            ->where('org_id', $org->id)
            ->orderBy('id', 'desc')
            ->limit(50)
            ->get()
            ->reverse()
            ->values();

        return view('admin-officer.chat.index', compact('org', 'messages'));
    }

    public function poll(Request $request)
    {
        $org = $this->myOrganization();
        if (!$org) return response()->json(['messages' => []]);

        $messages = OrgChatMessage::with('user')
            ->where('org_id', $org->id)
            ->where('id', '>', (int) $request->after)
            ->orderBy('id')
            ->get();

        return response()->json([
            'messages' => $messages->map(fn($m) => [
                'id'          => $m->id,
                'sender_name' => $m->user?->name ?? 'Unknown',
                'message'     => $m->message,
                'time'        => $m->created_at->setTimezone('Asia/Manila')->format('h:i A'),
                'is_mine'     => $m->user_id === Auth::id(),
            ]),
        ]);
    }

    public function send(Request $request)
    {
        $org = $this->myOrganization();
        if (!$org) return response()->json(['error' => 'Unauthorized'], 403);

        $request->validate(['message' => 'required|string|max:1000']);

        $msg = OrgChatMessage::create([
            'org_id'  => $org->id,
            'user_id' => Auth::id(),
            'message' => $request->message,
        ]);

        $msg->load('user');

        return response()->json([
            'message' => [
                'id'          => $msg->id,
                'sender_name' => $msg->user?->name ?? 'Unknown',
                'message'     => $msg->message,
                'time'        => $msg->created_at->setTimezone('Asia/Manila')->format('h:i A'),
                'is_mine'     => true,
            ],
        ]);
    }
}
