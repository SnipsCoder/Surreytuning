<?php

namespace App\Http\Controllers\Client;

use App\Enums\AttachmentType;
use App\Enums\FileRequestStatus;
use App\Enums\MessageType;
use App\Events\FileRequestSubmitted;
use App\Exceptions\InsufficientCreditsException;
use App\Http\Controllers\Controller;
use App\Http\Requests\FileRequest\StoreFileRequestRequest;
use App\Models\DtcCode;
use App\Models\FileOption;
use App\Models\FileRequest;
use App\Models\FileRequestAttachment;
use App\Models\FileRequestMessage;
use App\Models\FileRequestOption;
use App\Models\FileStage;
use App\Models\Setting;
use App\Models\TuningTool;
use App\Services\CreditService;
use App\Services\FileStorageService;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FileUploadController extends Controller
{
    public function create(Request $request)
    {
        $fileStages = FileStage::where('is_active', true)->orderBy('sort_order')->get();
        $tools = TuningTool::where('is_active', true)->orderBy('sort_order')->get();
        $fileOptions = FileOption::where('is_active', true)->orderBy('sort_order')->get();

        return view('client.upload.create', [
            'fileStages' => $fileStages,
            'tools' => $tools,
            'fileOptions' => $fileOptions,
        ]);
    }

    public function store(StoreFileRequestRequest $request, FileStorageService $fileStorageService, CreditService $creditService)
    {
        $dealer = $request->user()->dealer;

        // Work out what this job costs in file credits: the selected stage plus
        // every selected option, each discounted for this dealer, plus VAT where
        // applicable. File credits are a £-denominated wallet (1 credit = £1), so
        // we deduct the gross total — exactly like a product purchase.
        $stage = FileStage::find($request->input('file_stage_id'));
        $options = FileOption::whereIn('id', $request->input('file_options', []))->get();

        $vatRate = (float) Setting::get()->vat_rate;
        $netTotal = 0.0;
        $vatTotal = 0.0;

        $addLine = function ($priceNet, bool $vatApplicable) use ($dealer, $vatRate, &$netTotal, &$vatTotal) {
            $lineNet = $dealer->discountedPrice((float) $priceNet);
            $netTotal += $lineNet;

            if ($vatApplicable) {
                $vatTotal += round($lineNet * $vatRate / 100, 2);
            }
        };

        if ($stage) {
            $addLine($stage->price_net, (bool) $stage->vat_applicable);
        }

        foreach ($options as $option) {
            $addLine($option->price_net, (bool) $option->vat_applicable);
        }

        $netTotal = round($netTotal, 2);
        $vatTotal = round($vatTotal, 2);
        $grossTotal = round($netTotal + $vatTotal, 2);

        // Gate the submission: a dealer cannot start a chargeable job they cannot
        // pay for. Zero-priced stages remain free to submit.
        if ($grossTotal > 0 && ! $creditService->hasSufficientFileCredits($dealer, $grossTotal)) {
            return back()
                ->withInput()
                ->with('error', sprintf(
                    'Insufficient file credits. This job costs %s credits but your balance is %s. Please top up before submitting.',
                    number_format($grossTotal, 2),
                    number_format((float) $dealer->file_credit_balance, 2),
                ));
        }

        $makeFileRequest = fn () => DB::transaction(function () use ($request, $dealer, $netTotal, $vatTotal, $grossTotal) {
            $requestNumber = (int) (DB::table('file_requests')->lockForUpdate()->max('request_number') ?? 0) + 1;

            $fileRequest = FileRequest::create([
                'request_number' => $requestNumber,
                'dealer_id' => $dealer->id,
                'submitted_by_user_id' => $request->user()->id,
                'status' => FileRequestStatus::Pending,
                'registration' => $request->input('registration'),
                'vin_number' => $request->input('vin_number'),
                'make' => $request->input('make'),
                'model' => $request->input('model'),
                'engine' => $request->input('engine'),
                'engine_code' => $request->input('engine_code'),
                'year' => $request->input('year'),
                'fuel' => $request->input('fuel'),
                'transmission' => $request->input('transmission'),
                'bhp_before' => $request->input('bhp_before'),
                'file_type' => $request->input('file_type'),
                'torque_before_nm' => $request->input('torque_before_nm'),
                'ecu_model_no' => $request->input('ecu_model_no'),
                'file_stage_id' => $request->input('file_stage_id'),
                'tool_id' => $request->input('tool_id'),
                'client_notes' => $request->input('client_notes'),
                'price_net' => $netTotal,
                'vat_amount' => $vatTotal,
                'price_gross' => $grossTotal,
            ]);

            foreach ($request->input('file_options', []) as $fileOptionId) {
                $fileOption = FileOption::find($fileOptionId);

                if ($fileOption) {
                    FileRequestOption::create([
                        'file_request_id' => $fileRequest->id,
                        'file_option_id' => $fileOption->id,
                        'price_net' => $fileOption->price_net,
                    ]);
                }
            }

            foreach ($request->input('dtc_codes', []) as $code) {
                DtcCode::create([
                    'file_request_id' => $fileRequest->id,
                    'code' => $code,
                ]);
            }

            FileRequestMessage::create([
                'file_request_id' => $fileRequest->id,
                'sender_user_id' => $request->user()->id,
                'type' => MessageType::System,
                'body' => 'File request submitted by '.$request->user()->first_name.' '.$request->user()->last_name.'.',
                'is_internal' => false,
                'is_system' => true,
            ]);

            return $fileRequest;
        });

        // Two concurrent submissions can read the same MAX(request_number) before
        // either commits; the UNIQUE constraint then rejects the loser. Retry a
        // few times so the second request simply picks the next number instead of
        // 500-ing. (A row lock on an aggregate does not reliably gap-lock in MySQL.)
        $fileRequest = $this->createWithUniqueRetry($makeFileRequest);

        try {
            $stored = $fileStorageService->storeFile(
                $request->file('file'),
                (string) $dealer->id,
                (string) $fileRequest->request_number,
                AttachmentType::Original
            );

            FileRequestAttachment::create([
                'file_request_id' => $fileRequest->id,
                'uploader_user_id' => $request->user()->id,
                'attachment_type' => AttachmentType::Original,
                'original_filename' => $stored['original_filename'],
                'stored_filename' => $stored['stored_filename'],
                'file_path' => $stored['path'],
                'file_size_bytes' => $stored['file_size_bytes'],
                'mime_type' => $stored['mime_type'],
            ]);
        } catch (\InvalidArgumentException $e) {
            // The file failed a content/size check in FileStorageService. Roll
            // back the just-created request and show the dealer a friendly error
            // on the file field instead of a 500. No credits were taken yet.
            $fileRequest->delete();

            return back()
                ->withInput()
                ->withErrors(['file' => $e->getMessage()]);
        } catch (\Throwable $e) {
            $fileRequest->delete();

            throw $e;
        }

        // Now the file is safely stored, charge the dealer's file credits. The
        // deduction re-checks the balance under a row lock, so it is the real
        // guard against a race with another concurrent spend between the gate
        // check above and here.
        if ($grossTotal > 0) {
            try {
                $creditService->deductFileCredits(
                    $dealer,
                    $grossTotal,
                    'File request '.$fileRequest->request_number_formatted,
                    $request->user(),
                    $fileRequest->id,
                );

                $fileRequest->update(['is_charged' => true]);
            } catch (InsufficientCreditsException $e) {
                $fileRequest->delete();

                return back()
                    ->withInput()
                    ->with('error', 'Your file credit balance changed and is no longer sufficient for this job. Please top up and try again.');
            }
        }

        event(new FileRequestSubmitted($fileRequest));

        return redirect()
            ->route('client.file-requests.show', $fileRequest)
            ->with('success', 'File request submitted successfully.');
    }

    /**
     * Run a create-callback, retrying on a duplicate-key violation so a lost
     * race on request_number picks the next value instead of erroring.
     */
    private function createWithUniqueRetry(callable $callback, int $maxAttempts = 3): FileRequest
    {
        for ($attempt = 1; ; $attempt++) {
            try {
                return $callback();
            } catch (QueryException $e) {
                // SQLSTATE 23000 / 23505 = integrity constraint (duplicate key).
                $isDuplicate = in_array((string) $e->getCode(), ['23000', '23505'], true);

                if ($attempt >= $maxAttempts || ! $isDuplicate) {
                    throw $e;
                }
            }
        }
    }
}
