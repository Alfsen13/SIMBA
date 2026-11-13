// State pemilih potongan dokumen. Jaringan hanya lewat options.unduh supaya modul
// ini teruji Node tanpa DOM; nilai anggaran karakter dikirim Blade dari service
// supaya tidak ada angka yang diduplikasi.
export function konteksChunkState(options = {}) {
    const opts = options || {};
    const unduh = typeof opts.unduh === 'function' ? opts.unduh : async () => ({ data: [], total: 0, halaman: 1, total_halaman: 1 });

    const angka = (nilai, bawaan) => {
        const n = Number(nilai);

        return Number.isFinite(n) && n > 0 ? n : bawaan;
    };
    const utuh = (ids) => (Array.isArray(ids) ? ids : [])
        .map(Number)
        .filter((id) => Number.isInteger(id) && id > 0);

    return {
        chunkBatas: angka(opts.batas, 40),
        chunkPerPotongan: angka(opts.perPotongan, 1100),
        chunkPlafon: angka(opts.plafon, 20000),
        chunkAnggaran: angka(opts.anggaran, 8000),

        chunkDaftar: [],
        chunkCentang: {},
        chunkPanjang: {},
        chunkPertemuan: {},
        chunkTotal: 0,
        chunkHalaman: 1,
        chunkHalamanAkhir: 1,
        chunkSibuk: false,
        chunkSedangMengisi: false,
        chunkSudahDimuat: false,
        chunkDaftarLengkap: false,
        chunkKata: '',
        chunkPesan: '',
        chunkPesanKosong: false,
        chunkSiap: false,

        _unduh: unduh,
        _scope: typeof opts.scope === 'function' ? opts.scope : () => [],

        chunkIds() {
            return Object.keys(this.chunkCentang)
                .filter((id) => this.chunkCentang[id] === true)
                .map(Number)
                .sort((a, b) => a - b);
        },

        chunkJumlah() {
            return this.chunkIds().length;
        },

        chunkSisa() {
            return Math.max(0, this.chunkBatas - this.chunkJumlah());
        },

        // Server menyusun potongan berurutan document + chunk_index; urutan ini
        // hanya untuk input hidden dan angka counter.
        chunkDipilih(id) {
            return this.chunkCentang[Number(id)] === true;
        },

        chunkToggle(id) {
            const kunci = Number(id);

            if (this.chunkCentang[kunci] === true) {
                delete this.chunkCentang[kunci];
                this.chunkPesan = '';

                return true;
            }

            if (this.chunkJumlah() >= this.chunkBatas) {
                this.chunkPesan = this._pesan('batas', { max: this.chunkBatas });

                return false;
            }

            this.chunkCentang[kunci] = true;
            this.chunkPesan = '';

            return true;
        },

        chunkPilihHalaman() {
            const tambahan = [];

            this.chunkDaftar.forEach((item) => {
                if (!this.chunkDipilih(item.id) && this.chunkJumlah() + tambahan.length < this.chunkBatas) {
                    tambahan.push(Number(item.id));
                }
            });

            tambahan.forEach((id) => { this.chunkCentang[id] = true; });

            const lewat = this.chunkDaftar.filter((item) => !this.chunkDipilih(item.id)).length;
            this.chunkPesan = lewat > 0 ? this._pesan('halaman_penuh', { max: this.chunkBatas }) : '';
        },

        chunkKosongkan() {
            this.chunkCentang = {};
            this.chunkPesan = '';
        },

        // Pilihan lama boleh di luar halaman yang sedang dibuka dan bisa jadi
        // sudah hilang karena dokumen ditanam ulang; keduanya tetap dihitung.
        chunkSiapkan(ids) {
            const bulat = utuh(ids);

            this.chunkCentang = {};
            bulat.slice(0, this.chunkBatas).forEach((id) => { this.chunkCentang[id] = true; });
            this.chunkPesan = bulat.length > this.chunkBatas
                ? this._pesan('tersimpan_lebih', { max: this.chunkBatas })
                : '';

            return this.chunkIds();
        },

        chunkAdaDiDaftar(id) {
            return this.chunkDaftar.some((item) => Number(item.id) === Number(id));
        },

        chunkHilang() {
            if (!this.chunkSudahDimuat || !this.chunkDaftarLengkap) {
                return 0;
            }

            return this.chunkIds().filter((id) => !this.chunkAdaDiDaftar(id)).length;
        },

        // Kuota yang dipakai service: membesar mengikuti jumlah potongan, lalu
        // membentur plafon. Ini sumber peringatan "sebagian akan terpotong".
        chunkKuota() {
            return Math.min(this.chunkPlafon, Math.max(this.chunkAnggaran, this.chunkJumlah() * this.chunkPerPotongan));
        },

        chunkEstimasi() {
            return this.chunkIds().reduce((total, id) => total + Math.min(this.chunkPerPotongan, Number(this.chunkPanjang[id]) || 0), 0);
        },

        chunkTerpotong() {
            return this.chunkJumlah() > 0 && this.chunkEstimasi() > this.chunkKuota();
        },

        async chunkMuat(halaman = 1) {
            if (this.chunkSibuk) {
                return;
            }

            const scope = utuh(this._scope());

            if (scope.length === 0) {
                this.chunkDaftar = [];
                this.chunkPesan = this._pesan('tanpa_pertemuan');

                return;
            }

            this.chunkSibuk = true;
            this.chunkPesan = '';

            try {
                const hasil = await this._unduh({ mode: 'lengkap', pertemuan_ids: scope, hal: halaman });

                this.terapkan(hasil);
                this.chunkSudahDimuat = true;
                this.chunkDaftarLengkap = true;
                this.chunkPesanKosong = hasil.total === 0;
            } catch {
                this.chunkPesan = this._pesan('gagal_muat');
            } finally {
                this.chunkSibuk = false;
            }
        },

        // Panel hanya membuka/menutup; datanya dimuat sekali saja.
        chunkPanel() {
            this.chunkSiap = !this.chunkSiap;

            if (this.chunkSiap && !this.chunkSudahDimuat) {
                this.chunkMuat(1);
            }
        },

        chunkRingkasHeader() {
            return this._pesan('potongan_dipilih', { count: this.chunkJumlah() });
        },

        chunkTeksHalaman() {
            return this._pesan('halaman', { halaman: this.chunkHalaman, total: this.chunkHalamanAkhir });
        },

        chunkTeksEstimasi() {
            return this._pesan('estimasi', { estimasi: this.chunkEstimasi(), kuota: this.chunkKuota() });
        },

        // Label teknis satu potongan: halaman, nomor potongan, ukuran, dan skor
        // hanya ada saat hasil berasal dari "Isi otomatis".
        chunkMeta(c) {
            const bagian = [
                c.halaman ? 'hlm. ' + c.halaman : null,
                'potongan ' + (Number(c.chunk_index) + 1),
                Number(c.panjang) + ' karakter',
                c.skor ? 'kemiripan ' + c.skor : null,
            ];

            return bagian.filter(Boolean).join(' · ');
        },

        chunkPesanHilang() {
            const hilang = this.chunkHilang();

            return hilang > 0 ? this._pesan('hilang', { count: hilang }) : '';
        },

        chunkBuka() {
            this.chunkSiap = true;

            if (!this.chunkSudahDimuat) {
                this.chunkMuat(1);
            }
        },

        async chunkIsiOtomatis() {
            const kata = String(this.chunkKata || '').trim();
            const scope = utuh(this._scope());

            if (kata === '' || scope.length === 0) {
                this.chunkPesan = this._pesan(kata === '' ? 'tanpa_kata' : 'tanpa_pertemuan');

                return;
            }

            this.chunkSedangMengisi = true;
            this.chunkPesan = '';

            try {
                const hasil = await this._unduh({ mode: 'rekomendasi', pertemuan_ids: scope, q: kata });
                const usul = utuh(hasil.data.map((item) => item.id));

                // Daftar rekomendasi hanya memuat yang cocok, jadi tidak bisa
                // dipakai untuk menilai potongan mana yang sudah hilang.
                this.terapkan(hasil);
                this.chunkDaftarLengkap = false;

                const baru = [];

                usul.forEach((id) => {
                    if (this.chunkJumlah() + baru.length < this.chunkBatas) {
                        baru.push(id);
                    }
                });

                baru.forEach((id) => { this.chunkCentang[id] = true; });

                if (baru.length === 0) {
                    this.chunkPesan = this._pesan('tanpa_hasil');
                } else if (usul.length > baru.length) {
                    this.chunkPesan = this._pesan('terpotong', { count: usul.length - baru.length, max: this.chunkBatas });
                }
            } catch {
                this.chunkPesan = this._pesan('gagal_muat');
            } finally {
                this.chunkSedangMengisi = false;
            }
        },

        terapkan(hasil) {
            const list = Array.isArray(hasil.data) ? hasil.data : [];

            this.chunkDaftar = list;
            list.forEach((item) => this.catatItem(item));
            this.chunkTotal = Number(hasil.total) || 0;
            this.chunkHalaman = Number(hasil.halaman) || 1;
            this.chunkHalamanAkhir = Number(hasil.total_halaman) || 1;
        },

        catatItem(item) {
            const id = Number(item.id);

            if (id > 0 && !Number.isFinite(Number(this.chunkPanjang[id]))) {
                this.chunkPanjang[id] = Number(item.panjang) || 0;
                this.chunkPertemuan[id] = Number(item.pertemuan_id) || 0;
            }
        },

        // Guru melepas pertemuan = melepas potongan milik pertemuan itu. Id yang
        // belum pernah dimuat tidak bisa dilacak, jadi dibiarkan terpilih.
        chunkLepasDariPertemuan(pertemuanId) {
            const target = Number(pertemuanId);

            this.chunkIds().forEach((id) => {
                if (Number(this.chunkPertemuan[id]) === target) {
                    delete this.chunkCentang[id];
                }
            });
        },

        _pesan(kunci, args) {
            return typeof opts.kunci === 'function' ? opts.kunci(kunci, args) : kunci;
        },
    };
}

export function unduhChunk(url) {
    const CSRF = () => (document.querySelector('meta[name="csrf-token"]') || {}).getAttribute('content') || '';

    return async (params) => {
        const query = new URLSearchParams();

        (Array.isArray(params.pertemuan_ids) ? params.pertemuan_ids : []).forEach((id) => query.append('pertemuan_ids[]', String(id)));

        if (params.mode) {
            query.set('mode', String(params.mode));
        }
        if (params.hal) {
            query.set('hal', String(params.hal));
        }
        if (params.q) {
            query.set('q', String(params.q));
        }

        const res = await fetch(url + (url.includes('?') ? '&' : '?') + query.toString(), {
            headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': CSRF() },
            cache: 'no-store',
        });

        if (!res.ok) {
            throw new Error('HTTP ' + res.status);
        }

        return res.json();
    };
}
