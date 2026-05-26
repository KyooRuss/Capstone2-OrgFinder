<?php

namespace App\Http\Controllers\AdminOfficer;

use App\Http\Controllers\Controller;
use App\Models\OrganizationAccess;
use App\Models\MembershipRequest;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class MemberController extends Controller
{
    private function myOrganization()
    {
        return Auth::user()?->organizations()->first();
    }

    public function index(Request $request)
    {
        $org = $this->myOrganization();

        if (!$org) {
            return view('admin-officer.members.index', ['members' => collect(), 'org' => null]);
        }

        $query = User::whereHas('organizationAccess', fn($q) => $q
            ->where('organization_id', $org->id)
            ->where('position', 'Member'));

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('last_name', 'like', '%' . $request->search . '%')
                  ->orwhere('first_name', 'like', '%' . $request->search . '%')
                  ->orWhere('email', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->filled('status') && in_array($request->status, ['active', 'blocked'])) {
            $query->where('status', $request->status);
        }

        $members = $query->orderBy('last_name')->get();

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

    public function removeOfficer(User $user)
    {
        $this->authorizeMember($user);

        $user->update(['role' => 'student']);

        return response()->json(['success' => true, 'message' => 'User has been demoted to student.']);
    }

    public function block(User $user)
    {
        $this->authorizeMember($user);

        $user->update(['status' => 'blocked']);

        return response()->json(['success' => true, 'message' => 'Member blocked.']);
    }

    public function unblock(User $user)
    {
        $this->authorizeMember($user);

        $user->update(['status' => 'active']);

        return response()->json(['success' => true, 'message' => 'Member unblocked.']);
    }

    public function destroy(User $user)
    {
        $this->authorizeMember($user);

        $org = $this->myOrganization();

        // Remove from org
        OrganizationAccess::where('organization_id', $org->id)
            ->where('user_id', $user->id)
            ->delete();

        // Reset role to student if no officer positions remain
        $hasOfficerAccess = $user->organizationAccess()
            ->whereIn('position', ['Organization Adviser', 'Organization President'])
            ->exists();

        if (!$hasOfficerAccess) {
            $user->update(['role' => 'student']);
        }

        // Clean up membership request so they can re-apply later
        MembershipRequest::where('organization_id', $org->id)
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
