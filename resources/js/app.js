import Alpine from 'alpinejs'

import './pages/trial'
import './pages/admin-chat'
import './pages/guru-chat'
import './pages/siswa-chat'
import './pages/ai-tutor'
import './pages/kuis-builder'
import './pages/tugas-builder'
import './pages/prediksi-show'
import './pages/rumus-review'
import { initAktivitasDurasi } from './pages/aktivitas-durasi'
import { loadKatex, protectMath, renderMathText, startMathHydration } from './pages/math-content'
import { convertAsciiMath, findLatexIssues } from './pages/latex-check'

window.simbaMath = { loadKatex, protectMath, renderMathText }
window.simbaLatex = { findLatexIssues, convertAsciiMath }
window.Alpine = Alpine
Alpine.start()

startMathHydration()
initAktivitasDurasi()

if (document.querySelector('.reveal')) {
    import('./pages/welcome')
}
