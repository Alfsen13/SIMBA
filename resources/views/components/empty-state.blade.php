@props([
    'icon' => null,
    'message' => __('komponen.tidak_ada_data'),
    'action' => null,
    'actionLabel' => '',
])

<div class="flex flex-col items-center justify-center py-8">
    @if($icon)
        <div class="w-12 h-12 bg-slate-100 dark:bg-slate-800 rounded-lg flex items-center justify-center mb-3 ring-1 ring-slate-200 dark:ring-slate-700">
            {!! $icon !!}
        </div>
    @endif
    <p class="text-sm text-slate-500 dark:text-slate-400 max-w-xs text-center">{{ $message }}</p>
    @if($action && $actionLabel)
        <a href="{{ $action }}" class="mt-4 inline-flex items-center gap-1.5 text-sm font-medium text-emerald-700 dark:text-emerald-400 hover:text-emerald-700 dark:hover:text-emerald-300 transition-colors">
            {{ $actionLabel }}
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
        </a>
    @endif
</div>
