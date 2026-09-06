@props(['value' => 0, 'precise' => false])
{{-- Montant en euros, format français. --}}
<span {{ $attributes }}>{!! $precise ? \App\Support\Money::eurPrecise($value) : \App\Support\Money::eur($value) !!}</span>
