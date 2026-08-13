@props(['disabled' => false])

<input
    {{ $disabled ? 'disabled' : '' }}
    {!! $attributes->merge([
        'class' => 'w-full px-4 py-2.5 rounded-xl bg-paper dark:bg-ink border border-border-light dark:border-border-dark text-sm text-arang dark:text-kertas placeholder-muted-dark dark:placeholder-muted-light focus:outline-none focus:ring-2 focus:ring-accent focus:border-transparent transition-colors disabled:opacity-50'
    ]) !!}
>