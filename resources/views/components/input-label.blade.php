@props(['value'])

<label {{ $attributes->merge(['class' => 'block text-xs font-semibold text-arang dark:text-kertas uppercase tracking-wider mb-1.5']) }}>
    {{ $value ?? $slot }}
</label>