<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Dealer;
use App\Services\DataSubjectService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class GdprExportCommand extends Command
{
    protected $signature = 'gdpr:export
        {dealer : The dealer id to export}
        {--disk= : Storage disk to write the export to (defaults to config gdpr.export_disk)}';

    protected $description = 'Export everything the tenant holds about a dealer data subject as JSON (GDPR data portability)';

    public function handle(DataSubjectService $service): int
    {
        $dealer = Dealer::withTrashed()->find($this->argument('dealer'));

        if (! $dealer) {
            $this->error("No dealer found with id [{$this->argument('dealer')}].");

            return self::FAILURE;
        }

        $payload = $service->export($dealer);

        $disk = $this->option('disk') ?: config('gdpr.export_disk');
        $path = config('gdpr.export_path')."/dealer-{$dealer->id}-".now()->format('Ymd-His').'.json';

        Storage::disk($disk)->put(
            $path,
            json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)
        );

        $this->info("Exported dealer [{$dealer->id}] to [{$disk}://{$path}].");
        $this->line(sprintf(
            'Included: %d user(s), %d application(s), %d invoice(s), %d file request(s).',
            count($payload['users']),
            count($payload['applications']),
            count($payload['invoices']),
            count($payload['file_requests']),
        ));

        return self::SUCCESS;
    }
}
