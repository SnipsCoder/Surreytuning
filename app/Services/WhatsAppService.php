<?php

namespace App\Services;

use App\Models\FileRequest;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Sends WhatsApp notifications via the Meta WhatsApp Cloud API.
 *
 * WhatsApp does not permit free-text business-initiated messages, so the "job
 * complete" alert is sent as a pre-approved utility template with a single body
 * parameter — the job reference. The template body should read e.g.
 * "Job {{1}} is complete." so the recipient sees "Job STS-2026-00013 is complete."
 *
 * Credentials live (encrypted) in Settings. Until they are configured, a send is
 * logged and skipped rather than failing — so nothing breaks before the tenant's
 * Meta WhatsApp Business account is set up.
 */
class WhatsAppService
{
    private const GRAPH_VERSION = 'v21.0';

    public function isConfigured(): bool
    {
        $settings = Setting::get();

        return filled($settings->whatsapp_phone_number_id)
            && filled($settings->whatsapp_access_token)
            && filled($settings->whatsapp_template_name);
    }

    /**
     * Notify a recipient that their file/job is complete.
     *
     * @return array{status: 'ok'|'skipped'|'manual'|'error', reason?: string}
     */
    public function sendJobComplete(User $recipient, FileRequest $fileRequest): array
    {
        $to = preg_replace('/\D/', '', (string) $recipient->whatsapp_number);

        if (blank($to)) {
            return ['status' => 'skipped', 'reason' => 'no_whatsapp_number'];
        }

        if (! $this->isConfigured()) {
            Log::notice('WHATSAPP_NOT_CONFIGURED', [
                'to' => $to,
                'file_request' => $fileRequest->request_number_formatted,
            ]);

            return ['status' => 'manual', 'reason' => 'not_configured'];
        }

        $settings = Setting::get();

        try {
            $response = Http::withToken((string) $settings->whatsapp_access_token)
                ->timeout(20)
                ->post(
                    'https://graph.facebook.com/'.self::GRAPH_VERSION."/{$settings->whatsapp_phone_number_id}/messages",
                    [
                        'messaging_product' => 'whatsapp',
                        'to' => $to,
                        'type' => 'template',
                        'template' => [
                            'name' => $settings->whatsapp_template_name,
                            'language' => ['code' => $settings->whatsapp_template_language ?: 'en_GB'],
                            'components' => [[
                                'type' => 'body',
                                'parameters' => [
                                    ['type' => 'text', 'text' => $fileRequest->request_number_formatted],
                                ],
                            ]],
                        ],
                    ]
                );

            if ($response->successful()) {
                return ['status' => 'ok'];
            }

            Log::error('WhatsApp send failed', [
                'to' => $to,
                'http_status' => $response->status(),
                'body' => $response->body(),
            ]);

            return ['status' => 'error', 'reason' => 'http_'.$response->status()];
        } catch (\Throwable $e) {
            Log::error('WhatsApp send exception', ['to' => $to, 'message' => $e->getMessage()]);
            report($e);

            return ['status' => 'error', 'reason' => 'exception'];
        }
    }
}
