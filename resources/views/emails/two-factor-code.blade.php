<x-mail::message>
# Your verification code

Use the code below to complete your sign-in. It expires in **10 minutes**.

<x-mail::panel>
# {{ $mailable->code }}
</x-mail::panel>

If you did not attempt to sign in, you can safely ignore this email.

Thanks,
{{ config('app.name') }}
</x-mail::message>
