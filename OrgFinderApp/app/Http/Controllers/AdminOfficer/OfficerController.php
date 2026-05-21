<?php

namespace App\Http\Controllers\AdminOfficer;

use App\Http\Controllers\Controller;
use App\Models\OrganizationAccess;
use App\Models\User;
use Illuminate\Http\Request;

class OfficerController extends Controller
{
    private function myOrganization()
    {
        return auth()->user()->organizations()->first();
    }

    public function index(Request $request)
    {
        $org = $this->myOrganization();

        if (!$org) {
            return view('admin-officer.officers.index', ['officers' => collect(), 'org' => null]);
        }

        $query = User::whereHas('organizationAccess', fn($q) => $q->where('organization_id', $org->id))
            ->where('role', 'admin_officer');

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('email', 'like', '%' . $request->search . '%');
            });
        }

        $officers = $query->orderBy('name')->get();

        return view('admin-officer.officers.index', compact('officers', 'org'));
    }

    public function block(User $user)
    {
        $this->authorizeOfficer($user);
        $user->update(['status' => 'blocked']);

        return response()->json(['success' => true, 'message' => 'Officer blocked.']);
    }

    public function destroy(User $user)
    {
        $this->authorizeOfficer($user);

        $org = $this->myOrganization();
        OrganizationAccess::where('organization_id', $org->id)
            ->where('user_id', $user->id)
            ->delete();

        $user->update(['role' => 'student']);

        return response()->json(['success' => true, 'message' => 'Officer removed.']);
    }

    private function authorizeOfficer(User $user): void
    {
        $org = $this->myOrganization();
        $isMember = OrganizationAccess::where('organization_id', $org->id)
            ->where('user_id', $user->id)->exists();
        abort_if(!$isMember, 403);
    }
}
