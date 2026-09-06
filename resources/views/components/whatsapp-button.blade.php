@props(['message' => null])
@php
    $number = \App\Models\Setting::getCached('brand_whatsapp', config('aylalisse.whatsapp'));
    $digits = preg_replace('/[^0-9]/', '', (string) $number);
    $text = $message ?? "Bonjour AylaLisse, j'aimerais obtenir plus d'informations concernant le lissage.";
@endphp
@if ($digits)
    <a
        href="https://wa.me/{{ $digits }}?text={{ rawurlencode($text) }}"
        target="_blank"
        rel="noopener noreferrer"
        aria-label="Contacter AylaLisse sur WhatsApp"
        {{ $attributes->class([
            'fixed right-4 z-40 flex h-14 w-14 items-center justify-center rounded-full bg-cocoa text-white shadow-[0_10px_30px_-8px_rgba(37,21,15,0.55)] transition-transform hover:scale-105 hover:bg-espresso',
        ]) }}
    >
        <svg class="h-6 w-6" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
            <path d="M12.01 2C6.48 2 2 6.48 2 12.01c0 1.86.5 3.6 1.36 5.1L2 22l5.02-1.32a9.96 9.96 0 0 0 4.99 1.34h.01c5.52 0 10-4.48 10-10.01C22.02 6.48 17.54 2 12.01 2Zm0 18.2h-.01a8.2 8.2 0 0 1-4.18-1.15l-.3-.18-2.98.78.8-2.9-.2-.3a8.18 8.18 0 0 1-1.26-4.38c0-4.53 3.69-8.22 8.23-8.22 2.2 0 4.26.86 5.82 2.41a8.16 8.16 0 0 1 2.41 5.82c0 4.53-3.7 8.22-8.23 8.22Zm4.52-6.16c-.25-.12-1.47-.72-1.7-.81-.23-.08-.39-.12-.56.13-.16.24-.64.8-.79.97-.14.16-.29.18-.54.06-.25-.12-1.05-.39-2-1.23-.74-.66-1.24-1.47-1.39-1.72-.14-.24-.02-.38.11-.5.11-.11.25-.29.37-.43.12-.14.16-.24.24-.4.08-.16.04-.3-.02-.43-.06-.12-.56-1.34-.76-1.84-.2-.48-.4-.42-.56-.42h-.48c-.16 0-.42.06-.64.3-.22.24-.85.83-.85 2.02 0 1.19.87 2.34.99 2.5.12.16 1.71 2.6 4.14 3.65.58.25 1.03.4 1.38.51.58.18 1.11.16 1.53.1.47-.07 1.47-.6 1.67-1.18.21-.58.21-1.07.15-1.18-.06-.1-.23-.16-.48-.28Z"/>
        </svg>
    </a>
@endif
