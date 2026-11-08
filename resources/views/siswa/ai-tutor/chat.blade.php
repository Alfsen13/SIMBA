@php $title = 'AI Tutor — ' . $pertemuan->nama_pertemuan; @endphp

@extends('layouts.app')

@section('content')
<div class="py-6" x-data="aiTutorChat()" x-init="init()">
    {{-- Header --}}
    <div class="flex items-center justify-between mb-4">
        <div>
            <div class="flex items-center gap-2 text-sm text-slate-500 dark:text-slate-400 mb-1">
                <a href="{{ route('pelajar.ai-tutor') }}" class="hover:text-emerald-700 dark:hover:text-emerald-300 transition-colors">{{ __('navigation.ai_tutor') }}</a>
                <svg class="w-3.5 h-3.5 text-slate-500 dark:text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                <a href="{{ route('pelajar.ai-tutor.pertemuan', $kelas) }}" class="hover:text-emerald-700 dark:hover:text-emerald-300 transition-colors">{{ $kelas->nama_kelas }}</a>
                <svg class="w-3.5 h-3.5 text-slate-500 dark:text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                <span class="text-slate-700 dark:text-slate-300 font-medium">{{ __('publik.ai_tutor.pertemuan_label') }} {{ $pertemuan->urutan }}</span>
            </div>
            <h1 class="text-xl font-bold text-slate-900 dark:text-slate-100">{{ $pertemuan->nama_pertemuan }}</h1>
        </div>
        <div class="flex items-center gap-2">
            <button
                type="button"
                @click="sidebarOpen = !sidebarOpen"
                :aria-expanded="sidebarOpen.toString()"
                aria-controls="ai-tutor-sumber"
                aria-label="{{ __('publik.ai_tutor.toggle_sumber') }}"
                class="lg:hidden min-h-11 min-w-11 p-2 rounded-lg border border-slate-200 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors"
                title="{{ __('publik.ai_tutor.toggle_sumber') }}"
            >
                <svg class="w-5 h-5 text-slate-600 dark:text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
            </button>
            <a href="{{ route('pelajar.ai-tutor.pertemuan', $kelas) }}" class="inline-flex items-center min-h-11 min-w-11 text-sm text-slate-500 dark:text-slate-400 hover:text-emerald-700 dark:hover:text-emerald-300 transition-colors">
                &larr; {{ __('common.back') }}
            </a>
        </div>
    </div>

    {{-- Main container --}}
    <div class="flex gap-4 relative" style="height: calc(100vh - 200px); min-height: 480px;">

        {{-- Sidebar: read-only materi info --}}
        <div
            id="ai-tutor-sumber"
            class="w-72 shrink-0 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg overflow-hidden flex flex-col transition-all duration-300"
            :class="sidebarOpen ? 'visible opacity-100 translate-x-0' : 'invisible opacity-0 -translate-x-4 pointer-events-none lg:visible lg:opacity-100 lg:translate-x-0 lg:pointer-events-auto'"
            style="position: absolute; left: 0; top: 0; bottom: 0; z-index: 10;"
        >
            <div class="px-4 py-3 border-b border-slate-100 dark:border-slate-800 bg-slate-50/80 dark:bg-slate-800/80 backdrop-blur-sm sticky top-0 z-10">
                <div class="flex items-center justify-between">
                    <h2 class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">{{ __('publik.ai_tutor.sumber_materi') }}</h2>
                    <span class="text-xs text-slate-600 dark:text-slate-300 bg-slate-100 dark:bg-slate-800 px-2 py-0.5 rounded-full">{{ count($allMateriInPertemuan) }} {{ __('publik.ai_tutor.materi_aktif') }}</span>
                </div>
                <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-1">{{ __('publik.ai_tutor.semua_materi_referensi') }}</p>
            </div>

            <div class="flex-1 overflow-y-auto px-3 py-3 space-y-2">
                @foreach($allMateriInPertemuan as $materi)
                    <div class="rounded-lg border border-emerald-100 dark:border-emerald-800 bg-emerald-50/40 dark:bg-emerald-900/20 px-3 py-2.5">
                        <div class="flex items-start gap-2.5">
                            <div class="shrink-0 mt-0.5">
                                <div class="w-7 h-7 rounded-lg bg-emerald-100 dark:bg-emerald-600/20 flex items-center justify-center">
                                    <svg class="w-3.5 h-3.5 text-emerald-700 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                                </div>
                            </div>
                            <div class="min-w-0 flex-1">
                                <h3 class="text-sm font-medium text-emerald-900 dark:text-emerald-200 truncate">{{ $materi->judul }}</h3>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            @if($allMateriInPertemuan->first()->babSummary ?? false)
                <div class="border-t border-slate-100 dark:border-slate-800 bg-white dark:bg-slate-900">
                    <button @click="summaryOpen = !summaryOpen" class="w-full flex items-center justify-between px-4 py-2.5 text-xs font-semibold text-slate-500 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors">
                        <span>{{ __('publik.ai_tutor.ringkasan_bab') }}</span>
                        <svg class="w-3.5 h-3.5 transition-transform duration-200" :class="summaryOpen && 'rotate-180'" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </button>
                    <div x-show="summaryOpen" x-collapse class="px-4 pb-3">
                        <p class="text-xs text-slate-600 dark:text-slate-400 leading-relaxed">{{ \Illuminate\Support\Str::limit($allMateriInPertemuan->first()->babSummary->ringkasan, 500) }}</p>
                    </div>
                </div>
            @endif
        </div>

        <div class="hidden lg:block w-72 shrink-0"></div>

        {{-- Chat area --}}
        <div class="flex-1 flex flex-col bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg overflow-hidden">
            <div x-ref="messages" role="log" aria-live="polite" aria-relevant="additions" class="flex-1 overflow-y-auto px-6 py-5 space-y-5">
                <template x-for="(msg, idx) in messages" :key="idx">
                    <div class="flex gap-3 msg-fade-in" :class="msg.role === 'user' ? 'flex-row-reverse' : ''">
                        <template x-if="msg.role === 'assistant'">
                            <div class="shrink-0 mt-1">
                                <div class="w-8 h-8 rounded-full bg-gradient-to-br from-emerald-500 to-purple-600 flex items-center justify-center shadow-sm">
                                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/></svg>
                                </div>
                            </div>
                        </template>
                        <div class="max-w-[75%]" :class="msg.role === 'user' ? 'text-right' : ''">
                            <div
                                class="inline-block text-sm leading-relaxed px-4 py-2.5 rounded-lg text-left render-md"
                                :class="msg.role === 'user' ? 'bg-emerald-700 text-white rounded-br-md' : 'bg-slate-100 text-slate-800 rounded-bl-md'"
                                :data-raw="msg.content"
                                x-html="renderMarkdown(msg.content)"
                            ></div>
                            <div class="text-[10px] mt-1 px-1 text-slate-500 dark:text-slate-400" x-text="formatTime(msg.timestamp)"></div>
                        </div>
                    </div>
                </template>

                <template x-if="isLoading">
                    <div class="flex gap-3 msg-fade-in">
                        <div class="shrink-0 mt-1">
                            <div class="w-8 h-8 rounded-full bg-gradient-to-br from-emerald-500 to-purple-600 flex items-center justify-center shadow-sm">
                                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/></svg>
                            </div>
                        </div>
                        <div>
                            <div class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-lg rounded-bl-md bg-slate-100 dark:bg-slate-800">
                                <span class="typing-dot"></span>
                                <span class="typing-dot"></span>
                                <span class="typing-dot"></span>
                            </div>
                            <p role="status" aria-live="polite" class="sr-only" x-text="isLoading ? @js(__('publik.ai_tutor.sedang_memproses')) : ''"></p>
                        </div>
                    </div>
                </template>
            </div>

            <div x-show="messages.length === 0 && !isLoading" class="px-6 pb-3">
                <div class="flex flex-wrap gap-2">
                    <template x-for="(q, i) in suggestedQuestions" :key="i">
                        <button
                            @click="input = q; sendMessage()"
                            type="button"
                            class="min-h-11 min-w-11 text-xs px-3 py-1.5 rounded-full border border-emerald-200 dark:border-emerald-800 text-emerald-700 dark:text-emerald-400 hover:bg-emerald-50 dark:hover:bg-emerald-600/10 transition-colors"
                            x-text="q"
                        ></button>
                    </template>
                </div>
            </div>

            <div class="border-t border-slate-100 dark:border-slate-800 bg-slate-50/40 dark:bg-slate-900/30 px-4 py-3">
                <form @submit.prevent="sendMessage()" class="flex items-end gap-3">
                    <div class="flex-1 relative">
                        <label for="ai-tutor-pesan" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">{{ __('publik.ai_tutor.pesan') }}</label>
                        <textarea
                            id="ai-tutor-pesan"
                            x-ref="chatInput"
                            x-model="input"
                            @keydown="handleKeydown($event)"
                            rows="1"
                            placeholder="{{ __('publik.ai_tutor.placeholder_tanya') }}"
                            class="w-full resize-none rounded-lg border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 px-4 py-3 pr-12 text-sm text-slate-800 dark:text-slate-200 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-400 transition-all"
                            :disabled="isLoading"
                            @input="autoResize($event.target)"
                            style="max-height: 120px;"
                        ></textarea>
                        <div class="absolute right-2 bottom-2 text-[10px] text-slate-500 dark:text-slate-400" x-show="input.length > 0"><span x-text="input.length"></span> {{ __('publik.ai_tutor.karakter') }}</div>
                    </div>
                    <button
                        type="submit"
                        aria-label="{{ __('publik.ai_tutor.kirim_pesan') }}"
                        :disabled="!input.trim() || isLoading"
                        class="shrink-0 w-11 h-11 rounded-lg flex items-center justify-center transition-all"
                        :class="input.trim() && !isLoading ? 'bg-emerald-700 text-white hover:bg-emerald-800 shadow-sm' : 'bg-slate-100 text-slate-400 cursor-not-allowed'"
                    >
                        <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php
    $suggestedQuestions = [
        'Jelaskan konsep utama pada pertemuan ini',
        'Berikan contoh soal latihan',
        'Ringkas materi ini',
    ];
    $suggestedQuestionsJson = json_encode($suggestedQuestions);
