@extends('layouts.app')

@section('title', 'Kategori Mata Pelajaran - Pengelola Operator')

@section('content')
    <x-page-header
        title="Kategori Mata Pelajaran"
        subtitle="Kategori yang dikelola oleh pengelola operator"
    >
        <x-slot name="actions">
            <x-btn-primary :href="route('pengelola-operator.kategori-mata-pelajaran.create')">
                Tambah Kategori
            </x-btn-primary>
        </x-slot>
    </x-page-header>

    <x-card :padding="false">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 dark:bg-slate-800/50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase">Kategori</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase">Jumlah Mapel</th>
                        <th class="px-6 py-3 text-right text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse($kategoriList as $kategori)
                        <tr>
                            <td class="px-6 py-3 text-slate-900 dark:text-slate-100">
                                <span class="inline-flex items-center gap-2 px-2.5 py-1 rounded-full bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400 text-xs font-medium">
                                    {{ $kategori->nama }}
                                </span>
                            </td>
                            <td class="px-6 py-3 text-slate-600 dark:text-slate-400 text-xs">
                                {{ $kategori->mapel_count ?? '-' }}
                            </td>
                            <td class="px-6 py-3 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('pengelola-operator.kategori-mata-pelajaran.edit', $kategori->nama) }}" class="text-xs text-emerald-700 dark:text-emerald-400 hover:underline">Edit</a>
                                    <form action="{{ route('pengelola-operator.kategori-mata-pelajaran.destroy', $kategori->nama) }}" method="POST" onsubmit="return confirm('Hapus kategori ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="text-xs text-red-600 dark:text-red-400 hover:underline">Hapus</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="px-6 py-8 text-center text-slate-500">
                                <p class="text-sm">Belum ada kategori mata pelajaran.</p>
                                <a href="{{ route('pengelola-operator.kategori-mata-pelajaran.create') }}" class="mt-2 inline-flex items-center gap-1.5 text-sm font-medium text-emerald-700 dark:text-emerald-400 hover:text-emerald-700 dark:hover:text-emerald-300 transition-colors">
                                    Tambah Kategori Pertama
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                                </a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-card>
@endsection
