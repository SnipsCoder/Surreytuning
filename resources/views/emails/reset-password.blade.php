@php($brandColour = \App\Models\Setting::brandColour())
<x-emails.layout :subject="$subject">
    <h2>Reset Your Password</h2>
    <p>Hello {{ $name }},</p>
    <p>We received a request to reset the password for your account. Click the button below to choose a new password.</p>
    <a href="{{ $url }}" class="btn">Reset Password</a>
    <p style="margin-top:24px;">This password reset link will expire in {{ $expireMinutes }} minutes.</p>
    <p>If you did not request a password reset, no further action is required — you can safely ignore this email.</p>
    <p style="margin-top:24px;font-size:13px;color:#6b7280;">
        If the button above does not work, copy and paste the following link into your browser:<br>
        <a href="{{ $url }}" style="color:{{ $brandColour }};word-break:break-all;">{{ $url }}</a>
    </p>
</x-emails.layout>
