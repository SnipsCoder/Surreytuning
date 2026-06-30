@props(['make', 'size' => 'w-8 h-8'])

@php
    $domainMap = [
        'audi'          => 'audi.com',
        'bmw'           => 'bmw.com',
        'mercedes'      => 'mercedes-benz.com',
        'mercedes-benz' => 'mercedes-benz.com',
        'volkswagen'    => 'volkswagen.com',
        'vw'            => 'volkswagen.com',
        'ford'          => 'ford.com',
        'vauxhall'      => 'vauxhall.co.uk',
        'opel'          => 'opel.com',
        'toyota'        => 'toyota.com',
        'honda'         => 'honda.com',
        'nissan'        => 'nissan.com',
        'hyundai'       => 'hyundai.com',
        'kia'           => 'kia.com',
        'renault'       => 'renault.com',
        'peugeot'       => 'peugeot.com',
        'citroen'       => 'citroen.com',
        'fiat'          => 'fiat.com',
        'seat'          => 'seat.com',
        'skoda'         => 'skoda-auto.com',
        'volvo'         => 'volvocars.com',
        'land rover'    => 'landrover.com',
        'landrover'     => 'landrover.com',
        'jaguar'        => 'jaguar.com',
        'porsche'       => 'porsche.com',
        'ferrari'       => 'ferrari.com',
        'lamborghini'   => 'lamborghini.com',
        'maserati'      => 'maserati.com',
        'alfa romeo'    => 'alfaromeo.com',
        'subaru'        => 'subaru.com',
        'mazda'         => 'mazda.com',
        'mitsubishi'    => 'mitsubishi-motors.com',
        'suzuki'        => 'suzuki.com',
        'mini'          => 'mini.com',
        'bentley'       => 'bentleymotors.com',
        'rolls-royce'   => 'rolls-roycemotorcars.com',
        'aston martin'  => 'astonmartin.com',
        'mclaren'       => 'mclaren.com',
        'tesla'         => 'tesla.com',
        'jeep'          => 'jeep.com',
        'dodge'         => 'dodge.com',
        'chrysler'      => 'chrysler.com',
        'chevrolet'     => 'chevrolet.com',
        'cadillac'      => 'cadillac.com',
        'buick'         => 'buick.com',
        'gmc'           => 'gmc.com',
        'lexus'         => 'lexus.com',
        'infiniti'      => 'infiniti.com',
        'acura'         => 'acura.com',
    ];

    $key    = strtolower(trim($make ?? ''));
    $domain = $domainMap[$key] ?? null;

    $svgFallback = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="text-slate-500"><path d="M3 17h1.5l1-3h11l1 3H18a1 1 0 0 0 0 2h1a1 1 0 0 0 1-1v-1.27A2 2 0 0 0 18.73 15l-1-3A2 2 0 0 0 16 11H8a2 2 0 0 0-1.73 1l-1 3A2 2 0 0 0 4 16.73V18a1 1 0 0 0 1 1h1a1 1 0 0 0 0-2zm3-2 .5-1.5h11l.5 1.5H6zm.5 3a.5.5 0 1 1 0-1 .5.5 0 0 1 0 1zm11 0a.5.5 0 1 1 0-1 .5.5 0 0 1 0 1z"/></svg>';
@endphp

<div class="{{ $size }} rounded-full bg-white flex items-center justify-center overflow-hidden flex-shrink-0">
    @if ($domain)
        <img
            src="https://logo.clearbit.com/{{ $domain }}"
            alt="{{ $make }}"
            class="{{ $size }} object-contain"
            onerror="this.style.display='none';this.nextElementSibling.style.display='flex';"
        >
        <span style="display:none" class="{{ $size }} items-center justify-center">
            {!! $svgFallback !!}
        </span>
    @else
        <span class="{{ $size }} flex items-center justify-center">
            {!! $svgFallback !!}
        </span>
    @endif
</div>
