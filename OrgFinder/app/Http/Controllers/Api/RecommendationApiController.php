<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Organization;
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

    public function index(Request $request)
    {
        $user = $request->user();

        $user->loadMissing('profile');
        $userProgram    = $user->profile?->program;
        $userInterests  = $user->profile?->interest ?? [];
        $userSkills     = $user->profile?->skill_to_improve ?? [];
        $userActivities = $user->profile?->preferred_activity ?? [];

        $orgs = Organization::with(['photos'])
            ->whereNull('deleted_at')
            ->get()
            ->filter(function ($org) use ($userProgram) {
                $eligible = $org->eligible_programs;
                return empty($eligible) || in_array($userProgram, $eligible);
            });

        $scored = $orgs->map(function ($org) use ($userInterests, $userSkills, $userActivities, $userProgram) {
            [$score, $matchedTags] = $this->scoreOrg($org, $userInterests, $userSkills, $userActivities, $userProgram);
            return [
                'org'         => $org,
                'score'       => $score,
                'matchedTags' => $matchedTags,
            ];
        })
        ->sortByDesc('score')
        ->values();

        $matched   = $scored->filter(fn($i) => $i['score'] > 0)->take(10)->values();
        $displayed = $matched->isNotEmpty() ? $matched : $scored->take(10)->values();

        return response()->json([
            'recommendations' => $displayed->map(fn($item) => $this->formatOrg($item)),
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
                if (in_array($orgCat, $cats) || $this->partialMatch($orgCat, $cats)) {
                    $score += 3; $matchedTags[] = $interest; break;
                }
            }
        }

        foreach ($skills as $skill) {
            $cats = array_map('strtolower', self::SKILL_MAP[$skill] ?? []);
            foreach ($orgCategories as $orgCat) {
                if (in_array($orgCat, $cats) || $this->partialMatch($orgCat, $cats)) {
                    $score += 2; $matchedTags[] = $skill; break;
                }
            }
        }

        foreach ($activities as $activity) {
            $cats = array_map('strtolower', self::ACTIVITY_MAP[$activity] ?? []);
            foreach ($orgCategories as $orgCat) {
                if (in_array($orgCat, $cats) || $this->partialMatch($orgCat, $cats)) {
                    $score += 1; $matchedTags[] = $activity; break;
                }
            }
        }

        $programCats = array_map('strtolower', self::PROGRAM_MAP[$program ?? ''] ?? []);
        if (!empty($programCats)) {
            foreach ($orgCategories as $orgCat) {
                if (in_array($orgCat, $programCats) || $this->partialMatch($orgCat, $programCats)) {
                    $score += 1; break;
                }
            }
        }

        return [$score, array_unique($matchedTags)];
    }

    private function partialMatch(string $category, array $cats): bool
    {
        foreach ($cats as $c) {
            if (str_contains($category, $c) || str_contains($c, $category)) {
                return true;
            }
        }
        return false;
    }

    private function formatOrg(array $item): array
    {
        $org  = $item['org'];
        $tags = array_values($item['matchedTags']);
        $score = $item['score'];

        // Build match reason from tags
        if (!empty($tags)) {
            $reason = 'Matches your ' . implode(', ', array_slice($tags, 0, 3));
        } else {
            $reason = 'Explore this organization';
        }

        // Match percentage capped at 100 (max possible = 3*3 + 3*2 + 3*1 + 1 = 16)
        $matchPct = min(100, (int) round(($score / 16) * 100));

        return [
            'id'           => $org->id,
            'name'         => $org->org_name,
            'category'     => $org->category,
            'president'    => $org->president,
            'mission'      => $org->mission,
            'logo'         => $org->logo ? url('storage/' . $org->logo) : null,
            'score'        => $score,
            'match_pct'    => $matchPct,
            'match_reason' => $reason,
            'match_tags'   => $tags,
        ];
    }
}
