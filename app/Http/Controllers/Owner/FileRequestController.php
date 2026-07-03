<?php

namespace App\Http\Controllers\Owner;

use App\Enums\AttachmentType;
use App\Enums\FileRequestStatus;
use App\Enums\InvoiceType;
use App\Enums\MessageType;
use App\Events\FileRequestStatusChanged;
use App\Events\NewMessagePosted;
use App\Http\Controllers\Controller;
use App\Http\Requests\Owner\AddChargeRequest;
use App\Http\Requests\Owner\AddCreditRequest;
use App\Http\Requests\Owner\UpdateFileRequestStatusRequest;
use App\Models\AuditLog;
use App\Models\FileRequest;
use App\Models\FileRequestAttachment;
use App\Models\FileRequestMessage;
use App\Models\FileStage;
use App\Models\User;
use App\Services\CreditService;
use App\Services\FileStorageService;
use App\Services\InvoiceService;
use Illuminate\Http\Request;

class FileRequestController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', FileRequest::class);

        $fileRequests = FileRequest::query()
            ->active()
            ->with(['dealer', 'submittedBy', 'assignedTechnician', 'fileStage'])
            ->when($request->string('search')->toString(), function ($query, $search) {
                $query->where(function ($query) use ($search) {
                    $query->where('request_number', 'like', "%{$search}%")
                        ->orWhere('make', 'like', "%{$search}%")
                        ->orWhere('model', 'like', "%{$search}%")
                        ->orWhereHas('dealer', function ($query) use ($search) {
                            $query->where('company_name', 'like', "%{$search}%");
                        });
                });
            })
            ->when($request->string('status')->toString(), fn ($query, $status) => $query->where('status', $status))
            ->when($request->string('dealer_id')->toString(), fn ($query, $dealerId) => $query->where('dealer_id', $dealerId))
            ->when($request->string('assigned_technician_id')->toString(), fn ($query, $id) => $query->where('assigned_technician_id', $id))
            ->latest()
            ->paginate(25)
            ->withQueryString();

        $stages = FileStage::orderBy('sort_order')->get();
        $technicians = User::ownerTeam()->orderBy('first_name')->get();

        return view('owner.file-requests.index', [
            'fileRequests' => $fileRequests,
            'stages' => $stages,
            'technicians' => $technicians,
            'statuses' => FileRequestStatus::cases(),
        ]);
    }

    public function show(FileRequest $fileRequest)
    {
        $this->authorize('view', $fileRequest);

        $fileRequest->load([
            'dealer',
            'submittedBy',
            'assignedTechnician',
            'fileStage',
            'tool',
            'messages.sender',
            'attachments.uploader',
            'fileRequestOptions.fileOption',
            'dtcCodes',
        ]);

        $stages = FileStage::orderBy('sort_order')->get();
        $technicians = User::ownerTeam()->orderBy('first_name')->get();

        return view('owner.file-requests.show', [
            'fileRequest' => $fileRequest,
            'stages' => $stages,
            'technicians' => $technicians,
            'statuses' => FileRequestStatus::cases(),
        ]);
    }

    public function update(Request $request, FileRequest $fileRequest)
    {
        $this->authorize('view', $fileRequest);

        $validated = $request->validate([
            'file_stage_id' => ['nullable', 'exists:file_stages,id'],
            'assigned_technician_id' => ['nullable', 'exists:users,id'],
        ]);

        $fileRequest->update($validated);

        return back()->with('success', 'File request updated.');
    }

    public function archive(Request $request)
    {
        $this->authorize('viewAny', FileRequest::class);

        $fileRequests = FileRequest::query()
            ->archived()
            ->with(['dealer', 'submittedBy', 'assignedTechnician', 'fileStage'])
            ->when($request->string('search')->toString(), function ($query, $search) {
                $query->where(function ($query) use ($search) {
                    $query->where('request_number', 'like', "%{$search}%")
                        ->orWhere('make', 'like', "%{$search}%")
                        ->orWhere('model', 'like', "%{$search}%")
                        ->orWhereHas('dealer', function ($query) use ($search) {
                            $query->where('company_name', 'like', "%{$search}%");
                        });
                });
            })
            ->latest()
            ->paginate(25)
            ->withQueryString();

        return view('owner.file-requests.archive', [
            'fileRequests' => $fileRequests,
        ]);
    }

    public function updateStatus(UpdateFileRequestStatusRequest $request, FileRequest $fileRequest)
    {
        $this->authorize('view', $fileRequest);

        $status = FileRequestStatus::from($request->validated('status'));
        $oldStatus = $fileRequest->status;

        $fileRequest->update([
            'status' => $status,
            'closed_at' => $status === FileRequestStatus::Closed ? now() : $fileRequest->closed_at,
        ]);

        FileRequestMessage::create([
            'file_request_id' => $fileRequest->id,
            'sender_user_id' => $request->user()->id,
            'type' => MessageType::System,
            'body' => "Status changed to {$status->label()} by {$request->user()->full_name}",
            'is_system' => true,
        ]);

        FileRequestStatusChanged::dispatch($fileRequest, $oldStatus);

        return back()->with('success', 'Status updated.');
    }

    public function assign(Request $request, FileRequest $fileRequest)
    {
        $this->authorize('view', $fileRequest);

        $validated = $request->validate([
            'assigned_technician_id' => ['required', 'exists:users,id'],
        ]);

        $fileRequest->update($validated);

        return back()->with('success', 'Technician assigned.');
    }

    public function addCharge(AddChargeRequest $request, FileRequest $fileRequest, InvoiceService $invoiceService)
    {
        $this->authorize('addCharge', $fileRequest);

        $applyVat = $request->boolean('apply_vat');

        $invoice = $invoiceService->createInvoice(
            $fileRequest->dealer,
            $request->validated('description'),
            (float) $request->validated('amount_net'),
            InvoiceType::Manual,
            $request->user(),
            $fileRequest->id,
            FileRequest::class,
            $applyVat,
        );

        FileRequestMessage::create([
            'file_request_id' => $fileRequest->id,
            'sender_user_id' => $request->user()->id,
            'type' => MessageType::ChargeEvent,
            'body' => "Charge added: {$request->validated('description')} (£".number_format((float) $request->validated('amount_net'), 2).')',
            'is_internal' => false,
        ]);

        AuditLog::record(
            'file_request.charge_added',
            $request->user(),
            $fileRequest,
            (float) $request->validated('amount_net'),
            $request->validated('description'),
            ['invoice_id' => $invoice->id, 'apply_vat' => $applyVat],
        );

        return back()->with('success', 'Charge added.');
    }

    public function addCredit(AddCreditRequest $request, FileRequest $fileRequest, CreditService $creditService)
    {
        $this->authorize('addCredit', $fileRequest);

        $amount = (float) $request->validated('amount');
        $reason = $request->validated('reason');
        $dealer = $fileRequest->dealer;

        if ($request->validated('credit_type') === 'slave') {
            $creditService->addSlaveCredits($dealer, $amount, $reason, $request->user(), $fileRequest->id);
        } else {
            $creditService->addEvcCredits($dealer, $amount, $reason, $request->user());
        }

        FileRequestMessage::create([
            'file_request_id' => $fileRequest->id,
            'sender_user_id' => $request->user()->id,
            'type' => MessageType::CreditEvent,
            'body' => ucfirst($request->validated('credit_type'))." credit added: {$amount} ({$reason})",
            'is_internal' => false,
        ]);

        AuditLog::record(
            'file_request.credit_added',
            $request->user(),
            $fileRequest,
            $amount,
            $reason,
            ['credit_type' => $request->validated('credit_type'), 'dealer_id' => $dealer->id],
        );

        return back()->with('success', 'Credit added.');
    }

    public function void(Request $request, FileRequest $fileRequest)
    {
        $this->authorize('void', $fileRequest);

        $validated = $request->validate([
            'void_reason' => ['required', 'string', 'max:500'],
        ]);

        $fileRequest->update([
            'status' => FileRequestStatus::Void,
            'void_reason' => $validated['void_reason'],
            'closed_at' => now(),
        ]);

        FileRequestMessage::create([
            'file_request_id' => $fileRequest->id,
            'sender_user_id' => $request->user()->id,
            'type' => MessageType::System,
            'body' => "Job voided by {$request->user()->full_name}: {$validated['void_reason']}",
            'is_system' => true,
        ]);

        AuditLog::record(
            'file_request.voided',
            $request->user(),
            $fileRequest,
            null,
            $validated['void_reason'],
        );

        return back()->with('success', 'File request voided.');
    }

    public function respond(Request $request, FileRequest $fileRequest, FileStorageService $fileStorageService)
    {
        $this->authorize('respond', $fileRequest);

        $validated = $request->validate([
            'message' => ['nullable', 'string', 'max:5000', 'required_without:file'],
            'file' => ['nullable', 'file', 'max:51200', 'required_without:message'],
        ]);

        if ($request->hasFile('file')) {
            $stored = $fileStorageService->storeFile(
                $request->file('file'),
                (string) $fileRequest->dealer_id,
                (string) $fileRequest->request_number,
                AttachmentType::Returned,
            );

            FileRequestAttachment::create([
                'file_request_id' => $fileRequest->id,
                'uploader_user_id' => $request->user()->id,
                'attachment_type' => AttachmentType::Returned,
                'original_filename' => $stored['original_filename'],
                'stored_filename' => $stored['stored_filename'],
                'file_path' => $stored['path'],
                'file_size_bytes' => $stored['file_size_bytes'],
                'mime_type' => $stored['mime_type'],
            ]);
        }

        FileRequestMessage::create([
            'file_request_id' => $fileRequest->id,
            'sender_user_id' => $request->user()->id,
            'type' => MessageType::Message,
            'body' => $validated['message'] ?? null,
            'is_internal' => false,
        ]);

        $fileRequest->update(['status' => FileRequestStatus::Responded]);

        NewMessagePosted::dispatch($fileRequest->messages()->latest()->first());

        return back()->with('success', 'Response sent.');
    }
}
