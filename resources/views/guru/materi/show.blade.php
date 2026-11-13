@extends('layouts.app')

@section('title', $materi->judul . ' - SIMBA')

@section('content')
    <x-back-link
        href="{{ $materi->pertemuan_id ? route('pengajar.pertemuan.show', $materi->pertemuan_id) : route('pengajar.kelas.show', $materi->kelas_id) }}"
        :label="__('common.back')"
    />

    <x-card class="mt-4">
        <h1 class="text-xl font-bold text-slate-900 dark:text-slate-100">{{ $materi->judul }}</h1>
        <x-rag-status :document="$materi->document" />
        <dl class="mt-4 space-y-3">
            <div class="flex">
                <dt class="w-32 text-sm text-slate-500 dark:text-slate-400">{{ __('guru_kelas.materi.kelas') }}</dt>
                <dd class="text-sm text-slate-900 dark:text-slate-100">{{ $materi->kelas?->nama_kelas ?? '-' }}</dd>
            </div>
            <div class="flex">
                <dt class="w-32 text-sm text-slate-500 dark:text-slate-400">{{ __('guru_kelas.materi.bab') }}</dt>
                <dd class="text-sm text-slate-900 dark:text-slate-100">{{ $materi->bab ?? '-' }}</dd>
            </div>
            <div class="flex">
                <dt class="w-32 text-sm text-slate-500 dark:text-slate-400">{{ __('guru_kelas.materi.file') }}</dt>
                <dd class="text-sm">
                    @if($materi->file_path)
                        <div class="flex items-center gap-3">
                            <a
                                href="{{ Storage::url($materi->file_path) }}"
                                class="text-emerald-700 dark:text-emerald-400 hover:text-emerald-800 dark:hover:text-emerald-300"
                                target="_blank"
                            >
                                {{ __('guru_kelas.materi.lihat') }}
                            </a>
                            <a
                                href="{{ Storage::url($materi->file_path) }}"
                                class="text-emerald-700 dark:text-emerald-400 hover:text-emerald-800 dark:hover:text-emerald-300"
                                download
                            >
                                {{ __('guru_kelas.download') }}
                            </a>
                        </div>
                    @else
                        <span class="text-slate-500 dark:text-slate-400">{{ __('guru_kelas.materi.tidak_ada_file') }}</span>
                    @endif
                </dd>
            </div>
            <div class="flex">
                <dt class="w-32 text-sm text-slate-500 dark:text-slate-400">{{ __('guru_kelas.materi.diunggah_oleh') }}</dt>
                <dd class="text-sm">
                    {{ $materi->guru?->name ?? '-' }}
                </dd>
            </div>
        </dl>

        <x-ringkasan-materi :materi="$materi" />

        @php
            $dokumenPdf = $materi->document
                && $materi->document->status === 'completed'
                && strtolower((string) $materi->document->type) === 'pdf'
                && config('services.pdf.formula_ocr_enabled');
        @endphp

        @if($dokumenPdf)
            @php
                $ulasan = $materi->document->formulaReviews;
                $statusPindai = $materi->document->formula_ocr_status;
                $sedangBerjalan = in_array($statusPindai, ['pending', 'processing'], true);
                $menunggu = $ulasan->where('status', \App\Models\DocumentFormulaReview::STATUS_PENDING)->count();
                $pesanGagal = match ($materi->document->formula_ocr_error) {
                    'schedule_failed' => __('guru_kelas.materi.rumus_gagal_jadwal'),
                    'ocr_unavailable' => __('guru_kelas.materi.rumus_gagal_model'),
                    default => $materi->document->formula_ocr_error,
                };
                $pantau = [
                    'url' => route('pengajar.materi.rumus.status', $materi),
                    'berjalan' => $sedangBerjalan,
                    'batas' => \App\Jobs\DetectDocumentFormulas::budget(),
                    'jumlah' => $ulasan->count(),
                    'menunggu' => $menunggu,
                ];
            @endphp

            <section class="mt-6" aria-labelledby="rumus-materi" x-data="rumusPantau(@json($pantau))">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <h2 id="rumus-materi" class="text-lg font-semibold text-slate-900 dark:text-slate-100">{{ __('guru_kelas.materi.rumus_judul') }}</h2>
                    <form method="POST" action="{{ route('pengajar.materi.rumus.scan', $materi) }}">
                        @csrf
                        <x-btn-primary
                            type="submit"
                            size="sm"
                            class="min-h-11"
                            :disabled="$sedangBerjalan"
                            x-bind:disabled="sibuk"
                        >
                            {{ $ulasan->isEmpty() ? __('guru_kelas.materi.rumus_pindai') : __('guru_kelas.materi.rumus_pindai_ulang') }}
                        </x-btn-primary>
                    </form>
                </div>

                <p class="mt-1 flex flex-wrap items-center gap-2 text-sm text-slate-500 dark:text-slate-400">
                    <x-badge
                        :variant="match ($statusPindai) { 'completed' => 'info', 'partial' => 'warning', 'failed' => 'danger', 'pending', 'processing' => 'warning', default => 'default' }"
                        :label="$statusPindai ? __('guru_kelas.materi.rumus_doc_'.$statusPindai) : __('guru_kelas.materi.rumus_doc_belum')"
                    />
                    <span x-show="sibuk" x-cloak role="status">
                        {{ __('guru_kelas.materi.rumus_memindai') }}
                        <span x-text="sisa" aria-hidden="true"></span> {{ __('guru_kelas.materi.rumus_detik') }}
                    </span>
                    <span x-show="sibuk && jumlah > 0" x-cloak>
                        <span x-text="jumlah"></span> {{ __('guru_kelas.materi.rumus_hasil_sementara') }}
                    </span>
                    @if($menunggu > 0)
                        <span>{{ __('guru_kelas.materi.rumus_menunggu', ['count' => $menunggu]) }}</span>
                    @endif
                </p>

                @if($statusPindai === 'failed')
                    <p class="mt-2 text-sm text-red-700 dark:text-red-400">
                        {{ __('guru_kelas.materi.rumus_gagal', ['error' => $pesanGagal]) }}
                    </p>
                @elseif($statusPindai === 'partial')
                    <p class="mt-2 text-sm text-amber-700 dark:text-amber-400">{{ __('guru_kelas.materi.rumus_sebagian') }}</p>
                @endif

                <div x-show="sibuk" x-cloak class="mt-3 space-y-3" aria-hidden="true">
                    @foreach(range(1, 3) as $bayangan)
                        <div class="rounded-lg border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-4">
                            <div class="h-3 w-24 animate-pulse rounded bg-slate-200 dark:bg-slate-700"></div>
                            <div class="mt-3 h-28 w-full animate-pulse rounded bg-slate-100 dark:bg-slate-800"></div>
                            <div class="mt-3 h-8 w-full animate-pulse rounded bg-slate-100 dark:bg-slate-800"></div>
                            <div class="mt-3 h-8 w-32 animate-pulse rounded bg-slate-100 dark:bg-slate-800"></div>
                        </div>
                    @endforeach
                </div>

                <div x-show="terlambat" x-cloak class="mt-3 rounded-lg border border-amber-200 bg-amber-50 p-3 dark:border-amber-800 dark:bg-amber-950/30">
                    <p class="text-sm text-amber-900 dark:text-amber-200">{{ __('guru_kelas.materi.rumus_macet') }}</p>
                    <div class="mt-2 flex flex-wrap items-center gap-2">
                        <form method="POST" action="{{ route('pengajar.materi.rumus.scan', $materi) }}">
                            @csrf
                            <x-btn-primary type="submit" size="sm" class="min-h-11">{{ __('guru_kelas.materi.rumus_pindai_ulang') }}</x-btn-primary>
                        </form>
                        <x-btn-secondary type="button" size="sm" class="min-h-11" @click="window.location.reload()">
                            {{ __('guru_kelas.materi.rumus_muat_ulang') }}
                        </x-btn-secondary>
                    </div>
                </div>

                @if($ulasan->isNotEmpty())
                    <form
                        method="POST"
                        action="{{ route('pengajar.materi.rumus.setujui-banyak', $materi) }}"
                        x-data="rumusKeranjang({{ json_encode([
                            'pending' => $ulasan->where('status', \App\Models\DocumentFormulaReview::STATUS_PENDING)->pluck('id')->all(),
                            'pesan' => __('guru_kelas.materi.rumus_keranjang_tanya'),
                        ]) }})"
                        class="mt-3 space-y-3"
                    >
                        @csrf
                        @foreach($ulasan as $ulasanRumus)
                            <x-rumus-review :review="$ulasanRumus" />
                        @endforeach

                        <div
                            x-show="pending.length > 0"
                            x-cloak
                            class="sticky bottom-0 flex flex-wrap items-center gap-2 rounded-lg border border-slate-200 bg-white/95 p-3 backdrop-blur dark:border-slate-800 dark:bg-slate-900/95"
                        >
                            <label class="flex items-center gap-2 text-sm font-medium text-slate-700 dark:text-slate-300">
                                <input
                                    type="checkbox"
                                    :checked="semua"
                                    @change="balikSemua()"
                                    class="size-4 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500/30 dark:border-slate-600 dark:bg-slate-800"
                                >
                                <span>{{ __('guru_kelas.materi.rumus_keranjang_pilih_semua') }}</span>
                            </label>

                            <span class="text-sm text-slate-600 dark:text-slate-400" role="status" aria-live="polite">
                                <span x-text="jumlah"></span> {{ __('guru_kelas.materi.rumus_keranjang_dipilih') }}
                            </span>

                            <div class="ml-auto flex flex-wrap items-center gap-2">
                                <x-btn-primary
                                    type="submit"
                                    size="sm"
                                    class="min-h-11"
                                    x-bind:disabled="jumlah === 0"
                                >
                                    {{ __('guru_kelas.materi.rumus_keranjang_setujui') }}
                                </x-btn-primary>
                                <x-btn-secondary
                                    type="submit"
                                    size="sm"
                                    color="red"
                                    class="min-h-11"
                                    formaction="{{ route('pengajar.materi.rumus.tolak-banyak', $materi) }}"
                                    x-bind:disabled="jumlah === 0"
                                    @click="if (! tanyaTolak()) $event.preventDefault()"
                                >
                                    {{ __('guru_kelas.materi.rumus_keranjang_tolak') }}
                                </x-btn-secondary>
                            </div>
                        </div>
                    </form>
                @elseif(!$sedangBerjalan)
                    <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">
                        {{ $statusPindai === 'completed' ? __('guru_kelas.materi.rumus_belum_ada') : __('guru_kelas.materi.rumus_belum_dipindai') }}
                    </p>
                @endif
            </section>
        @endif

        <section class="mt-6" aria-labelledby="potongan-materi">
            <h2 id="potongan-materi" class="text-lg font-semibold text-slate-900 dark:text-slate-100">{{ __('guru_kelas.materi.potongan_dokumen') }}</h2>
            @if($materi->document && $materi->document->chunks->isNotEmpty())
                <details class="mt-2">
                    <summary class="cursor-pointer min-h-11 py-3 text-sm font-medium text-emerald-700 dark:text-emerald-400 hover:underline">
                        {{ __('guru_kelas.materi.lihat_potongan', ['count' => $materi->document->chunks->count()]) }}
                    </summary>
                    <div class="mt-2 space-y-3">
                        @foreach($materi->document->chunks as $chunk)
                            <div class="rounded-lg border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-800/50 p-4">
                                <p class="text-xs font-semibold text-slate-600 dark:text-slate-400">
                                    {{ __('guru_kelas.materi.bagian', ['number' => $chunk->chunk_index + 1]) }}
                                    @if($chunk->page_start !== null)
                                        <span>({{ __('guru_kelas.materi.halaman', ['number' => $chunk->page_start]) }}@if($chunk->page_end !== null && $chunk->page_end !== $chunk->page_start){{ '–'.$chunk->page_end }}@endif)</span>
                                    @endif
                                </p>
                                <p class="mt-1 overflow-x-auto whitespace-pre-wrap break-words text-sm leading-relaxed text-slate-700 dark:text-slate-300"><x-math :value="\App\Services\RumusPenanda::manusiakan($chunk->content)" /></p>
                            </div>
                        @endforeach
                    </div>
                </details>
            @else
                <p class="mt-2 text-sm text-slate-600 dark:text-slate-400">{{ __('guru_kelas.materi.potongan_belum_tersedia') }}</p>
            @endif
        </section>

        @if($materi->file_path)
            @php $tipeFile = strtolower($materi->file_type ?? ''); @endphp
            @if($tipeFile === 'pdf')
                <div class="mt-6 rounded-lg overflow-hidden border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900" style="height: 75vh;">
                    <iframe src="{{ Storage::url($materi->file_path) }}" class="w-full h-full border-0"></iframe>
                </div>
            @elseif(in_array($tipeFile, ['png', 'jpg', 'jpeg', 'gif', 'webp']))
                <div class="mt-6 rounded-lg overflow-hidden border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-2">
                    <img src="{{ Storage::url($materi->file_path) }}" alt="{{ $materi->judul }}" class="max-w-full mx-auto rounded-lg">
                </div>
            @else
                <p class="mt-6 text-sm text-slate-500 dark:text-slate-400">{{ __('guru_kelas.materi.pratinjau_tidak_tersedia') }} ({{ strtoupper($tipeFile ?: '?') }})</p>
            @endif
        @endif
    </x-card>
@endsection
