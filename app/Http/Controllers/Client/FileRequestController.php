<?php

namespace App\Http\Controllers\Client;

use App\Enums\FileRequestStatus;
use App\Http\Controllers\Controller;
use App\Models\FileRequest;
use App\Models\Invoice;
use App\Models\Setting;
use Illuminate\Http\Request;

class FileRequestController extends Controller
{
    public function index(Request $request)
    {
        $query = FileRequest::where('dealer_id', $request->user()->dealer_id)->active();

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $fileRequests = $query->latest()->get();

        return view('client.file-requests.index', [
            'fileRequests' => $fileRequests,
            'statuses' => FileRequestStatus::cases(),
            'view' => $request->input('view', 'table'),
        ]);
    }

    public function archive(Request $request)
    {
        $query = FileRequest::where('dealer_id', $request->user()->dealer_id)->archived(30);

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('request_number', 'like', "%{$search}%")
                    ->orWhere('registration', 'like', "%{$search}%")
                    ->orWhere('make', 'like', "%{$search}%")
                    ->orWhere('model', 'like', "%{$search}%");
            });
        }

        $fileRequests = $query->latest()->get();

        return view('client.file-requests.archive', [
            'fileRequests' => $fileRequests,
        ]);
    }

    public function show(Request $request, FileRequest $fileRequest)
    {
        $this->authorize('view', $fileRequest);

        $fileRequest->load([
            'dealer',
            'fileStage',
            'tool',
            'fileRequestOptions.fileOption',
            'attachments.uploader',
            'messages' => function ($query) {
                $query->where('is_internal', false)->with('sender')->orderBy('created_at');
            },
        ]);

        $invoices = Invoice::where('related_type', FileRequest::class)
            ->where('related_id', $fileRequest->id)
            ->latest()
            ->get();

        return view('client.file-requests.show', [
            'fileRequest' => $fileRequest,
            'invoices' => $invoices,
            'whatsappNumber' => Setting::get()->whatsapp_business_number,
        ]);
    }
}
