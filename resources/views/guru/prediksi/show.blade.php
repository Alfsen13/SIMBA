@extends('layouts.app')

@section('title', __('guru_analitik.prediksi.monitor_page_title') . ' - SIMBA')

@section('content')
    @php
        $badgeVariant = [
            'aman' => 'success',
            'perlu_pantau' => 'warning',
            'berisiko_tinggi' => 'danger',
        ];

        $suffix = [
            'rata_durasi_belajar' => ' mnt',
            'persen_tugas_terkumpul' => '%',
            'tren_nilai' => '',
            'persen_kehadiran' => '%',
            'rata_nilai' => '',
            'jumlah_pertemuan_tidak_aktif' => '',
        ];

        $indicatorCards = $fitur
            ? collect(\App\Services\FiturPertemuanService::fiturModel())
                ->map(fn ($key) => [
                    'label' => \App\Services\FiturPertemuanService::FEATURE_LABELS[$key],
                    'value' => $fitur->{$key},
                    'suffix' => $suffix[$key] ?? '',
                ])->values()->all()
            : [];
    @endphp

    <x-page-header :title="__('guru_analitik.prediksi.monitor_page_title')" :subtitle="__('guru_analitik.prediksi.monitor_subtitle')">
        <x-slot:actions>
            <a href="{{ route('pengajar.prediksi') }}" class="text-sm font-medium text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white transition-colors">
                &larr; {{ __('guru_analitik.prediksi.back_to_list') }}
            </a>
            {{-- Sinkron: satu siklus prediksi 0,8-5,7 detik, jadi guru langsung
                 melihat hasilnya tanpa reload. Overlay harus berada di dalam
                 x-data yang sama dengan form, kalau tidak state `generating`
                 tidak terjangkau. --}}
            <div x-data="{ generating: false }">
                <form
                    action="{{ route('pengajar.prediksi.run') }}"
                    method="POST"
                    @submit="generating = true"
                >
                    @csrf
                    <x-btn-primary type="submit" size="sm" x-bind:disabled="generating">
                        <svg x-show="!generating" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                        <svg x-show="generating" x-cloak class="w-4 h-4 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                        </svg>
                        <span x-show="!generating">{{ __('guru_analitik.prediksi.run') }}</span>
                        <span x-show="generating" x-cloak>{{ __('guru_analitik.prediksi.calculating') }}</span>
                    </x-btn-primary>
                </form>

                <x-loading-overlay
                    :judul="__('guru_analitik.prediksi.overlay_risk')"
                    :heading="__('guru_analitik.prediksi.overlay_heading')"
                    :catatan="__('guru_analitik.prediksi.overlay_note')"
                />
            </div>
        </x-slot:actions>
    </x-page-header>

    @if($siswaOptions->isEmpty())
        <x-card>
            <p class="text-sm text-slate-500 dark:text-slate-400">{{ __('guru_analitik.prediksi.no_student_in_class') }}</p>
        </x-card>
    @else
        <x-card class="mb-4">
            <div class="flex flex-col sm:flex-row sm:items-center gap-3">
                <label for="siswa-select" class="text-sm font-medium text-slate-700 dark:text-slate-300 whitespace-nowrap">
                    {{ __('guru_analitik.prediksi.select_student') }}
                </label>
                <select id="siswa-select" onchange="window.location.href = this.value"
                    class="w-full sm:max-w-md rounded-lg border-slate-200 dark:border-slate-800 text-sm focus:ring-emerald-500 focus:border-emerald-500">
                    @foreach($siswaOptions as $opsi)
                        <option value="{{ route('pengajar.prediksi.show', ['siswa' => $opsi->id]) }}" @selected($siswa && $siswa->id === $opsi->id)>
                            {{ $opsi->name }}
                        </option>
                    @endforeach
                </select>
            </div>
        </x-card>

        @if($siswa)
            <x-card class="mb-4">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div class="flex items-center gap-4">
                        <div class="w-10 h-10 rounded-full bg-emerald-100 dark:bg-emerald-600/20 text-emerald-700 dark:text-emerald-300 flex items-center justify-center font-bold">
                            {{ mb_strtoupper(mb_substr($siswa->name, 0, 1)) }}
                        </div>
                        <div>
                            <h2 class="text-lg font-semibold text-slate-900 dark:text-slate-100">{{ $siswa->name }}</h2>
                            <p class="text-sm text-slate-500 dark:text-slate-400">
                                {{ $siswa->kelasDiikuti->pluck('nama_kelas')->join(', ') ?: '-' }}
                            </p>
                        </div>
                    </div>
                    @if($pred)
                        <div class="flex flex-wrap items-center gap-x-4 gap-y-2">
                            <div class="flex items-center gap-2" title="{{ __('guru_analitik.prediksi.model_output_title') }}">
                                <span class="text-xs text-slate-500 dark:text-slate-400">{{ __('guru_analitik.prediksi.model_prediction') }}</span>
                                <x-badge variant="{{ $badgeVariant[$pred->hasil] ?? 'default' }}" dot>
                                    {{ $pred->hasil }}
                                </x-badge>
                            </div>
                            <div class="flex items-center gap-2" title="{{ __('guru_analitik.prediksi.threshold_status_title') }}">
                                <span class="text-xs text-slate-500 dark:text-slate-400">{{ __('guru_analitik.prediksi.threshold_short') }}</span>
                                <x-badge variant="{{ $badgeVariant[$pred->labelAmbang()] ?? 'default' }}" dot>
                                    {{ $pred->labelAmbang() }}
                                </x-badge>
                                <span class="text-sm font-semibold {{ $pred->skor_risiko >= 40 ? 'text-red-600 dark:text-red-400' : ($pred->skor_risiko >= 20 ? 'text-amber-700 dark:text-amber-400' : 'text-green-800 dark:text-green-400') }}">
                                    {{ number_format($pred->skor_risiko, 1) }}
                                </span>
                            </div>
                            @if($rf?->kategori_risiko_rf)
                                {{-- Kategori RF ditampilkan sebagai pembanding di sebelah
                                     kategori baseline, bukan menggantikannya. --}}
                                <div class="flex items-center gap-2" title="{{ __('guru_analitik.prediksi.rf_badge_title') }}">
                                    <span class="text-xs text-slate-500 dark:text-slate-400">{{ __('guru_analitik.prediksi.rf_label') }}</span>
                                    <x-badge variant="{{ $badgeVariant[$rf->kategori_risiko_rf] ?? 'default' }}">
                                        {{ $rf->kategori_risiko_rf }}
                                    </x-badge>
                                </div>
                            @endif
                        </div>
                    @endif
                </div>
            </x-card>

            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3 mb-4">
                @forelse($indicatorCards as $card)
                    <x-card class="!p-4">
                        <p class="text-xs font-medium text-slate-500 dark:text-slate-400 mb-1">{{ $card['label'] }}</p>
                        <p class="text-xl font-bold text-slate-900 dark:text-slate-100 {{ isset($card['value']) && in_array($card['label'], ['Pertemuan Tidak Aktif', 'Tren Nilai'], true) && $card['value'] < 0 ? 'text-red-600' : '' }}">
                            {{ is_null($card['value']) ? '-' : $card['value'] . ($card['suffix'] ?? '') }}
                        </p>
                    </x-card>
                @empty
                    <x-card class="!p-4 col-span-full">
                        <p class="text-sm text-slate-500 dark:text-slate-400">{{ __('guru_analitik.prediksi.indicator_unavailable') }}</p>
                    </x-card>
                @endforelse
            </div>

            <x-card class="mb-4">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-4">
                    <h3 class="text-base font-semibold text-slate-900 dark:text-slate-100">{{ __('guru_analitik.prediksi.indicator_progress') }}</h3>
                    <div id="indicator-buttons" class="flex flex-wrap gap-1.5"></div>
                </div>
                @if($history->isEmpty())
                    <p class="text-sm text-slate-500 dark:text-slate-400">
                        {{ __('guru_analitik.prediksi.history_unavailable') }}
                    </p>
                @else
                    <div class="relative h-72">
                        <canvas id="chart-indikator"></canvas>
                    </div>
                @endif
            </x-card>

            <x-card class="mb-4">
                <h3 class="text-base font-semibold text-slate-900 dark:text-slate-100">
                    {{ __('guru_analitik.prediksi.next_forecast_title') }}
                </h3>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 mb-4">
                    {{ __('guru_analitik.prediksi.next_forecast_desc1') }}
                    {{ __('guru_analitik.prediksi.next_forecast_desc2') }}
                </p>

                @if(! $mendatang['siap'])
                    <div class="rounded-lg bg-amber-50 dark:bg-amber-500/10 border border-amber-200 dark:border-amber-800 p-4">
                        <p class="text-sm text-amber-800 dark:text-amber-300">
                            {{ $mendatang['alasan'] ?? __('guru_analitik.prediksi.next_forecast_unavailable') }}
                        </p>
                    </div>
                @else
                    @php
                        $sekarang = $mendatang['sekarang_nilai'];
                        $prediksi = $mendatang['nilai'];
                        $delta = ($sekarang === null || $prediksi === null) ? null : round($prediksi - $sekarang, 1);
                        $baik = $delta === null ? null : $delta >= 0;
                        $warna = $baik === null ? 'text-slate-500' : ($baik ? 'text-green-800 dark:text-green-400' : 'text-red-600');
                    @endphp
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="rounded-lg border border-slate-200 dark:border-slate-800 p-4">
                            <p class="text-xs font-medium text-slate-500 dark:text-slate-400">{{ __('guru_analitik.prediksi.next_meeting_grade') }}</p>
                            <div class="flex items-baseline gap-2 mt-1">
                                <span class="text-xl font-bold text-slate-900 dark:text-slate-100">
                                    {{ number_format($prediksi, 1) }}
                                </span>
                                @if($delta !== null)
                                    <span class="text-sm font-semibold {{ $warna }}">
                                        {{ $delta > 0 ? '+' : '' }}{{ number_format($delta, 1) }}
                                    </span>
                                @endif
                            </div>
                            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                                {{ __('guru_analitik.prediksi.last_meeting') }}
                                @if($sekarang === null)
                                    <span class="italic">{{ __('guru_analitik.prediksi.no_data') }}</span>
                                @else
                                    {{ number_format($sekarang, 1) }}
                                @endif
                            </p>
                        </div>

                        @if(($mendatang['persentase_meleset'] ?? null) !== null)
                            <div class="rounded-lg border border-slate-200 dark:border-slate-800 p-4">
                                <p class="text-xs font-medium text-slate-500 dark:text-slate-400">{{ __('guru_analitik.prediksi.meleset_title') }}</p>
                                <div class="flex items-baseline gap-2 mt-1">
                                    <span class="text-xl font-bold text-slate-900 dark:text-slate-100">{{ number_format($mendatang['persentase_meleset'], 1) }}%</span>
                                </div>
                                <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-1">
                                    {{ __('guru_analitik.prediksi.meleset_note', ['ideal' => number_format((float) ($mendatang['nilai_ideal'] ?? 100), 0)]) }}
                                </p>
                            </div>
                        @endif
                    </div>

                    @php
                        // Metadata ramalan yang tersimpan, ditampilkan supaya nilai
                        // prediksi tidak berdiri sendiri tanpa identitas pertemuan
                        // sumber/target dan status kelengkapan datanya.
                        $metaRamalan = [
                            __('guru_analitik.prediksi.forecast_source') => $mendatang['pertemuan'] ?? null,
                            __('guru_analitik.prediksi.forecast_target') => $mendatang['pertemuan_target'] ?? null,
                            __('guru_analitik.prediksi.status_data') => ($mendatang['status_data'] ?? null) === 'lengkap'
                                ? __('guru_analitik.prediksi.status_lengkap')
                                : __('guru_analitik.prediksi.status_tidak_lengkap'),
                            __('guru_analitik.prediksi.nilai_aktual') => ($mendatang['nilai_aktual'] ?? null) === null
                                ? null
                                : number_format($mendatang['nilai_aktual'], 1),
                            __('guru_analitik.prediksi.mae_individual') => ($mendatang['mae_individual'] ?? null) === null
                                ? null
                                : number_format($mendatang['mae_individual'], 2),
                            __('guru_analitik.prediksi.model_version') => $mendatang['model_versi'] ?? null,
                        ];
                        $bm = $mendatang['benchmark'] ?? null;
                        $labelPertemuan = ! empty($mendatang['pertemuan']) ? ' — '.$mendatang['pertemuan'] : '';
                    @endphp

                    <div class="mt-4 grid grid-cols-1 sm:grid-cols-3 gap-x-6 gap-y-1">
                        @foreach($metaRamalan as $labelMeta => $nilaiMeta)
                            <div class="flex items-baseline justify-between gap-3 border-b border-slate-100 dark:border-slate-800 py-1">
                                <span class="text-[11px] text-slate-500 dark:text-slate-400">{{ $labelMeta }}</span>
                                <span class="text-[11px] font-medium text-slate-700 dark:text-slate-300 text-right">
                                    {{ $nilaiMeta ?? __('guru_analitik.prediksi.no_value_yet') }}
                                </span>
                            </div>
                        @endforeach
                    </div>

                    @if(! empty($mendatang['input']))
                        <div class="mt-5 border-t border-slate-200 dark:border-slate-800 pt-4">
                            <h4 class="text-xs font-semibold text-slate-700 dark:text-slate-300 mb-2">
                                {{ __('guru_analitik.prediksi.forecast_basis', ['suffix' => $labelPertemuan]) }}
                            </h4>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-6">
                                @php $setengahInput = (int) ceil(count($mendatang['input']) / 2); @endphp
                                @foreach([array_slice($mendatang['input'], 0, $setengahInput), array_slice($mendatang['input'], $setengahInput)] as $kolom)
                                    <table class="w-full text-xs">
                                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                                            @foreach($kolom as $in)
                                                <tr>
                                                    <td class="py-1.5 pr-3 text-slate-600 dark:text-slate-400">{{ $in['label'] }}</td>
                                                    <td class="py-1.5 text-right font-medium text-slate-800 dark:text-slate-200">
                                                        @if($in['nilai'] === null)
                                                            <span class="italic text-slate-500 dark:text-slate-400">{{ __('guru_analitik.prediksi.no_data') }}</span>
                                                        @else
                                                            {{ number_format($in['nilai'], 1) }}{{ $in['suffix'] }}
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                @endforeach
                            </div>
                            <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-2">
                                {{ __('guru_analitik.prediksi.forecast_meaning_1') }}
                                <span class="font-medium text-slate-600 dark:text-slate-400">{{ number_format($prediksi, 1) }}</span>.
                                {{ __('guru_analitik.prediksi.forecast_meaning_2') }}
                            </p>
                        </div>
                    @endif

                    <details class="mt-5 border-t border-slate-200 dark:border-slate-800 pt-4">
                        <summary class="cursor-pointer text-xs font-semibold text-slate-700 dark:text-slate-300">
                            {{ __('guru_analitik.prediksi.why_forecast', ['model' => $bm['pemenang_nama'] ?? __('guru_analitik.prediksi.svr_linear')]) }}
                        </summary>

                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-2 mb-2">
                            {{ __('guru_analitik.prediksi.why_intro') }} <em>{{ __('guru_analitik.prediksi.why_all') }}</em>
                            {{ __('guru_analitik.prediksi.why_mapping') }} <span class="font-medium">{{ __('guru_analitik.prediksi.why_target') }}</span>.
                            {{ __('guru_analitik.prediksi.why_pairs') }}
                            &ldquo;{{ __('guru_analitik.prediksi.why_pair_from') }} &rarr; {{ __('guru_analitik.prediksi.why_pair_to') }}&rdquo;.
                        </p>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mb-3">
                            <span class="font-medium text-slate-600 dark:text-slate-400">{{ __('guru_analitik.prediksi.how_title') }}</span>
                            {{ __('guru_analitik.prediksi.how_line1') }} {{ __('guru_analitik.prediksi.how_line2a') }} (&epsilon;) {{ __('guru_analitik.prediksi.how_line2b') }}
                            {{ __('guru_analitik.prediksi.how_line3') }}
                        </p>

                        @if($bm && ! empty($bm['benchmark']))

                            <div class="mt-3 rounded-lg bg-slate-50 dark:bg-slate-800/50 p-3 text-[11px] leading-relaxed text-slate-500 dark:text-slate-400">
                                <p class="font-semibold text-slate-600 dark:text-slate-400 mb-1">{{ __('guru_analitik.prediksi.glossary_title') }}</p>
                                <ul class="space-y-1">
                                    <li>
                                        <span class="font-medium text-slate-700 dark:text-slate-300">MAE</span> ({{ __('guru_analitik.prediksi.mae_name') }}) =
                                        {{ __('guru_analitik.prediksi.mae_def') }} (0&ndash;100).
                                        {{ __('guru_analitik.prediksi.mae_example') }}
                                        <span class="font-medium">{{ __('guru_analitik.prediksi.smaller_is_better') }}</span>
                                    </li>
                                    <li>
                                        <span class="font-medium text-slate-700 dark:text-slate-300">RMSE</span> ({{ __('guru_analitik.prediksi.rmse_name') }}) =
                                        {{ __('guru_analitik.prediksi.rmse_def') }} &ge; MAE.
                                        <span class="font-medium">{{ __('guru_analitik.prediksi.smaller_is_better') }}</span>
                                    </li>
                                    <li>
                                        <span class="font-medium text-slate-700 dark:text-slate-300">R&sup2;</span> ({{ __('guru_analitik.prediksi.r2_name') }}) =
                                        {{ __('guru_analitik.prediksi.r2_def') }} &le; 1.
                                        {{ __('guru_analitik.prediksi.r2_def2') }}
                                        <span class="font-medium">{{ __('guru_analitik.prediksi.r2_negative') }}</span>
                                        {{ __('guru_analitik.prediksi.r2_def3') }} <span class="font-medium">n/a</span>
                                        {{ __('guru_analitik.prediksi.r2_def4') }}
                                    </li>
                                </ul>
                                <p class="mt-2">
                                    {{ __('guru_analitik.prediksi.tested_at', ['waktu' => $bm['dilatih_pada'] ?? __('guru_analitik.prediksi.unknown_time')]) }}
                                </p>
                            </div>
                        @endif
                    </details>

                    {{-- Pembanding: Random Forest dilatih dari label acuan baseline,
                         jadi yang diukur di sini adalah kemampuan meniru aturan
                         baseline, bukan akurasi prediksi risiko yang sebenarnya. --}}
                    <details class="mt-5 border-t border-slate-200 dark:border-slate-800 pt-4">
                        <summary class="cursor-pointer text-xs font-semibold text-slate-700 dark:text-slate-300">
                            {{ __('guru_analitik.prediksi.rf_details_title') }}
                        </summary>

                        @if($benchmarkRf && ($benchmarkRf['akurasi'] ?? null) !== null)
                            <p class="text-xs text-slate-500 dark:text-slate-400 mt-2">
                                {{ __('guru_analitik.prediksi.rf_details_intro') }}
                            </p>

                            <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-1 mt-2 text-xs">
                                @foreach([
                                    __('guru_analitik.prediksi.rf_accuracy') => $benchmarkRf['akurasi'] ?? null,
                                    __('guru_analitik.prediksi.rf_precision') => $benchmarkRf['precision_macro'] ?? null,
                                    __('guru_analitik.prediksi.rf_recall') => $benchmarkRf['recall_macro'] ?? null,
                                    __('guru_analitik.prediksi.rf_f1') => $benchmarkRf['f1_macro'] ?? null,
                                ] as $labelRf => $nilaiRf)
                                    <div class="flex items-baseline justify-between gap-3 border-b border-slate-100 dark:border-slate-800 py-1">
                                        <dt class="text-slate-500 dark:text-slate-400">{{ $labelRf }}</dt>
                                        <dd class="font-medium text-slate-800 dark:text-slate-200">{{ number_format((float) $nilaiRf * 100, 1) }}%</dd>
                                    </div>
                                @endforeach
                                <div class="flex items-baseline justify-between gap-3 border-b border-slate-100 dark:border-slate-800 py-1">
                                    <dt class="text-slate-500 dark:text-slate-400">{{ __('guru_analitik.prediksi.rf_test_samples') }}</dt>
                                    <dd class="font-medium text-slate-800 dark:text-slate-200">{{ $benchmarkRf['test_samples'] ?? '-' }}</dd>
                                </div>
                            </dl>

                            @if(! empty($benchmarkRf['confusion_matrix']))
                                <p class="text-[11px] font-semibold text-slate-600 dark:text-slate-400 mt-3 mb-1">{{ __('guru_analitik.prediksi.rf_matrix_title') }}</p>
                                <table class="text-[11px]">
                                    <thead>
                                        <tr>
                                            <th class="pr-3 text-left font-medium text-slate-500 dark:text-slate-400"></th>
                                            @foreach(\App\Services\KlasifikasiRisikoService::LABELS as $labelKolom)
                                                <th class="px-2 py-0.5 font-medium text-slate-500 dark:text-slate-400">{{ $labelKolom }}</th>
                                            @endforeach
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($benchmarkRf['confusion_matrix'] as $labelBaris => $kolom)
                                            <tr>
                                                <td class="pr-3 py-0.5 font-medium text-slate-600 dark:text-slate-400">{{ $labelBaris }}</td>
                                                @foreach($kolom as $jumlah)
                                                    <td class="px-2 py-0.5 text-center text-slate-700 dark:text-slate-300">{{ $jumlah }}</td>
                                                @endforeach
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            @endif

                            <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-3">{{ __('guru_analitik.prediksi.rf_caveat') }}</p>
                        @else
                            <p class="text-xs text-slate-500 dark:text-slate-400 mt-2">{{ __('guru_analitik.prediksi.rf_unavailable') }}</p>
                        @endif
                    </details>
                @endif
            </x-card>

            <x-card>
                <h3 class="text-base font-semibold text-slate-900 dark:text-slate-100 mb-4">{{ __('guru_analitik.prediksi.engagement_heading') }}</h3>
                <div class="relative h-60">
                    <canvas id="chart-aktivitas"></canvas>
                </div>
            </x-card>

            {{-- `@json`, BUKAN `Js::from()`: `Js::from()` menghasilkan EKSPRESI
                 JavaScript (`JSON.parse('...')`), yang hanya benar bila ditulis
                 sebagai `const d = {{ Js::from($x) }}`. Di sini payload dibaca
                 lewat `JSON.parse(dataEl.textContent)`, jadi isinya harus JSON
                 murni. Kalau dibungkus `Js::from()`, textContent berisi
                 `JSON.parse('...')` -> JSON.parse() meledak -> chart gagal
                 render tanpa error yang terlihat. `@json` = json_encode(...,
                 15) yang aman terhadap `</script>`. --}}
            <script id="prediksi-show-data" type="application/json">@json($chartData)</script>
        @else
            <x-card>
                <p class="text-sm text-slate-500 dark:text-slate-400">{{ __('guru_analitik.prediksi.select_student_hint') }}</p>
            </x-card>
        @endif
    @endif
@endsection
