import Alpine from 'alpinejs'

import './pages/trial'
import './pages/admin-chat'
import './pages/guru-chat'
import './pages/siswa-chat'
import './pages/ai-tutor'
import './pages/kuis-builder'
import './pages/tugas-builder'
import './pages/prediksi-show'
import { initAktivitasDurasi } from './pages/aktivitas-durasi'
import { renderMathElements } from './pages/math-content'

window.Alpine = Alpine
Alpine.start()

renderMathElements()
initAktivitasDurasi()

if (document.querySelector('.reveal')) {
    import('./pages/welcome')
}
