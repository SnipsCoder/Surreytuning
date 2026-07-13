@extends('layouts.legal')

@section('heading', 'Privacy Policy')

@section('content')
    <p class="text-gray-400">Last updated {{ now()->format('j F Y') }}.</p>

    <p>
        {{ \App\Models\Setting::brandName() }} ("we", "us") is committed to protecting the
        personal data of the dealers and users who use this portal. This policy explains what we
        collect, why we hold it, and the rights you have over it.
    </p>

    <h2 class="text-lg font-semibold text-white pt-2">What we collect</h2>
    <ul class="list-disc pl-5 space-y-1">
        <li>Account details: your name, email address, and contact number.</li>
        <li>Business details: your company name, country, and invoice address.</li>
        <li>Service data: file tuning requests, messages, invoices, and credit transactions.</li>
        <li>Security data: authentication and two-factor records, and login timestamps.</li>
    </ul>

    <h2 class="text-lg font-semibold text-white pt-2">Why we hold it</h2>
    <p>
        We process this data to operate your account, deliver the tuning services you request,
        raise invoices, and meet our legal and accounting obligations.
    </p>

    <h2 class="text-lg font-semibold text-white pt-2">How long we keep it</h2>
    <p>
        We keep personal data only as long as needed. Unsuccessful dealer applications are removed
        automatically after our retention period. Financial records (invoices and credit ledgers)
        are retained for the statutory accounting period, but are severed from your identity if you
        ask us to erase your data.
    </p>

    <h2 class="text-lg font-semibold text-white pt-2">Your rights</h2>
    <p>
        You may request a copy of the data we hold about you (data portability) or ask us to erase
        your personal data (right to erasure). Where we retain financial records for accounting, we
        will anonymise them so they can no longer be linked to you.
    </p>

    <h2 class="text-lg font-semibold text-white pt-2">Contact</h2>
    <p>
        To exercise any of these rights, contact us at
        @if ($contactEmail)
            <a href="mailto:{{ $contactEmail }}" class="text-brand hover:underline">{{ $contactEmail }}</a>.
        @else
            our usual support address.
        @endif
    </p>
@endsection
