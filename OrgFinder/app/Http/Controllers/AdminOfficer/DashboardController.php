<?php

namespace App\Http\Controllers\AdminOfficer;

use App\Http\Controllers\Controller;
use App\Models\OrganizationAccess;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    private const RESPONSIBILITIES = [
        'president'      => 'As President, you are responsible for leading the organization with integrity. Set the vision, guide your team, make key decisions, and represent your organization with pride. Your leadership shapes the culture and direction of every member.',
        'vice_president' => 'As Vice President, you support the President and step in when needed. Coordinate officers, bridge communication between leadership and members, and ensure every initiative runs smoothly from start to finish.',
        'secretary'      => 'As Secretary, you are the backbone of the organization\'s records. Take accurate minutes, manage important documents and communications, and ensure nothing critical ever falls through the cracks.',
        'adviser'        => 'As Adviser, your wisdom and experience guide the organization. Mentor members, provide sound and balanced advice, uphold the organization\'s standards, and help bridge the gap between students and administration.',
        'officer'        => 'As an Officer, you are the engine that drives events and activities forward. Take full ownership of your assigned tasks, coordinate with your team, and deliver quality output that reflects well on the entire organization.',
        'member'         => 'As a Member, your active participation is what makes the organization thrive. Follow the guidance of your leaders, join activities wholeheartedly, support your fellow members, and uphold the values and reputation of your organization.',
    ];

    private const QUOTES = [
        'Great things are built by people who show up every single day.',
        'Your contribution matters — even the smallest effort drives the team forward.',
        'Leadership is not a position. It is a choice to make a difference.',
        'One step at a time. Consistency beats perfection every time.',
        'The strength of the team is each individual member. The strength of each member is the team.',
        'Discipline is the bridge between goals and accomplishment.',
        'Show up, work hard, and inspire others to do the same.',
        'Excellence is not a skill — it is an attitude.',
        'Together, we can achieve what none of us could alone.',
        'Your role in this organization is valuable. Never underestimate it.',
        'Serve with purpose. Lead with heart. Act with integrity.',
        'Be the reason your organization succeeds today.',
        'Small acts of dedication create big moments of success.',
        'Your organization needs the best version of you — give it freely.',
        'True leaders lift others up as they rise.',
    ];

    public function index()
    {
        $user = Auth::user();
        $org  = $user->organizations()->first();

        $rawPosition = null;
        $positionKey = 'member';
        if ($org) {
            $rawPosition = OrganizationAccess::where('organization_id', $org->id)
                ->where('user_id', $user->id)
                ->value('position');
            $positionKey = strtolower(str_replace([' ', '-'], '_', $rawPosition ?? 'member'));
            if (!array_key_exists($positionKey, self::RESPONSIBILITIES)) {
                $positionKey = 'member';
            }
        }

        $responsibility = self::RESPONSIBILITIES[$positionKey];
        $quote          = self::QUOTES[(int) date('j') % count(self::QUOTES)];
        $positionLabel  = ucwords(str_replace('_', ' ', $positionKey));

        $membersCount  = $org ? $org->accessUsers()->count() : 0;
        $eventsCount   = $org ? $org->events()->whereNull('deleted_at')->count() : 0;
        $pendingCount  = $org ? $org->membershipRequests()->where('status', 'pending')->count() : 0;

        return view('admin-officer.dashboard', compact(
            'org', 'rawPosition', 'positionLabel',
            'responsibility', 'quote',
            'membersCount', 'eventsCount', 'pendingCount'
        ));
    }
}
