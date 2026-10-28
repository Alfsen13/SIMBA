{{--
    Skrip no-FOUC: pasang class `dark` di <html> SEBELUM paint pertama.

    Prioritas: pilihan tersimpan di localStorage dulu; kalau belum ada,
    ikuti preferensi OS (prefers-color-scheme). Harus berupa skrip inline
    sinkron di <head> — kalau menunggu bundle JS, halaman sempat berkedip
    putih sebelum berubah gelap.
--}}
<script>
    (function () {
        try {
            var stored = localStorage.getItem('theme');
            var prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
            var dark = stored ? stored === 'dark' : prefersDark;
            document.documentElement.classList.toggle('dark', dark);
        } catch (e) {
            /* localStorage bisa diblokir (mode privat) — abaikan, tetap mode terang */
        }
    })();
</script>
