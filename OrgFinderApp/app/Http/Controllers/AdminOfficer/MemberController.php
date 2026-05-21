<?php

namespace App\Http\Controllers\AdminOfficer;

use App\Http\Controllers\Controller;
use App\Models\OrganizationAccess;
use App\Models\User;
use Illuminate\Http\Request;

class MemberController extends Controller
{
    private function myOrganization()
    {
        return auth()->user()->organizations()->first();
    }

    public function index(Request $request)
    {
        $org = $this->myOrganization();

        if (!$org) {
            return view('admin-officer.members.index', ['members' => collect(), 'org' => null]);
        }

        $query = User::whereHas('organizationAccess', fn($q) => $q->where('organization_id', $org->id))
            ->where('role', 'student');

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('email', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->filled('status') && in_array($request->status, ['active', 'blocked'])) {
            $query->where('status', $request->status);
        }

        $members = $query->orderBy('name')->get();

        return view('admin-officer.members.index', compact('members', 'org'));
    }

    public function store(Request $request)
    {
        $org = $this->myOrganization();

        $data = $request->validate(['email' => ['required', 'email']]);

        $user = User::where('email', $data['email'])->where('role', 'student')->first();

        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Student not found.'], 404);
        }

        $already = OrganizationAccess::where('organization_id', $org->id)
            ->where('user_id', $user->id)->exists();

        if ($already) {
            return response()->json(['success' => false, 'message' => 'Student is already a member.'], 422);
        }

        OrganizationAccess::create([
            'organization_id' => $org->id,
            'user_id'         => $user->id,
            'position'        => 'Member',
        ]);

        return response()->json(['success' => true, 'message' => 'Member added.']);
    }

    public function makeOfficer(User $user)
    {
        $this->authorizeMember($user);

        $user->update(['role' => 'admin_officer']);

        return response()->json(['success' => true, 'message' => 'User is now an admin officer.']);
    }

    public function block(User $user)
    {
        $this->authorizeMember($user);

        $user->update(['status' => 'blocked']);

        return response()->json(['success' => true, 'message' => 'Member blocked.']);
    }

    public function destroy(User $user)
    {
        $this->authorizeMember($user);

        $org = $this->myOrganization();
        OrganizationAccess::where('organization_id', $org->id)
            ->where('user_id', $user->id)
            ->delete();

        return response()->json(['success' => true, 'message' => 'Member removed.']);
    }

    private function authorizeMember(User $user): void
    {
        $org = $this->myOrganization();
        $isMember = OrganizationAccess::where('organization_id', $org->id)
            ->where('user_id', $user->id)->exists();
        abort_if(!$isMember, 403);
    }
}
