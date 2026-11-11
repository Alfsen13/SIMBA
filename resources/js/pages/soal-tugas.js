
function normalisasiPilihan(pilihan) {
    const list = Array.isArray(pilihan) ? pilihan.slice(0, 4) : [];

    while (list.length < 4) {
        list.push('');
    }

    return list.map((p) => (p === null || p === undefined ? '' : String(p)));
}

function normalisasiJawaban(jawaban) {
    const n = Number(jawaban);

    return [0, 1, 2, 3].includes(n) ? n : 0;
}

// Field keakurasian soal terhadap materi hanya ada pada soal hasil generate AI.
// Disimpan secara kondisional supaya soal manual tidak ikut membawa key kosong.
export function sertakanAkurasi(target, sumber) {
    if (Object.hasOwn(sumber, 'kemiripan_materi') && sumber.kemiripan_pertanyaan === target.pertanyaan) {
        const nilai = sumber.kemiripan_materi;
        if (nilai === null || (typeof nilai === 'number' && Number.isFinite(nilai) && nilai >= -1 && nilai <= 1)) {
            target.kemiripan_materi = nilai;
            target.kemiripan_pertanyaan = target.pertanyaan;
        }
    }

    const n = Number(sumber.akurasi_materi);

    if (sumber.akurasi_materi !== null && sumber.akurasi_materi !== undefined && sumber.akurasi_materi !== '' && Number.isFinite(n)) {
        target.akurasi_materi = Math.max(0, Math.min(100, Math.round(n)));
    }

    const catatan = typeof sumber.catatan_akurasi === 'string' ? sumber.catatan_akurasi.trim() : '';

    if (catatan !== '') {
        target.catatan_akurasi = catatan;
    }

    return target;
}

export function normalisasiSoalPG(soal) {
    const s = soal || {};

    return sertakanAkurasi({
        pertanyaan: s.pertanyaan || '',
        pilihan: normalisasiPilihan(s.pilihan),
        jawaban: normalisasiJawaban(s.jawaban),
        bloom: s.bloom || null
    }, s);
}

export function normalisasiSoalEsai(soal) {
    const s = soal || {};

    return sertakanAkurasi({
        pertanyaan: s.pertanyaan || '',
        referensi: s.referensi || '',
        bloom: s.bloom || null
    }, s);
}
