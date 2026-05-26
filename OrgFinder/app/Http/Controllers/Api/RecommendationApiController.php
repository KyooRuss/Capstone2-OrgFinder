<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Organization;
use App\Services\GeminiEmbeddingService;
use Illuminate\Http\Request;

class RecommendationApiController extends Controller
{
    // Weighted: interests = 3pts, skills = 2pts, activities = 1pt per match
    private const INTEREST_MAP = [
        'Technology'              => ['Technology', 'Information Technology', 'Programming', 'Software Development', 'Systems & Networking', 'Information Systems', 'Business & Technology Integration', 'Research', 'Innovation', 'Academic Organization'],
        'Programming'             => ['Programming', 'Software Development', 'Technology', 'Information Technology', 'Systems & Networking', 'Academic Organization'],
        'Networking'              => ['Systems & Networking', 'Information Technology', 'Technology', 'Information Systems'],
        'Arts'                    => ['Arts & Design', 'Creative', 'Creative Services', 'Multimedia', 'Performing Arts', 'Photography', 'Photo & Video Editing', 'Media Production', 'Entertainment', 'Dancing', 'Choreography', 'Sign Language', 'Non-Academic'],
        'Gaming'                  => ['Gaming', 'E-Sports', 'Competition', 'Team Strategy', 'Entertainment'],
        'Design'                  => ['Arts & Design', 'Creative', 'Creative Services', 'Multimedia', 'Photography', 'Photo & Video Editing'],
        'Animation'               => ['Multimedia', 'Creative Services', 'Arts & Design', 'Creative', 'Media Production', 'Recording & Production', 'Audio & Audiovisual Media'],
        'Music'                   => ['Music Publishing', 'Singing', 'Music Collaboration', 'Recording & Production', 'Performing Arts', 'Audio & Audiovisual Media', 'Entertainment', 'Creative Services', 'Media Production'],
        'Dancing'                 => ['Dancing', 'Choreography', 'Performing Arts', 'Entertainment', 'Creative', 'Arts & Design'],
        'Cyber Security'          => ['Information Technology', 'Systems & Networking', 'Technology', 'Information Systems'],
        'Artificial Intelligence' => ['Technology', 'Research', 'Information Technology', 'Academic Organization', 'Innovation'],
        'Analytics'               => ['Research', 'Academic Organization', 'Technology', 'Information Technology'],
        'Machine Learning'        => ['Technology', 'Research', 'Academic Organization', 'Innovation'],
        'Innovation'              => ['Innovation', 'Research', 'Technology', 'Business & Technology Integration', 'Academic Organization'],
        'Leadership'              => ['Leadership', 'Communication', 'Service', 'Community', 'Discipline', 'Academic Organization', 'Educational', 'Sign Language', 'Mental First Aid', 'Non-Academic'],
        'Sports'                  => ['Competition', 'Team Strategy', 'E-Sports', 'Gaming', 'Service', 'Discipline'],
    ];

    private const SKILL_MAP = [
        'Public Speaking'    => ['Leadership', 'Communication', 'Educational', 'Academic Organization', 'Service', 'Community', 'Guidance & Counseling', 'Sign Language', 'Mental First Aid'],
        'Leadership'         => ['Leadership', 'Service', 'Community', 'Discipline', 'Academic Organization', 'Educational', 'Mental First Aid'],
        'Project Management' => ['Leadership', 'Service', 'Business & Technology Integration', 'Academic Organization', 'Community'],
        'Arts'               => ['Arts & Design', 'Creative', 'Creative Services', 'Multimedia', 'Performing Arts', 'Photography', 'Photo & Video Editing'],
        'Programming'        => ['Programming', 'Software Development', 'Technology', 'Information Technology', 'Systems & Networking'],
        'Cybersecurity'      => ['Information Technology', 'Systems & Networking', 'Technology'],
        'UI/UX Design'       => ['Creative', 'Arts & Design', 'Creative Services', 'Multimedia', 'Technology'],
        'Graphic Design'     => ['Arts & Design', 'Creative', 'Multimedia', 'Creative Services', 'Photography', 'Photo & Video Editing'],
    ];

    private const ACTIVITY_MAP = [
        'Training'    => ['Educational', 'Leadership', 'Service', 'Community', 'Academic Organization', 'Discipline', 'Guidance & Counseling', 'Mental First Aid', 'Sign Language'],
        'Forum'       => ['Communication', 'Leadership', 'Educational', 'Community', 'Academic Organization', 'Mental Health', 'Mental First Aid'],
        'Seminar'     => ['Educational', 'Academic Organization', 'Leadership', 'Community', 'Mental Health', 'Guidance & Counseling', 'Mental First Aid'],
        'Competition' => ['Competition', 'E-Sports', 'Gaming', 'Team Strategy', 'Academic Organization'],
        'E-sports'    => ['E-Sports', 'Gaming', 'Competition', 'Team Strategy', 'Entertainment'],
        'Workshop'    => ['Educational', 'Creative', 'Arts & Design', 'Technology', 'Creative Services', 'Multimedia', 'Recording & Production', 'Photography', 'Media Production'],
        'Hackathons'  => ['Programming', 'Software Development', 'Technology', 'Information Technology', 'Competition', 'Team Strategy', 'Innovation', 'Academic Organization'],
    ];

    // Program → likely categories for bonus matching
    private const PROGRAM_MAP = [
        'BSIT'  => ['Information Technology', 'Technology', 'Programming', 'Systems & Networking', 'Information Systems', 'Academic Organization'],
        'BSCS'  => ['Programming', 'Software Development', 'Technology', 'Information Technology', 'Research', 'Academic Organization'],
        'BSIS'  => ['Information Systems', 'Information Technology', 'Business & Technology Integration', 'Technology'],
        'BSCpE' => ['Technology', 'Systems & Networking', 'Information Technology', 'Research', 'Academic Organization'],
    ];

