@php
    use App\Enums\InvoiceStatus;

    $providerName = $settings instanceof \App\Models\Setting ? $settings->resolveBrandName() : \App\Models\Setting::brandName();
    $brandColour = $settings instanceof \App\Models\Setting ? $settings->resolveBrandColour() : \App\Models\Setting::brandColour();
    $prefix = $settings->invoice_reference_prefix ?: 'INV';
    $reference = $prefix . '-' . $invoice->invoice_number;
    $vatRate = rtrim(rtrim(number_format((float) $settings->vat_rate, 2), '0'), '.');

    // Embed the brand logo as a base64 data URI so DomPDF never needs network access.
    $logoData = null;
    $logoPath = public_path('images/logo.png');
    if (is_file($logoPath)) {
        $logoData = 'data:image/png;base64,' . base64_encode((string) file_get_contents($logoPath));
    }

    $isPaid = $invoice->status === InvoiceStatus::Paid;
    $isVoid = $invoice->status === InvoiceStatus::Void;
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Invoice {{ $reference }}</title>
    <style>
        * { box-sizing: border-box; }
        html, body { margin: 0; padding: 0; }
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 12px;
            color: #1f2937;
            line-height: 1.5;
        }
        .header {
            background-color: #ffffff;
            padding: 24px 32px;
            color: #0f172a;
        }
        .header table { width: 100%; border-collapse: collapse; }
        .header td { vertical-align: middle; }
        .header .logo { height: 40px; }
        .header .brand { font-size: 20px; font-weight: bold; color: #0f172a; }
        .header .doc-title {
            text-align: right;
            font-size: 26px;
            font-weight: bold;
            letter-spacing: 2px;
            color: #000000;
        }
        .header .doc-ref { text-align: right; font-size: 12px; color: #475569; }
        .accent-bar { height: 4px; background-color: {{ $brandColour }}; }

        .body { padding: 28px 32px; }

        .meta table { width: 100%; border-collapse: collapse; }
        .meta td { vertical-align: top; width: 50%; padding: 0; }
        .panel-label {
            text-transform: uppercase;
            font-size: 9px;
            letter-spacing: 1px;
            color: #94a3b8;
            font-weight: bold;
            margin-bottom: 4px;
        }
        .party-name { font-size: 13px; font-weight: bold; color: #0f172a; }
        .muted { color: #475569; }
        .muted-line { color: #475569; white-space: pre-line; }

        .info-table { width: 100%; border-collapse: collapse; margin-top: 8px; }
        .info-table td { padding: 2px 0; }
        .info-table .k { color: #94a3b8; }
        .info-table .v { text-align: right; color: #0f172a; font-weight: bold; }

        .status-pill {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 999px;
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .status-paid { background-color: #dcfce7; color: #166534; }
        .status-issued { background-color: #fef9c3; color: #854d0e; }
        .status-void { background-color: #e5e7eb; color: #4b5563; }

        table.lines { width: 100%; border-collapse: collapse; margin-top: 28px; }
        table.lines thead th {
            background-color: #f1f5f9;
            color: #475569;
            text-transform: uppercase;
            font-size: 9px;
            letter-spacing: 1px;
            text-align: left;
            padding: 8px 12px;
            border-bottom: 2px solid #e2e8f0;
        }
        table.lines thead th.num { text-align: right; }
        table.lines tbody td {
            padding: 12px;
            border-bottom: 1px solid #e2e8f0;
            vertical-align: top;
        }
        table.lines tbody td.num { text-align: right; }

        .totals { width: 100%; margin-top: 16px; border-collapse: collapse; }
        .totals .spacer { width: 60%; }
        .totals table { width: 100%; border-collapse: collapse; }
        .totals .k { padding: 4px 12px; color: #475569; text-align: right; }
        .totals .v { padding: 4px 12px; color: #0f172a; text-align: right; font-weight: bold; }
        .totals .grand .k, .totals .grand .v {
            font-size: 15px;
            color: #0f172a;
            border-top: 2px solid #0f172a;
            padding-top: 8px;
        }

        .notes {
            margin-top: 32px;
            padding-top: 16px;
            border-top: 1px solid #e2e8f0;
            font-size: 11px;
            color: #64748b;
        }
        .notes .heading { font-weight: bold; color: #334155; margin-bottom: 4px; }

        .footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            padding: 12px 32px;
            font-size: 9px;
            color: #94a3b8;
            border-top: 1px solid #e2e8f0;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="header">
        <table>
            <tr>
                <td>
                    @if ($logoData)
                        <img class="logo" src="{{ $logoData }}" alt="{{ $providerName }}">
                    @else
                        <span class="brand">{{ $providerName }}</span>
                    @endif
                </td>
                <td>
                    <div class="doc-title">INVOICE</div>
                    <div class="doc-ref">{{ $reference }}</div>
                </td>
            </tr>
        </table>
    </div>
    <div class="accent-bar"></div>

    <div class="body">
        <table class="meta">
            <tr>
                <td>
                    <div class="panel-label">From</div>
                    <div class="party-name">{{ $providerName }}</div>
                    @if ($settings->invoice_address)
                        <div class="muted-line">{{ $settings->invoice_address }}</div>
                    @endif
                    @if ($settings->company_number)
                        <div class="muted">Company No: {{ $settings->company_number }}</div>
                    @endif
                    @if ($settings->vat_number)
                        <div class="muted">VAT No: {{ $settings->vat_number }}</div>
                    @endif
                </td>
                <td>
                    <div class="panel-label">Bill To</div>
                    <div class="party-name">{{ $invoice->dealer->company_name }}</div>
                    @if ($invoice->dealer->invoice_address)
                        <div class="muted-line">{{ $invoice->dealer->invoice_address }}</div>
                    @endif
                    @if ($invoice->dealer->country)
                        <div class="muted">{{ $invoice->dealer->country }}</div>
                    @endif

                    <table class="info-table">
                        <tr>
                            <td class="k">Invoice No.</td>
                            <td class="v">{{ $reference }}</td>
                        </tr>
                        <tr>
                            <td class="k">Issue Date</td>
                            <td class="v">{{ $invoice->created_at->format('d M Y') }}</td>
                        </tr>
                        <tr>
                            <td class="k">Status</td>
                            <td class="v">
                                <span class="status-pill {{ $isPaid ? 'status-paid' : ($isVoid ? 'status-void' : 'status-issued') }}">
                                    {{ $invoice->status->label() }}
                                </span>
                            </td>
                        </tr>
                        @if ($invoice->paid_at)
                        <tr>
                            <td class="k">Paid On</td>
                            <td class="v">{{ $invoice->paid_at->format('d M Y') }}</td>
                        </tr>
                        @endif
                    </table>
                </td>
            </tr>
        </table>

        <table class="lines">
            <thead>
                <tr>
                    <th>Description</th>
                    <th class="num">Net</th>
                    <th class="num">VAT ({{ $vatRate }}%)</th>
                    <th class="num">Amount</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>
                        {{ $invoice->description }}
                        <div class="muted" style="font-size:10px; margin-top:2px;">{{ $invoice->type->label() }}</div>
                    </td>
                    <td class="num">&pound;{{ number_format($invoice->amount_net, 2) }}</td>
                    <td class="num">&pound;{{ number_format($invoice->vat_amount, 2) }}</td>
                    <td class="num">&pound;{{ number_format($invoice->amount_gross, 2) }}</td>
                </tr>
            </tbody>
        </table>

        <table class="totals">
            <tr>
                <td class="spacer"></td>
                <td>
                    <table>
                        <tr>
                            <td class="k">Subtotal</td>
                            <td class="v">&pound;{{ number_format($invoice->amount_net, 2) }}</td>
                        </tr>
                        <tr>
                            <td class="k">VAT ({{ $vatRate }}%)</td>
                            <td class="v">&pound;{{ number_format($invoice->vat_amount, 2) }}</td>
                        </tr>
                        <tr class="grand">
                            <td class="k">Total Due</td>
                            <td class="v">&pound;{{ number_format($invoice->amount_gross, 2) }}</td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>

        @if ($isPaid)
            <div class="notes">
                <div class="heading">Payment received</div>
                This invoice was paid in full on {{ $invoice->paid_at?->format('d M Y') }}. No further action is required.
            </div>
        @elseif (! $isVoid)
            <div class="notes">
                <div class="heading">Payment</div>
                Please settle this invoice through your dealer portal. Reference: {{ $reference }}.
            </div>
        @endif
    </div>

    <div class="footer">
        {{ $providerName }}@if ($settings->vat_number) &middot; VAT No: {{ $settings->vat_number }}@endif @if ($settings->company_number) &middot; Company No: {{ $settings->company_number }}@endif
    </div>
</body>
</html>
