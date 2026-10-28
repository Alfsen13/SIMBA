@props([
    'headers' => [],
    'rows' => null,
    'padding' => false,
])

<div class="overflow-x-auto rounded-lg border border-slate-200 dark:border-slate-800">
    <table class="table-zebra w-full text-sm">
        <thead class="bg-slate-50 dark:bg-slate-800/50 border-b border-slate-200 dark:border-slate-800 sticky top-0 z-10">
            <tr>
                @foreach($headers as $header)
                    <th class="text-left px-3 py-2 text-[11px] font-semibold uppercase tracking-wider text-slate-600 dark:text-slate-300 whitespace-nowrap">
                        {{ $header }}
                    </th>
                @endforeach
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
            {{ $slot }}
        </tbody>
    </table>
</div>
