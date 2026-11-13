import { konteksChunkState, unduhChunk } from './konteks-chunk.js';

const KONFIG_ELEMEN = 'konteks-chunk-konfig';

// Batas dan teks pesan dikirim Blade dari satu tempat supaya JS tidak
// pernah memegang angka yang berbeda dari yang divalidasi server.
export function konteksKonfigurasi() {
    const elemen = typeof document === 'undefined' ? null : document.getElementById(KONFIG_ELEMEN);
    let isi = {};

    if (elemen) {
        try {
            isi = JSON.parse(elemen.textContent) || {};
        } catch {
            isi = {};
        }
    }

    return { batas: 40, perPotongan: 1100, plafon: 20000, anggaran: 8000, pesan: {}, ...isi };
}

export function pesanDariKatalog(katalog) {
    const pesan = (katalog && katalog.pesan) || {};

    return (kunci, args = {}, bawaan = '') => {
        const template = pesan[kunci];

        if (typeof template !== 'string') {
            return bawaan;
        }

        return template.replace(/:[a-z_]+/gi, (potongan) => {
            const nama = potongan.slice(1);

            return args[nama] === undefined ? potongan : String(args[nama]);
        });
    };
}

export function idUtuh(nilai) {
    return (Array.isArray(nilai) ? nilai : [])
        .map(Number)
        .filter((id) => Number.isInteger(id) && id > 0);
}

