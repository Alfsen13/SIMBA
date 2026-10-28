@extends('layouts.app')

@section('title', __('navigation.student_features') . ' - SIMBA')

@section('content')
    <x-page-header
        :title="__('guru_analitik.fitur.page_title')"
        :subtitle="__('guru_analitik.fitur.page_subtitle')"
    />

    <div class="mb-4 rounded-lg border border-sky-200 dark:border-sky-800 bg-sky-50 dark:bg-sky-500/10 p-4">
        <div class="flex items-start gap-3">
            <span class="mt-0.5 flex-shrink-0 text-sky-600 dark:text-sky-400">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </span>
            <p class="text-sm text-sky-900 dark:text-sky-200">
                {{ __('guru_analitik.fitur.banner_1') }} <strong>{{ __('guru_analitik.fitur.banner_derived') }}</strong>
                {{ __('guru_analitik.fitur.banner_2') }}
                {{ __('guru_analitik.fitur.banner_3a') }} <strong>{{ __('navigation.prediction') }}</strong>
                {{ __('guru_analitik.fitur.banner_3b') }}
            </p>
        </div>
    </div>

    <x-card :padding="false">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 dark:bg-slate-800/50 border-b border-slate-200 dark:border-slate-800">
                    <tr>
                        <x-th align="left">{{ __('guru_analitik.label.student') }}</x-th>
                        <x-th align="left">{{ __('guru_analitik.fitur.avg_score') }}</x-th>
                        <x-th align="left">{{ __('guru_analitik.fitur.pct_late') }}</x-th>
                        <x-th align="left">{{ __('guru_analitik.fitur.pct_missed') }}</x-th>
                        <x-th align="left">{{ __('guru_analitik.fitur.overall_score') }}</x-th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse($siswaList as $siswa)
                        @php $fitur = $siswa->fiturHybridSiswa; @endphp
                        <x-tr class="hover:bg-slate-50 dark:hover:bg-slate-800">
                            <x-td class="font-medium text-slate-900 dark:text-slate-100">
                                {{ $siswa->name }}
                            </x-td>
                            <x-td>@include('guru.fitur-siswa._nilai', ['nilai' => $fitur?->rata_nilai])</x-td>
                            <x-td>@include('guru.fitur-siswa._nilai', ['nilai' => $fitur?->persen_terlambat, 'sebalik' => true])</x-td>
                            <x-td>@include('guru.fitur-siswa._nilai', ['nilai' => $fitur?->persen_terlewat, 'sebalik' => true])</x-td>
                            <x-td>@include('guru.fitur-siswa._nilai', ['nilai' => $fitur?->nilai_keseluruhan])</x-td>
                        </x-tr>
                    @empty
                        <x-tr>
                            <x-td align="center" muted colspan="5" class="py-6">
                                {{ __('guru_analitik.prediksi.no_student') }}
                            </x-td>
                        </x-tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4 border-t border-slate-100 dark:border-slate-800">
            {{ $siswaList->links() }}
        </div>
    </x-card>
@endsection
