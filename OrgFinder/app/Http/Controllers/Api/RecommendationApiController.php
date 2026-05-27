<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Organization;
use App\Services\GeminiEmbeddingService;
use Illuminate\Http\Request;

class RecommendationApiController extends Controller
{
    // Each preference maps to exact (1.0) and related (0.7) org categories
    // Keys must match exactly what ProfileAssessmentScreen sends
    private const MATCH_MAP = [
        'interest' => [
            'Technology' => [
                'exact'   => ['Technology', 'Information Technology'],
                'related' => ['Programming', 'Software Development', 'Innovation', 'Research', 'Academic Org', 'Systems & Networking', 'Business & Technology Integration'],
            ],
            'Programming' => [
                'exact'   => ['Programming', 'Software Development'],
                'related' => ['Technology', 'Information Technology', 'Systems & Networking', 'Academic Org'],
            ],
            'Networking' => [
                'exact'   => ['Systems & Networking'],
                'related' => ['Information Technology', 'Technology', 'Information Systems'],
            ],
            'Arts' => [
                'exact'   => ['Arts & Design', 'Arts and Design', 'Creative'],
                'related' => ['Multimedia', 'Performing Arts', 'Photography', 'Creative Services', 'Media Production', 'Entertainment', 'Non-Academic'],
            ],
            'Leadership' => [
                'exact'   => ['Leadership', 'Communication', 'Service', 'Discipline'],
                'related' => ['Community', 'Academic Org', 'Educational', 'Sign Language', 'Non-Academic'],
            ],
            'Research' => [
                'exact'   => ['Research', 'Innovation'],
                'related' => ['Technology', 'Academic Org', 'Information Technology', 'Educational'],
            ],
            'Dancing' => [
                'exact'   => ['Dancing', 'Choreography'],
                'related' => ['Performing Arts', 'Entertainment', 'Creative', 'Arts & Design', 'Singing'],
            ],
            'Photography' => [
                'exact'   => ['Photography', 'Photo & Video Editing', 'Photo and Video Editing'],
                'related' => ['Arts & Design', 'Creative Services', 'Multimedia', 'Entertainment', 'Media Production'],
            ],
            'Gaming' => [
                'exact'   => ['Gaming', 'E-Sports', 'E-Sport'],
                'related' => ['Competition', 'Team Strategy', 'Entertainment'],
            ],
            'Sign Language' => [
                'exact'   => ['Sign Language'],
                'related' => ['Community', 'Educational', 'Communication', 'Non-Academic'],
            ],
            'Photo Video Editing' => [
                'exact'   => ['Photo & Video Editing', 'Photo and Video Editing', 'Photography'],
                'related' => ['Multimedia', 'Creative Services', 'Arts & Design', 'Media Production'],
            ],
            'Singing' => [
                'exact'   => ['Singing', 'Music Publishing', 'Music Collaboration'],
                'related' => ['Recording & Production', 'Performing Arts', 'Audio & Audiovisual Media', 'Audio and Audiovisual Media', 'Entertainment', 'Creative Services'],
            ],
            'Mental First Aid' => [
                'exact'   => ['Mental First Aid', 'Mental Health', 'Guidance & Counseling'],
                'related' => ['Community', 'Educational', 'Service', 'Communication'],
            ],
            'Acting' => [
                'exact'   => ['Performing Arts'],
                'related' => ['Entertainment', 'Creative Services', 'Arts & Design', 'Media Production', 'Creative'],
            ],
            'Innovation' => [
                'exact'   => ['Innovation', 'Research'],
                'related' => ['Technology', 'Business & Technology Integration', 'Academic Org'],
            ],
            'Recording Production' => [
                'exact'   => ['Recording & Production', 'Audio & Audiovisual Media', 'Audio and Audiovisual Media'],
                'related' => ['Music Publishing', 'Performing Arts', 'Creative Services', 'Media Production', 'Entertainment'],
            ],
            'Music Publishing' => [
                'exact'   => ['Music Publishing', 'Music Collaboration', 'Singing'],
                'related' => ['Recording & Production', 'Performing Arts', 'Audio & Audiovisual Media', 'Creative Services', 'Entertainment'],
            ],
        ],
        'skill' => [
            'Programming' => [
                'exact'   => ['Programming', 'Software Development'],
                'related' => ['Technology', 'Information Technology', 'Systems & Networking'],
            ],
            'Sign Language Fluency' => [
                'exact'   => ['Sign Language'],
                'related' => ['Community', 'Educational', 'Communication', 'Non-Academic'],
            ],
            'Singing' => [
                'exact'   => ['Singing', 'Music Publishing', 'Music Collaboration'],
                'related' => ['Recording & Production', 'Performing Arts', 'Audio & Audiovisual Media', 'Entertainment'],
            ],
            'Leadership' => [
                'exact'   => ['Leadership', 'Discipline'],
                'related' => ['Service', 'Community', 'Academic Org', 'Educational', 'Mental First Aid', 'Communication'],
            ],
            'Voice Acting' => [
                'exact'   => ['Performing Arts'],
                'related' => ['Entertainment', 'Creative Services', 'Arts & Design', 'Media Production', 'Creative'],
            ],
            'Research Writing' => [
                'exact'   => ['Research'],
                'related' => ['Academic Org', 'Educational', 'Leadership', 'Innovation'],
            ],
            'Public Speaking' => [
                'exact'   => ['Communication', 'Leadership'],
                'related' => ['Educational', 'Academic Org', 'Service', 'Community', 'Sign Language', 'Mental First Aid', 'Guidance & Counseling'],
            ],
            'Music Production' => [
                'exact'   => ['Recording & Production', 'Audio & Audiovisual Media', 'Audio and Audiovisual Media'],
                'related' => ['Music Publishing', 'Performing Arts', 'Creative Services', 'Entertainment'],
            ],
            'Stage Performance' => [
                'exact'   => ['Performing Arts'],
                'related' => ['Dancing', 'Choreography', 'Singing', 'Entertainment', 'Creative Services'],
            ],
            'Strategic Gaming' => [
                'exact'   => ['Gaming', 'E-Sports', 'E-Sport'],
                'related' => ['Competition', 'Team Strategy', 'Entertainment'],
            ],
            'Event Planning' => [
                'exact'   => ['Leadership', 'Service'],
                'related' => ['Community', 'Academic Org', 'Educational', 'Communication'],
            ],
            'Dancing' => [
                'exact'   => ['Dancing', 'Choreography'],
                'related' => ['Performing Arts', 'Entertainment', 'Creative', 'Arts & Design'],
            ],
        ],
        'activity' => [
            'Competition' => [
                'exact'   => ['Competition', 'E-Sports', 'E-Sport'],
                'related' => ['Gaming', 'Team Strategy', 'Academic Org'],
            ],
            'E-Sports Tournament' => [
                'exact'   => ['E-Sports', 'E-Sport', 'Gaming'],
                'related' => ['Competition', 'Team Strategy', 'Entertainment'],
            ],
            'Training' => [
                'exact'   => ['Educational', 'Leadership'],
                'related' => ['Service', 'Community', 'Academic Org', 'Discipline', 'Mental First Aid', 'Sign Language'],
            ],
            'Seminar' => [
                'exact'   => ['Educational', 'Academic Org'],
                'related' => ['Leadership', 'Community', 'Mental Health', 'Guidance & Counseling', 'Mental First Aid'],
            ],
            'Peer Counseling' => [
                'exact'   => ['Mental Health', 'Guidance & Counseling', 'Mental First Aid'],
                'related' => ['Community', 'Educational', 'Service', 'Communication'],
            ],
            'Public Speaking Event' => [
                'exact'   => ['Communication', 'Leadership'],
                'related' => ['Educational', 'Academic Org', 'Service', 'Community', 'Sign Language'],
            ],
            'Workshop' => [
                'exact'   => ['Educational', 'Creative'],
                'related' => ['Arts & Design', 'Technology', 'Creative Services', 'Multimedia', 'Recording & Production', 'Photography', 'Media Production'],
            ],
            'Tech Talk' => [
                'exact'   => ['Technology', 'Innovation'],
                'related' => ['Information Technology', 'Research', 'Programming', 'Academic Org'],
            ],
            'Theater Performance' => [
                'exact'   => ['Performing Arts'],
                'related' => ['Entertainment', 'Creative Services', 'Arts & Design', 'Dancing', 'Singing'],
            ],
            'Media Production' => [
                'exact'   => ['Media Production', 'Recording & Production'],
                'related' => ['Multimedia', 'Creative Services', 'Arts & Design', 'Entertainment', 'Photography'],
            ],
            'Forum' => [
                'exact'   => ['Communication', 'Leadership'],
                'related' => ['Educational', 'Community', 'Academic Org', 'Mental Health', 'Mental First Aid'],
            ],
        ],
    ];

