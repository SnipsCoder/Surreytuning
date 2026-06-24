<?php

namespace App\Http\Controllers\Owner;

use App\Enums\MessageType;
use App\Events\NewMessagePosted;
use App\Http\Controllers\Controller;
use App\Models\FileRequest;
use App\Models\FileRequestMessage;
use Illuminate\Http\Request;

class FileRequestMessageController extends Controller
{
    public function store(Request $request, FileRequest $fileRequest)
    {
        $this->authorize('view', $fileRequest);

        $validated = $request->validate([
            'body' => ['required', 'string', 'max:5000'],
            'is_internal' => ['boolean'],
        ]);

        $isInternal = $request->boolean('is_internal');

        if ($isInternal) {
            $this->authorize('viewInternalNotes', FileRequest::class);
        }

        $message = FileRequestMessage::create([
            'file_request_id' => $fileRequest->id,
            'sender_user_id' => $request->user()->id,
            'type' => $isInternal ? MessageType::InternalNote : MessageType::Message,
            'body' => $validated['body'],
            'is_internal' => $isInternal,
        ]);

        if (! $isInternal) {
            NewMessagePosted::dispatch($message);
        }

        return back()->with('success', 'Message sent.');
    }
}
