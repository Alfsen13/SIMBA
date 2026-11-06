@extends('layouts.app')

@section('title', 'Dashboard Operator - SIMBA')

@section('content')
    <x-page-header
        title="Dashboard Operator"
        subtitle="Selamat datang, {{ auth()->user()->name }}. Kelola pengguna, kelas, dan mata pelajaran."
    />

    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 mb-6">
        <x-stat-card :value="$stats['total_pelajar']" :label="__('operator.stat_total_pelajar')" color="blue" icon='<svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>' />
        <x-stat-card :value="$stats['total_pengajar']" :label="__('operator.stat_total_pengajar')" color="emerald" icon='<svg class="w-5 h-5 text-emerald-700 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>' />
        <x-stat-card :value="$stats['total_kelas']" :label="__('operator.stat_total_kelas')" color="purple" icon='<svg class="w-5 h-5 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>' />
        <x-stat-card :value="$stats['pending_users']" :label="__('operator.stat_pending_verification')" color="amber" icon='<svg class="w-5 h-5 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>' />
    </div>

    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 mb-6">
        <x-stat-card :value="$stats['total_materi']" :label="__('operator.stat_total_materi')" color="cyan" icon='<svg class="w-5 h-5 text-cyan-600 dark:text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>' />
        <x-stat-card :value="$stats['total_tugas']" :label="__('operator.stat_total_tugas')" color="violet" icon='<svg class="w-5 h-5 text-violet-600 dark:text-violet-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>' />
        <x-stat-card :value="$stats['total_remedial']" :label="__('operator.stat_total_remedial')" color="teal" icon='<svg class="w-5 h-5 text-teal-600 dark:text-teal-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>' />
    </div>

    <x-card>
        <div class="flex flex-col sm:flex-row gap-4 justify-between items-start sm:items-center">
            <div>
                <h2 class="text-sm font-semibold text-slate-900 dark:text-slate-100">Tindakan Cepat</h2>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Pilih tugas yang ingin Anda lakukan.</p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <x-btn-primary :href="route('operator.users.index')">
                    Kelola Pengguna
                </x-btn-primary>
                <x-btn-primary :href="route('operator.kelas.index')" class="bg-slate-700 hover:bg-slate-800">
                    Kelola Kelas
                </x-btn-primary>
                <x-btn-primary :href="route('operator.mata-pelajaran.index')" class="bg-slate-700 hover:bg-slate-800">
                    Mata Pelajaran
                </x-btn-primary>
                <x-btn-primary :href="route('operator.mata-pelajaran.index')" class="bg-slate-700 hover:bg-slate-800">
                    Materi Pengajar
                </x-btn-primary>
                <x-btn-primary :href="route('operator.berita.index')" class="bg-slate-700 hover:bg-slate-800">
                    Berita
                </x-btn-primary>
                <x-btn-primary :href="route('operator.import')" class="bg-slate-700 hover:bg-slate-800">
                    Impor Data
                </x-btn-primary>
            </div>
        </div>
    </x-card>
@endsection
