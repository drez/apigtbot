




            -- Default groups
INSERT IGNORE INTO `authy_group` (`id_authy_group`, `name`, `desc`, `default_group`, `admin`, `date_creation`, `date_modification`, `id_group_creation`, `id_creation`, `id_modification`) VALUES
(1, 'User', NULL, 1, 0, '2020-07-15 20:20:19', '2020-07-15 20:20:19', NULL, NULL, NULL),
(2, 'Admin', NULL, 0, 1, '2020-07-15 20:20:24', '2020-07-15 20:20:24', NULL, NULL, NULL);

            -- API settings
INSERT INTO `config` (`id_config`, `id_creation`, `id_modification`, `date_creation`, `date_modification`, `category`, `config`, `value`, `system`, `description`, `type`, `id_group_creation`) VALUES
(1, NULL, NULL, '0000-00-00 00:00:00', '2026-08-11 01:25:08', 0, 'app_status', 'prod', 0, 'API gate. prod (default, fail-closed): unknown calls Deny (401). dev: unknown calls auto-create in api_rbac as Allow AND served unauthenticated — LOCAL DEV ONLY, never ship.', 'string', 2),
(2, NULL, NULL, '0000-00-00 00:00:00', '2026-08-11 01:25:08', 0, 'app_name', 'apigtbot', 0, 'Sets the title.', 'string', 2),
(12, NULL, NULL, '0000-00-00 00:00:00', '2026-08-11 01:25:08', 1, 'vendor_logo', '/public/img/icon.png', 0, 'The url of the vendor small logo icon.', 'string', 2),
(13, NULL, NULL, '0000-00-00 00:00:00', '2026-08-11 01:25:08', 1, 'vendor_logo_login', '/public/img/icon.png', 0, 'The url of the vendor logo.', 'string', 2),
(14, NULL, NULL, '0000-00-00 00:00:00', '2026-08-11 01:25:08', 1, 'vendor_url', 'https://apigoat.com', 0, 'The url for the logo link.', 'string', 2),
(15, NULL, NULL, '2020-08-26 07:56:30', '2026-08-11 01:25:08', 2, 'app_max_per_page', '50', NULL, 'Define the number of item to display per page.', NULL, 2),
(16, NULL, NULL, '2020-08-26 07:57:01', '2026-08-11 01:25:08', 2, 'app_child_max_per_page', '25', NULL, 'Define the number of item to display per page in Child lists.', NULL, 2);

            -- API settings
INSERT INTO `config` (`id_config`, `id_creation`, `id_modification`, `date_creation`, `date_modification`, `category`, `config`, `value`, `system`, `description`, `type`, `id_group_creation`) VALUES
(3, NULL, NULL, '0000-00-00 00:00:00', '2026-08-11 01:25:08', 1, 'api_ips', '[\"192.168.0.10\"]', 0, 'Sets the ip address(es) allowed to access the API. Affect the CORS.', 'json_array', 2),
(4, NULL, NULL, '0000-00-00 00:00:00', '2026-08-11 01:25:08', 0, 'email_host', 'localhost', 0, '', 'string', 2),
(5, NULL, NULL, '0000-00-00 00:00:00', '2026-08-11 01:25:08', 0, 'email_username', '', 0, '', 'string', NULL),
(6, NULL, NULL, '0000-00-00 00:00:00', '2026-08-11 01:25:08', 0, 'email_password', '', 0, '', 'string', NULL),
(7, NULL, NULL, '0000-00-00 00:00:00', '2026-08-11 01:25:08', 0, 'email_port', '', 0, '', 'string', 2),
(8, NULL, NULL, '0000-00-00 00:00:00', '2026-08-11 01:25:08', 0, 'email_protocol', 'SMTPS', 0, 'One of \"STARTTLS, SMTPS, SMTP\"', 'string', NULL),
(9, NULL, NULL, '0000-00-00 00:00:00', '2026-08-11 01:25:08', 0, 'email_from', 'goatcheese@apigoat.com', 0, '', 'string', 2),
(10, NULL, NULL, '0000-00-00 00:00:00', '2026-08-11 01:25:08', 1, 'app_gui_url', 'http://localhost:3000/', 0, 'The url where resides you GUI app ending with slash (/).', 'string', 2);

