@extends('layouts.app')

@section('title', __('guru_analitik.remedial.detail_title') . ' - SIMBA')

@section('content')
    <div class="max-w-4xl mx-auto">
        <x-back-link
            href="{{ route('pengajar.remedial.index') }}"
            :label="__('common.back')"
        />

        <x-card>
            <div class="p-4 sm:p-6">
                <div class="flex items-center gap-2 mb-1">
                    <x-badge
                        variant="{{ $remedial->status === 'belum_dibaca' ? 'warning' : 'success' }}"
                        :dot="true"
                    >
                        {{ $remedial->status === 'belum_dibaca' ? __('guru_analitik.remedial.status_unread') : ($remedial->status === 'selesai' ? __('guru_analitik.remedial.status_done') : __('guru_analitik.remedial.status_read')) }}
                    </x-badge>
                    <span class="text-xs text-slate-500 dark:text-slate-400">
                        {{ $remedial->generated_at?->format('d M Y H:i') ?: '-' }}
                    </span>
                </div>

                <h1 class="text-xl font-bold text-slate-900 dark:text-slate-100 mt-3 mb-4">
                    {{ $remedial->topik_lemah ?: __('guru_analitik.remedial.fallback_title') }}
                </h1>

                <div class="bg-slate-50 dark:bg-slate-800/50 rounded-lg p-4 mb-4">
                    <h2 class="text-sm font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-2">
                        {{ __('guru_analitik.remedial.student_info') }}
                    </h2>
                    <p class="text-slate-900 dark:text-slate-100 font-medium">
                        {{ $remedial->siswa?->name ?: '-' }}
                    </p>
                    <p class="text-sm text-slate-500 dark:text-slate-400">
                        {{ $remedial->siswa?->email ?: '-' }}
                    </p>
                </div>

                <div class="mb-4">
                    <h2 class="text-sm font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-3">
                        {{ __('guru_analitik.remedial.summary') }}
                    </h2>
                    <div class="prose prose-sm max-w-none text-slate-700 dark:text-slate-300 leading-relaxed whitespace-pre-wrap">
                        {{ $remedial->ringkasan }}
                    </div>
                </div>

                @if($remedial->generasi_gagal_at && empty($remedial->tugas_remedial))
                    <div class="mb-4 rounded-lg border border-amber-200 bg-amber-50 p-4 text-sm text-amber-800 dark:border-amber-800 dark:bg-amber-500/10 dark:text-amber-300">
                        <p>{{ __('guru_analitik.remedial.generation_failed_message') }}</p>
                        <form method="POST" action="{{ route('pengajar.remedial.retry-generation', $remedial) }}" class="mt-3">
                            @csrf
                            <x-btn-secondary type="submit" size="sm">{{ __('guru_analitik.remedial.retry_generation') }}</x-btn-secondary>
                        </form>
                    </div>
                @elseif(empty($remedial->tugas_remedial))
                    <div class="mb-4 rounded-lg border border-blue-100 bg-blue-50 p-4 text-sm text-blue-700 dark:border-blue-800 dark:bg-blue-500/10 dark:text-blue-300">
                        {{ __('guru_analitik.remedial.generation_pending') }}
                    </div>
                @endif

                @if(empty($remedial->tugas_remedial) && $remedial->latihan)
                    <div class="border-t pt-6 mb-4">
                        <h2 class="text-sm font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-3">
                            {{ __('guru_analitik.remedial.exercise') }}
                        </h2>
                        @if(! empty($remedial->latihan_items))
                            <x-remedial-latihan :items="$remedial->latihan_items" />
                        @else
                            <div class="prose prose-sm max-w-none text-slate-700 dark:text-slate-300 leading-relaxed whitespace-pre-wrap">
                                {{ $remedial->latihan }}
                            </div>
                        @endif
                    </div>
                @endif

                @if(! empty($remedial->bloom_tujuan))
                    <div class="border-t pt-6 mb-4">
                        <h2 class="text-sm font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-3">
                            {{ __('guru_analitik.remedial.learning_objectives') }}
                            <span class="normal-case font-normal">{{ __('guru_analitik.remedial.bloom_taxonomy') }}</span>
                        </h2>
                        <ul class="space-y-2">
                            @foreach($remedial->bloom_tujuan as $t)
                                @php $kode = strtoupper((string) ($t['kode'] ?? '')); @endphp
                                <li class="flex items-start gap-2">
                                    <span class="inline-flex items-center shrink-0 rounded-md text-xs font-bold px-2 py-1
                                        {{ match($kode) {
                                            'C1' => 'bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300',
                                            'C2' => 'bg-blue-50 text-blue-700',
                                            'C3' => 'bg-emerald-50 text-emerald-700',
                                            'C4' => 'bg-purple-50 text-purple-700',
                                            'C5' => 'bg-amber-50 text-amber-700',
                                            'C6' => 'bg-green-50 text-green-700',
                                            default => 'bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300',
                                        } }}">
                                        {{ $kode }}
                                    </span>
                                    <div class="text-sm text-slate-600 dark:text-slate-400">
                                        <span class="font-medium block text-slate-800 dark:text-slate-200">{{ $t['level'] ?? '-' }}</span>
                                        {{ $t['tujuan'] ?? '' }}
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @if(! empty($remedial->tugas_remedial))
                    <div class="border-t pt-6 mb-4">
                        <h2 class="text-sm font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-3">
                            {{ __('guru_analitik.remedial.ai_task_title') }}
                        </h2>
                        @if($remedial->hasil_remedial)
                            <div class="mb-4 p-4 rounded-lg border
                                {{ $remedial->bloom_tercapai ? 'bg-green-50 dark:bg-green-500/10 border-green-200 dark:border-green-800' : 'bg-amber-50 border-amber-200' }}">
                                <p class="font-semibold {{ $remedial->bloom_tercapai ? 'text-green-700 dark:text-green-300' : 'text-amber-700' }}">
                                    {{ $remedial->bloom_tercapai
                                        ? __('guru_analitik.remedial.mastered')
                                        : __('guru_analitik.remedial.not_mastered') }}
                                </p>
                                <p class="text-sm text-slate-700 dark:text-slate-300 mt-1 whitespace-pre-wrap">{{ $remedial->mastery_alasan }}</p>
                                <p class="text-xs text-slate-500 dark:text-slate-400 mt-2">
                                    {{ __('guru_analitik.remedial.meta_ai', [
                                        'tanggal' => $remedial->dinilai_at?->format('d M Y H:i') ?: '-',
                                        'konteks' => __('guru_analitik.remedial.bloom_context'),
                                    ]) }}
                                </p>
                            </div>
                        @elseif($remedial->jawaban_remedial)
                            <div class="p-4 rounded-lg border border-blue-100 dark:border-blue-800 bg-blue-50 dark:bg-blue-500/10 text-sm text-blue-700 dark:text-blue-300 mb-4">
                                {{ __('guru_analitik.remedial.awaiting_review') }}
                            </div>
                        @else
                            <p class="text-sm text-slate-500 dark:text-slate-400 mb-4">{{ __('guru_analitik.remedial.not_started') }}</p>
                        @endif

                        @foreach($remedial->tugas_remedial as $i => $s)
                            @php
                                $kode = strtoupper((string) ($s['bloom'] ?? ''));
                                $hasil = is_array($remedial->hasil_remedial ?? null)
                                    ? collect($remedial->hasil_remedial)->firstWhere('indeks', $i)
                                    : null;
                            @endphp
                            <div class="border border-slate-100 dark:border-slate-800 rounded-lg p-3 mb-3">
                                <div class="flex items-center gap-2 mb-1">
                                    <p class="text-sm font-medium text-slate-900 dark:text-slate-100">{{ $i + 1 }}. <x-math :value="$s['pertanyaan'] ?? __('guru_analitik.remedial.question_fallback')" /></p>
                                    <span class="ml-auto text-[10px] font-bold px-2 py-0.5 rounded
                                        {{ match($kode) {
                                            'C1' => 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400',
                                            'C2' => 'bg-blue-50 text-blue-600',
                                            'C3' => 'bg-emerald-50 text-emerald-700',
                                            'C4' => 'bg-purple-50 text-purple-600',
                                            'C5' => 'bg-amber-50 text-amber-600',
                                            'C6' => 'bg-green-50 text-green-700',
                                            default => 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400',
                                        } }}">
                                        {{ $kode }} — {{ $s['bloom'] ?? '' }}
                                    </span>
                                </div>
                                @if(! empty($s['referensi']))
                                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                                        <span class="font-medium text-slate-500 dark:text-slate-400">{{ __('guru_analitik.remedial.answer_reference') }}</span> {{ $s['referensi'] }}
                                    </p>
                                @endif
                                @if($remedial->jawaban_remedial)
                                    <p class="text-sm text-slate-700 dark:text-slate-300 mt-2 whitespace-pre-wrap">
                                        <span class="text-slate-500 dark:text-slate-400">{{ __('guru_analitik.remedial.student_answer') }}</span> {{ $remedial->jawaban_remedial[$i] ?? '-' }}
                                    </p>
                                @endif
                                @if($hasil)
                                    @php
                                        $variant = match($hasil['status'] ?? '') {
                                            'benar' => 'verified',
                                            'mendekati' => 'warning',
                                            default => 'danger',
                                        };
                                    @endphp
                                    <div class="flex items-center gap-2 mt-2">
                                        <x-badge :variant="$variant">{{ ucfirst($hasil['status'] ?? 'mendekati') }}</x-badge>
                                        <span class="text-xs text-slate-500 dark:text-slate-400">{{ __('guru_analitik.remedial.score_label') }} {{ $hasil['skor'] ?? '-' }}/100</span>
                                        @if($hasil['bloom'] ?? null)
                                            <span class="text-xs text-slate-500 dark:text-slate-400">{{ __('guru_analitik.remedial.measured_at', ['level' => $hasil['bloom']]) }}</span>
                                        @endif
                                        @if(! empty($hasil['alasan']))
                                            <span class="text-xs text-slate-500 dark:text-slate-400">— {{ $hasil['alasan'] }}</span>
                                        @endif
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @endif

                <div class="border-t pt-4 flex items-center justify-between text-xs text-slate-500 dark:text-slate-400">
                    <span>{{ __('guru_analitik.remedial.provider_label', ['provider' => $remedial->provider_llm ?: '-']) }}</span>
                    @if($remedial->prediksiAi)
                        <span>{{ __('guru_analitik.remedial.risk_score_label', ['skor' => $remedial->prediksiAi->skor_risiko ?? '-']) }}</span>
                    @endif
                </div>
            </div>
        </x-card>
    </div>
@endsection