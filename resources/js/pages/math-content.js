 

let katexPromise = null;

function loadKatex() {
    if (!katexPromise) {
        katexPromise = Promise.all([
            import('katex'),
            import('katex/dist/katex.min.css'),
        ]).then(([mod]) => mod.default);
    }

    return katexPromise;
}

function escapeHtml(text) {
    return text
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;');
}

 
export function renderMathText(raw, katex) {
    if (!raw) return '';

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

    let html = escapeHtml(text);
    html = html.replace(/\*\*(.+?)\*\*/g, '<strong>$1</strong>');
    html = html.replace(/\n/g, '<br>');

    return html.replace(/\u0000KX(\d+)\u0000/g, (m, i) => placeholders[Number(i)] || '');
}

export function renderMathElements(root = document) {
    const elems = root.querySelectorAll('.math-content');
    if (elems.length === 0) return;

    loadKatex()
        .then((katex) => {
            elems.forEach((el) => {
                const raw = el.getAttribute('data-raw');
                if (raw != null) el.innerHTML = renderMathText(raw, katex);
            });
        })
        .catch(() => {});
}

