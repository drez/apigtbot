<?php
/**
 * OAuth consent page — template-canonical, drift-synced by gc into every
 * project (public/view/oauth-consent.php). Rendered by the runtime's
 * OAuthAuthorizeService; when this file is absent or throws, the service
 * falls back to its inline page.
 *
 * PRESENTATION ONLY. Pre-escaped vars: $productName, $logoUrl, $faviconUrl,
 * $clientName, $actionUrl. Pre-built, pre-escaped HTML: $whoHtml (signed-in
 * user line), $scopeItemsHtml (the kid-friendly scope <li> list — wording is
 * owned by the runtime service), $hiddenFieldsHtml (carried-over authorize
 * params + CSRF field, emit verbatim inside EVERY form — the Allow/Deny and
 * switch-account forms both need it). Never echo raw request data here.
 *
 * Self-contained on purpose: no project CSS bundles, no per-user theme
 * lookup — unauthenticated page, no-store.
 */
?><!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= $productName ?> — <?= _('Authorize access') ?></title>
<?php if ($faviconUrl !== '') : ?>
<link rel="icon" type="image/png" href="<?= $faviconUrl ?>">
<?php endif; ?>
<style>
    body { font-family: Arial, Helvetica, sans-serif; background: #f4f6f8; margin: 0; }
    .gc-oauth-card { max-width: 420px; margin: 48px auto; background: #fff; padding: 28px; border-radius: 10px; box-shadow: 0 2px 12px rgba(0, 0, 0, .08); }
    .gc-oauth-logo { text-align: center; margin-bottom: 16px; }
    .gc-oauth-logo img { max-height: 64px; max-width: 220px; }
    .gc-oauth-card h2 { margin-top: 0; color: #2f2f2f; }
    .gc-oauth-card p { color: #555; }
    .gc-oauth-who { color: #333; font-weight: 600; }
    .gc-oauth-card ul { color: #333; margin-top: 0; }
    .gc-oauth-card li { padding: 4px 0; }
    .gc-oauth-decide { display: flex; gap: 12px; margin-top: 18px; }
    .gc-oauth-decide button { flex: 1; padding: 10px; border: 0; border-radius: 6px; cursor: pointer; font-size: 15px; }
    .gc-oauth-deny { background: #eee; color: #333; }
    .gc-oauth-allow { background: #00d1b2; color: #fff; }
    .gc-oauth-switch { margin-top: 14px; text-align: center; }
    .gc-oauth-switch button { background: none; border: 0; color: #06c; cursor: pointer; font-size: 14px; text-decoration: underline; padding: 0; }
</style>
</head>
<body>
<div class="gc-oauth-card">
    <?php if ($logoUrl !== '') : ?>
    <div class="gc-oauth-logo"><img src="<?= $logoUrl ?>" alt="<?= $productName ?>"></div>
    <?php endif; ?>
    <h2><?= sprintf(_('Authorize %s'), '<strong>' . $clientName . '</strong>') ?></h2>
    <?= $whoHtml ?>
    <p><?= sprintf(_('"%s" wants to connect to your %s account.'), $clientName, $productName) ?></p>
    <p style="margin-bottom:4px;"><?= _('It will be able to:') ?></p>
    <ul><?= $scopeItemsHtml ?></ul>
    <form method="post" action="<?= $actionUrl ?>" class="gc-oauth-decide">
        <?= $hiddenFieldsHtml ?>
        <button type="submit" name="consent" value="deny" class="gc-oauth-deny"><?= _('Deny') ?></button>
        <button type="submit" name="consent" value="allow" class="gc-oauth-allow"><?= _('Allow') ?></button>
    </form>
    <form method="post" action="<?= $actionUrl ?>" class="gc-oauth-switch">
        <?= $hiddenFieldsHtml ?>
        <button type="submit" name="switch_account" value="1"><?= _('Use a different account') ?></button>
    </form>
</div>
</body>
</html>
