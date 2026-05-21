<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Organization;
use Illuminate\Http\Request;

class RecommendationApiController extends Controller
{
    // Category-to-interest mapping for rule-based matching
    private const INTEREST_CATEGORY_MAP = [
        'Technology'           => ['Technology', 'IT', 'Computer Science'],
        'Programming'          => ['Technology', 'Programming'],
        'Networking'           => ['Technology', 'Networking'],
        'Arts'                 => ['Arts', 'Creative'],
        'Leadership'           => ['Leadership', 'Communication', 'Discipline'],
        'Research'             => ['Research', 'Educational', 'Innovation'],
        'Dancing'              => ['Dancing', 'Performing Arts', 'Entertainment'],
        'Photography'          => ['Photography', 'Creative', 'Photo and Video Editing'],
        'Sign Language'        => ['Sign Language', 'Communication', 'Service'],
        'Gaming'               => ['Gaming', 'E-Sport', 'Technology'],
        'Music Publishing'     => ['Music Publishing', 'Recording & Production'],
        'Singing'              => ['Singing', 'Performing Arts', 'Music Publishing'],
        'Innovation'           => ['Technology', 'Entrepreneurship', 'Research'],
        'Photo Video Editing'  => ['Photo and Video Editing', 'Photography'],
        'Mental First Aid'     => ['Mental First Aid', 'Mental Health', 'Leadership'],
        'Acting'               => ['Performing Arts', 'Entertainment'],
        'Recording Production' => ['Recording & Production', 'Audio and Audiovisual Media', 'Creative Services'],
    ];

    private const SKILL_CATEGORY_MAP = [
        'Programming'          => ['Technology', 'Innovation'],
        'Research Writing'     => ['Research', 'Educational'],
        'Leadership'           => ['Leadership', 'Discipline', 'Service'],
        'Public Speaking'      => ['Leadership', 'Communication'],
        'Music Production'       => ['Recording & Production', 'Music Publishing'],
        'Singing'                => ['Singing', 'Performing Arts'],
        'Dancing'                => ['Dancing', 'Performing Arts'],
        'Stage Performance'      => ['Performing Arts', 'Entertainment'],
        'Voice Acting'           => ['Entertainment', 'Audio and Audiovisual Media'],
        'Sign Language Fluency'  => ['Sign Language', 'Communication'],
        'Strategic Gaming'       => ['Gaming', 'E-Sport'],
        'Event Planning'         => ['Community', 'Leadership', 'Service'],
    ];

    private const ACTIVITY_CATEGORY_MAP = [
        'Training'        => ['Leadership', 'Service', 'Educational'],
        'Forum'           => ['Communication', 'Leadership', 'Educational'],
        'Seminar'         => ['Educational', 'Leadership'],
        'Peer Counseling' => ['Mental Health', 'Guidance & Counseling'],
        'Competition'     => ['Competition', 'E-sports', 'Gaming'],
        'Media Production'     => ['Recording & Production', 'Creative Services'],
        'Workshop'        => ['Education', 'Training', 'Creative'],
        'Art Exhibit'          => ['Creative', 'Performing Arts'],
        'Tech Talk'            => ['Technology', 'Educational'],
        'E-Sports Tournament'  => ['E-Sport', 'Gaming', 'Competition'],
        'Public Speaking Event'=> ['Leadership', 'Communication'],
        'Film Showing'         => ['Entertainment', 'Audio and Audiovisual Media'],
        'Theater Performance'  => ['Performing Arts', 'Creative'],
    ];

    public function index(Request $request)
    {
        $user = $request->user();

        $matchCategories = collect();

        foreach (($user->interests ?? []) as $interest) {
            $cats = self::INTEREST_CATEGORY_MAP[$interest] ?? [];
            $matchCategories = $matchCategories->merge($cats);
        }
        foreach (($user->skills ?? []) as $skill) {
            $cats = self::SKILL_CATEGORY_MAP[$skill] ?? [];
            $matchCategories = $matchCategories->merge($cats);
        }
        foreach (($user->activities ?? []) as $activity) {
            $cats = self::ACTIVITY_CATEGORY_MAP[$activity] ?? [];
            $matchCategories = $matchCategories->merge($cats);
        }

        $matchCategories = $matchCategories->unique()->values();

        $orgs = Organization::with(['photos'])
            ->whereNull('deleted_at')
            ->get();

        // Score each org
        $scored = $orgs->map(function ($org) use ($matchCategories) {
            $score = 0;
            if ($org->category && $matchCategories->contains($org->category)) {
                $score = $matchCategories->filter(fn($c) => $c === $org->category)->count();
            }
            return ['org' => $org, 'score' => $score];
        })
        ->sortByDesc('score')
        ->filter(fn($item) => $item['score'] > 0)
        ->take(10)
        ->values();

        // Fall back to all orgs if no matches
        if ($scored->isEmpty()) {
            $scored = $orgs->take(10)->map(fn($o) => ['org' => $o, 'score' => 0]);
        }

        return response()->json([
            'recommendations' => $scored->map(fn($item) => [
                'id'       => $item['org']->id,
                'org_name' => $item['org']->name,
                'category' => $item['org']->category,
                'president'=> $item['org']->president,
                'mission'  => $item['org']->mission,
                'logo'     => $item['org']->logo ? asset('storage/' . $item['org']->logo) : null,
                'score'    => $item['score'],
                'match_reason' => $this->matchReason($item['org'], request()->user()),
            ])->values(),
        ]);
    }

    private function matchReason(Organization $org, $user): string
    {
        $interests = $user->interests ?? [];
        $skills    = $user->skills ?? [];

        $matched = array_merge(
            array_filter($interests, fn($i) => in_array($org->category, self::INTEREST_CATEGORY_MAP[$i] ?? [])),
            array_filter($skills, fn($s) => in_array($org->category, self::SKILL_CATEGORY_MAP[$s] ?? []))
        );

        if (empty($matched)) return "Matches your profile";

        return 'Matches your interest in ' . implode(' and ', array_unique($matched));
    }
}
