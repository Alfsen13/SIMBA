@extends('layouts.app')

@section('title', __('operator.title_user_detail') . ' - SIMBA')

@section('content')
    <x-back-link
        href="{{ route('operator.users.index') }}"
        :label="__('common.back')"
    />

    <div class="max-w-full">
        <x-card :padding="false">
            <div class="bg-gradient-to-r from-emerald-600 to-emerald-800 px-6 py-8">
                <div class="flex items-center gap-4">
                    <div class="w-16 h-16 rounded-full bg-white/20 flex items-center justify-center text-2xl font-bold text-white">
                        {{ substr($user->name, 0, 1) }}
                    </div>
                    <div class="text-white">
                        <h1 class="text-xl font-bold">{{ $user->name }}</h1>
                        <p class="text-emerald-200 text-sm mt-0.5">{{ $user->email }}</p>
                    </div>
                </div>
            </div>

            <div class="px-6 py-5 space-y-5">
                <div class="flex flex-wrap gap-3">
                    <x-badge
                        :variant="$user->role"
                        :dot="true"
                        :label="ucfirst($user->role)"
                    />
                    <x-badge
                        :variant="$user->status"
                        :dot="true"
                        :label="ucfirst($user->status)"
                    />
                </div>

                <div class="border-t border-slate-100 dark:border-slate-800 pt-5">
                    <h3 class="text-sm font-semibold text-slate-900 dark:text-slate-100 uppercase tracking-wider mb-4">{{ __('operator.account_info') }}</h3>
                    <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <dt class="text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider">{{ __('operator.th_name') }}</dt>
                            <dd class="mt-1 text-sm text-slate-900 dark:text-slate-100">{{ $user->name }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider">{{ __('operator.th_email') }}</dt>
                            <dd class="mt-1 text-sm text-slate-900 dark:text-slate-100">{{ $user->email }}</dd>
                        </div>
                        @if($user->nip)
                            <div>
                                <dt class="text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider">{{ __('operator.th_nip') }}</dt>
                                <dd class="mt-1 text-sm text-slate-900 dark:text-slate-100">{{ $user->nip }}</dd>
                            </div>
                        @endif
                        @if($user->nisn)
                            <div>
                                <dt class="text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider">{{ __('operator.th_nisn') }}</dt>
                                <dd class="mt-1 text-sm text-slate-900 dark:text-slate-100">{{ $user->nisn }}</dd>
                            </div>
                        @endif
                        <div>
                            <dt class="text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider">{{ __('operator.th_role') }}</dt>
                            <dd class="mt-1 text-sm text-slate-900 dark:text-slate-100">{{ ucfirst($user->role) }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider">{{ __('common.status') }}</dt>
                            <dd class="mt-1 text-sm text-slate-900 dark:text-slate-100">{{ ucfirst($user->status) }}</dd>
                        </div>
                        @if($user->verified_at)
                            <div>
                                <dt class="text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider">{{ __('operator.verified_at') }}</dt>
                                <dd class="mt-1 text-sm text-slate-900 dark:text-slate-100">{{ $user->verified_at->format('d M Y H:i') }}</dd>
                            </div>
                        @endif
                        <div>
                            <dt class="text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider">{{ __('operator.joined_at') }}</dt>
                            <dd class="mt-1 text-sm text-slate-900 dark:text-slate-100">{{ $user->created_at->format('d M Y H:i') }}</dd>
                        </div>
                    </dl>
                </div>

                @php
                    $profil = $user->profilPendidikan;
                    $aplikasi = $user->aplikasiPengajar;
                @endphp

                @if($user->role === 'pelajar')
                    <div class="border-t border-slate-100 dark:border-slate-800 pt-5">
                        <h3 class="text-sm font-semibold text-slate-900 dark:text-slate-100 uppercase tracking-wider mb-4">{{ __('operator.profil_pendidikan') }}</h3>
                        @if($profil)
                            <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <dt class="text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider">{{ __('operator.profil_jalur') }}</dt>
                                    <dd class="mt-1 text-sm text-slate-900 dark:text-slate-100">{{ $profil->jalur?->nama ?? '—' }}</dd>
                                </div>
                                <div>
                                    <dt class="text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider">{{ __('operator.profil_jenjang') }}</dt>
                                    <dd class="mt-1 text-sm text-slate-900 dark:text-slate-100">{{ $profil->jenjang?->nama ?? '—' }}</dd>
                                </div>
                                <div>
                                    <dt class="text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider">{{ __('operator.profil_tingkat') }}</dt>
                                    <dd class="mt-1 text-sm text-slate-900 dark:text-slate-100">{{ $profil->tingkat?->nama ?? '—' }}</dd>
                                </div>
                                <div>
                                    <dt class="text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider">{{ __('operator.profil_institusi') }}</dt>
                                    <dd class="mt-1 text-sm text-slate-900 dark:text-slate-100">{{ $profil->institusi ?? '—' }}</dd>
                                </div>
                                <div>
                                    <dt class="text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider">{{ __('operator.profil_program') }}</dt>
                                    <dd class="mt-1 text-sm text-slate-900 dark:text-slate-100">{{ $profil->program ?? '—' }}</dd>
                                </div>
                                <div>
                                    <dt class="text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider">{{ __('operator.profil_minat') }}</dt>
                                    <dd class="mt-1 text-sm text-slate-900 dark:text-slate-100">{{ $profil->minat ?? '—' }}</dd>
                                </div>
                            </dl>
                        @else
                            <p class="text-sm text-slate-500 dark:text-slate-400 italic">{{ __('operator.profil_pendidikan_kosong') }}</p>
                        @endif
                    </div>
                @endif

                @if($user->role === 'pengajar')
                    <div class="border-t border-slate-100 dark:border-slate-800 pt-5">
                        <h3 class="text-sm font-semibold text-slate-900 dark:text-slate-100 uppercase tracking-wider mb-4">{{ __('operator.profil_pengajar') }}</h3>
                        @if($aplikasi)
                            <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <dt class="text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider">{{ __('operator.profil_pendidikan_tertinggi') }}</dt>
                                    <dd class="mt-1 text-sm text-slate-900 dark:text-slate-100">{{ $aplikasi->pendidikan_tertinggi ?? '—' }}</dd>
                                </div>
                                <div>
                                    <dt class="text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider">{{ __('operator.profil_kategori_mapel') }}</dt>
                                    <dd class="mt-1 text-sm text-slate-900 dark:text-slate-100">{{ $aplikasi->kategori_mapel ?? '—' }}</dd>
                                </div>
                                <div>
                                    <dt class="text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider">{{ __('operator.profil_jenjang_dan_tingkat') }}</dt>
                                    <dd class="mt-1 text-sm text-slate-900 dark:text-slate-100">{{ $aplikasi->jenjang_dan_tingkat ?? '—' }}</dd>
                                </div>
                                <div>
                                    <dt class="text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider">{{ __('operator.profil_pengalaman') }}</dt>
                                    <dd class="mt-1 text-sm text-slate-900 dark:text-slate-100 whitespace-pre-line">{{ $aplikasi->pengalaman_mengajar ?? '—' }}</dd>
                                </div>
                            </dl>

                            <div class="mt-4">
                                <p class="text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-2">{{ __('operator.profil_mapel') }}</p>
                                @if($aplikasi->mata_pelajaran)
                                    <div class="flex flex-wrap gap-1.5">
                                        @foreach($aplikasi->mata_pelajaran as $mapel)
                                            <x-badge variant="default" :label="$mapel" />
                                        @endforeach
                                    </div>
                                @else
                                    <p class="text-sm text-slate-500 dark:text-slate-400 italic">{{ __('operator.profil_mapel_kosong') }}</p>
                                @endif
                            </div>

                            <div class="mt-4">
                                <p class="text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-2">{{ __('operator.profil_berkas') }}</p>
                                <div class="space-y-3">
                                    @if($aplikasi->cv_path)
                                        <div>
                                            <div class="flex flex-wrap items-center gap-2 text-sm">
                                            <button type="button" onclick="toggleDoc('cv-doc', this)" aria-expanded="false" aria-controls="cv-doc" class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-medium bg-emerald-50 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400 hover:bg-emerald-100 dark:hover:bg-emerald-900/50 border border-emerald-200 dark:border-emerald-800 transition-colors">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                                <span id="cv-doc-toggle">Lihat CV</span>
                                            </button>
                                            <a href="{{ route('operator.users.berkas', ['user' => $user, 'jenis' => 'cv']) }}" class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-medium bg-slate-50 dark:bg-slate-800 text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 border border-slate-200 dark:border-slate-700 transition-colors">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                                Unduh CV
                                            </a>
                                            </div>
                                                <div id="cv-doc" class="hidden mt-3">
                                                    <div class="border border-slate-200 dark:border-slate-700 rounded-lg overflow-hidden bg-white dark:bg-slate-900 shadow-sm w-full">
                                                        <iframe src="{{ route('operator.users.berkas', ['user' => $user, 'jenis' => 'cv']) }}" title="{{ __('operator.preview_cv') }}" class="w-full h-[calc(100vh-400px)] min-h-[400px]" frameborder="0"></iframe>
                                                    </div>
                                                </div>
                                        </div>
                                    @endif
                                    @if($aplikasi->ijazah_path)
                                        <div>
                                            <div class="flex flex-wrap items-center gap-2 text-sm">
                                                <button type="button" onclick="toggleDoc('ijazah-doc', this)" aria-expanded="false" aria-controls="ijazah-doc" class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-medium bg-emerald-50 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400 hover:bg-emerald-100 dark:hover:bg-emerald-900/50 border border-emerald-200 dark:border-emerald-800 transition-colors">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                                    <span id="ijazah-doc-toggle">Lihat Ijazah</span>
                                                </button>
                                                <a href="{{ route('operator.users.berkas', ['user' => $user, 'jenis' => 'ijazah']) }}" class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-medium bg-slate-50 dark:bg-slate-800 text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 border border-slate-200 dark:border-slate-700 transition-colors">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                                    Unduh Ijazah
                                                </a>
                                            </div>
                                            <div id="ijazah-doc" class="hidden mt-3">
                                                <div class="border border-slate-200 dark:border-slate-700 rounded-lg overflow-hidden bg-white dark:bg-slate-900 shadow-sm w-full">
                                                    <iframe src="{{ route('operator.users.berkas', ['user' => $user, 'jenis' => 'ijazah']) }}" title="{{ __('operator.preview_ijazah') }}" class="w-full h-[calc(100vh-400px)] min-h-[400px]" frameborder="0"></iframe>
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                    @if(! $aplikasi->cv_path && ! $aplikasi->ijazah_path)
                                        <span class="text-slate-500 dark:text-slate-400 italic">{{ __('operator.profil_tanpa_berkas') }}</span>
                                    @endif
                                </div>
                            </div>
                        @else
                            <p class="text-sm text-slate-500 dark:text-slate-400 italic">{{ __('operator.profil_pengajar_kosong') }}</p>
                        @endif
                    </div>
                @endif

                @if($user->status === 'pending' || $user->status !== 'pending')
                    <div class="border-t border-slate-100 dark:border-slate-800 pt-5">
                        <h3 class="text-sm font-semibold text-slate-900 dark:text-slate-100 uppercase tracking-wider mb-4">{{ __('common.actions') }}</h3>
                        <div class="flex flex-wrap gap-3">
                            @if($user->status === 'pending')
                                <form action="{{ route('operator.users.verify', $user) }}" method="POST">
                                    @csrf
                                    <x-btn-primary type="submit" size="sm">{{ __('operator.verify_account') }}</x-btn-primary>
                                </form>
                                <form action="{{ route('operator.users.reject', $user) }}" method="POST">
                                    @csrf
                                    <x-btn-secondary type="submit" color="red" size="sm">{{ __('operator.title_reject') }}</x-btn-secondary>
                                </form>
                            @endif
                            @if($user->id !== auth()->id())
                                <form action="{{ route('operator.users.destroy', $user) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus akun {{ addslashes($user->name) }}? Tindakan ini tidak dapat dibatalkan.');">
                                    @csrf
                                    @method('DELETE')
                                    <x-btn-secondary type="submit" color="red" size="sm">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                        Hapus Akun
                                    </x-btn-secondary>
                                </form>
                            @endif
                        </div>
                    </div>
                @endif
            </div>
        </x-card>
    </div>

    <script>
        function toggleDoc(id, button) {
            var el = document.getElementById(id);
            var toggle = document.getElementById(id + '-toggle');
            if (el.classList.contains('hidden')) {
                el.classList.remove('hidden');
                button.setAttribute('aria-expanded', 'true');
                if (toggle) toggle.textContent = toggle.textContent.replace('Lihat', 'Sembunyikan');
            } else {
                el.classList.add('hidden');
                button.setAttribute('aria-expanded', 'false');
                if (toggle) toggle.textContent = toggle.textContent.replace('Sembunyikan', 'Lihat');
            }
        }
    </script>
@endsection
