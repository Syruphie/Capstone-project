<?php
declare(strict_types=1);

require_once dirname(__DIR__, 3) . '/includes/app_paths.php';

$__base = app_base_href();
?>
<base href="<?php echo htmlspecialchars($__base, ENT_QUOTES, 'UTF-8'); ?>">
<script>window.__APP_BASE__ = <?php echo json_encode(app_web_base(), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;</script>
