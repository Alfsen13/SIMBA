@extends('layouts.app')

@section('title', __('navigation.quizzes') . ' - SIMBA')

@section('content')
    <x-page-header
        :title="__('guru_soal.daftar_kuis')"
        :subtitle="__('guru_soal.subtitle_daftar_kuis')"
    />

    <x-card :padding="false">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 dark:bg-slate-800/50 border-b border-slate-200 dark:border-slate-800">
                <tr>
                    <x-th align="left">{{ __('guru_soal.judul') }}</x-th>
                    <x-th align="left">{{ __('guru_soal.kelas') }}</x-th>
                    <x-th align="left">{{ __('guru_soal.pertemuan') }}</x-th>
                    <x-th align="left">{{ __('guru_soal.durasi') }}</x-th>
                    <x-th align="left">{{ __('common.active') }}</x-th>
                    <x-th align="left">{{ __('guru_soal.peserta') }}</x-th>
                    <x-th align="left">{{ __('common.actions') }}</x-th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                @forelse($kuisList as $kuis)
                    <x-tr>
                        <x-td class="font-medium text-slate-900 dark:text-slate-100">{{ $kuis->judul }}</x-td>
                        <x-td>{{ $kuis->kelas?->nama_kelas ?? '-' }}</x-td>
                        <x-td>{{ $kuis->pertemuan?->nama_pertemuan ?? '-' }}</x-td>
                        <x-td>{{ __('guru_soal.durasi_menit', ['menit' => $kuis->durasi_menit]) }}</x-td>
                        <x-td>
                            <x-badge :variant="$kuis->is_aktif ? 'verified' : 'danger'">
                                {{ $kuis->is_aktif ? __('common.active') : __('guru_soal.nonaktif') }}
                            </x-badge>
                        </x-td>
                        <x-td>
                            <span class="text-sm {{ $kuis->hasil_count > 0 ? 'text-green-800 dark:text-green-400 font-medium' : 'text-slate-500 dark:text-slate-400' }}">
                                {{ __('guru_soal.jumlah_siswa', ['jumlah' => $kuis->hasil_count]) }}
                            </span>
                        </x-td>
                        <x-td>
                            <div class="flex items-center gap-2">
                                <x-btn-secondary href="{{ route('pengajar.kuis.edit', $kuis) }}" size="sm">
                                    {{ __('guru_soal.edit') }}
                                </x-btn-secondary>
                                <form
                                    action="{{ route('pengajar.kuis.destroy', $kuis) }}"
                                    method="POST"
                                    x-data
                                    x-on:submit.prevent="confirm('{{ __('guru_soal.konfirmasi_hapus_kuis') }}') && $el.submit()"
                                >
                                    @csrf
                                    @method('DELETE')
                                    <button
                                        type="submit"
                                        class="text-sm text-red-600 dark:text-red-400 hover:text-red-800"
                                    >
                                        {{ __('common.delete') }}
                                    </button>
                                </form>
                            </div>
                        </x-td>
                    </x-tr>
                @empty
                    <x-tr>
                        <x-td align="center" muted colspan="7" class="py-6">{{ __('guru_soal.kosong_kuis') }}</x-td>
                    </x-tr>
                @endforelse
            </tbody>
        </table>
        <div class="p-4 border-t border-slate-100 dark:border-slate-800">{{ $kuisList->links() }}</div>
    </x-card>
@endsection
