<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ProfController extends Controller
{
    public function index(Request $request)
    {
        $query = User::where('role', 'prof')->with('profile');

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('last_name', 'like', '%' . $request->search . '%')
                  ->orWhere('first_name', 'like', '%' . $request->search . '%')
                  ->orWhere('email', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->filled('filter') && in_array($request->filter, ['active', 'blocked'])) {
            $query->where('status', $request->filter);
        }

        $profs = $query->latest()->get()->map(fn($u) => [
            'id'         => $u->id,
            'first_name' => $u->first_name,
            'last_name'  => $u->last_name,
            'email'      => $u->email,
            'department' => $u->profile?->department ?? '—',
            'status'     => $u->status,
        ]);

        return view('super-admin.profs.index', compact('profs'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name'  => 'required|string|max:255',
            'email'      => 'required|email|unique:users,email',
            'department' => 'required|string|max:255',
            'password'   => 'required|string|min:8',
        ]);

        $user = User::create([
            'first_name' => $data['first_name'],
            'last_name'  => $data['last_name'],
            'email'      => $data['email'],
            'password'   => Hash::make($data['password']),
            'role'       => 'prof',
        ]);

        $user->profile()->create([
            'department'        => $data['department'],
            'profile_completed' => true,
        ]);

        return response()->json(['message' => 'Professor account created successfully.']);
    }

    public function block(User $user)
    {
        $user->update(['status' => 'blocked']);
        return response()->json(['message' => 'Professor blocked successfully.']);
    }

    public function unblock(User $user)
    {
        $user->update(['status' => 'active']);
        return response()->json(['message' => 'Professor unblocked successfully.']);
    }

    public function destroy(User $user)
    {
        $user->delete();
        return response()->json(['message' => 'Professor removed successfully.']);
    }
}
