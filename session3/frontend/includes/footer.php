<?php
/**
 * Footer Admin — Tailwind CSS
 */
?>
        </main><!-- fin main content -->

        <footer class="border-t border-white/5 bg-[#111120] px-6 py-4 flex flex-col sm:flex-row items-center justify-between text-xs text-slate-400 gap-2">
            <span>© <?= date('Y') ?> FreelanceAdmin — Panneau d'administration</span>
            <span class="text-slate-500 font-mono">
                PHP <?= PHP_MAJOR_VERSION . '.' . PHP_MINOR_VERSION ?> &bull; MySQL &bull; Tailwind CSS &bull; OOP Architecture
            </span>
        </footer>

    </div><!-- fin flex-1 md:pl-64 -->

<!-- Admin JavaScript -->
<script src="<?= BASE_URL ?>/frontend/assets/js/admin.js"></script>

</body>
</html>
