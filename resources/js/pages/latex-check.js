const PEMBATAS = [
    { open: '$$', close: '$$' },
    { open: '\\[', close: '\\]' },
    { open: '\\(', close: '\\)' },
    { open: '$', close: '$' },
];

// Memecah teks menjadi bagian biasa dan bagian rumus. Pembatas yang tidak
// tertutup dianggap teks biasa, sehingga konverter tidak pernah menyentuhnya
// dan pemeriksa bisa melaporkannya sebagai masalah.
export function splitMath(raw) {
    const segments = [];
    const text = String(raw ?? '');
    let index = 0;
    let plainStart = 0;

    const simpanPlain = (end) => {
        if (end > plainStart) {
            segments.push({ type: 'plain', text: text.slice(plainStart, end), start: plainStart, end });
        }
    };

    while (index < text.length) {
        const delimiter = PEMBATAS
            .filter((d) => text.startsWith(d.open, index))
            .sort((a, b) => b.open.length - a.open.length)[0];

        if (!delimiter) {
            index += 1;
            continue;
        }

        // `$` tunggal hanya boleh berpasang pada baris yang sama.
        const from = index + delimiter.open.length;
        const batas = delimiter.open === '$' ? (text.indexOf('\n', from) === -1 ? text.length : text.indexOf('\n', from)) : text.length;
        let close = -1;
        let at = from;

        while (at < batas) {
            if (text[at] === '\\') {
                at += 2;
                continue;
            }
            if (text.startsWith(delimiter.close, at)) {
                close = at;
                break;
            }
            at += 1;
        }

        if (close === -1 || close === from) {
            index += 1;
            continue;
        }

        simpanPlain(index);
        segments.push({
            type: 'math',
            text: text.slice(from, close),
            open: delimiter.open,
            close: delimiter.close,
            start: index,
            end: close + delimiter.close.length,
        });
        index = close + delimiter.close.length;
        plainStart = index;
    }

    simpanPlain(text.length);

    return segments;
}

function jumlahArgumen(after) {
    let i = 0;
    let args = 0;

    while (i < after.length && args < 2) {
        while (/\s/.test(after[i] ?? '')) i += 1;

        if (after[i] === '[') {
            let depth = 0;
            while (i < after.length) {
                if (after[i] === '[') depth += 1;
                if (after[i] === ']') depth -= 1;
                i += 1;
                if (depth === 0) break;
            }
            continue;
        }

        if (after[i] === '{') {
            let depth = 0;
            while (i < after.length) {
                if (after[i] === '{') depth += 1;
                if (after[i] === '}') depth -= 1;
                i += 1;
                if (depth === 0) break;
            }
            args += 1;
            continue;
        }

        if (/[0-9a-zA-Z\\]/.test(after[i] ?? '')) {
            i += after[i] === '\\' ? 2 : 1;
            args += 1;
            continue;
        }

        break;
    }

    return args;
}

function keseimbanganKurung(latex) {
    let depth = 0;
    let terendah = 0;

    for (let i = 0; i < latex.length; i += 1) {
        if (latex[i] === '\\') {
            if (latex[i + 1] === '{') depth += 1;
            if (latex[i + 1] === '}') depth -= 1;
            i += 1;
            continue;
        }
        if (latex[i] === '{') depth += 1;
        if (latex[i] === '}') depth -= 1;
        terendah = Math.min(terendah, depth);
    }

    return { depth, terendah };
}

export function findLatexIssues(raw) {
    if (!raw) return [];

    const issues = [];

    for (const segment of splitMath(raw)) {
        if (segment.type === 'plain') {
            const teks = segment.text;

            if (teks.includes('$$')) {
                issues.push({ start: segment.start, end: segment.end, message: 'Pembatas $$ belum ditutup.' });
            }
            if (teks.includes('\\[')) {
                issues.push({ start: segment.start, end: segment.end, message: 'Pembatas \\[ belum ditutup dengan \\].' });
            }
            if (teks.includes('\\(')) {
                issues.push({ start: segment.start, end: segment.end, message: 'Pembatas \\( belum ditutup dengan \\).' });
            }

            continue;
        }

        const latex = segment.text;
        const { depth, terendah } = keseimbanganKurung(latex);

        if (terendah < 0) {
            issues.push({ start: segment.start, end: segment.end, message: 'Tanda } tanpa pasangan { dalam rumus.' });
        } else if (depth > 0) {
            issues.push({ start: segment.start, end: segment.end, message: 'Tanda { belum ditutup dalam rumus.' });
        }

        const begins = latex.match(/\\begin\{[^}]*\}/g) || [];
        const ends = latex.match(/\\end\{[^}]*\}/g) || [];
        if (begins.length !== ends.length) {
            issues.push({ start: segment.start, end: segment.end, message: 'Lingkungan \\begin tidak berpasangan dengan \\end.' });
        }

        for (const match of latex.matchAll(/\\(d?t?frac|sqrt)\b/g)) {
            const perlu = match[1] === 'sqrt' ? 1 : 2;
            if (jumlahArgumen(latex.slice(match.index + match[0].length)) < perlu) {
                issues.push({
                    start: segment.start,
                    end: segment.end,
                    message: `\\${match[1]} kehilangan argumennya.`,
                });
            }
        }
    }

    return issues;
}

const ATURAN = [
    { pattern: /(?<![\w.\\$])sqrt\(([^()\n]+)\)/g, ganti: (_, isi) => '$\\sqrt{' + isi.trim() + '}$' },
    { pattern: /(?<![\w.\\$])int_([A-Za-z0-9]+)\^([A-Za-z0-9]+)/g, ganti: (_, a, b) => '$\\int_{' + a + '}^{' + b + '}$' },
    { pattern: /(\d+(?:[.,]\d+)?)\s*deg\b/g, ganti: (_, n) => `$${n}^\\circ$` },
    { pattern: /(?<![\w./$-])(\d{1,3})\/(\d{1,3})(?![\w./-])/g, ganti: (_, a, b) => `$\\frac{${a}}{${b}}$` },
    { pattern: /(?<![\w.$])(\w)\^\{?([A-Za-z0-9]+)\}?(?![\w.])/g, ganti: (_, basis, pangkat) => `$${basis}^{${pangkat}}$` },
    { pattern: /(?<=[\d*])\s*\bpi\b/g, ganti: () => '$\\pi$' },
    { pattern: /(?<![<>!=])<=(?!=)/g, ganti: () => '$\\le$' },
    { pattern: /(?<![<>!=])>=(?!=)/g, ganti: () => '$\\ge$' },
    { pattern: /(?<![<>!=\/])!=(?!=)/g, ganti: () => '$\\ne$' },
];

export function convertAsciiMath(raw) {
    if (raw == null) return { text: raw ?? '', changes: [] };

    const changes = [];

    const text = splitMath(raw)
        .map((segment) => {
            if (segment.type === 'math') return segment.open + segment.text + segment.close;

            return ATURAN.reduce((teks, aturan) => teks.replace(aturan.pattern, (...match) => {
                const to = aturan.ganti(...match);
                changes.push({ from: match[0], to });
                return to;
            }), segment.text);
        })
        .join('');

    return { text, changes };
}
