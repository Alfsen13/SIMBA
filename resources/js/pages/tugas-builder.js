import { konteksSoalState } from './konteks-soal';
import { normalisasiSoalEsai, normalisasiSoalPG } from './soal-tugas';

window.tugasBuilder = function (initial = {}, options = {}) {
    const opts = options || {};
    const data = initial || {};
    const rawSoal = Array.isArray(data.soal) ? data.soal : [];
    const tipe = data.tipe || 'upload';

    return {
        ...konteksSoalState(opts, opts.pertemuanId ?? opts.selectedPertemuan ?? null),
        tipe,




        soal: rawSoal.map(normalisasiSoalEsai),
        questions: rawSoal.map(normalisasiSoalPG),
        bloomLevels: Array.isArray(opts.bloomLevels) ? opts.bloomLevels : [],
        pertemuanId: opts.pertemuanId || null,
        selectedPertemuan: opts.selectedPertemuan ?? opts.pertemuanId ?? null,
        materiUtama: opts.materiUtama ? String(opts.materiUtama) : '',
        materiPendukung: Array.isArray(opts.materiPendukung) ? opts.materiPendukung.map(String) : [],
        generateUrl: opts.generateUrl || '',
        generating: false,
        genError: '',
        genMessage: '',
        genJumlah: 5,
        genKesulitan: 'mudah',
        genTopik: '',

        init() {
            this.initKonteks();



            this.$watch('selectedPertemuan', () => {
                const milikPertemuanIni = this.materiDari(this.selectedPertemuan || this.pertemuanId)
                    .some(m => String(m.id) === String(this.materiUtama));

                if (this.materiUtama && !milikPertemuanIni) {
                    this.materiUtama = '';
                }



                if (this.materiUtama) {
                    this.materiPendukung = this.materiPendukung
                        .filter(id => String(id) !== String(this.materiUtama));
                }
            });
        },

        addSoal() {
            this.soal.push({ pertanyaan: '', referensi: '', bloom: null });
        },
        removeSoal(i) {
            this.soal.splice(i, 1);
        },
        addQuestion() {
            this.questions.push({ pertanyaan: '', pilihan: ['', '', '', ''], jawaban: 0, bloom: null });
        },
        removeQuestion(i) {
            this.questions.splice(i, 1);
        },
        bloomLabel(code) {
            const found = this.bloomLevels.find(l => l.code === code);
            return found ? found.code + ' â€” ' + found.name_id : (code || '');
        },
        soalJson() {
            return JSON.stringify(this.tipe === 'pilihan_ganda' ? this.questions : this.soal);
        },
        async generateSoal() {
            if (this.generating) {
                return;
            }

            const pertemuanId = this.selectedPertemuan || this.pertemuanId;
            if (!pertemuanId) {
                this.genError = 'Pilih pertemuan terlebih dahulu.';
                return;
            }

            this.generating = true;
            this.genError = '';
            this.genMessage = '';

            try {
                const res = await fetch(this.generateUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': (document.querySelector('meta[name="csrf-token"]') || {}).getAttribute('content') || ''
                    },
                    body: JSON.stringify({
                        pertemuan_id: pertemuanId,
                        tipe: this.tipe,
                        jumlah: this.genJumlah,
                        kesulitan: this.genKesulitan,
                        topik: this.genTopik || '',
                        ...this.payloadKonteks()
                    })
                });

                const text = await res.text();
                let data = null;

                try {
                    data = JSON.parse(text);
                } catch {
                    this.genError = 'Respons server tidak valid. Silakan coba lagi.';
                    this.generating = false;
                    return;
                }

                if (!(res.ok && data && data.status === 'queued' && data.key)) {
                    // Sync response (generate langsung mengembalikan soal)
                    if (res.ok && data && data.success && Array.isArray(data.soal)) {
                        const hasil = Array.isArray(data.soal) ? data.soal : [];
                        if (this.tipe === 'essai') {
                            hasil.map(normalisasiSoalEsai).forEach((s) => this.soal.push(s));
                        } else {
                            hasil.map(normalisasiSoalPG).forEach((q) => this.questions.push(q));
                        }
                        this.genMessage = 'Berhasil menambahkan ' + hasil.length + ' soal. Silakan periksa dan rapikan sebelum menyimpan.';
                        this.generating = false;
                        return;
                    }
                    this.genError = (data && data.message) ? data.message : 'Gagal memulai proses generate soal. Silakan coba lagi.';
                    this.generating = false;
                    return;
                }

                const statusUrl = this.generateUrl.replace('/generate', '/generate-status') + '?key=' + encodeURIComponent(data.key);
                let attempts = 0;
                const maxAttempts = 800; // ~20 menit (800 * 1.5s)
                let consecutiveErrors = 0;

                const poll = setInterval(async () => {
                    attempts++;

                    try {
                        const r = await fetch(statusUrl, {
                            headers: { 'Accept': 'application/json' },
                            cache: 'no-store'
                        });

                        const st = await r.json();
                        consecutiveErrors = 0;

                        if (!st || st.status === 'processing') {
                            if (attempts >= maxAttempts) {
                                clearInterval(poll);
                                this.genError = 'Proses generate terlalu lama. Silakan coba lagi.';
                                this.generating = false;
                            }
                            return;
                        }

                        if (st.status === 'done' && Array.isArray(st.soal)) {
                            clearInterval(poll);

                            const hasil = Array.isArray(st.soal) ? st.soal : [];

                            if (this.tipe === 'essai') {
                                hasil.map(normalisasiSoalEsai).forEach((s) => this.soal.push(s));
                            } else {
                                hasil.map(normalisasiSoalPG).forEach((q) => this.questions.push(q));
                            }

                            this.genMessage = 'Berhasil menambahkan ' + hasil.length + ' soal dari ' + this.ringkasanKonteks() + '. Silakan periksa dan rapikan sebelum menyimpan.';
                            this.generating = false;
                            return;
                        }

                        if (st.status === 'failed' || st.status === 'expired') {
                            clearInterval(poll);
                            this.genError = st.message || 'Gagal menghasilkan soal. Silakan coba lagi.';
                            this.generating = false;
                            return;
                        }
                    } catch {
                        consecutiveErrors++;
                        if (consecutiveErrors >= 3 || attempts >= maxAttempts) {
                            clearInterval(poll);
                            this.genError = 'Terjadi kesalahan saat memantau proses generate. Silakan coba lagi.';
                            this.generating = false;
                        }
                    }
                }, 1500);
            } catch {
                this.genError = 'Terjadi kesalahan saat menghubungi server. Silakan coba lagi.';
                this.generating = false;
            }
        }
    };
};
