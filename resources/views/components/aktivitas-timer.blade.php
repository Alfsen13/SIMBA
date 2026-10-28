@props(['log' => null])
{{--
    Penanda heartbeat durasi belajar. Elemen ini tidak tampil; JS
    (`pages/aktivitas-durasi.js`) mengirim ping ke `pelajar.aktivitas.durasi`
    memakai nilai `data-url`.
--}}
@if($log)
    <div
        id="aktivitas-durasi"
        data-url="{{ route('pelajar.aktivitas.durasi', $log) }}"
        hidden
    ></div>
@endif
