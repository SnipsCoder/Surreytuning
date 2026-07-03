@props(['make', 'size' => 'w-8 h-8'])

@php
    $colorMap = [
        'bmw'           => ['bg' => '#1C69D4', 'text' => '#ffffff'],
        'audi'          => ['bg' => '#BB0A14', 'text' => '#ffffff'],
        'mercedes'      => ['bg' => '#1a1a1a', 'text' => '#c0c0c0'],
        'mercedes-benz' => ['bg' => '#1a1a1a', 'text' => '#c0c0c0'],
        'volkswagen'    => ['bg' => '#001E50', 'text' => '#ffffff'],
        'vw'            => ['bg' => '#001E50', 'text' => '#ffffff'],
        'ford'          => ['bg' => '#003476', 'text' => '#ffffff'],
        'vauxhall'      => ['bg' => '#CC0000', 'text' => '#ffffff'],
        'opel'          => ['bg' => '#FFBE00', 'text' => '#000000'],
        'toyota'        => ['bg' => '#EB0A1E', 'text' => '#ffffff'],
        'honda'         => ['bg' => '#CC0000', 'text' => '#ffffff'],
        'nissan'        => ['bg' => '#C3002F', 'text' => '#ffffff'],
        'hyundai'       => ['bg' => '#002C5F', 'text' => '#ffffff'],
        'kia'           => ['bg' => '#05141F', 'text' => '#ffffff'],
        'renault'       => ['bg' => '#EFDF00', 'text' => '#000000'],
        'peugeot'       => ['bg' => '#0D0D0D', 'text' => '#ffffff'],
        'citroen'       => ['bg' => '#D01C2A', 'text' => '#ffffff'],
        'fiat'          => ['bg' => '#8B0000', 'text' => '#ffffff'],
        'seat'          => ['bg' => '#1A1A1A', 'text' => '#ffffff'],
        'skoda'         => ['bg' => '#4BA82E', 'text' => '#ffffff'],
        'volvo'         => ['bg' => '#003057', 'text' => '#ffffff'],
        'land rover'    => ['bg' => '#005A2B', 'text' => '#ffffff'],
        'landrover'     => ['bg' => '#005A2B', 'text' => '#ffffff'],
        'jaguar'        => ['bg' => '#1A1A1A', 'text' => '#C4A35A'],
        'porsche'       => ['bg' => '#000000', 'text' => '#C0A060'],
        'ferrari'       => ['bg' => '#CC0000', 'text' => '#FFD700'],
        'lamborghini'   => ['bg' => '#1A1A00', 'text' => '#FFD700'],
        'maserati'      => ['bg' => '#003087', 'text' => '#ffffff'],
        'alfa romeo'    => ['bg' => '#8B0000', 'text' => '#ffffff'],
        'subaru'        => ['bg' => '#003087', 'text' => '#ffffff'],
        'mazda'         => ['bg' => '#910A0A', 'text' => '#ffffff'],
        'mitsubishi'    => ['bg' => '#CC0000', 'text' => '#ffffff'],
        'suzuki'        => ['bg' => '#004A97', 'text' => '#ffffff'],
        'mini'          => ['bg' => '#1A1A1A', 'text' => '#ffffff'],
        'bentley'       => ['bg' => '#1A3A1A', 'text' => '#C4A35A'],
        'rolls-royce'   => ['bg' => '#1A1A1A', 'text' => '#C4A35A'],
        'aston martin'  => ['bg' => '#003A5C', 'text' => '#C4A35A'],
        'mclaren'       => ['bg' => '#FF8000', 'text' => '#000000'],
        'tesla'         => ['bg' => '#CC0000', 'text' => '#ffffff'],
        'jeep'          => ['bg' => '#1B3A1B', 'text' => '#ffffff'],
        'dodge'         => ['bg' => '#CC0000', 'text' => '#ffffff'],
        'chevrolet'     => ['bg' => '#D4AF37', 'text' => '#000000'],
        'lexus'         => ['bg' => '#1A1A1A', 'text' => '#C4A35A'],
        'infiniti'      => ['bg' => '#1A1A1A', 'text' => '#ffffff'],
        'acura'         => ['bg' => '#CC0000', 'text' => '#ffffff'],
    ];

    $key     = strtolower(trim($make ?? ''));
    $colors  = $colorMap[$key] ?? ['bg' => '#334155', 'text' => '#94a3b8'];
    $initials = strtoupper(substr(trim($make ?? '?'), 0, 3));
@endphp

<div class="{{ $size }} rounded-lg flex items-center justify-center flex-shrink-0 font-bold"
     style="background-color: {{ $colors['bg'] }}; color: {{ $colors['text'] }}; font-size: 9px; letter-spacing: -0.5px;">
    {{ $initials }}
</div>
