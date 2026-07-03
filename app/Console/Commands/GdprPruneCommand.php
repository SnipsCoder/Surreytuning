<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Enums\ApplicationStatus;
use App\Models\DealerApplication;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class GdprPruneCommand extends Command
{
    protected $signature = 'gdpr:prune {--dry-run : Report what would be pruned without deleting anything}';

    protected $description = 'Apply the data-retention policy: remove personal data the tenant no longer needs (GDPR storage limitation)';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');

        // 1. Rejected dealer applications that never became a customer.
        $rejectedDays = (int) config('gdpr.retention.rejected_applications_days');
        $rejectedQuery = DealerApplication::query()
            ->where('status', ApplicationStatus::Rejected)
            ->where('created_at', '<', now()->subDays($rejectedDays));

        $rejectedCount = $rejectedQuery->count();

        // 2. Expired one-time email 2FA codes left sitting on user rows.
        $staleOtpQuery = User::query()
            ->whereNotNull('email_otp_code')
            ->where('email_otp_expires_at', '<', now());

        $staleOtpCount = $staleOtpQuery->count();

        if ($dryRun) {
            $this->info('Dry run — no data deleted.');
            $this->line("Rejected applications older than {$rejectedDays} days: {$rejectedCount}");
            $this->line("Expired email OTP codes to clear: {$staleOtpCount}");

            return self::SUCCESS;
        }

        DB::transaction(function () use ($rejectedQuery, $staleOtpQuery) {
            $rejectedQuery->delete();

            $staleOtpQuery->update([
                'email_otp_code' => null,
                'email_otp_expires_at' => null,
            ]);
        });

        $this->info("Pruned {$rejectedCount} rejected application(s) and cleared {$staleOtpCount} expired OTP code(s).");

        return self::SUCCESS;
    }
}