    public function index(Request $request, GeminiEmbeddingService $gemini)
    {
        $user = $request->user();
        $user->loadMissing('profile');

        $userProgram    = $user->profile?->program;
        $userInterests  = $user->profile?->interest ?? [];
        $userSkills     = $user->profile?->skill_to_improve ?? [];
        $userActivities = $user->profile?->preferred_activity ?? [];

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
        $userText  = $gemini->userToText($userProgram, $userInterests, $userSkills, $userActivities);
        $orgArray  = $orgs->values()->all();
        $ranked    = $gemini->rankOrgs($orgArray, $userText);

        // If Gemini is unavailable, fall back to keyword scoring
        if (!$ranked) {
            return $this->keywordFallback($orgs, $userInterests, $userSkills, $userActivities, $userProgram);
        }

        // Map Gemini's ranked IDs back to org models; only keep genuinely relevant results
        $orgById = $orgs->keyBy('id');
        $results = collect($ranked)
            ->filter(fn($r) => isset($orgById[$r['id']]) && ($r['match_pct'] ?? 0) >= 30)
            ->map(fn($r) => $this->formatOrgAI($orgById[$r['id']], $r['match_pct'], $r['match_reason']))
            ->take(10)
            ->values();

        return response()->json(['recommendations' => $results]);
    }

    private function keywordFallback($orgs, array $interests, array $skills, array $activities, ?string $program)
    {
        $hasProgramBonus = !empty(self::PROGRAM_MAP[$program ?? '']);
        $maxScore = max(1,
            count($interests)  * 3 +
            count($skills)     * 2 +
            count($activities) * 1 +
            ($hasProgramBonus ? 1 : 0)
        );

        $scored = $orgs->map(function ($org) use ($interests, $skills, $activities, $program) {
            [$score, $matchedTags] = $this->scoreOrg($org, $interests, $skills, $activities, $program);
            return ['org' => $org, 'score' => $score, 'matchedTags' => $matchedTags];
        })
        ->sort(function ($a, $b) {
            if ($b['score'] !== $a['score']) return $b['score'] <=> $a['score'];
            return ($b['org']->is_recruiting ? 1 : 0) <=> ($a['org']->is_recruiting ? 1 : 0);
        })
        ->values();

        $matched   = $scored->filter(fn($i) => $i['score'] > 0)->take(10)->values();
        $displayed = $matched->isNotEmpty() ? $matched : $scored->take(10)->values();

        return response()->json([
            'recommendations' => $displayed->map(fn($item) => $this->formatOrg($item, $maxScore)),
        ]);
    }

    private function scoreOrg(Organization $org, array $interests, array $skills, array $activities, ?string $program): array
    {
        $score       = 0;
        $matchedTags = [];

        $orgCategories = array_map('strtolower', (array) ($org->category ?? []));
        $orgCategories = array_filter($orgCategories);

        if (empty($orgCategories)) return [0, []];

        foreach ($interests as $interest) {
            $cats = array_map('strtolower', self::INTEREST_MAP[$interest] ?? []);
            foreach ($orgCategories as $orgCat) {
                if (in_array($orgCat, $cats, true)) {
                    $score += 3; $matchedTags[] = $interest; break;
                }
            }
        }

        foreach ($skills as $skill) {
            $cats = array_map('strtolower', self::SKILL_MAP[$skill] ?? []);
            foreach ($orgCategories as $orgCat) {
                if (in_array($orgCat, $cats, true)) {
                    $score += 2; $matchedTags[] = $skill; break;
                }
            }
        }

        foreach ($activities as $activity) {
            $cats = array_map('strtolower', self::ACTIVITY_MAP[$activity] ?? []);
            foreach ($orgCategories as $orgCat) {
                if (in_array($orgCat, $cats, true)) {
                    $score += 1; $matchedTags[] = $activity; break;
                }
            }
        }

        $programCats = array_map('strtolower', self::PROGRAM_MAP[$program ?? ''] ?? []);
        if (!empty($programCats)) {
            foreach ($orgCategories as $orgCat) {
                if (in_array($orgCat, $programCats, true)) {
                    $score += 1; break;
                }
            }
        }

        return [$score, array_unique($matchedTags)];
    }

    private function formatOrgAI(Organization $org, int $matchPct, string $matchReason): array
    {
        return [
            'id'           => $org->id,
            'name'         => $org->org_name,
            'category'     => $org->category,
            'president'    => $org->president,
            'mission'      => $org->mission,
            'logo'         => $org->logo ? url('storage/' . $org->logo) : null,
            'is_recruiting'=> $org->is_recruiting,
            'match_pct'    => $matchPct,
            'match_reason' => $matchReason,
            'match_tags'   => [],
        ];
    }

    private function formatOrg(array $item, int $maxScore): array
    {
        $org   = $item['org'];
        $tags  = array_values($item['matchedTags']);
        $score = $item['score'];

        $reason = !empty($tags)
            ? 'Matches your ' . implode(', ', array_slice($tags, 0, 3))
            : 'Explore this organization';

        $matchPct = (int) round(($score / $maxScore) * 100);

        return [
            'id'           => $org->id,
            'name'         => $org->org_name,
            'category'     => $org->category,
            'president'    => $org->president,
            'mission'      => $org->mission,
            'logo'         => $org->logo ? url('storage/' . $org->logo) : null,
            'is_recruiting'=> $org->is_recruiting,
            'score'        => $score,
            'match_pct'    => $matchPct,
            'match_reason' => $reason,
            'match_tags'   => $tags,
        ];
    }
}
