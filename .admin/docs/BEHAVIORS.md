# Behaviors & Parameters — Quick Reference

_Auto-generated on `gc build` from the GoatCheese behavior catalog._
_Ordered by type. A ✓ in **Used here** means the parameter is attached_
_to at least one table in this project (see the per-table list at the bottom)._

A table opts into generated admin CRUD by attaching the `GoatCheese`
behavior in its `*.hjson`, then tuning it via `parameters:`. This page is
the scannable menu of what you can set. For full syntax, emits, examples
and gotchas of each entry, see the auto-generated
`.claude/skills/goatcheese-behaviors/SKILL.md`.

> **gc first, custom code last.** Before writing ANY custom PHP/JS, check
> this catalog: use an existing behavior as-is, compose several, or extend
> one in the emitter. Create a NEW behavior only when the feature is
> **global** (plausibly reusable by other gc projects AND expressible
> generically via HJSON parameters). Inline code in wrappers/`Domains/` is
> reserved for truly project-specific logic. Full ladder + litmus test:
> "Decision policy" in `.claude/skills/goatcheese-behaviors/SKILL.md`.

## Behaviors

Feature emitters keyed off a parameter. Attach via
`behaviors: { GoatCheese: { parameters: { ... } } }`.

| Behavior | HJSON trigger | Summary | Used here |
| --- | --- | --- | --- |
| `ApiSupport` | `with_api` | Generates the REST API response dispatcher for a table when with_api is set. | ✓ |
| `AutoValue` | &mdash; | Legacy/dormant auto-value behavior — no functional emit in the current codebase. |  |
| `BulkUpdate` | `bulk_update` | Checkbox multi-select in the list plus a bulk-update dialog for editing many rows. |  |
| `ChildColumns` | `set_child_colunms` | Show derived columns from related FK tables in list and edit views. | ✓ |
| `ChildSelect` | `child_select` | DORMANT (cascade non-functional) — was cascading dependent dropdowns. The Region->City cascade is dead on both ends (no fields-shaped emit + no mod/act tra... |  |
| `CloneEntry` | `clone_entry` | Adds a "Duplicate" action that copies a record (and optionally its children). |  |
| `CryptedField` | `is_crypted_colunms` | Encrypt/decrypt specified columns at save/display time. |  |
| `ExportChild` | `export_child` | DORMANT — export_child is NOT converter-whitelisted, so it is dropped before reaching the emitter and emits nothing. Do NOT rely on it. (Was: Excel .xls ex... |  |
| `i18n` | `i18n_langs` | Bridges Propel i18n into generated forms — virtual per-language columns and getters. | ✓ |
| `ParentTable` | `set_parent_table` | Establishes parent-table context for a child entity (parent id/name/pk vars). | ✓ |
| `PopupSelect` | `multiple_fenetre` | Multi-select popup modal with a checkbox grid for many-to-many style selection. |  |
| `PoSupport` | &mdash; | Sync i18n translations to gettext .po/.mo files and back. |  |
| `Search` | `add_search_columns` | Advanced search form + Propel filter query chain (incl. foreign/child columns). | ✓ |
| `SearchTabs` | `search_tabs` | Tabbed quick-filter navigation over an existing search column. |  |
| `TopNav` | `set_top_nav` | Top navigation bar (logo + Home/Profile/Support/Dashboard/Menu icons). |  |
| `Wysiwyg` | `is_wysiwyg_colunms` | Attach a CKEditor WYSIWYG editor to designated columns. | ✓ |

## Parameters

Keys you set under `parameters:`; each has a dedicated
`Parameters/*.php` class.

| Parameter (HJSON key) | Summary | Used here |
| --- | --- | --- |
| `add_crossref_filter` | Filter a cross-reference (junction) child table with field/value/operator tuples. |  |
| `add_hooks` | Register lifecycle hooks (beforeSave, afterSave, beforeDelete, afterList, ...). | ✓ |
| `add_mass_action` | Bulk row actions (Delete, Archive, custom) on selected list rows. |  |
| `add_menu` | Add this table's admin form to the left-hand menu (with model/route/parent). |  |
| `add_prune_action` | Adds a "Prune" select in the list header that deletes every row older than a chosen age window (log-retention UX for audit/log tables). | ✓ |
| `add_row_link` | Per-row external link (icon button) in the list action cell, e.g. "preview on the public site". |  |
| `add_total` | Sum numeric columns and show row totals in the list footer. | ✓ |
| `format_date_columns` | Show date/datetime columns human-readable in list cells (browser locale) while keeping ISO values in the database and form inputs. |  |
| `format_phone_columns` | Normalize phone fields to digits-only in the database while displaying them with spaces in the UI (input + list cells). |  |
| `is_drive_backed` | Declare an entity with no MySQL persistence; CRUD targets a FileStorageInterface (Google Drive in v1). The emitter generates a list/edit/upload/delete UX bac... |  |
| `is_file_upload_table` | Enable the gcUpload file-upload UI with size/mime limits and image thumbnails. | ✓ |
| `set_autocomplete` | Turn an FK column into a text-autocomplete input with optional multi-field display, cascade filters, and JS defaults. |  |
| `set_child_link` | Declared on the parent table: turns a child list's Add/Delete into LINK/UNLINK of existing child rows — "Add" becomes a search picker that sets the child's... |  |
| `set_comment_columns` | Render a column's input inside the previous column's form row (the comment slot) instead of its own row. |  |
| `set_date_cascade_delete` | When a row is deleted, detect date-linked sibling rows (same project/user/date) in other tables and prompt to delete them too. |  |
| `set_menu` | Configure a drawer menu group: default folding, icon, color, sidebar position (order), and an optional dashboard deep-link. | ✓ |
| `set_menu_icon` | Per-table sidebar glyph (a RemixIcon class). | ✓ |
| `set_menu_priority` | Control this table's sort order within its menu group. | ✓ |
| `set_menu_subtitle` | Subtitle/description text shown under the menu item. |  |
| `set_parent_menu` | Place this table's menu under a parent menu category. | ✓ |
| `set_parent_table` | Designate the parent table when a child has multiple FKs. | ✓ |
| `set_quick_add` | Add a "+ Add New" inline-create control next to an FK select so the user can create the related record in a modal without leaving the host form. |  |
| `set_refresh_on_child_change` | Keep derived parent fields fresh in the open edit drawer when one of its child lists changes. |  |
| `set_search_columns` | Guarantee a plain BTREE index on each named column in the generated SQL — for the natural-label columns hit by list search, autocomplete and MCP find. |  |
| `set_summary_cards` | KPI/aggregate cards rendered atop a list — each card is a count/sum/avg/min/max over the table, optionally under a static equality/IN filter, computed serv... | ✓ |
| `sync_accounting` | Declares how one table maps to the accounting provider (role(s), field map, lines, taxes) and emits the post-save/post-delete service hooks that enqueue sync... |  |
| `with_accounting_sync` | Database-level: emits the accounting-sync (QuickBooks & friends) storage tables — acct_connection (headless: provider UNIQUE, realm_id, encrypted tokens, s... |  |
| `with_child_tables` | Declare child one-to-many / many-to-many relations rendered as nested lists/tabs. | ✓ |
| `with_country` | Auto-create a country reference table with locale columns. |  |
| `with_i18n` | Per-locale sibling columns: for each listed column injects a `<col>_fr` sibling mirroring its type/size (base column stays the en value), emits an ->i18n('<c... |  |
| `with_legacy_hash` | On the auth table: accept a pre-bcrypt (md5/sha1) imported password hash at login, once — on first successful legacy match the password is re-hashed to bcr... |  |
| `with_mcp` | Database-level: emits the OAuth 2.1 Authorization Server storage tables (oauth_client, oauth_auth_code, oauth_access_token, oauth_refresh_token) — headless... | ✓ |
| `with_mobile` | Marks a project as mobile-enabled and guards that the OAuth 2.1 AS storage exists (so the Expo app's authorization_code + S256 PKCE flow has somewhere to per... |  |
| `with_multi_tenant` | Flag a tenant-id column for multi-tenant isolation. |  |
| `with_pdf` | Standard record-PDF capability: template-driven rendering (header/footer from Template-entity rows, seeded pdf_<type> presets), saved copies in an auto-injec... |  |
| `with_refresh_tokens` | On the authy table: emits a headless `authy_refresh_token` table (family_id INDEX, token_hash UNIQUE, expires/family_expires, revoked ENUM, created_at/last_u... | ✓ |
| `with_register` | Public user registration form with email-confirmation flow. |  |
| `with_stripe` | Standard Stripe integration: declared per payable table, it promotes to database scope on the schema pass (run-once scan, the with_pdf pattern) to inject the... |  |
| `with_vector` | Bundle MariaDB (11.7+) vector similarity search onto a table: (re)creates the HNSW VECTOR index each build and emits a findNearest() Service method plus pack... |  |

## Inline parameters

Same `parameters:` syntax, but read directly by the emitter with no
dedicated `Parameters/*.php` file.

| Parameter (HJSON key) | Summary | Used here |
| --- | --- | --- |
| `add_child_bulk` | Adds a "Bulk Update" button and dialog to a child list so many child rows can be edited at once (child counterpart of bulk_update). |  |
| `add_child_search_columns` | Advanced-search/filter controls for CHILD lists — declared on the PARENT and keyed by child table name (same tuple shape as add_search_columns). |  |
| `add_field_groups` | Groups edit-form fields into ordered, optionally-titled .form-card sections (v2 mobile edit form). |  |
| `add_search_columns` | Declares the advanced-search/filter controls for the PARENT table's list view (which columns become searchable and how each is matched). | ✓ |
| `add_tab_columns` | Maps named tab labels to the trigger column that opens each tab, splitting a long edit form into tabbed panes. | ✓ |
| `add_title_link` | Wrap an FK field's <label> in a direct link to the foreign selected entry's edit page. |  |
| `admin_columns` | Legacy alias of is_root_columns; lists columns shown only to root/admin users — but readers use the new name, so the emitted parameter is dead. |  |
| `auth_passwd_column` | Recognized-but-inert alias for the password-hash column; the active param the emitters read is set_password_columns (default ["passwd_hash"]). |  |
| `auth_session_val` | Declares extra columns/relations to hydrate into the login session ($_SESSION sessVar) at authentication time. |  |
| `bulk_update` | Enables multi-row selection in the list plus a "Bulk update" dialog that edits the named columns across all checked rows (drives the BulkUpdate behavior). |  |
| `calculated_prefix` | Orphaned/legacy parameter: whitelisted by the converter but read by no code in the GoatCheese or runtime source. |  |
| `checkbox_all_child` | Adds a per-row selection checkbox column plus an "Un/Check all" control to a child list, enabling mass actions on child rows. | ✓ |
| `child_remove_add_link` | Suppress the "Add new" link in the listed child tables' UI. |  |
| `child_remove_edit_link` | Suppress the "Edit" link in the listed child tables' UI. |  |
| `child_select` | DORMANT / non-functional. Was meant to drive cascading dependent dropdowns (Region -> City); the cascade is dead on both ends and emits no working JS. |  |
| `child_table_read_only` | Marks one or more child lists as read-only — suppresses their Add, Delete and bulk controls and forces read-only child forms. |  |
| `clone_entry` | Adds a "Clone"/"Duplicate" action that deep-copies a row (and selected child tables / fields) into a new editable record (drives the CloneEntry behavior). |  |
| `comment_columns` | Legacy alias of set_comment_columns — renders a column's input inside the PREVIOUS column's form row (comment slot); still read as a fallback. |  |
| `common_filter` | Table-level filter (array of tuples) auto-applied to this table's query whenever it is loaded as the foreign/related table of an FK select or child list. |  |
| `copy_link` | Converter alias (=> add_child_insert_wysiwyg_tables); the "To editor" copy-to-WYSIWYG link on image-upload child tables is gated by image upload + WYSIWYG co... |  |
| `filter_select` | Legacy alias of set_selectbox_filters (per-FK-column dropdown filters); whitelisted but read only under the new name. |  |
| `i18n_langs` | Declares the locales for which translated (Propel i18n) columns get their own form fields/tabs in the edit view. | ✓ |
| `is_builder` | Database-level flag read while generating config/config.php; marks the database/project as a builder instance. |  |
| `is_file_upload` | Legacy/short alias of is_file_upload_table (marks a table as a file-upload table driving the gcUpload widget); whitelisted but read only under the full name. |  |
| `is_group_table` | Intended marker designating a table as the ACL group table (authy_group); recognized by the converter but currently inert in the emitter. | ✓ |
| `is_rights_column` | Lists the ACL bitmask columns (all/owner/group rights) so the generator hides them from lists and serializes the rights checkbox UI back into them on save. | ✓ |
| `is_root_columns` | Flags the boolean column(s) marking a user as root/superuser (ACL bypass), e.g. ["is_root"]. | ✓ |
| `is_wysiwyg_colunms` | Lists the textarea columns rendered as a rich-text WYSIWYG (CKEditor / gcEditor) editor in the edit form (drives the Wysiwyg behavior). | ✓ |
| `max_child` | Intended to cap the number of addable rows in a child list — currently DORMANT: the parameter read is commented out, so it has no effect. |  |
| `multiple_fenetre` | Configures popup-select ("multiple window") pickers: FK fields chosen from a pop-up checkbox list of a related table instead of a plain dropdown (drives Popu... |  |
| `order_select` | Per-FK-column ordering for the generated relation/select query: maps an FK column name to an [orderColumn, direction] pair. |  |
| `owner_visible` | Lists auth-table columns to expose on the generated self-service "My account" form so a user can edit their own profile fields. |  |
| `parent_table` | Legacy alias of set_parent_table; whitelisted but read only under the new name (dead key). | ✓ |
| `readonly_columns` | Legacy spelling of set_readonly_columns; whitelisted in the converter but NOT read by the current emitter (dead key). |  |
| `required_child` | Intended to require a minimum number of rows in a child list (with a "Please add at least N" prompt) — currently DORMANT: the read is commented out. |  |
| `search_tabs` | Whitelist of PARENT search fields kept VISIBLE in the search form; every search field NOT listed is hidden (collapsed behind quick-filter tabs). |  |
| `search_tabs_child` | Per-child-table whitelist of CHILD search fields kept visible in the child search form (child equivalent of search_tabs). |  |
| `set_child_colunms` | Per-column display/edit config for child/FK columns (note the "colunms" typo). | ✓ |
| `set_config` | Database-level config object supplying project_url / project_root used when emitting the generated config bootstrap. | ✓ |
| `set_debug_level` | Intended DB-level Propel debug/logging verbosity (0-3) emitted into the generated propel-init; currently hard-disabled in the generator. | ✓ |
| `set_field_groups` | Wraps each tab's fields in <section class="sw-group"> grid containers (with optional title), keyed by tab label. |  |
| `set_form_title` | Builds the edit-form breadcrumb/title from one or more record columns (or literal/object tokens). |  |
| `set_identity_actions` | Adds clickable action icons (tel/mailto/sms/url) to a record's identity card, mapping a column to a URI scheme. |  |
| `set_input_options` | Per-column input attributes/options (type, placeholder, pattern, ...), incl. type:"location" (address search + Leaflet map). | ✓ |
| `set_label_link` | Recognized label-link parameter (controls label/link rendering for a field). |  |
| `set_list_hide_columns` | Hides the named columns from the list (index) view; they still render in the edit form. | ✓ |
| `set_list_hide_columns_except` | Inverse of set_list_hide_columns — the list view shows ONLY the named columns and drops every other column. |  |
| `set_main_label` | Names the ordered columns composing a record's primary display label (list-row .name and edit-form identity card). |  |
| `set_order_child_list_columns` | Default sort order for a child (sub-table) list, keyed by child-table name. | ✓ |
| `set_order_list_columns` | Sets the default sort order of the main list view as an ordered array of [column, "ASC"\|"DESC"] pairs. | ✓ |
| `set_pills` | Renders selected columns as colored status "pill" badges in list rows / cards, keyed column -> pill type. |  |
| `set_readonly_columns` | Renders the named columns as read-only (locked) in the edit form while still displaying their values; also excluded from API writes. | ✓ |
| `set_selectbox_filters` | Filter a selectbox column's options by a parent FK. |  |
| `set_top_nav` | Intended to drive the TopNav behavior (custom top-navigation bar config); whitelisted by the converter but currently has no live reader. |  |
| `total_columns` | Legacy main-list column-totals parameter — DORMANT: superseded by add_total; its only read is commented out. |  |
| `total_columns_child` | Legacy child-list column-totals parameter — DORMANT: its read is commented out, so it produces no footer totals. |  |
| `unit_caption` | Legacy/whitelisted key for per-column unit captions; NOT read by the current emitter (which reads set_form_unit_caption instead). |  |
| `with_api` | Enable the REST API (JSON CRUD) for this table — drives the ApiSupport behavior. | ✓ |

## Used in this project

Parameters actually set on each table's `GoatCheese` behavior
(pure schema inspection — no emit guessing).

### `authy` (Authy)

- `is_auth_table`: `true`
- `is_root_columns`: `["is_root"]`
- `set_password_columns`: `["passwd_hash"]`
- `is_rights_column`: `["rights_all","rights_owner","rights_group"]`
- `add_tab_columns`: `{"Rights":"rights_all"}`
- `set_list_hide_columns`: `["rights","passwd_hash","rights_all","rights_owner","rights_group","google_sub","google_email","reset_token_hash","reset_token_expires"]`
- `i18n_langs`: `["en_US"]`
- `logo_url`: `""`
- `set_parent_menu`: `Settings`
- `set_input_options`: `{"location_address":{"type":"location","lat":"location_lat","lng":"location_lng","country":"ca"}}`
- `set_menu_priority`: `200`
- `add_search_columns`: `{"Name":[["username","%val","or"],["email","%val"]],"Primary group":[["id_authy_group","%val"]]}`
- `with_child_tables`: `["authy_group_x","authy_log"]`
- `with_refresh_tokens`: `1`

### `push_device` (PushDevice)

- `i18n_langs`: `["en_US"]`
- `logo_url`: `""`
- `set_parent_menu`: `Settings`
- `set_menu_priority`: `210`
- `set_child_colunms`: `{"id_authy":["username"]}`

### `country` (Country)

- `i18n_langs`: `["en_US"]`
- `logo_url`: `""`
- `set_parent_menu`: `Settings`

### `grid_run` (GridRun)

- `i18n_langs`: `["en_US"]`
- `logo_url`: `""`
- `set_menu_icon`: `ri-line-chart-line`
- `set_parent_menu`: `Trading`
- `with_child_tables`: `["bot_order","trade_cycle","bot_event","bot_command"]`
- `add_total`: `{"trade_cycle":[["realized_pnl","$"],["fees_total","$"]]}`
- `set_order_list_columns`: `[["date_creation","DESC"]]`
- `set_list_hide_columns`: `["run_uid","breakout_buffer_pct","max_open_orders","last_tick_at","sim_bal_base","sim_bal_quote","engine_state"]`
- `set_readonly_columns`: `["daily_loss_limit_quote","max_unrealized_loss_quote","max_position_quote","max_order_quote","breakout_policy","atr_stop_mult","atr_initial_mult","reentry_cooldown","engine_state"]`
- `add_tab_columns`: `{"Grid + budget":"p_low","Risk limits":"max_position_quote","Trend settings":"trend_tf","Telemetry":"last_tick_at"}`

### `bot_order` (BotOrder)

- `i18n_langs`: `["en_US"]`
- `logo_url`: `""`
- `set_parent_table`: `grid_run`
- `set_child_colunms`: `{"id_grid_run":["label"]}`
- `set_summary_cards`: `[{"label":"Open buys","agg":"count","filter":{"state":"BUY_OPEN"}},{"label":"Open sells","agg":"count","filter":{"state":"SELL_OPEN"}},{"label":"Vetoed","agg":"count","filter":{"state":"Vetoed"}}]`
- `set_order_child_list_columns`: `[["date_modification","DESC"]]`
- `add_search_columns`: `{"Client order id":[["client_order_id","%val"]],"Side":[["side","%val","multiple"]],"State":[["state","%val","multiple"]],"Simulated":[["simulated","val"]]}`
- `set_list_hide_columns`: `["legacy_buy_price","legacy_buy_fee"]`
- `set_readonly_columns`: `["client_order_id","exchange_order_id","filled_qty","fee_paid","simulated","legacy_buy_price","legacy_buy_fee"]`

### `trade_cycle` (TradeCycle)

- `i18n_langs`: `["en_US"]`
- `logo_url`: `""`
- `set_parent_table`: `grid_run`
- `set_child_colunms`: `{"id_grid_run":["label"]}`
- `set_summary_cards`: `[{"label":"PnL (sim)","agg":"sum","col":"realized_pnl","format":"money","filter":{"simulated":1}},{"label":"PnL (real)","agg":"sum","col":"realized_pnl","format":"money","filter":{"simulated":0}},{...`
- `add_search_columns`: `{"Simulated":[["simulated","val"]]}`
- `set_order_child_list_columns`: `[["date_creation","DESC"]]`

### `bot_event` (BotEvent)

- `i18n_langs`: `["en_US"]`
- `logo_url`: `""`
- `set_parent_table`: `grid_run`
- `set_child_colunms`: `{"id_grid_run":["label"]}`
- `set_order_child_list_columns`: `[["date_creation","DESC"]]`
- `add_search_columns`: `{"Level":[["level","%val","multiple"]],"Kind":[["kind","%val"]]}`

### `bot_command` (BotCommand)

- `i18n_langs`: `["en_US"]`
- `logo_url`: `""`
- `set_parent_table`: `grid_run`
- `set_child_colunms`: `{"id_grid_run":["label"]}`

### `sim_wallet` (SimWallet)

- `i18n_langs`: `["en_US"]`
- `logo_url`: `""`
- `set_parent_menu`: `Settings`
- `set_menu_priority`: `6`
- `set_readonly_columns`: `["asset","qty"]`

### `market_summary` (MarketSummary)

- `i18n_langs`: `["en_US"]`
- `logo_url`: `""`
- `set_parent_menu`: `Settings`
- `set_menu_priority`: `5`
- `set_order_list_columns`: `[["computed_at","DESC"]]`
- `set_list_hide_columns`: `["recent_candles"]`
- `set_readonly_columns`: `["symbol","tf","price","ema20","ema50","ema200","rsi14","atr14","atr_pct","trend","swing_high","swing_low","candles_used","recent_candles","computed_at","depth_imbalance_avg","adx14","atr_pct_rank"...`

### `market_regime` (MarketRegime)

- `i18n_langs`: `["en_US"]`
- `logo_url`: `""`
- `set_parent_menu`: `Settings`
- `set_menu_priority`: `6`
- `set_order_list_columns`: `[["date_creation","DESC"]]`
- `set_readonly_columns`: `["symbol","tf","price","trend","rsi14","atr_pct","adx14","atr_pct_rank","taker_buy_ratio","vol_zscore","funding_rate","depth_imbalance","depth_imbalance_avg"]`

### `bot_decision` (BotDecision)

- `i18n_langs`: `["en_US"]`
- `logo_url`: `""`
- `set_parent_table`: `grid_run`
- `set_child_colunms`: `{"id_grid_run":["label"]}`
- `set_order_child_list_columns`: `[["date_creation","DESC"]]`
- `set_readonly_columns`: `["source","p_low","p_high","n_levels","reason","price_at","realized_before","eval_status","eval_at","applied_at","cycles_delta","realized_delta","price_move_pct","verdict"]`

### `authy_group` (AuthyGroup)

- `i18n_langs`: `["en_US"]`
- `logo_url`: `""`
- `is_group_table`: `true`
- `set_parent_menu`: `Settings`
- `set_menu_priority`: `50`
- `is_rights_column`: `["rights_all", "rights_owner", "rights_group"]`
- `add_tab_columns`: `{"Rights":"rights_all"}`
- `set_list_hide_columns`: `["rights_all", "rights_owner", "rights_group"]`

### `authy_group_x` (AuthyGroupX)

- `i18n_langs`: `["en_US"]`
- `logo_url`: `""`
- `parent_table`: `authy`
- `checkbox_all_child`: `yes`

### `authy_log` (AuthyLog)

- `i18n_langs`: `["en_US"]`
- `logo_url`: `""`
- `set_parent_menu`: `Settings`
- `set_menu_priority`: `0`
- `set_order_list_columns`: `[["timestamp", "DESC"]]`

### `message` (Message)

- `i18n_langs`: `["en_US"]`
- `logo_url`: `""`
- `set_parent_menu`: `Settings`
- `add_search_columns`: `{"Label":[["label", "%val"]]}`
- `set_list_hide_columns`: `["text"]`
- `set_menu_priority`: `10`
- `set_readonly_columns`: `["label"]`

### `config` (Config)

- `i18n_langs`: `["en_US"]`
- `logo_url`: `""`
- `set_menu_priority`: `0`
- `set_parent_menu`: `Settings`

### `api_rbac` (ApiRbac)

- `set_parent_menu`: `Settings`
- `add_search_columns`: `{
                    "Scope":[["scope", "%val"]],
                    "Model":[["model", "%val"]],
                    "Action":[["action", "%val"]]}`
- `set_order_list_columns`: `[["date_creation", "DESC"]]`
- `set_list_hide_columns`: `["query"]`
- `with_child_tables`: `["api_log"]`
- `set_order_child_list_columns`: `{"api_log": [["time", "DESC"]]}`

### `api_log` (ApiLog)

- `set_parent_menu`: `Settings`
- `set_child_colunms`: `{"id_api_rbac":["model", "action", "query"], "id_authy":["username"]}`
- `add_prune_action`: `{"column":"time"}`
- `set_order_list_columns`: `[["time", "DESC"]]`

### `template` (Template)

- `set_parent_menu`: `Settings`
- `add_search_columns`: `{"Name": [["name", "%val"]]}`
- `set_order_list_columns`: `[["date_creation", "DESC"]]`
- `is_wysiwyg_colunms`: `["body", "footer"]`
- `set_list_hide_columns`: `["color_1", "color_2", "color_3", "body", "footer"]`
- `with_child_tables`: `["template_file"]`
- `add_child_insert_wysiwyg_tables`: `["template_file"]`

### `template_file` (TemplateFile)

- `is_file_upload_table`: `{
            "thumbnail": "Yes",
            "filters" : {
                    "max_file_size" : "10mb"
            },
            "image_support":"yes"}`