-- Base API Rbac rules
-- SECURITY: the two ApiGoat/sendEmail rows are seeded scope=Public, rule=Deny.
-- They were historically Public+Allow (since 2020), which served the unauthenticated
-- email-send endpoint (EmailService) to anyone -- a user-enumeration + mail-abuse
-- vector. The only consumer (legacy ApiGoat/reset) was retired; the route is now
-- vestigial. Deny hard-blocks it (401) in every app_status. BOTH rows are kept (not
-- deduped): RbacMiddleware::findBestMatch matches a row only when it carries every
-- body key the request sends, so row 1 ({Email,TemplateName}) does NOT cover the
-- {...,debug} variant -- row 87 must stay to Deny that signature too.
INSERT INTO `api_rbac` (`id_api_rbac`, `model`, `action`, `body`, `query`, `method`, `scope`, `rule`, `date_creation`, `date_modification`, `id_group_creation`, `id_creation`, `id_modification`) VALUES
(1, 'ApiGoat', 'sendEmail', '{\"Email\":\"*\",\"TemplateName\":\"*\"}', '', 1, 1, 1, '2020-07-23 07:26:30', '2020-07-23 07:26:46', NULL, NULL, NULL),
(3, 'Authy', 'auth', '{\"u\":\"*\",\"pw\":\"*\"}', '', 1, 1, 0, '2020-07-24 09:51:13', '2020-07-25 09:18:54', NULL, NULL, NULL),
(88, 'Authy', 'refresh', '{\"refresh_token\":\"*\"}', '', 1, 1, 0, '2026-06-26 00:00:00', '2026-06-26 00:00:00', NULL, NULL, NULL),
(49, 'Authy', 'list', NULL, NULL, 0, 0, 0, '2020-08-02 05:03:05', '2020-08-02 05:03:05', NULL, NULL, NULL),
(87, 'ApiGoat', 'sendEmail', '{
    \"Email\": \"*\",
    \"TemplateName\": \"*\",
    \"debug\": \"*\"
}', '', 1, 1, 1, '2020-08-04 12:38:54', '2020-08-04 12:39:37', NULL, NULL, NULL),
(89, '_meta', 'list', '', '', 0, 0, 0, '2026-06-27 00:00:00', '2026-06-27 00:00:00', NULL, NULL, NULL),
(90, 'mcp', 'list', '', '', 1, 1, 0, '2026-06-28 00:00:00', '2026-06-28 00:00:00', NULL, NULL, NULL);

-- Default template
INSERT IGNORE INTO `template` (`id_template`, `name`, `status`, `subject`, `body`, `date_creation`, `date_modification`, `id_group_creation`, `id_creation`, `id_modification`) VALUES
(1, 'Forgotten password email', 0, 'Forgotten password request from APIgoat', '<p>Please <a href=\"[Utils-GuiUrl][Authy-ValidationKey]\">click here</a> to change your password or use this link:&nbsp;[Utils-Url][Authy-ValidationKey]</p>
', '2020-07-22 07:58:29', '2020-07-23 07:41:20', NULL, NULL, NULL),
(2, 'Confirm your email', 0, 'Confirm your APIgoat registration email', '<p>Hi!</p>

<p>Welcome to APIgoat! We hope ypou will enjoy creating new App easily.</p>

<p><a href=\"[Utils-GuiUrl]confirm?validationKey=[Authy-ValidationKey]\">Please click here to confirm your email.</a></p>

<p>If you have any problems, do not hesitate to contact us at beta@apigoat.com</p>

<p>The APIgoat team.</p>
', '2020-08-02 05:35:04', '2020-08-04 12:40:56', NULL, NULL, NULL);
;
