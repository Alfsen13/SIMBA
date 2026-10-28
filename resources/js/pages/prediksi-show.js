


const gridColor = '#e5e7eb';
const font = { family: 'inherit', size: 11 };

const palette = ['#6366f1', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6', '#14b8a6', '#dc2626'];

function baseOptions() {
    return {
        responsive: true,
        maintainAspectRatio: false,
        interaction: { mode: 'index', intersect: false },
        plugins: {
            legend: { labels: { boxWidth: 10, boxHeight: 10, font } },
            tooltip: { titleFont: font, bodyFont: font },
        },
        scales: {
            x: { ticks: { font, maxRotation: 45 }, grid: { color: gridColor } },
            y: { ticks: { font }, grid: { color: gridColor } },
        },
    };
}

function dataset(label, data, color) {
    return {
        label,
        data,
        borderColor: color,
        backgroundColor: color,
        pointRadius: 2.5,
        pointHoverRadius: 4,
        borderWidth: 2,
        tension: 0.3,
        spanGaps: true,
    };
}

async function initPrediksiShow() {
    const dataEl = document.getElementById('prediksi-show-data');
    if (!dataEl) return;

    const data = JSON.parse(dataEl.textContent);
    const { Chart } = await import('chart.js/auto');

    const riwayat = data.riwayat || { labels: [], features: [] };
    const buttonsEl = document.getElementById('indicator-buttons');
    const canvas = document.getElementById('chart-indikator');

    if (riwayat.features.length > 0 && riwayat.labels.length > 0 && canvas) {
        buttonsEl.innerHTML = riwayat.features
            .map(
                (f, i) =>
                    `<button type="button" data-feature="${f.key}"
                        class="px-3 py-1.5 rounded-lg text-xs font-medium transition-colors ring-1 ring-inset
                        ring-slate-200 dark:ring-slate-700 text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800">${f.label}</button>`
            )
            .join('');

        const chart = new Chart(canvas, {
            type: 'line',
            data: { labels: riwayat.labels, datasets: [] },
            options: {
                ...baseOptions(),
                plugins: { legend: { display: false }, tooltip: { titleFont: font, bodyFont: font } },
            },
        });

        const setIndicator = (key) => {
            const index = riwayat.features.findIndex((f) => f.key === key);
            const meta = index >= 0 ? riwayat.features[index] : riwayat.features[0];
            const i = index >= 0 ? index : 0;

            chart.data.datasets = [dataset(meta.label, meta.data, palette[i % palette.length])];
            chart.data.labels = riwayat.labels;
            chart.options.scales.y.title = { display: true, text: meta.label, font };
            chart.update();

            buttonsEl.querySelectorAll('button[data-feature]').forEach((btn) => {
                const active = btn.dataset.feature === meta.key;
                btn.classList.toggle('bg-emerald-700', active);
                btn.classList.toggle('text-white', active);
                btn.classList.toggle('ring-emerald-600', active);
                btn.classList.toggle('text-slate-600', !active);
                btn.classList.toggle('dark:text-slate-300', !active);
            });
        };



        buttonsEl.querySelectorAll('button[data-feature]').forEach((btn) => {
            btn.addEventListener('click', () => setIndicator(btn.dataset.feature));
        });

        window.PrediksiShow = { ...(window.PrediksiShow || {}), setIndicator };
        // Default: rata_durasi_belajar (dulu index 1 saat frekuensi akses masih
        // ada di deret; sekarang frekuensi keluar dari grafik jadi index 0).
        setIndicator(riwayat.features[0].key);
    } else {
        buttonsEl?.classList.add('hidden');
    }

    const aktivitas = data.aktivitas;
    const canvasAktivitas = document.getElementById('chart-aktivitas');
    if (aktivitas && aktivitas.labels.length > 0 && canvasAktivitas) {
        new Chart(canvasAktivitas, {
            type: 'line',
            data: {
                labels: aktivitas.labels,
                datasets: (aktivitas.datasets || []).map((d, i) =>
                    dataset(d.label, d.data, palette[i % palette.length])
                ),
            },
            options: baseOptions(),
        });
    }
}

document.addEventListener('DOMContentLoaded', initPrediksiShow);

