@extends('layouts.app')

@section('title', __('guru_analitik.nilai.page_title') . ' - SIMBA')

@section('content')
    <x-page-header
        :title="__('guru_analitik.nilai.page_title')"
        :subtitle="__('guru_analitik.nilai.page_subtitle')"
    />

    @if($kelasList->isEmpty())
        <x-card>
            <p class="text-sm text-slate-500 dark:text-slate-400">{{ __('guru_analitik.nilai.no_class') }}</p>
        </x-card>
    @elseif(! $kelas)
        <x-card>
            <p class="text-sm text-slate-500 dark:text-slate-400">{{ __('guru_analitik.nilai.class_not_found') }}</p>
        </x-card>
    @else
        <x-card class="mb-4">
            <form method="GET" class="flex flex-wrap items-end gap-3">
                <label class="block">
                    <span class="block text-xs font-medium text-slate-600 dark:text-slate-400 mb-1">{{ __('guru_analitik.label.class') }}</span>
                    <select
                        name="kelas"
                        onchange="this.form.submit()"
                        class="rounded-md border-slate-300 dark:border-slate-700 text-sm focus:border-emerald-500 focus:ring-emerald-500"
                    >
                        @foreach($kelasList as $k)
                            <option value="{{ $k->id }}" @selected($kelas && $kelas->id === $k->id)>
                                {{ $k->nama_kelas }}
                            </option>
                        @endforeach
                    </select>
                </label>
                <noscript>
                    <x-btn-primary type="submit">{{ __('guru_analitik.nilai.show') }}</x-btn-primary>
                </noscript>
                <p class="text-xs text-slate-500 dark:text-slate-400">
                    <span class="font-semibold text-emerald-700 dark:text-emerald-400">K</span> = {{ __('guru_analitik.nilai.legend_quiz') }},
                    <span class="font-semibold text-emerald-700 dark:text-emerald-400">T</span> = {{ __('guru_analitik.nilai.legend_assignment') }}.
                    {{ __('guru_analitik.nilai.legend_hint') }}
                </p>
            </form>
        </x-card>

        @if($jumlahKolom === 0)
            <x-card>
                <p class="text-sm text-slate-500 dark:text-slate-400">
                    {{ __('guru_analitik.label.class') }} <span class="font-semibold">{{ $kelas->nama_kelas }}</span> {{ __('guru_analitik.nilai.class_no_quiz') }}
                </p>
            </x-card>
        @else
            <x-card :padding="false">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-slate-50 dark:bg-slate-800/50 border-b border-slate-200 dark:border-slate-800">
                            <tr>
                                <th class="sticky left-0 z-10 bg-slate-50 dark:bg-slate-800/50 px-4 py-3 text-left font-semibold text-slate-600 dark:text-slate-400">
                                    {{ __('guru_analitik.label.student') }}
                                </th>
                                @foreach($kolom as $kol)
                                    <th class="px-3 py-3 text-center font-semibold align-bottom" title="{{ $kol['judul'] }}{{ $kol['pertemuan'] ? ' — '.$kol['pertemuan'] : '' }}">
                                        <span class="block text-emerald-700 dark:text-emerald-400">
                                            {{ $kol['kode'] }}
                                        </span>
                                        <span class="block max-w-[90px] truncate text-[10px] font-normal text-slate-500 dark:text-slate-400">
                                            {{ $kol['pendek'] }}
                                        </span>
                                    </th>
                                @endforeach
                                <th class="border-l border-slate-200 dark:border-slate-800 px-4 py-3 text-center font-semibold text-slate-600 dark:text-slate-400">
                                    {{ __('guru_analitik.label.average') }}
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                            @forelse($siswaList as $siswa)
                                <tr class="hover:bg-slate-50 dark:hover:bg-slate-800">
                                    <td class="sticky left-0 z-10 bg-white dark:bg-slate-900 px-4 py-2.5 font-medium">
                                        <a href="{{ route('pengajar.nilai.show', ['siswa' => $siswa->id, 'kelas' => $kelas->id]) }}"
                                           class="text-emerald-700 dark:text-emerald-400 hover:text-emerald-800 dark:hover:text-emerald-300 hover:underline">
                                            {{ $siswa->name }}
                                        </a>
                                    </td>
                                    @foreach($kolom as $kol)
                                        @php $v = $nilai[$siswa->id][$kol['key']] ?? null; @endphp
                                        <td class="px-3 py-2.5 text-center">
                                            @if($v === null)
                                                <span class="text-slate-300">-</span>
                                            @else
                                                <span @class([
                                                    'rounded px-2 py-0.5 text-xs font-semibold',
                                                    'bg-red-100 text-red-700 dark:bg-red-500/20 dark:text-red-300' => $v < 60,
                                                    'bg-amber-100 text-amber-700 dark:bg-amber-500/20 dark:text-amber-300' => $v >= 60 && $v < 75,
                                                    'bg-emerald-100 text-emerald-700 dark:bg-emerald-600/20 dark:text-emerald-300' => $v >= 75,
                                                ])>{{ number_format($v, 1) }}</span>
                                            @endif
                                        </td>
                                    @endforeach
                                    <td class="border-l border-slate-200 dark:border-slate-800 px-4 py-2.5 text-center font-bold text-slate-900 dark:text-slate-100">
                                        {{ $rata[$siswa->id] === null ? '-' : number_format($rata[$siswa->id], 1) }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="{{ $jumlahKolom + 2 }}" class="px-4 py-6 text-center text-slate-500 dark:text-slate-400">
                                        {{ __('guru_analitik.nilai.no_student_in_class') }}
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </x-card>
        @endif
    @endif
@endsection
