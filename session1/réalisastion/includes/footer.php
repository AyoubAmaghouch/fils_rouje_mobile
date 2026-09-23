<?php
/**
 * Footer Admin
 * Include en bas de chaque page : require_once __DIR__ . '/../../includes/footer.php';
 */
?>
        </main><!-- fin page-wrapper -->

        <footer class="admin-footer">
            <span>© <?= date('Y') ?> FreelanceAdmin — Panneau d'administration</span>
            <span style="font-size:11px;">
                PHP <?= PHP_MAJOR_VERSION . '.' . PHP_MINOR_VERSION ?> &bull; MySQL &bull; PDO
            </span>
        </footer>

    </div><!-- fin main-content -->

</div><!-- fin admin-layout -->

<!-- Admin JavaScript -->
<script src="<?= BASE_URL ?>/assets/js/admin.js"></script>

</body>
</html>
