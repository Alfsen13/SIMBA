

let katexPromise = null;
let katexCssLoaded = false;
function loadKatex() {
    if (!katexPromise) {
        katexPromise = Promise.all([
            import('katex'),
            import('katex/dist/katex.min.css').then(() => { katexCssLoaded = true; }),
        ]).then(([mod]) => mod.default);
    }
    return katexPromise;
}

window.aiTutor = function () {
    return {
        messages: [],
        input: '',
        pertemuanId: null,
        sesiId: null,
        isStreaming: false,
        streamingText: '',
        sidebarOpen: false,
        summaryOpen: true,
        katex: null,
        katexReady: null,
        _mathPlaceholders: [],
        suggestedQuestions: [
            'Jelaskan inti dari materi ini',
            'Buatkan rangkuman singkat',
            'Apa saja poin penting yang perlu dipahami?',
        ],

        init() {
            this.pertemuanId = window.chatPertemuanId || null;
            if (!this.pertemuanId) {
                console.error('pertemuanId not set! Check window.chatPertemuanId');
                this.messages = [{
                    role: 'assistant',
                    content: '⚠️ Error: ID pertemuan tidak ditemukan. Silakan muat ulang halaman.',
                    timestamp: Date.now()
                }];
                return;
            }
            this.messages = [{
                role: 'assistant',
                content: 'Halo! Semua materi dalam pertemuan ini sudah menjadi sumber referensi saya. Silakan ajukan pertanyaan apa saja tentang materi tersebut.',
                timestamp: Date.now()
            }];
            this.prepareKatex();
        },

        async prepareKatex() {
            if (this.katexReady) return this.katexReady;
            this.katexReady = loadKatex().then((katex) => {
                this.katex = katex;
                this.rerenderAll();
                return katex;
            });
            return this.katexReady;
        },

        rerenderAll() {

            if (this.messages && this.messages.length) {
                this.$nextTick(() => {
                    const nodes = this.$refs && this.$refs.messages
                        ? this.$refs.messages.querySelectorAll('.render-md')
                        : [];
                    nodes.forEach((el) => {
                        const raw = el.getAttribute('data-raw');
                        if (raw != null) el.innerHTML = this.renderMarkdown(raw);
                    });
                    const st = this.$refs && this.$refs.streaming;
                    if (st && this.streamingText) st.innerHTML = this.renderMarkdown(this.streamingText);
                });
            }
        },

        renderMath(text) {
            if (!text || !this.katex) return text;
            const placeholders = [];
            let result = text;

            result = result.replace(/\$\$([\s\S]+?)\$\$/g, (m, latex) => {
                const ph = `\u0000KX${placeholders.length}\u0000`;
                placeholders.push(this.katex.renderToString(latex.trim(), { throwOnError: false, displayMode: true }));
                return ph;
            });
            result = result.replace(/\\\[([\s\S]+?)\\\]/g, (m, latex) => {
                const ph = `\u0000KX${placeholders.length}\u0000`;
                placeholders.push(this.katex.renderToString(latex.trim(), { throwOnError: false, displayMode: true }));
                return ph;
            });

            result = result.replace(/\\\(([\s\S]+?)\\\)/g, (m, latex) => {
                const ph = `\u0000KX${placeholders.length}\u0000`;
                placeholders.push(this.katex.renderToString(latex.trim(), { throwOnError: false, displayMode: false }));
                return ph;
            });
            result = result.replace(/(^|[^\\])\$([^$]+?)\$/g, (m, pre, latex) => {
                const ph = `\u0000KX${placeholders.length}\u0000`;
                placeholders.push(this.katex.renderToString(latex.trim(), { throwOnError: false, displayMode: false }));
                return pre + ph;
            });

            this._mathPlaceholders = placeholders;
            return result;
        },

        restoreMath(html) {
            const placeholders = this._mathPlaceholders || [];
            return html.replace(/\u0000KX(\d+)\u0000/g, (m, i) => placeholders[Number(i)] || '');
        },

        normalizeJudul(judul) {
            return String(judul || '')
                .toLowerCase()
                .replace(/\bhlm\.?\s*\d+\s*(?:[-â€“]\s*\d+)?/g, ' ')
                .replace(/[^\p{L}\p{N}\s]+/gu, ' ')
                .replace(/\s+/g, ' ')
                .trim();
        },


        resolveMateriUrl(judul) {
            const map = window.materiUrlMap || {};
            if (map[judul]) return map[judul];

            const key = this.normalizeJudul(judul);
            if (key && map[key]) return map[key];
            if (!key || key.length < 3) return null;

            const match = Object.keys(map).find((k) => k && (k.includes(key) || key.includes(k)));

            return match ? map[match] : null;
        },

        renderMarkdown(text) {
            if (!text) return '';

            this.prepareKatex();

            let html = this.renderMath(text);

            html = html
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;');

            html = html.replace(/\[\[([^\]]+)\]\]/g, (match, judul) => {
                const url = this.resolveMateriUrl(judul);
                const icon = '<svg class="w-3 h-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>';
                if (url) {
                    return `<a href="${url}" target="_blank" class="inline-flex items-center gap-1 bg-emerald-50 border border-emerald-200 text-emerald-700 hover:bg-emerald-100 hover:border-emerald-300 rounded-md px-1.5 py-0.5 text-xs font-medium leading-none cursor-pointer transition-colors no-underline" title="Buka materi: ${judul}">${icon}${judul}</a>`;
                }
                return `<span class="inline-flex items-center gap-1 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-md px-1.5 py-0.5 text-xs font-medium leading-none" title="${judul}">${icon}${judul}</span>`;
            });

            html = html.replace(/\*\*(.+?)\*\*/g, '<strong>$1</strong>');
            html = html.replace(/\*(.+?)\*/g, '<em>$1</em>');
            html = html.replace(/\n/g, '<br>');

            html = this.restoreMath(html);

            return html;
        },

        formatTime(ts) {
            if (!ts) return '';
            const d = new Date(ts);
            return d.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
        },

        autoResize(el) {
            el.style.height = 'auto';
            el.style.height = Math.min(el.scrollHeight, 120) + 'px';
        },

        handleKeydown(e) {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                this.sendMessage();
            }
        },

        async startSession() {
            if (!this.pertemuanId) return;

            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
            if (!csrfToken) {
                const msg = 'CSRF token tidak ditemukan. Pastikan meta tag csrf-token ada di layout.';
                console.error(msg);
                this.showError(msg);
                throw new Error(msg);
            }

            try {
                const res = await fetch(window.apiChatStartUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    credentials: 'same-origin',
                    body: JSON.stringify({
                        pertemuan_id: this.pertemuanId,
                    })
                });
                const data = await res.json();
                if (!res.ok) {
                    throw new Error(data.message || `HTTP ${res.status}: ${res.statusText}`);
                }
                if (data.success) {
                    this.sesiId = data.data.sesi_id;
                } else {
                    throw new Error(data.message || 'Gagal memulai sesi chat');
                }
            } catch (e) {
                console.error('Failed to start session:', e);
                this.showError('Gagal memulai sesi: ' + e.message);
                throw e;
            }
        },

        showError(message) {
            this.messages.push({
                role: 'assistant',
                content: `⚠️ ${message}`,
                timestamp: Date.now()
            });
            this.isStreaming = false;
            this.streamingText = '';
            this.scrollToBottom();
        },

        async sendMessage() {
            if (!this.input.trim() || this.isStreaming || !this.pertemuanId) return;

            if (!this.sesiId) {
                try {
                    await this.startSession();
                } catch (e) {
                    return; // Error already shown by startSession
                }
            }
            if (!this.sesiId) return;

            const userMsg = this.input.trim();
            this.messages.push({ role: 'user', content: userMsg, timestamp: Date.now() });
            this.input = '';
            this.isStreaming = true;
            this.streamingText = '';
            this.scrollToBottom();

            try {
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
                if (!csrfToken) {
                    throw new Error('CSRF token tidak ditemukan');
                }

                const response = await fetch(window.apiChatSendUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    credentials: 'same-origin',
                    body: JSON.stringify({
                        sesi_id: this.sesiId,
                        pertemuan_id: this.pertemuanId,
                        pesan: userMsg
                    })
                });

                if (!response.ok) {
                    const errData = await response.json().catch(() => ({}));
                    throw new Error(errData.message || `HTTP ${response.status}: ${response.statusText}`);
                }

                const reader = response.body.getReader();
                const decoder = new TextDecoder();

                while (true) {
                    const { done, value } = await reader.read();
                    if (done) break;

                    const chunk = decoder.decode(value, { stream: true });
                    const lines = chunk.split('\n').filter(l => l.startsWith('data: '));

                    for (const line of lines) {
                        try {
                            const json = JSON.parse(line.slice(6));
                            if (json.done) {
                                this.messages.push({ role: 'assistant', content: this.streamingText, timestamp: Date.now() });
                                this.streamingText = '';
                                this.isStreaming = false;
                                this.scrollToBottom();
                            } else if (json.token) {
                                this.streamingText += json.token;
                                this.scrollToBottom();
                            }
                        } catch (e) {}
                    }
                }
            } catch (e) {
                console.error('Send message error:', e);
                this.showError('Gagal mengirim pesan: ' + e.message);
            }

            this.scrollToBottom();
        },

        scrollToBottom() {
            this.$nextTick(() => {
                if (this.$refs.messages) {
                    this.$refs.messages.scrollTop = this.$refs.messages.scrollHeight;
                }
            });
        }
    };
};

