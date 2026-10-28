@extends('layouts.app')

@section('title', 'Kelola Akun Operator - Pengelola Operator')

@section('content')
    <x-page-header
        title="Akun Operator"
        subtitle="Buat dan tugaskan operator per kategori mata pelajaran"
    >
        <x-slot name="actions">
            <x-btn-primary :href="route('pengelola-operator.operator.create')">
                Tambah Operator
            </x-btn-primary>
        </x-slot>
    </x-page-header>

    <x-card :padding="false">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 dark:bg-slate-800/50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase">Nama</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase">Email</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase">Kategori Mapel</th>
                        <th class="px-6 py-3 text-right text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse($operators as $operator)
                        <tr>
                            <td class="px-6 py-3 text-slate-900 dark:text-slate-100 font-medium">{{ $operator->name }}</td>
                            <td class="px-6 py-3 text-slate-600 dark:text-slate-400">{{ $operator->email }}</td>
                            <td class="px-6 py-3 text-slate-600 dark:text-slate-400">
                                <span class="inline-flex items-center gap-1.5 px-2 py-1 rounded bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 text-xs font-medium">
                                    {{ $operator->kategori_mapel ?? '-' }}
                                </span>
                            </td>
                            <td class="px-6 py-3 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('pengelola-operator.operator.edit', $operator) }}" class="text-emerald-700 dark:text-emerald-400 hover:underline">Edit</a>
                                    @if($operator->id !== auth()->id())
                                        <form action="{{ route('pengelola-operator.operator.destroy', $operator) }}" method="POST" onsubmit="return confirm('Hapus operator ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button class="text-red-600 dark:text-red-400 hover:underline">Hapus</button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-8 text-center text-slate-500">
                                <p class="text-sm">Belum ada operator.</p>
                                <a href="{{ route('pengelola-operator.operator.create') }}" class="mt-2 inline-flex items-center gap-1.5 text-sm font-medium text-emerald-700 dark:text-emerald-400 hover:text-emerald-700 dark:hover:text-emerald-300 transition-colors">
                                    Tambah Operator Pertama
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                                </a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($operators->hasPages())
            <div class="p-4 border-t border-slate-100 dark:border-slate-800">
                {{ $operators->links() }}
            </div>
        @endif
    </x-card>
@endsection
