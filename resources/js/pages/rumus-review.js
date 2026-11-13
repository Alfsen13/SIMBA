import { mathPreviewState } from './math-content';
import { keranjangState } from './rumus-keranjang';
import { pantauState } from './rumus-pantau';

window.rumusPantau = function (options) {
    return pantauState(options);
};

window.rumusKeranjang = function (options) {
    return keranjangState(options);
};

window.rumusTeks = function (teks) {
    return {
        ...mathPreviewState(),
        teks: typeof teks === 'string' ? teks : '',

        init() {
            this.initMathPreview();
        },
    };
};

window.rumusReview = function (latex) {
    return {
        ...mathPreviewState(),
        latex: typeof latex === 'string' ? latex : '',

        init() {
            this.initMathPreview();
        },

        get siapDikirim() {
            return this.latex.trim() !== '';
        },
    };
};
