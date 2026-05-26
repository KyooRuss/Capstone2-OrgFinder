<?php

namespace App\Console\Commands;

use App\Models\Organization;
use App\Services\GeminiEmbeddingService;
use Illuminate\Console\Command;

class GenerateOrgEmbeddings extends Command
{
    protected $signature   = 'orgs:embed {--force : Re-generate even if embedding already exists}';
    protected $description = 'Generate Gemini embeddings for all organizations';

    public function handle(GeminiEmbeddingService $gemini): int
    {
        $orgs = Organization::whereNull('deleted_at')
            ->when(!$this->option('force'), fn($q) => $q->whereNull('embedding'))
            ->get();

        if ($orgs->isEmpty()) {
            $this->info('All organizations already have embeddings. Use --force to regenerate.');
            return 0;
        }

        $bar = $this->output->createProgressBar($orgs->count());
        $bar->start();

        foreach ($orgs as $org) {
            $text   = $gemini->orgToText($org);
            $vector = $gemini->embed($text);

            if ($vector) {
                $org->update(['embedding' => $vector]);
            } else {
                $this->newLine();
                $this->warn("Failed to embed: {$org->org_name}");
            }

            $bar->advance();
            usleep(200000); // 0.2s — stay within free-tier rate limit
        }

        $bar->finish();
        $this->newLine();
        $this->info('Done!');

        return 0;
    }
}
