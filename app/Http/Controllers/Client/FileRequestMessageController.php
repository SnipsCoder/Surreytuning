<?php

namespace App\Http\Controllers\Client;

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
        ]);

        $message = FileRequestMessage::create([
            'file_request_id' => $fileRequest->id,
            'sender_user_id' => $request->user()->id,
            'type' => MessageType::Message,
            'body' => $validated['body'],
            'is_internal' => false,
            'is_system' => false,
        ]);

        event(new NewMessagePosted($message));

        return back()->with('success', 'Message sent.');
    }
}
