 

import { convertAsciiMath, findLatexIssues } from './latex-check.js';

let katexPromise = null;

export function loadKatex() {
    if (!katexPromise) {
        katexPromise = Promise.all([
            import('katex'),
            import('katex/dist/katex.min.css'),
        ]).then(([mod]) => mod.default);
    }

    return katexPromise;
}

export function escapeHtml(text) {
    return text
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;');
}

 
export function protectMath(raw, katex) {
    const placeholders = [];

    const simpan = (latex, displayMode) => {
        const ph = `\u0000KX${placeholders.length}\u0000`;

        try {
            placeholders.push(katex.renderToString(latex.trim(), {
                throwOnError: false,
                displayMode,
            }));
        } catch {
            placeholders.push(escapeHtml(latex));
        }

        return ph;
    };

    let text = raw;

    text = text.replace(/\$\$([\s\S]+?)\$\$/g, (m, latex) => simpan(latex, true));
    text = text.replace(/\\\[([\s\S]+?)\\\]/g, (m, latex) => simpan(latex, true));

    text = text.replace(/\\\(([\s\S]+?)\\\)/g, (m, latex) => simpan(latex, false));
    text = text.replace(/(^|[^\\])\$([^$]+?)\$/g, (m, pre, latex) => pre + simpan(latex, false));

    return {
        text,
        restore: html => html.replace(/\u0000KX(\d+)\u0000/g, (m, i) => placeholders[Number(i)] || ''),
    };
}

export function renderMathText(raw, katex) {
    if (!raw) return '';

    const math = protectMath(raw, katex);
    let html = escapeHtml(math.text);
    html = html.replace(/\*\*(.+?)\*\*/g, '<strong>$1</strong>');
    html = html.replace(/\n/g, '<br>');

    return math.restore(html);
}

export function mathPreviewState() {
    return {
        mathKatex: null,
        initMathPreview() {
            loadKatex().then(katex => {
                this.mathKatex = katex;
            }).catch(error => console.error('Gagal memuat penampil rumus:', error));
        },
        hasMath(text) {
            return /\$\$[\s\S]+?\$\$|\\\[[\s\S]+?\\\]|\\\([\s\S]+?\\\)|(^|[^\\])\$[^$\n]+?\$/.test(text || '');
        },
        mathPreview(text) {
            return this.mathKatex ? renderMathText(text, this.mathKatex) : escapeHtml(text || '');
        },
        periksaRumus(text) {
            return findLatexIssues(text || '');
        },
        rapikanRumus(text) {
            return convertAsciiMath(text || '').text;
        },
        akanDirapikan(text) {
            return this.rapikanRumus(text) !== (text || '');
        },
    };
}

export function hydrateMathElement(el, katex) {
    if (!el || typeof el.getAttribute !== 'function') return false;

    const raw = el.getAttribute('data-raw');
    if (raw == null) return false;

    // Simpan nilai yang sudah dirender supaya konten baru bisa dibedakan
    // dari konten yang sudah pernah di-render (penting saat AI streaming).
    if (el.dataset && el.dataset.mathRenderedRaw === raw) return false;

    el.innerHTML = renderMathText(raw, katex);
    if (el.dataset) el.dataset.mathRenderedRaw = raw;

    return true;
}

export function renderMathElements(root = document) {
    const elems = root.querySelectorAll('.math-content');
    if (elems.length === 0) return;

    loadKatex()
        .then((katex) => {
            elems.forEach((el) => hydrateMathElement(el, katex));
        })
        .catch(error => console.error('Gagal memuat penampil rumus:', error));
}

let mathObserver = null;
let mathObserverTimer = null;

export function startMathHydration(root = document.body) {
    renderMathElements(root);

    if (mathObserver || typeof MutationObserver === 'undefined') return;

    mathObserver = new MutationObserver(() => {
        // Render hasil kita sendiri juga memicu observer; lewat debounce, lalu
        // satu putaran kosong menghentikan siklusnya.
        if (mathObserverTimer) return;

        mathObserverTimer = setTimeout(() => {
            mathObserverTimer = null;
            renderMathElements();
        }, 150);
    });

    mathObserver.observe(root, {
        childList: true,
        subtree: true,
        characterData: true,
        attributes: true,
        attributeFilter: ['data-raw'],
    });
}

