<?php

namespace App\Http\Controllers\AdminOfficer;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class TrashController extends Controller
{
    private function myOrganization()
    {
        return auth()->user()->organizations()->first();
    }

    public function members(Request $request)
    {
        $org = $this->myOrganization();

        if (!$org) {
            return view('admin-officer.trash.members', ['members' => collect(), 'org' => null]);
        }

        $query = User::onlyTrashed()
            ->whereHas('organizationAccess', fn($q) => $q->where('organization_id', $org->id))
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

        return view('admin-officer.trash.members', compact('members', 'org'));
    }

    public function officers(Request $request)
    {
        $org = $this->myOrganization();

        if (!$org) {
            return view('admin-officer.trash.officers', ['officers' => collect(), 'org' => null]);
        }

        $query = User::onlyTrashed()
            ->whereHas('organizationAccess', fn($q) => $q->where('organization_id', $org->id))
            ->where('role', 'admin_officer');

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('email', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->filled('status') && in_array($request->status, ['active', 'blocked'])) {
            $query->where('status', $request->status);
        }

        $officers = $query->orderBy('name')->get();

        return view('admin-officer.trash.officers', compact('officers', 'org'));
    }

    public function restoreUser($id)
    {
        $user = User::onlyTrashed()->findOrFail($id);
        $user->restore();

        return response()->json(['success' => true, 'message' => 'User restored.']);
    }

    public function forceDeleteUser($id)
    {
        $user = User::onlyTrashed()->findOrFail($id);
        $user->forceDelete();

        return response()->json(['success' => true, 'message' => 'User permanently deleted.']);
    }
}
