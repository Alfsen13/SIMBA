window.adminChat = function () {
    return {
        messages: [],
        input: '',
        loading: false,
        async sendMessage() {
            if (!this.input.trim() || this.loading) return;
            const msg = this.input;
            this.messages.push({ id: Date.now(), role: 'user', content: msg });
            this.input = '';
            this.loading = true;
            this.$nextTick(() => this.$refs.messages.scrollTop = this.$refs.messages.scrollHeight);
            try {
                const res = await fetch(window.adminChatSendUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    credentials: 'same-origin',
                    body: JSON.stringify({ pesan: msg })
                });
                const data = await res.json();
                if (data.success) {
                    this.messages.push({ id: Date.now(), role: 'assistant', content: data.data.pesan });
                }
            } catch (e) {
                this.messages.push({ id: Date.now(), role: 'assistant', content: 'Terjadi kesalahan.' });
            }
            this.loading = false;
            this.$nextTick(() => this.$refs.messages.scrollTop = this.$refs.messages.scrollHeight);
        }
    };
};
