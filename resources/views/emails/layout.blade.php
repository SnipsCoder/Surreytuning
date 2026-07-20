@php($settings = \App\Models\Setting::get())
@php($brandName = \App\Models\Setting::brandName())
@php($brandColour = \App\Models\Setting::brandColour())
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $subject ?? $brandName }}</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f4f4f5; margin: 0; padding: 0; color: #374151; }
        .wrapper { max-width: 600px; margin: 40px auto; background: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,.1); }
        .header { background: {{ $brandColour }}; padding: 24px 32px; }
        .header h1 { color: #fff; margin: 0; font-size: 20px; font-weight: 700; letter-spacing: .5px; }
        .header img { max-height: 36px; max-width: 220px; display: block; }
        .body { padding: 32px; font-size: 15px; line-height: 1.7; }
        .body h2 { font-size: 18px; color: #111827; margin-top: 0; }
        .btn { display: inline-block; margin-top: 20px; padding: 12px 24px; background: {{ $brandColour }}; color: #fff; text-decoration: none; border-radius: 6px; font-weight: 600; font-size: 14px; }
        .footer { background: #f9fafb; border-top: 1px solid #e5e7eb; padding: 20px 32px; font-size: 12px; color: #9ca3af; }
        p { margin: 0 0 14px; }
    </style>
</head>
<body>
<div class="wrapper">
    <div class="header">
        @if ($settings && ($settings->portal_logo || $settings->logo_dark))
            <img src="{{ route('branding.logo') }}" alt="{{ $brandName }}">
        @else
            <h1>{{ $brandName }}</h1>
        @endif
    </div>
    <div class="body">
        {{ $slot }}
    </div>
    <div class="footer">
        &copy; {{ date('Y') }} {{ $brandName }}. This email was sent to you because you have an account on our dealer portal.
        @if ($settings && $settings->support_email)
            <br>Need help? Contact us at <a href="mailto:{{ $settings->support_email }}" style="color:#9ca3af;">{{ $settings->support_email }}</a>.
        @endif
    </div>
</div>
</body>
</html>
