<?php
/**
 * Footer Template
 * RIS Form System - Margosatubig, Zamboanga del Sur LGU
 */

$is_dashboard = isset($dashboard_layout) && $dashboard_layout === true;

$app_url = defined('APP_URL')
    ? rtrim(APP_URL, '/')
    : '';

$lgu_name = defined('LGU_NAME')
    ? LGU_NAME
    : 'Local Government Unit';

$app_version = defined('APP_VERSION')
    ? APP_VERSION
    : '1.0';

$lgu_website = defined('LGU_WEBSITE')
    ? rtrim(LGU_WEBSITE, '/')
    : 'https://www.margosatubig.gov.ph';

$website_display = preg_replace(
    '#^https?://#i',
    '',
    $lgu_website
);
?>

<?php if (!$is_dashboard): ?>
    </main>
<?php endif; ?>

<style>
    .footer {
        width: 100%;
        padding: 24px 20px;
        color: #918a7a;
        background: linear-gradient(180deg, #11100d 0%, #080808 100%);
        border-top: 1px solid rgba(212, 175, 55, 0.2);
        font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
    }

    .footer-content {
        width: 100%;
        max-width: 1350px;
        margin: 0 auto;
        text-align: center;
    }

    .footer-divider {
        width: 75px;
        height: 2px;
        margin: 0 auto 14px;
        background: linear-gradient(90deg, transparent, #d4af37, transparent);
    }

    .footer p { margin: 6px 0; color: #918a7a; font-size: 0.82rem; line-height: 1.6; }
    .footer strong { color: #f3d77a; font-weight: 700; }
    .footer-link { color: #d4af37; text-decoration: none; border-bottom: 1px solid rgba(212, 175, 55, 0.35); }
    .footer-link:hover, .footer-link:focus { color: #f3d77a; border-bottom-color: #f3d77a; }
    .footer-link:focus { outline: 3px solid rgba(212, 175, 55, 0.28); outline-offset: 3px; border-radius: 3px; }

    <?php if ($is_dashboard): ?>
        .footer { width: calc(100% - 270px); margin-left: 270px; }
    <?php endif; ?>

    @media (max-width: 768px) {
        .footer { width: 100%; margin-left: 0; padding: 20px 14px; }
        .footer p { font-size: 0.76rem; }
    }
</style>

<footer class="footer" role="contentinfo" aria-label="Site footer">
    <div class="footer-content">
        <div class="footer-divider"></div>
        <p>
            &copy; <?php echo date('Y'); ?>
            <strong><?php echo htmlspecialchars($lgu_name, ENT_QUOTES, 'UTF-8'); ?></strong>
            &mdash; RIS Form System v<?php echo htmlspecialchars($app_version, ENT_QUOTES, 'UTF-8'); ?>
        </p>
        <p>
            All rights reserved. For inquiries, please visit
            <a class="footer-link" href="<?php echo htmlspecialchars($lgu_website, ENT_QUOTES, 'UTF-8'); ?>" target="_blank" rel="noopener noreferrer">
                <?php echo htmlspecialchars($website_display, ENT_QUOTES, 'UTF-8'); ?>
            </a>.
        </p>
    </div>
</footer>

<?php $print_actions_js = $app_url . '/js/print-actions.js'; ?>
<script src="<?php echo htmlspecialchars($print_actions_js, ENT_QUOTES, 'UTF-8'); ?>" defer></script>

<?php
$validation_js = $app_url . '/js/form-validation.js';
$handler_js = $app_url . '/js/form-handler.js';
?>
<script src="<?php echo htmlspecialchars($validation_js, ENT_QUOTES, 'UTF-8'); ?>" defer></script>
<script src="<?php echo htmlspecialchars($handler_js, ENT_QUOTES, 'UTF-8'); ?>" defer></script>

</body>
</html>
