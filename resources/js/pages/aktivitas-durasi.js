const FLUSH_MS = 30000;
const MAKS_GAP_DETIK = 300;

function csrfToken() {
    return document.querySelector('meta[name="csrf-token"]')?.content ?? '';
}

/**
 * Heartbeat durasi belajar untuk halaman pelajar. Waktu hanya dihitung saat
 * tab aktif (visibility), lalu dikirim berkala + saat halaman disembunyikan
 * lewat `sendBeacon`. Server mengakumulasi ke `aktivitas_log.metadata.durasi_menit`
 * yang jadi sumber `rata_durasi_belajar`.
 */
export function initAktivitasDurasi() {
    const el = document.getElementById('aktivitas-durasi');
    if (!el || !el.dataset.url) return;

    const url = el.dataset.url;

    let terkumpul = 0;
    let terakhirHitung = Date.now();
    let aktif = document.visibilityState !== 'hidden';

    const serap = () => {
        const sekarang = Date.now();
        if (aktif) {
            const detik = Math.round((sekarang - terakhirHitung) / 1000);
            // Abaikan lompatan besar (tab dibekukan browser / jam berubah).
            if (detik > 0 && detik < MAKS_GAP_DETIK) {
                terkumpul += detik;
            }
        }
        terakhirHitung = sekarang;
    };

    const kirim = () => {
        serap();
        if (terkumpul <= 0) return;

        const detik = terkumpul;
        terkumpul = 0;

        const form = new FormData();
        form.append('_token', csrfToken());
        form.append('detik', String(detik));

        if (navigator.sendBeacon) {
            navigator.sendBeacon(url, form);
        } else {
            fetch(url, { method: 'POST', body: form, keepalive: true });
        }
    };

    window.setInterval(kirim, FLUSH_MS);

    document.addEventListener('visibilitychange', () => {
        if (document.visibilityState === 'hidden') {
            kirim();
            aktif = false;
        } else {
            aktif = true;
            terakhirHitung = Date.now();
        }
    });

    window.addEventListener('pagehide', kirim);
}
