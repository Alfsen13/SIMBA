
<script>
    (function () {
        try {
            var stored = localStorage.getItem('theme');
            var prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
            var dark = stored ? stored === 'dark' : prefersDark;
            document.documentElement.classList.toggle('dark', dark);
        } catch (e) {
            /* localStorage bisa diblokir (mode privat) — abaikan, tetap mode terang */
        }
    })();
</script>
<?php /**PATH C:\xampp\htdocs\SIMBA(1)\resources\views/components/theme-script.blade.php ENDPATH**/ ?>