<?php

namespace App\Services;

use App\Models\Dealer;
use App\Models\Setting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * EVC WinOLS reseller link.
 *
 * Surrey Tuning acts as a WinOLS reseller; each dealer is an EVC customer with
 * their own EVC user number. When a dealer buys EVC credits in the portal, this
 * service allocates those credits to their real EVC account under the reseller
 * account (credentials in Settings: evc_account_number / evc_password).
 *
 * The exact EVC reseller API is not yet wired in. Until EVC_API_URL is set, an
 * allocation is *logged for manual action* (the owner adds the credits on the
 * EVC website) rather than failing — so a dealer's purchase always completes and
 * the portal balance is always correct. Once EVC's reseller API spec is known,
 * fill in performAllocation() and set EVC_API_URL, and the flow becomes fully
 * automatic with no other code changes.
 */
class EvcService
{
    /**
     * Whether the automatic EVC reseller API is configured.
     */
    public function isConfigured(): bool
    {
        return filled(config('services.evc.api_url'));
    }

    /**
     * Allocate EVC credits to a dealer's EVC account.
     *
     * @return array{status: 'ok'|'manual'|'skipped'|'error', reference?: ?string, reason?: string}
     */
    public function allocateCredits(Dealer $dealer, float $credits, string $reason = ''): array
    {
        $evcNumber = $dealer->evc_number;

        if (blank($evcNumber)) {
            Log::warning('EVC allocation skipped: dealer has no EVC number', [
                'dealer_id' => $dealer->id,
                'credits' => $credits,
            ]);

            return ['status' => 'skipped', 'reason' => 'no_evc_number'];
        }

        if (! $this->isConfigured()) {
            // No reseller API wired yet — record it so the owner can add the
            // credits manually on the EVC website. Search logs for this tag.
            Log::notice('EVC_MANUAL_ALLOCATION_REQUIRED', [
                'dealer_id' => $dealer->id,
                'evc_number' => $evcNumber,
                'credits' => $credits,
                'reason' => $reason,
            ]);

            return ['status' => 'manual', 'reason' => 'api_not_configured'];
        }

        return $this->performAllocation($evcNumber, $credits, $reason);
    }

    /**
     * The real EVC reseller API call. The endpoint, payload shape and auth below
     * are a best-effort placeholder — adjust to EVC's documented reseller API
     * (see evc.de ResellerEn.pdf) once available. Authenticates as the reseller
     * using the credentials stored in Settings.
     *
     * @return array{status: 'ok'|'error', reference?: ?string, reason?: string}
     */
    private function performAllocation(string $evcNumber, float $credits, string $reason): array
    {
        $settings = Setting::get();

        $url = rtrim((string) config('services.evc.api_url'), '/')
            .'/'.ltrim((string) config('services.evc.allocate_path'), '/');

        try {
            $response = Http::asForm()
                ->withBasicAuth(
                    (string) $settings->evc_account_number,
                    (string) $settings->evc_password,
                )
                ->timeout(20)
                ->post($url, [
                    'client' => $evcNumber,
                    'credits' => $credits,
                    'reason' => $reason,
                ]);

            if ($response->successful()) {
                return [
                    'status' => 'ok',
                    'reference' => $response->json('reference'),
                ];
            }

            Log::error('EVC allocation failed', [
                'evc_number' => $evcNumber,
                'credits' => $credits,
                'http_status' => $response->status(),
                'body' => $response->body(),
            ]);

            return ['status' => 'error', 'reason' => 'http_'.$response->status()];
        } catch (\Throwable $e) {
            Log::error('EVC allocation exception', [
                'evc_number' => $evcNumber,
                'credits' => $credits,
                'message' => $e->getMessage(),
            ]);
            report($e);

            return ['status' => 'error', 'reason' => 'exception'];
        }
    }
}
