@props(['variant' => 'full', 'class' => 'h-7 w-7'])

@if ($variant === 'icon')
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" class="{{ $class }}" aria-hidden="true">
        <path d="M8.5 20V6" />
        <path d="M8.5 12.2A6.2 6.2 0 0 1 14.7 6h2.6" />
        <path d="M14.2 3.1 17.6 6l-3.4 2.9" />
    </svg>
@else
    <span class="inline-flex items-center gap-2">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" class="h-7 w-7 shrink-0" aria-hidden="true">
            <path d="M8.5 20V6" />
            <path d="M8.5 12.2A6.2 6.2 0 0 1 14.7 6h2.6" />
            <path d="M14.2 3.1 17.6 6l-3.4 2.9" />
        </svg>
        <span class="text-xl font-bold leading-none tracking-tight">rentra</span>
    </span>
@endif


