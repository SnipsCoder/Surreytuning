@extends('layouts.legal')

@section('heading', 'Terms & Conditions')

@section('content')
    @if ($terms)
        <div class="whitespace-pre-wrap text-gray-300">{{ $terms }}</div>
    @else
        <p class="text-gray-400">Our terms and conditions are being finalised. Please contact us if you need a copy.</p>
    @endif
@endsection
