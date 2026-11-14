const KONFIG_ELEMEN = 'konteks-soal-konfig';

// Teks ringkasan dikirim Blade dari katalog terjemahan supaya JS tidak
// menempelkan kalimat yang bisa berbeda dari yang dipakai halaman.
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

    return { pesan: {}, ...isi };
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
    const pesan = typeof opts.kunci === 'function' ? opts.kunci : pesanDariKatalog(konteksKonfigurasi());

    return {
        sumberKonteks: Array.isArray(opts.sumberKonteks) ? opts.sumberKonteks : [],
        konteksPertemuan: [],
        konteksMateri: [],
        panelMateri: {},

        initKonteks() {
            const dipilih = idUtuh(tersimpan.pertemuan_ids);

            if (dipilih.length > 0) {
                this.konteksPertemuan = dipilih;
            } else if (this.konteksPertemuan.length === 0 && defaultPertemuanId) {
                this.konteksPertemuan = [Number(defaultPertemuanId)];
            }

            this.konteksMateri = idUtuh(tersimpan.materi_ids);
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
        },

        ringkasanKonteks() {
            if (this.konteksPertemuan.length === 0) {
                return pesan('pakai_pertemuan', {}, 'Memakai materi pertemuan ini.');
            }

            const namaPertemuan = this.konteksPertemuan.map(id => {
                const pertemuan = this.sumberKonteks.find(p => Number(p.id) === Number(id));

                return pertemuan ? pertemuan.nama : '#' + id;
            });

            const jumlahMateri = this.konteksMateri.length;

            return namaPertemuan.join(' + ') + (jumlahMateri > 0
                ? ' (' + pesan('materi_dipilih', { count: jumlahMateri }, jumlahMateri + ' materi dipilih') + ')'
                : ' (' + pesan('semua_materi', {}, 'seluruh materi') + ')');
        },

        payloadKonteks() {
            return {
                konteks_pertemuan_ids: this.konteksPertemuan,
                konteks_materi_ids: this.konteksMateri,
            };
        },
    };
}
