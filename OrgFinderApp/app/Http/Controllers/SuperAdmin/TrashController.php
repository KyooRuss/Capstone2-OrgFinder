<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Http\Request;

class TrashController extends Controller
{
    public function organizations(Request $request)
    {
        $query = Organization::onlyTrashed()
            ->withCount(['accessUsers as members_count', 'events as events_count']);

        if ($request->filled('search')) {
            $query->where('last_name', 'like', '%' . $request->search . '%')
                  ->orWhere('first_name', 'like', '%' . $request->search . '%');
        }

        $organizations = $query->latest('deleted_at')->get();

        return view('super-admin.trash.organizations', compact('organizations'));
    }

    public function events(Request $request)
    {
        $query = Event::onlyTrashed()->with('organization');

        if ($request->filled('search')) {
            $query->where('title', 'like', '%' . $request->search . '%');
        }

        $events = $query->latest('deleted_at')->get();

        return view('super-admin.trash.events', compact('events'));
    }

    public function adminOfficers(Request $request)
    {
        $query = User::onlyTrashed()->where('role', 'admin_officer');

        if ($request->filled('search')) {
            $query->where('last_name', 'like', '%' . $request->search . '%')
                  ->orWhere('first_name', 'like', '%' . $request->search . '%');
        }

        $officers = $query->latest('deleted_at')->get();

        return view('super-admin.trash.admin-officers', compact('officers'));
    }

    public function students(Request $request)
    {
        $query = User::onlyTrashed()->where('role', 'student');

        if ($request->filled('search')) {
            $query->where('last_name', 'like', '%' . $request->search . '%')
                  ->orWhere('first_name', 'like', '%' . $request->search . '%');
        }

        $students = $query->latest('deleted_at')->get();

        return view('super-admin.trash.students', compact('students'));
    }

    public function restoreOrganization(Organization $organization)
    {
        Organization::onlyTrashed()->findOrFail($organization->id)->restore();

        return response()->json(['message' => 'Organization restored successfully.']);
    }

    public function forceDeleteOrganization(Organization $organization)
    {
        $org = Organization::onlyTrashed()->findOrFail($organization->id);
        $org->photos()->each(fn($p) => \Storage::disk('public')->delete($p->photo_path));
        if ($org->logo) \Storage::disk('public')->delete($org->logo);
        $org->forceDelete();

        return response()->json(['message' => 'Organization permanently deleted.']);
    }

    public function restoreEvent(Event $event)
    {
        Event::onlyTrashed()->findOrFail($event->id)->restore();

        return response()->json(['message' => 'Event restored successfully.']);
    }

    public function forceDeleteEvent(Event $event)
    {
        $e = Event::onlyTrashed()->findOrFail($event->id);
        if ($e->poster) \Storage::disk('public')->delete($e->poster);
        $e->forceDelete();

        return response()->json(['message' => 'Event permanently deleted.']);
    }

    public function restoreUser(User $user)
    {
        User::onlyTrashed()->findOrFail($user->id)->restore();

        return response()->json(['message' => 'User restored successfully.']);
    }

    public function forceDeleteUser(User $user)
    {
        User::onlyTrashed()->findOrFail($user->id)->forceDelete();

        return response()->json(['message' => 'User permanently deleted.']);
    }
}
