<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\AuditLog;
use App\Models\Dealer;
use App\Models\DealerApplication;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Data-subject-level GDPR operations for an individual dealer (a customer
 * business) and its users, within the current tenant.
 *
 * This is deliberately separate from the tenant-level `tenants:delete`
 * offboarding path: that erases an entire tuning business (a whole tenant DB);
 * this handles one data subject *inside* a live tenant.
 */
class DataSubjectService
{
    /**
     * Build a structured, machine-readable export of everything the tenant
     * holds about a dealer data subject (GDPR Art. 20 — data portability).
     *
     * Secrets (password hashes, 2FA seeds, OTP codes) are never included.
     *
     * @return array<string, mixed>
     */
    public function export(Dealer $dealer): array
    {
        $dealer->loadMissing([
            'users',
            'fileRequests',
            'invoices',
            'slaveCreditTransactions',
            'evcCreditTransactions',
        ]);

        $emails = $dealer->users->pluck('email')->filter()->values();

        return [
            'exported_at' => now()->toIso8601String(),
            'dealer' => [
                'id' => $dealer->id,
                'company_name' => $dealer->company_name,
                'country' => $dealer->country,
                'invoice_address' => $dealer->invoice_address,
                'status' => $dealer->status?->value,
                'slave_credit_balance' => $dealer->slave_credit_balance,
                'evc_credit_balance' => $dealer->evc_credit_balance,
                'terms_accepted_at' => $dealer->terms_accepted_at?->toIso8601String(),
                'notes' => $dealer->notes,
                'created_at' => $dealer->created_at?->toIso8601String(),
            ],
            'users' => $dealer->users->map(fn (User $user) => [
                'id' => $user->id,
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'email' => $user->email,
                'role' => $user->role?->value,
                'is_primary_contact' => $user->is_primary_contact,
                'whatsapp_number' => $user->whatsapp_number,
                'last_login_at' => $user->last_login_at?->toIso8601String(),
                'created_at' => $user->created_at?->toIso8601String(),
            ])->all(),
            'applications' => DealerApplication::query()
                ->whereIn('email', $emails)
                ->get()
                ->map(fn (DealerApplication $application) => [
                    'id' => $application->id,
                    'company_name' => $application->company_name,
                    'contact_name' => $application->contact_name,
                    'email' => $application->email,
                    'phone' => $application->phone,
                    'country' => $application->country,
                    'message' => $application->message,
                    'status' => $application->status?->value,
                    'terms_accepted_at' => $application->terms_accepted_at?->toIso8601String(),
                    'created_at' => $application->created_at?->toIso8601String(),
                ])->all(),
            // Transactional / financial records — the subject's own data, no
            // secrets. Exported wholesale so the package stays complete even as
            // these schemas evolve.
            'file_requests' => $dealer->fileRequests->toArray(),
            'invoices' => $dealer->invoices->toArray(),
            'slave_credit_transactions' => $dealer->slaveCreditTransactions->toArray(),
            'evc_credit_transactions' => $dealer->evcCreditTransactions->toArray(),
        ];
    }

    /**
     * Erase a dealer data subject (GDPR Art. 17 — right to erasure).
     *
     * Personal identifiers are anonymised in place and users are soft-deleted
     * and locked out. Financial records (invoices, credit ledger) are retained
     * for the statutory accounting period — the standard GDPR carve-out — but
     * are severed from any personal identity by anonymising the dealer they
     * hang off. The action is written to the audit log for accountability.
     *
     * @return array<string, int> counts of what was anonymised
     */
    public function erase(Dealer $dealer, ?User $actor = null, ?string $reason = null): array
    {
        return DB::transaction(function () use ($dealer, $actor, $reason) {
            $dealer->loadMissing('users');

            $emails = $dealer->users->pluck('email')->filter()->values();

            $applicationCount = DealerApplication::query()
                ->whereIn('email', $emails)
                ->get()
                ->each(function (DealerApplication $application) {
                    $application->forceFill([
                        'company_name' => 'Erased (GDPR)',
                        'contact_name' => 'Erased',
                        'email' => $this->placeholderEmail('application', $application->id),
                        'phone' => null,
                        'message' => null,
                    ])->save();
                })->count();

            $userCount = $dealer->users->each(function (User $user) {
                $user->forceFill([
                    'first_name' => 'Erased',
                    'last_name' => 'User',
                    'email' => $this->placeholderEmail('user', $user->id),
                    'password' => Hash::make(Str::random(64)),
                    'avatar' => null,
                    'whatsapp_number' => null,
                    'two_factor_method' => null,
                    'two_factor_secret' => null,
                    'two_factor_confirmed_at' => null,
                    'two_factor_recovery_codes' => null,
                    'email_otp_code' => null,
                    'email_otp_expires_at' => null,
                    'remember_token' => null,
                ])->save();

                $user->delete();
            })->count();

            $dealer->forceFill([
                'company_name' => 'Erased dealer #'.$dealer->id,
                'invoice_address' => null,
                'notes' => null,
            ])->save();

            $dealer->delete();

            $counts = [
                'dealers' => 1,
                'users' => $userCount,
                'applications' => $applicationCount,
            ];

            AuditLog::record('gdpr_erased', $actor, $dealer, null, $reason, $counts);

            return $counts;
        });
    }

    private function placeholderEmail(string $prefix, int|string $id): string
    {
        return "erased-{$prefix}-{$id}@gdpr.invalid";
    }
}
