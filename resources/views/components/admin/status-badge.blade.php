@props(['status'])

<span {{ $attributes->class(['badge', $status->badgeClasses()]) }}>{{ $status->label() }}</span>
