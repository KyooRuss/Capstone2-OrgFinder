<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\OrganizationAccess;
use Illuminate\Http\Request;

class AdminOfficerController extends Controller
{
    public function index(Request $request)
    {
        $query = User::where('role', 'admin_officer')
            ->with(['organizationAccess' => fn($q) => $q->with(['organization' => fn($q) => $q->withTrashed()])]);

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('last_name', 'like', '%' . $request->search . '%')
                  ->orWhere('first_name', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->filled('filter') && in_array($request->filter, ['active', 'blocked'])) {
            $query->where('status', $request->filter);
        }

        $officers = $query->get()->map(function ($user, $index) {
            $access = $user->organizationAccess->first();
            $orgName = $access?->organization?->org_name
                ?? ($access ? '(Deleted Organization)' : '—');
            return [
                'id'           => $user->id,
                'admin_number' => 'A' . str_pad($index + 1, 4, '0', STR_PAD_LEFT),
                'last_name'    => $user->last_name,
                'first_name'   => $user->first_name,
                'organization' => $orgName,
                'position'     => $access?->position ?? '—',
                'status'       => $user->status,
            ];
        });

        return view('super-admin.admin-officers.index', compact('officers'));
    }

    public function block(User $user)
    {
        $user->update(['status' => 'blocked']);

        return response()->json(['message' => 'Admin officer blocked successfully.']);
    }

    public function unblock(User $user)
    {
        $user->update(['status' => 'active']);

        return response()->json(['message' => 'Admin officer unblocked successfully.']);
    }

    public function destroy(User $user)
    {
        $user->delete();

        return response()->json(['message' => 'Admin officer removed successfully.']);
    }
}
