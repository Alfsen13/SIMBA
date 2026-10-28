@extends('layouts.app')

@section('title', 'Dashboard Pengelola Operator - SIMBA')

@section('content')
    <x-page-header
        title="Dashboard Pengelola Operator"
        subtitle="Kelola kategori mata pelajaran dan akun operator"
    />

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <x-stat-card title="Operator" :value="$jumlahOperator" icon="user-group" />
        <x-stat-card title="Kategori Mapel" :value="$jumlahKategori" icon="book-open" />
        <x-stat-card title="Pengajar" :value="$jumlahPengajar" icon="academic-cap" />
        <x-stat-card title="Pelajar" :value="$jumlahPelajar" icon="users" />
    </div>

    <x-card>
        <div class="flex flex-col sm:flex-row gap-4 justify-between items-start sm:items-center">
            <div>
                <h2 class="text-sm font-semibold text-slate-900 dark:text-slate-100">Tindakan Cepat</h2>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Pilih tugas yang ingin Anda lakukan.</p>
            </div>
            <div class="flex items-center gap-2">
                <x-btn-primary :href="route('pengelola-operator.kategori-mata-pelajaran.index')">
                    Kelola Kategori Mata Pelajaran
                </x-btn-primary>
                <x-btn-primary :href="route('pengelola-operator.operator.index')" class="bg-slate-700 hover:bg-slate-800">
                    Kelola Akun Operator
                </x-btn-primary>
            </div>
        </div>
    </x-card>
@endsection
