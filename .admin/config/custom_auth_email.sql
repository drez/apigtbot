-- Bilingual "Forgotten password email" seed (central `template` table, flat `lang`).
-- BASE SEED (no dev-only marker): runs on build, survives resetdata, deploys to prod.
-- Runs AFTER basedata.sql, which emits the single English baseline row with the legacy
-- [Utils-GuiUrl] token. We upgrade the fr_CA (lang=0) row to French with the modern
-- {{reset_link}} token, and add the en_US (lang=1) row. Both steps are guarded so an
-- operator's manual edits are never clobbered and rebuilds are idempotent.
--
-- STATUS = 1 (Inactive) IS LOAD-BEARING: letterhead resolution
-- (TemplateResolver::activeForLang) picks the newest status=0 (Active) row per language, so
-- an Active auth-email row gets selected as a quote/invoice LETTERHEAD (the "password reset
-- shows on top" bug). Auth-email rows must be status=1 to stay out of letterhead resolution
-- (same trick the Terms rows use). Password reset finds them by NAME regardless of status
-- (login.php filterByName->filterByLang, no status filter), so this does not affect reset.
-- The trailing UPDATE normalises any pre-existing Active auth rows (the status=0 row
-- basedata.sql seeds, plus rows written by earlier versions of this seed).

UPDATE `template` SET
  `subject` = 'Réinitialisation de votre mot de passe',
  `body` = '<html><body style="font-family:Open Sans,Arial,sans-serif;color:#2f2f2f;"><div style="max-width:600px;margin:30px auto;"><h2 style="color:#00a4de;">Réinitialisation du mot de passe</h2><p>Quelqu''un a demandé la réinitialisation du mot de passe de ce compte.</p><p><a href="{{reset_link}}" style="display:inline-block;padding:10px 18px;background:#00d1b2;color:#fff;border-radius:6px;text-decoration:none;">Définir un nouveau mot de passe</a></p><p style="color:#888;font-size:13px;">Ce lien est valide pendant une heure et ne peut être utilisé qu''une seule fois.</p><p style="color:#888;font-size:13px;">Si vous n''êtes pas à l''origine de cette demande, ignorez ce courriel — votre mot de passe demeure inchangé.</p></div></body></html>'
  WHERE `name` = 'Forgotten password email' AND `lang` = 0 AND `body` LIKE '%[Utils-GuiUrl]%';

INSERT INTO `template` (`name`, `subject`, `status`, `body`, `lang`, `date_creation`)
  SELECT 'Forgotten password email', 'Password reset', 1,
    '<html><body style="font-family:Open Sans,Arial,sans-serif;color:#2f2f2f;"><div style="max-width:600px;margin:30px auto;"><h2 style="color:#00a4de;">Password reset</h2><p>Someone requested a password reset for this account.</p><p><a href="{{reset_link}}" style="display:inline-block;padding:10px 18px;background:#00d1b2;color:#fff;border-radius:6px;text-decoration:none;">Set a new password</a></p><p style="color:#888;font-size:13px;">This link is valid for one hour and can be used once.</p><p style="color:#888;font-size:13px;">If you did not request this, ignore this email — your password is unchanged.</p></div></body></html>',
    1, NOW()
  FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `template` WHERE `name` = 'Forgotten password email' AND `lang` = 1);

-- Keep every auth-email row OUT of letterhead resolution (status=1 = Inactive), regardless of
-- what status basedata.sql or an earlier seed version wrote. Idempotent.
UPDATE `template` SET `status` = 1
  WHERE `name` IN ('Forgotten password email', 'Confirm your email') AND `status` <> 1;