?>

<script>
    window.aiTutorConfig = {
        pertemuanId: {{ $pertemuan->id }},
        apiAskUrl: @json(route('pelajar.ai-tutor.ask')),
        materiUrlMap: @json($materiUrlMap),
        suggestedQuestions: {!! $suggestedQuestionsJson !!},
    };
</script>

@pushOnce('scripts')
<script>
    function aiTutorChat() {
        return {
            messages: [],
            input: '',
            isLoading: false,
            sidebarOpen: false,
            summaryOpen: false,
            suggestedQuestions: window.aiTutorConfig?.suggestedQuestions || [],

            init() {
                this.$nextTick(() => this.scrollToBottom());
            },

            async sendMessage() {
                if (!this.input.trim() || this.isLoading) return;

                const userMsg = this.input.trim();
                const history = this.messages
                    .filter(msg => msg.role === 'user' || msg.role === 'assistant')
                    .slice(-10)
                    .map(({ role, content }) => ({ role, content }));
                this.input = '';
                this.isLoading = true;

                this.messages.push({
                    role: 'user',
                    content: userMsg,
                    timestamp: new Date().toISOString()
                });

                this.$nextTick(() => this.scrollToBottom());

                try {
                    const response = await fetch(window.aiTutorConfig.apiAskUrl, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                            'Accept': 'application/json',
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({
                            pertemuan_id: window.aiTutorConfig.pertemuanId,
                            question: userMsg,
                            history,
                        }),
                    });

                    if (!response.ok) {
                        const error = await response.json().catch(() => ({}));
                        throw new Error(error.error || error.message || 'Gagal memproses pertanyaan');
                    }

                    const data = await response.json();

                    this.messages.push({
                        role: 'assistant',
                        content: data.answer,
                        timestamp: new Date().toISOString(),
                        references: data.references || []
                    });

                } catch (error) {
                    console.error(error);
                    this.messages.push({
                        role: 'assistant',
                        content: 'Maaf, terjadi kesalahan. Silakan coba lagi.',
                        timestamp: new Date().toISOString()
                    });
                } finally {
                    this.isLoading = false;
                    this.$nextTick(() => this.scrollToBottom());
                }
            },

            handleKeydown(event) {
                if (event.key === 'Enter' && !event.shiftKey) {
                    event.preventDefault();
                    this.sendMessage();
                }
            },

            autoResize(textarea) {
                textarea.style.height = 'auto';
                textarea.style.height = Math.min(textarea.scrollHeight, 120) + 'px';
            },

            scrollToBottom() {
                const el = this.$refs.messages;
                if (el) {
                    el.scrollTop = el.scrollHeight;
                }
            },

            formatTime(iso) {
                const date = new Date(iso);
                return date.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
            },

            renderMarkdown(text) {
                if (!text) return '';
                let html = this.escapeHtml(text);

                html = html.replace(/\[\[([^\]]+)\]\]/g, (match, judul) => {
                    const key = this.normalizeJudul(judul);
                    const url = window.aiTutorConfig.materiUrlMap[key];
                    const label = `<span class="inline-flex items-center gap-1 text-emerald-700 font-medium">${judul}</span>`;

                    return url
                        ? `<a href="${this.escapeHtml(url)}" target="_blank" rel="noopener noreferrer" class="underline">${label}</a>`
                        : label;
                });

                return html
                    .replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>')
                    .replace(/\*(.*?)\*/g, '<em>$1</em>')
                    .replace(/`([^`]+)`/g, '<code class="px-1.5 py-0.5 bg-slate-200 dark:bg-slate-700 rounded text-xs">$1</code>')
                    .replace(/\n/g, '<br>');
            },

            escapeHtml(text) {
                return String(text).replace(/[&<>"']/g, character => ({
                    '&': '&amp;',
                    '<': '&lt;',
                    '>': '&gt;',
                    '"': '&quot;',
                    "'": '&#039;',
                })[character]);
            },

            normalizeJudul(judul) {
                return String(judul || '')
                    .toLowerCase()
                    .replace(/\bhlm\.?\s*\d+\s*(?:[-–]\s*\d+)?/g, ' ')
                    .replace(/[^\p{L}\p{N}\s]+/gu, ' ')
                    .replace(/\s+/g, ' ')
                    .trim();
            },
        };
    }
</script>
@endpushOnce
@endsection