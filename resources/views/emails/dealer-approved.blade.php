<x-emails.layout subject="Welcome to Surrey Tuning Services — Set Your Password">
    <h2>Welcome to Surrey Tuning Services</h2>
    <p>Hi {{ $application->contact_name }},</p>
    <p>Great news — your dealer account application for <strong>{{ $application->company_name }}</strong> has been approved. You can now access the Surrey Tuning Services dealer portal.</p>
    <p>Please click the button below to set your password and activate your account. This link will expire in 60 minutes.</p>
    <a href="{{ $resetUrl }}" class="btn">Set Your Password</a>
    <p style="margin-top: 20px; font-size: 13px; color: #6b7280;">If you didn't apply for an account, please ignore this email.</p>
</x-emails.layout>
