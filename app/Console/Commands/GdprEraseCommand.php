<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Dealer;
use App\Services\DataSubjectService;
use Illuminate\Console\Command;
use Throwable;

class GdprEraseCommand extends Command
{
    protected $signature = 'gdpr:erase
        {dealer : The dealer id to erase}
        {--reason= : Reason recorded in the audit log}
        {--force : Skip the interactive confirmation prompt}';

    protected $description = 'Erase a dealer data subject: anonymise personal data, lock out users, retain financial records (GDPR right to erasure)';

    public function handle(DataSubjectService $service): int
    {
        $dealer = Dealer::find($this->argument('dealer'));

        if (! $dealer) {
            $this->error("No dealer found with id [{$this->argument('dealer')}].");

            return self::FAILURE;
        }

        $this->warn("This will PERMANENTLY anonymise dealer [{$dealer->id}] ({$dealer->company_name}) and all its users.");
        $this->warn('Financial records (invoices, credit ledger) are retained for accounting but severed from any identity. This is irreversible.');

        if (! $this->option('force') && ! $this->confirm("Erase dealer [{$dealer->id}]?")) {
            $this->info('Aborted. No changes made.');

            return self::SUCCESS;
        }

        try {
            $counts = $service->erase(
                $dealer,
                null,
                $this->option('reason') ?: 'Data-subject erasure request',
            );
        } catch (Throwable $e) {
            $this->error("Erasure failed: {$e->getMessage()}");

            report($e);

            return self::FAILURE;
        }

        $this->info(sprintf(
            'Erased dealer [%s]: anonymised %d user(s) and %d application(s). Recorded in the audit log.',
            $dealer->id,
            $counts['users'],
            $counts['applications'],
        ));

        return self::SUCCESS;
    }
}
