export function keranjangState(options = {}) {
    const pending = Array.isArray(options.pending)
        ? options.pending.map(Number).filter((id) => Number.isInteger(id) && id > 0)
        : [];
    const tanya = typeof options.tanya === 'function' ? options.tanya : (pesan) => globalThis.confirm(pesan);

    return {
        pending,
        pesan: typeof options.pesan === 'string' ? options.pesan : '',
        centang: {},
        _tanya: tanya,

        get terpilih() {
            return this.pending.filter((id) => this.centang[id] === true);
        },

        get jumlah() {
            return this.terpilih.length;
        },

        get semua() {
            return this.pending.length > 0 && this.jumlah === this.pending.length;
        },

        pilihSemua() {
            this.centang = Object.fromEntries(this.pending.map((id) => [id, true]));
        },

        kosongkan() {
            this.centang = {};
        },

        /** Kotak "pilih semua": menandai semua kartu pending, atau mengosongkan bila sudah penuh. */
        balikSemua() {
            if (this.semua) {
                this.kosongkan();
            } else {
                this.pilihSemua();
            }
        },

        tanyaTolak() {
            return this._tanya(this.pesan.replace(':count', this.jumlah));
        },
    };
}
