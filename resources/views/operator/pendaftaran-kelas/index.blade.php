@extends('layouts.app')

@section('title', 'Pendaftaran Kelas - Operator')

@section('content')
    <div class="py-6">
        <x-page-header
            :title="__('navigation.class_registration')"
            :subtitle="__('operator.registration_subtitle')"
        />

        @if($permintaan->isEmpty())
            <x-empty-state :message="__('operator.empty_registrations_page')" />
        @else
            <x-card :padding="false">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-slate-50 dark:bg-slate-800/50 border-b border-slate-200 dark:border-slate-800">
                            <tr>
                                <x-th align="left">{{ __('operator.th_student') }}</x-th>
                                <x-th align="left">{{ __('navigation.classes') }}</x-th>
                                <x-th align="left">{{ __('operator.th_submitted') }}</x-th>
                                <x-th align="right">{{ __('common.actions') }}</x-th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                            @foreach($permintaan as $p)
                                <x-tr>
                                    <x-td>
                                        <div class="font-medium text-slate-900 dark:text-slate-100">{{ $p->siswa_nama }}</div>
                                        <div class="text-xs text-slate-500 dark:text-slate-400">{{ $p->siswa_email }}</div>
                                    </x-td>
                                    <x-td>
                                        <div class="font-medium text-slate-900 dark:text-slate-100">{{ $p->nama_kelas }}</div>
                                        <div class="text-xs text-slate-500 dark:text-slate-400">
                                            {{ $p->kode_kelas }}@if($p->kategori) &middot; {{ $p->kategori }}@endif
                                        </div>
                                    </x-td>
                                    <x-td nowrap>{{ \Carbon\Carbon::parse($p->created_at)->format('d/m/Y H:i') }}</x-td>
                                    <x-td align="right" nowrap>
                                        <div class="flex items-center justify-end gap-2">
                                            <form method="POST" action="{{ route('operator.pendaftaran-kelas.setujui', ['kelas' => $p->kelas_id, 'siswa' => $p->siswa_id]) }}">
                                                @csrf
                                                <x-btn-primary type="submit" size="sm">{{ __('operator.approve') }}</x-btn-primary>
                                            </form>
                                            <form method="POST" action="{{ route('operator.pendaftaran-kelas.tolak', ['kelas' => $p->kelas_id, 'siswa' => $p->siswa_id]) }}">
                                                @csrf
                                                <x-btn-secondary type="submit" size="sm" color="red">{{ __('operator.title_reject') }}</x-btn-secondary>
                                            </form>
                                        </div>
                                    </x-td>
                                </x-tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </x-card>
            <div class="mt-4">{{ $permintaan->links() }}</div>
        @endif
    </div>
@endsection
