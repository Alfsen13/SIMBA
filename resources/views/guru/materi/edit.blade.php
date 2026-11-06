@extends('layouts.app')

@section('title', __('guru_kelas.materi.edit_title') . ' - SIMBA')

@section('content')
    <x-back-link
        href="{{ $materi->pertemuan_id ? route('pengajar.pertemuan.show', $materi->pertemuan_id) : route('pengajar.kelas.show', $materi->kelas_id) }}"
        :label="__('common.back')"
    />

    <x-page-header :title="__('guru_kelas.materi.edit_title')" />

    <x-card>
        <form
            action="{{ route('pengajar.materi.update', $materi) }}"
            method="POST"
            enctype="multipart/form-data"
        >
            @csrf
            @method('PUT')
            <x-form-columns :cols="2">
                <x-form-input
                    name="judul"
                    :label="__('guru_kelas.materi.judul')"
                    :value="old('judul', $materi->judul)"
                    required
                />
                <x-form-input
                    name="bab"
                    :label="__('guru_kelas.materi.bab')"
                    :value="old('bab', $materi->bab)"
                />
            </x-form-columns>
            <div
                x-data="{
                    kelasId: '{{ $materi->kelas_id }}',
                    pertemuanList: {{ $pertemuanList->map(fn($p, $k) => $p->map(fn($v) => ['id' => $v->id, 'nama' => $v->nama]))->toJson() }}
                }"
            >
                <x-form-select
                    name="kelas_id"
                    :label="__('guru_kelas.materi.kelas')"
                    :options="$kelasList->pluck('nama_kelas', 'id')->toArray()"
                    :value="$materi->kelas_id"
                    required
                />
                <div class="mt-5">
                    <label for="pertemuan_id" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">{{ __('guru_kelas.materi.pertemuan_opsional') }}</label>
                    <select
                        name="pertemuan_id"
                        id="pertemuan_id"
                        class="w-full px-4 py-2.5 text-sm bg-slate-50 dark:bg-slate-800/50 border border-slate-200 dark:border-slate-800 rounded-lg focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 outline-none transition"
                    >
                        <option value="">{{ __('guru_kelas.materi.pilih_pertemuan') }}</option>
                        <template x-for="p in (pertemuanList[kelasId] || [])" :key="p.id">
                            <option
                                x-bind:value="p.id"
                                x-text="p.nama"
                                x-bind:selected="p.id == {{ $materi->pertemuan_id ?? 'null' }}"
                            ></option>
                        </template>
                    </select>
                </div>
            </div>

            <x-file-preview
                :label="__('guru_kelas.materi.file')"
                :current-path="$materi->file_path"
                :current-type="$materi->file_type"
            />
            <div class="mt-3 rounded-lg border border-amber-200 bg-amber-50 p-3 text-sm text-amber-900 dark:border-amber-800 dark:bg-amber-950/30 dark:text-amber-200">
                {{ __('guru_kelas.materi.ringkasan_upload_notice') }}
            </div>
            <p class="text-xs text-slate-500 dark:text-slate-400">{{ __('guru_kelas.materi.kosongkan_file') }}</p>

            <x-btn-primary
                type="submit"
                class="w-full"
            >
                {{ __('guru_kelas.update') }}
            </x-btn-primary>
        </form>
    </x-card>
@endsection
