<?php

namespace App\Services;

use App\Models\Organization;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeminiEmbeddingService
{
    private string $apiKey;
    private string $endpoint = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash-lite:generateContent';

    public function __construct()
    {
        $this->apiKey = config('services.gemini.key');
    }

    /**
     * Ask Gemini to rank organizations for a student and return scored results.
     *
     * @param  Organization[] $orgs
     * @return array|null  Array of ['id' => int, 'match_pct' => int, 'match_reason' => string] or null on failure
     */
    public function rankOrgs(array $orgs, string $userProfile): ?array
    {
        $orgList = collect($orgs)->map(function ($org, $i) {
            $cats = implode(', ', (array) ($org->category ?? []));
            return ($i + 1) . ". [{$org->id}] {$org->org_name} — {$cats}. Mission: " . ($org->mission ?? 'N/A');
        })->implode("\n");

        $prompt = <<<PROMPT
You are a student organization recommender. Given a student profile and a list of organizations, score each organization by how genuinely relevant it is to the student's actual interests, skills, and preferred activities.

Student profile:
{$userProfile}

Organizations:
{$orgList}

Rules:
- Only give a high match_pct if the organization's focus directly aligns with the student's profile.
- Do NOT invent or stretch connections (e.g., do not connect Photography clubs to Gaming interests).
- match_reason must honestly describe WHY it is a match based on the student's actual profile fields.
- If an organization has no clear connection to the student's profile, give it a low match_pct (below 30).

Return ONLY a valid JSON array (no markdown, no explanation) like this:
[
  {"id": 5, "match_pct": 92, "match_reason": "Matches your interest in Technology and Programming"},
  {"id": 3, "match_pct": 75, "match_reason": "Aligns with your skills in Leadership"},
  {"id": 7, "match_pct": 10, "match_reason": "No strong connection to your profile"}
]

Include all organizations sorted from most to least relevant. match_pct must be 0-100.
PROMPT;

        try {
            $response = Http::timeout(20)->post("{$this->endpoint}?key={$this->apiKey}", [
                'contents' => [
                    ['parts' => [['text' => $prompt]]],
                ],
                'generationConfig' => [
                    'temperature'     => 0.1,
                    'responseMimeType' => 'application/json',
                ],
            ]);

            if ($response->failed()) {
                Log::warning('Gemini rank failed', ['status' => $response->status(), 'body' => $response->body()]);
                return null;
            }

            $text = $response->json('candidates.0.content.parts.0.text');
            return json_decode($text, true);
        } catch (\Throwable $e) {
            Log::error('Gemini rank exception', ['message' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Build a descriptive text string for a user's profile.
     */
    public function userToText(
        ?string $program,
        array   $interests,
        array   $skills,
        array   $activities
    ): string {
        $parts = array_filter([
            $program    ? "Program: {$program}"                          : null,
            $interests  ? 'Interests: '  . implode(', ', $interests)    : null,
            $skills     ? 'Skills: '     . implode(', ', $skills)       : null,
            $activities ? 'Activities: ' . implode(', ', $activities)   : null,
        ]);

        return implode('. ', $parts);
    }

    /**
     * Build a descriptive text string for an organization.
     */
    public function orgToText(Organization $org): string
    {
        $categories = implode(', ', (array) ($org->category ?? []));
        $parts = array_filter([
            $org->org_name,
            $org->mission,
            $categories ? "Categories: {$categories}" : null,
        ]);

        return implode('. ', $parts);
    }
}
