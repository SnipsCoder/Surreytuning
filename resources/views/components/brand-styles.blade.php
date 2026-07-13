@php
    try {
        $brandHex = \App\Models\Setting::first()?->theme_colour ?: '#e63012';
    } catch (\Throwable $e) {
        $brandHex = '#e63012';
    }

    $brandHex = ltrim($brandHex, '#');

    if (strlen($brandHex) === 3) {
        $brandHex = $brandHex[0].$brandHex[0].$brandHex[1].$brandHex[1].$brandHex[2].$brandHex[2];
    }

    if (! preg_match('/^[0-9a-fA-F]{6}$/', $brandHex)) {
        $brandHex = 'e63012';
    }

    $r = hexdec(substr($brandHex, 0, 2));
    $g = hexdec(substr($brandHex, 2, 2));
    $b = hexdec(substr($brandHex, 4, 2));

    $dark = fn ($c) => max(0, (int) round($c * 0.85));
@endphp
<style>:root { --brand: {{ $r }} {{ $g }} {{ $b }}; --brand-dark: {{ $dark($r) }} {{ $dark($g) }} {{ $dark($b) }}; }</style>
