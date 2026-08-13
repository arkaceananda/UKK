@props(['status'])

@if ($status)
    <div {{ $attributes->merge(['class' => 'px-4 py-2.5 rounded-xl bg-daun/10 border border-daun/30 text-sm text-daun font-medium']) }}>
        {{ $status }}
    </div>
@endif