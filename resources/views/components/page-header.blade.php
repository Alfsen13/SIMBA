@props([
    'title' => '',
    'subtitle' => '',
    'actions' => null,
])

<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-4">
    <div>
        <h1 class="text-lg font-semibold tracking-tight text-slate-900 dark:text-white">{{ $title }}</h1>
        @if($subtitle)
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">{{ $subtitle }}</p>
        @endif
    </div>
    @if($actions)
        <div class="flex items-center gap-2 shrink-0">
            {{ $actions }}
        </div>
    @endif
</div>
