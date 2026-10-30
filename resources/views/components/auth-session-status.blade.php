@props(['status'])

@if ($status)
    <div {{ $attributes->merge(['class' => 'font-medium text-sm text-sidan-600']) }}>
        {{ $status }}
    </div>
@endif
