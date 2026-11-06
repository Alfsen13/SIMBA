@props(['document'])

@if($document)
    @php
        $statusLabel = __('guru_kelas.materi.rag_status_' . $document->status);
        $statusClasses = match ($document->status) {
            'completed' => 'border-emerald-200 bg-emerald-50 text-emerald-800 dark:border-emerald-800 dark:bg-emerald-950/30 dark:text-emerald-200',
            'failed' => 'border-red-200 bg-red-50 text-red-800 dark:border-red-800 dark:bg-red-950/30 dark:text-red-200',
            default => 'border-amber-200 bg-amber-50 text-amber-800 dark:border-amber-800 dark:bg-amber-950/30 dark:text-amber-200',
        };
    @endphp

    <div class="mb-4 rounded-lg border p-3 text-sm {{ $statusClasses }}" role="status">
        <p class="font-medium">{{ __('guru_kelas.materi.rag_status_title', ['status' => $statusLabel]) }}</p>
        @if($document->status === 'completed')
            <p class="mt-1">{{ __('guru_kelas.materi.rag_ready') }}</p>
        @elseif($document->status === 'failed')
            <p class="mt-1">{{ __('guru_kelas.materi.rag_failed', ['error' => $document->error_message ?: __('guru_kelas.materi.rag_unknown_error')]) }}</p>
        @endif
    </div>
@endif