    public function index(Request $request, GeminiEmbeddingService $gemini)
    {
        $user = $request->user();
        $user->loadMissing('profile');

        $userProgram    = $user->profile?->program;
        $userInterests  = array_slice($user->profile?->interest ?? [], 0, 3);
        $userSkills     = array_slice($user->profile?->skill_to_improve ?? [], 0, 3);
        $userActivities = array_slice($user->profile?->preferred_activity ?? [], 0, 3);

        // Exclude orgs the user already belongs to
        $memberOrgIds = $user->organizations()->pluck('organizations.id')->flip()->toArray();

        $orgs = Organization::whereNull('deleted_at')
            ->get()
            ->filter(function ($org) use ($userProgram, $memberOrgIds) {
                if (isset($memberOrgIds[$org->id])) return false;
                $eligible = $org->eligible_programs;
                return empty($eligible) || in_array($userProgram, $eligible);
            });

        // Build user profile text and ask Gemini to rank the orgs
        $userText = $gemini->userToText($userProgram, $userInterests, $userSkills, $userActivities);
        $orgArray = $orgs->values()->all();
        $ranked   = $gemini->rankOrgs($orgArray, $userText);

        // If Gemini is unavailable, fall back to weighted formula scoring
        if (!$ranked) {
            return $this->formulaFallback($orgs, $userInterests, $userSkills, $userActivities);
        }

        // Map Gemini's ranked IDs back to org models; only keep genuinely relevant results
        $orgById = $orgs->keyBy('id');
        $results = collect($ranked)
            ->filter(fn($r) => isset($orgById[$r['id']]) && ($r['match_pct'] ?? 0) >= 30)
            ->map(fn($r) => $this->formatOrgAI($orgById[$r['id']], $r['match_pct'], $r['match_reason']))
            ->take(4)
            ->values();

        return response()->json(['recommendations' => $results]);
    }

