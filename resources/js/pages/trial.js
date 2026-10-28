window.trialChat = function () {
    return {
        messages: [{
            role: 'bot',
            content: 'Halo! Saya AI Tutor SIMBA 👋\n\nSaya siap bantu kamu belajar. Coba tanya apa pun tentang pelajaran — Matematika, IPA, IPS, Bahasa, atau lainnya. Gratis, tanpa perlu daftar!',
            time: new Date().toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' })
        }],
        input: '',
        loading: false,
        sessionId: crypto.randomUUID(),
        isAtBottom: true,

        init() {
            this.$watch('loading', val => {
                if (!val) this.$nextTick(() => { this.$refs.input?.focus(); });
            });
            this.$nextTick(() => this.scrollToBottom());
        },

        sendMessage() {
            if (!this.input.trim() || this.loading) return;
            const msg = this.input.trim();
            this.messages.push({
                role: 'user',
                content: msg,
                time: new Date().toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' })
            });
            this.input = '';
            this.loading = true;
            this.$nextTick(() => this.scrollToBottom());

            fetch('/api/trial/send', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content
                },
                body: JSON.stringify({ session_id: this.sessionId, message: msg })
            })
            .then(r => r.json())
            .then(d => {
                const reply = d.data?.response || 'Maaf, AI tidak memberikan respons. Silakan coba pertanyaan lain.';
                this.messages.push({
                    role: 'bot',
                    content: reply,
                    time: new Date().toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' })
                });
                this.$nextTick(() => this.scrollToBottom());
            })
            .catch(() => {
                this.messages.push({
                    role: 'bot',
                    content: 'Maaf, terjadi kesalahan. Silakan coba lagi nanti.',
                    time: new Date().toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' })
                });
                this.$nextTick(() => this.scrollToBottom());
            })
            .finally(() => { this.loading = false; });
        },

        resetChat() {
            if (this.loading) return;
            this.messages = [{
                role: 'bot',
            content: 'Halo! Saya AI Tutor SIMBA 👋\n\nSaya siap bantu kamu belajar. Coba tanya apa pun tentang pelajaran. Gratis, tanpa perlu daftar!',
                time: new Date().toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' })
            }];
            this.sessionId = crypto.randomUUID();
            this.$nextTick(() => this.scrollToBottom());
        },

        handleScroll() {
            const el = this.$refs.chatbox;
            if (!el) return;
            this.isAtBottom = el.scrollHeight - el.scrollTop - el.clientHeight < 60;
        },

        scrollToBottom() {
            const el = this.$refs.chatbox;
            if (!el) return;
            el.scrollTop = el.scrollHeight;
            this.isAtBottom = true;
        }
    };
};
