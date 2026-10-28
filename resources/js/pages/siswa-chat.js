window.chatbot = function () {
    return {
        messages: [{ role: 'bot', content: 'Halo! Ada yang bisa saya bantu?' }],
        input: '',
        loading: false,
        sendMessage() {
            if (!this.input.trim() || this.loading) return;
            this.messages.push({ role: 'user', content: this.input });
            this.loading = true;
            const msg = this.input;
            this.input = '';
            fetch(window.siswaChatSendUrl, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                body: JSON.stringify({ message: msg }),
                credentials: 'same-origin'
            })
            .then(r => r.json())
            .then(d => {
                this.messages.push({ role: 'bot', content: d.reply || 'Maaf, saya tidak bisa menjawab.' });
                this.$nextTick(() => this.$refs.chatbox.scrollTop = this.$refs.chatbox.scrollHeight);
            })
            .catch(() => {
                this.messages.push({ role: 'bot', content: 'Terjadi kesalahan. Silakan coba lagi.' });
            })
            .finally(() => { this.loading = false; });
        }
    };
};
