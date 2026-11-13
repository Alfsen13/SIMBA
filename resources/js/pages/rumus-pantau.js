async function ambilJsonDefault(url) {
    const respons = await fetch(url, { headers: { Accept: 'application/json' }, cache: 'no-store' });
    if (!respons.ok) {
        throw new Error('Gagal mengambil status pemindaian.');
    }

    return respons.json();
}

/**
 * State polling pemindaian rumus. Efek (fetch, reload, timer) bisa disuntik
 * supaya keadaan macet dan selesai teruji tanpa peramban.
 */
export function pantauState(options = {}) {
    const batas = Number.isFinite(options.batas) ? Math.max(1, Math.trunc(options.batas)) : 180;
    const dokumen = options.dokumen !== undefined ? options.dokumen
        : (typeof globalThis.document === 'object' ? globalThis.document : null);

    return {
        url: typeof options.url === 'string' ? options.url : '',
        berjalan: options.berjalan === true,
        batas,
        setiap: Number.isFinite(options.setiap) ? Math.max(1, Math.trunc(options.setiap)) : 3,
        jeda: Number.isFinite(options.jeda) ? Math.max(10, Math.trunc(options.jeda)) : 1000,
        detik: 0,
        jumlah: Number.isFinite(options.jumlah) ? Math.max(0, Math.trunc(options.jumlah)) : 0,
        menunggu: Number.isFinite(options.menunggu) ? Math.max(0, Math.trunc(options.menunggu)) : 0,
        terlambat: false,
        selesai: false,
        _tik: null,
        _lihat: null,
        _sedangCek: false,
        _gagal: 0,
        _dokumen: dokumen,
        _ambil: typeof options.ambil === 'function' ? options.ambil : ambilJsonDefault,
        _muatUlang: typeof options.muatUlang === 'function' ? options.muatUlang : () => globalThis.location.reload(),
        _setInterval: typeof options.setInterval === 'function' ? options.setInterval : (fn, ms) => globalThis.setInterval(fn, ms),
        _clearInterval: typeof options.clearInterval === 'function' ? options.clearInterval : (id) => globalThis.clearInterval(id),

        get sisa() {
            return Math.max(0, this.batas - this.detik);
        },

        /** Masih ada harapan hasil baru datang, jadi UI menampilkan progres. */
        get sibuk() {
            return this.berjalan && !this.terlambat && !this.selesai;
        },

        init() {
            if (!this.berjalan || !this.url) {
                return;
            }

            this._tik = this._setInterval(() => this.detak(), this.jeda);

            // Peramban men throttle timer tab yang di background, jadi pemindaian
            // pendek bisa selesai tanpa pernah terpantau. Cek lagi saat tab tampak.
            if (this._dokumen) {
                this._lihat = () => {
                    if (this.sibuk && this._dokumen.visibilityState !== 'hidden') {
                        this.periksa();
                    }
                };
                this._dokumen.addEventListener('visibilitychange', this._lihat);
            }
        },

        destroy() {
            this.berhenti();
        },

        berhenti() {
            if (this._tik !== null && this._tik !== undefined) {
                this._clearInterval(this._tik);
            }
            this._tik = null;

            if (this._lihat && this._dokumen) {
                this._dokumen.removeEventListener('visibilitychange', this._lihat);
                this._lihat = null;
            }
        },

        detak() {
            if (this.selesai) {
                this.berhenti();

                return;
            }

            this.detik++;

            // Tick pertama langsung cek: pemindaian bisa selesai di bawah
            // interval reguler dan halaman harus segara memuat ulang.
            if (this.detik === 1 || this.detik % this.setiap === 0) {
                this.periksa();
            }

            // Server yang memutuskan macet lewat detak region. Angka lokal hanya
            // cadangan kalau polling gagal terus, dengan plafon keras.
            if ((this.detik > this.batas && this._gagal >= 3) || this.detik > this.batas * 3) {
                this.terlambat = true;
                this.berhenti();
            }
        },

        async periksa() {
            if (this._sedangCek || this.selesai) {
                return;
            }

            this._sedangCek = true;

            try {
                this.serap(await this._ambil(this.url));
                this._gagal = 0;
            } catch {
                this._gagal++;
            } finally {
                this._sedangCek = false;
            }
        },

        serap(data) {
            if (!data || typeof data !== 'object' || this.selesai) {
                return;
            }

            if (Number.isFinite(data.jumlah)) {
                this.jumlah = Math.max(0, Math.trunc(data.jumlah));
            }
            if (Number.isFinite(data.menunggu)) {
                this.menunggu = Math.max(0, Math.trunc(data.menunggu));
            }
            if (Number.isFinite(data.batas)) {
                this.batas = Math.max(1, Math.trunc(data.batas));
            }

            this.berjalan = data.berjalan === true;

            if (data.terlambat === true) {
                this.terlambat = true;
                this.berhenti();

                return;
            }

            if (this.berjalan) {
                return;
            }

            // Status terminal: hasil review baru tampil utuh setelah halaman dimuat ulang.
            this.selesai = true;
            this.berhenti();
            this._muatUlang();
        },
    };
}
