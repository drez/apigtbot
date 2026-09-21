<?php
/**
 * OAuth login page — template-canonical, drift-synced by gc into every
 * project (public/view/oauth-login.php). Rendered by the runtime's
 * OAuthAuthorizeService; when this file is absent or throws, the service
 * falls back to its inline page, so edits here can degrade but never break
 * the OAuth flow.
 *
 * PRESENTATION ONLY. Every var below arrives pre-escaped ($productName,
 * $logoUrl, $faviconUrl, $clientName, $actionUrl) or as pre-built,
 * pre-escaped HTML ($errorHtml, $hiddenFieldsHtml — the carried-over
 * authorize params + CSRF field, emit verbatim inside every form). Never
 * echo raw request data here.
 *
 * Self-contained on purpose: no project CSS bundles, no BuilderLayout, no
 * per-user theme lookup — this page renders before anyone is signed in
 * (Chrome Custom Tabs, no-store).
 */
?><!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= $productName ?> — <?= _('Sign in') ?></title>
<?php if ($faviconUrl !== '') : ?>
<link rel="icon" type="image/png" href="<?= $faviconUrl ?>">
<?php endif; ?>
<style>
    body { font-family: Arial, Helvetica, sans-serif; background: #f4f6f8; margin: 0; }
    .gc-oauth-card { max-width: 360px; margin: 48px auto; background: #fff; padding: 28px; border-radius: 10px; box-shadow: 0 2px 12px rgba(0, 0, 0, .08); }
    .gc-oauth-logo { text-align: center; margin-bottom: 16px; }
    .gc-oauth-logo img { max-height: 64px; max-width: 220px; }
    .gc-oauth-card h2 { margin-top: 0; color: #2f2f2f; }
    .gc-oauth-card p { color: #555; }
    .gc-oauth-card form { display: flex; flex-direction: column; gap: 12px; }
    .gc-oauth-card input { padding: 10px; border: 1px solid #ccc; border-radius: 6px; font-size: 15px; }
    .gc-oauth-card button { padding: 10px; background: #00d1b2; color: #fff; border: 0; border-radius: 6px; cursor: pointer; font-size: 15px; }
</style>
</head>
<body>
<div class="gc-oauth-card">
    <?php if ($logoUrl !== '') : ?>
    <div class="gc-oauth-logo"><img src="<?= $logoUrl ?>" alt="<?= $productName ?>"></div>
    <?php endif; ?>
    <h2><?= sprintf(_('Sign in to %s'), $productName) ?></h2>
    <p><?= sprintf(_('%s is requesting access to your %s account.'), '<strong>' . $clientName . '</strong>', $productName) ?></p>
    <?= $errorHtml ?>
    <form method="post" action="<?= $actionUrl ?>">
        <?= $hiddenFieldsHtml ?>
        <input type="text" name="u" placeholder="<?= _('Username or email') ?>" autocomplete="username" required>
        <input type="password" name="p" placeholder="<?= _('Password') ?>" autocomplete="current-password" required>
        <button type="submit"><?= _('Sign in') ?></button>
    </form>
</div>
</body>
</html>
