 
export function konteksSoalState(options = {}, defaultPertemuanId = null) {
    const opts = options || {};

    return {
        sumberKonteks: Array.isArray(opts.sumberKonteks) ? opts.sumberKonteks : [],
        konteksPertemuan: [],
        konteksMateri: [],

        initKonteks() {
            if (this.konteksPertemuan.length === 0 && defaultPertemuanId) {
                this.konteksPertemuan = [Number(defaultPertemuanId)];
            }
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
                return 'Memakai materi pertemuan ini.';
            }

            const namaPertemuan = this.konteksPertemuan.map(id => {
                const pertemuan = this.sumberKonteks.find(p => Number(p.id) === Number(id));

                return pertemuan ? pertemuan.nama : 'Pertemuan #' + id;
            });

            const jumlahMateri = this.konteksMateri.length;

            return namaPertemuan.join(' + ')
                + (jumlahMateri > 0 ? ' (' + jumlahMateri + ' materi dipilih)' : ' (seluruh materi)');
        },

        payloadKonteks() {
            return {
                konteks_pertemuan_ids: this.konteksPertemuan,
                konteks_materi_ids: this.konteksMateri
            };
        }
    };
}