export function konteksSoalState(options = {}, defaultPertemuanId = null) {
    const opts = options || {};
    const tersimpan = opts.tersimpan && typeof opts.tersimpan === 'object' ? opts.tersimpan : {};
    const konfigurasi = konteksKonfigurasi();
    const pesan = typeof opts.kunci === 'function' ? opts.kunci : pesanDariKatalog(konfigurasi);
    let diri = null;

    const state = {
        ...konteksChunkState({
            batas: opts.chunkBatas ?? konfigurasi.batas,
            perPotongan: opts.chunkPerPotongan ?? konfigurasi.perPotongan,
            plafon: opts.chunkPlafon ?? konfigurasi.plafon,
            anggaran: opts.chunkAnggaran ?? konfigurasi.anggaran,
            kunci: pesan,
            unduh: typeof opts.unduh === 'function' ? opts.unduh : (opts.chunkUrl ? unduhChunk(opts.chunkUrl) : undefined),
            // Potongan ikut pertemuan yang dipilih, dan daftar pertemuannya baru
            // diketahui setelah state ini terbentuk.
            scope: () => (diri ? diri.konteksPertemuan : []),
        }),

        sumberKonteks: Array.isArray(opts.sumberKonteks) ? opts.sumberKonteks : [],
        konteksPertemuan: [],
        konteksMateri: [],
        ragTopK: '',
        panelMateri: {},

        initKonteks() {
            const dipilih = idUtuh(tersimpan.pertemuan_ids);

            if (dipilih.length > 0) {
                this.konteksPertemuan = dipilih;
            } else if (this.konteksPertemuan.length === 0 && defaultPertemuanId) {
                this.konteksPertemuan = [Number(defaultPertemuanId)];
            }

            this.konteksMateri = idUtuh(tersimpan.materi_ids);

            if (tersimpan.rag_top_k) {
                this.ragTopK = String(tersimpan.rag_top_k);
            }

            this.chunkSiapkan(tersimpan.chunk_ids);

            // Pilihan yang tersimpan harus terlihat begitu halaman dibuka,
            // termasuk bila ada potongan yang sudah hilang karena menanam ulang.
            if (this.chunkJumlah() > 0) {
                this.chunkPanel();
            }
        },

        panelMateriTerbuka(pertemuanId) {
            return this.panelMateri[Number(pertemuanId)] ?? true;
        },

        balikPanelMateri(pertemuanId) {
            const kunci = Number(pertemuanId);

            this.panelMateri[kunci] = !this.panelMateriTerbuka(kunci);
        },

        pertemuanDipilih(id) {
            return this.konteksPertemuan.some(p => Number(p) === Number(id));
        },

        materiDipilih(id) {
            return this.konteksMateri.some(m => Number(m) === Number(id));
        },

        materiDari(pertemuanId) {
            const pertemuan = this.sumberKonteks.find(p => Number(p.id) === Number(pertemuanId));

            return pertemuan && Array.isArray(pertemuan.materi) ? pertemuan.materi : [];
        },

        semuaMateri() {
            const hasil = [];

            this.sumberKonteks.forEach(p => {
                (Array.isArray(p.materi) ? p.materi : []).forEach(m => {
                    hasil.push({ ...m, pertemuan: p.nama || '', pertemuan_id: p.id });
                });
            });

            return hasil;
        },

        jumlahMateriDipilih(pertemuanId) {
            return this.materiDari(pertemuanId).filter(m => this.materiDipilih(m.id)).length;
        },

        semuaMateriTerpilih(pertemuanId) {
            const daftar = this.materiDari(pertemuanId);

            return daftar.length > 0 && daftar.every(m => this.materiDipilih(m.id));
        },

        isiSemuaMateri(pertemuanId) {
            if (this.semuaMateriTerpilih(pertemuanId)) {
                this.materiDari(pertemuanId).forEach(m => this.toggleMateriKonteks(m.id));
            } else {
                this.materiDari(pertemuanId).forEach(m => {
                    if (!this.materiDipilih(m.id)) {
                        this.toggleMateriKonteks(m.id);
                    }
                });
            }
        },

        togglePertemuanKonteks(id) {
            const nilai = Number(id);

            if (this.pertemuanDipilih(nilai)) {
                this.konteksPertemuan = this.konteksPertemuan.filter(p => Number(p) !== nilai);

                const sisaIdMateri = this.materiDari(nilai).map(m => Number(m.id));

                this.konteksMateri = this.konteksMateri.filter(m => !sisaIdMateri.includes(Number(m)));
                this.chunkLepasDariPertemuan(nilai);
            } else {
                this.konteksPertemuan = [...this.konteksPertemuan, nilai];
            }
        },

        toggleMateriKonteks(id) {
            const nilai = Number(id);

            if (this.materiDipilih(nilai)) {
                this.konteksMateri = this.konteksMateri.filter(m => Number(m) !== nilai);
            } else {
                this.konteksMateri = [...this.konteksMateri, nilai];
            }
        },

        resetKonteks() {
            this.konteksPertemuan = [];
            this.konteksMateri = [];
            this.chunkKosongkan();
        },

        ringkasanKonteks() {
            if (this.konteksPertemuan.length === 0) {
                return this._pesan('pakai_pertemuan', {}, 'Memakai materi pertemuan ini.');
            }

            const namaPertemuan = this.konteksPertemuan.map(id => {
                const pertemuan = this.sumberKonteks.find(p => Number(p.id) === Number(id));

                return pertemuan ? pertemuan.nama : '#' + id;
            });

            let ringkas = namaPertemuan.join(' + ');
            const jumlahMateri = this.konteksMateri.length;
            const jumlahPotongan = this.chunkJumlah();

            ringkas += jumlahMateri > 0
                ? ' (' + this._pesan('materi_dipilih', { count: jumlahMateri }, jumlahMateri + ' materi dipilih)')
                : ' (' + this._pesan('semua_materi', {}, 'seluruh materi') + ')';

            if (jumlahPotongan > 0) {
                ringkas += ' · ' + this._pesan('potongan_dipilih', { count: jumlahPotongan }, jumlahPotongan + ' potongan');
            }

            return ringkas;
        },

        payloadKonteks() {
            return {
                konteks_pertemuan_ids: this.konteksPertemuan,
                konteks_materi_ids: this.konteksMateri,
                konteks_chunk_ids: this.chunkIds(),
                rag_top_k: this.ragTopK === '' ? null : Number(this.ragTopK)
            };
        },

        _pesan(kunci, args, bawaan) {
            return pesan(kunci, args, bawaan);
        },
    };

    diri = state;

    return state;
}