    // Weighted formula: Score = (0.5 × Ir/3) + (0.3 × Sr/3) + (0.2 × Ar/3)
    private function formulaFallback($orgs, array $interests, array $skills, array $activities)
    {
        $scored = $orgs->map(function ($org) use ($interests, $skills, $activities) {
            $orgCategories = array_filter(array_map('strtolower', (array) ($org->category ?? [])));

            if (empty($orgCategories)) {
                return ['org' => $org, 'match_pct' => 0, 'tags' => []];
            }

            [$Ir, $interestTags] = $this->componentScore($interests,  $orgCategories, 'interest');
            [$Sr, $skillTags]    = $this->componentScore($skills,     $orgCategories, 'skill');
            [$Ar, $activityTags] = $this->componentScore($activities, $orgCategories, 'activity');

            $score    = (0.5 * ($Ir / 3)) + (0.3 * ($Sr / 3)) + (0.2 * ($Ar / 3));
            $matchPct = (int) round($score * 100);
            $tags     = array_unique(array_merge($interestTags, $skillTags, $activityTags));

            return ['org' => $org, 'match_pct' => $matchPct, 'tags' => $tags];
        })
        ->filter(fn($i) => $i['match_pct'] >= 30)
        ->sortByDesc('match_pct')
        ->take(4)
        ->values();

        return response()->json([
            'recommendations' => $scored->map(fn($item) => $this->formatOrgFormula($item)),
        ]);
    }

    // Normalize category strings: lowercase, & → and, trailing plurals for e-sport/s, academic org → academic organization
    private function normalizeCategory(string $cat): string
    {
        $c = strtolower(trim($cat));
        $c = str_replace('&', 'and', $c);
        $c = preg_replace('/\s+/', ' ', $c);
        $c = str_replace('academic org', 'academic organization', $c);
        $c = preg_replace('/\be-sport\b/', 'e-sports', $c);
        return $c;
    }

    // Returns [total_score, matched_tags] for one component (interest/skill/activity)
    private function componentScore(array $preferences, array $orgCategories, string $type): array
    {
        $total          = 0.0;
        $tags           = [];
        $normalizedOrgs = array_map(fn($c) => $this->normalizeCategory($c), $orgCategories);

        foreach ($preferences as $pref) {
            $map = self::MATCH_MAP[$type][$pref] ?? null;
            if (!$map) continue;

            $exact   = array_map(fn($c) => $this->normalizeCategory($c), $map['exact']   ?? []);
            $related = array_map(fn($c) => $this->normalizeCategory($c), $map['related'] ?? []);
            $best    = 0.0;

            foreach ($normalizedOrgs as $cat) {
                if (in_array($cat, $exact, true)) {
                    $best = 1.0; break;
                }
                if (in_array($cat, $related, true)) {
                    $best = 0.7;
                }
            }

            $total += $best;
            if ($best > 0) $tags[] = $pref;
        }

        return [$total, $tags];
    }

    private function formatOrgAI(Organization $org, int $matchPct, string $matchReason): array
    {
        return [
            'id'            => $org->id,
            'name'          => $org->org_name,
            'category'      => $org->category,
            'president'     => $org->president,
            'mission'       => $org->mission,
            'logo'          => $org->logo ? url('storage/' . $org->logo) : null,
            'is_recruiting' => $org->is_recruiting,
            'match_pct'     => $matchPct,
            'match_reason'  => $matchReason,
            'match_tags'    => [],
        ];
    }

    private function formatOrgFormula(array $item): array
    {
        $org  = $item['org'];
        $tags = array_values($item['tags']);

        $reason = !empty($tags)
            ? 'Matches your ' . implode(', ', array_slice($tags, 0, 3))
            : 'Explore this organization';

        return [
            'id'            => $org->id,
            'name'          => $org->org_name,
            'category'      => $org->category,
            'president'     => $org->president,
            'mission'       => $org->mission,
            'logo'          => $org->logo ? url('storage/' . $org->logo) : null,
            'is_recruiting' => $org->is_recruiting,
            'match_pct'     => $item['match_pct'],
            'match_reason'  => $reason,
            'match_tags'    => $tags,
        ];
    }
}
