# Edit Guide

A GoatCheese-generated project is a blend of **three layers**. Knowing
which layer a file belongs to tells you whether your edit survives:

1. **Generated** &mdash; emitted from your `*.hjson` schema on every `gc build`
   (`Built/` dirs, routes, menus, permissions). Overwritten **every build**.
2. **Template** &mdash; copied from the shared `apigoat/template` when the
   project was created (`gc create`) and kept in step with it by drift-sync.
   Overwritten on `gc upgrade` / `gc build --sync-template`, **not** on a
   plain `gc build`. See [The Template layer](#the-template-layer).
3. **Yours** &mdash; hand-written extensions (`*Wrapper.php`, `Domains/`,
   `schema/*.hjson`, your tests). Never overwritten.

This file maps which is which so you don't waste edits on files that get
regenerated or re-synced out from under you, and shows the canonical
patterns for adding behavior the right way. The `gc` commands that drive
all of this are listed under [gc commands](#gc-commands).

**If you are an AI assistant or new contributor: read this file first,
then `SCHEMA-OVERVIEW.md` and `API.md`.**

## Before you write custom code — gc first

Most features do **not** need custom code here: they are a GoatCheese
behavior/parameter set in `*.hjson`. The catalog index lives in
`docs/BEHAVIORS.md` (short form) and
`.claude/skills/goatcheese-behaviors/SKILL.md` (full reference + decision
policy). The ladder, in order — stop at the first rung that fits:

1. existing behavior/parameter as-is;
2. compose several;
3. extend a behavior in the emitter (generic gap);
4. **new behavior** &mdash; only when the feature (or its extractable part) is
   **global**: plausibly reused by other gc projects and expressible
   generically via HJSON parameters;
5. inline code in the wrappers/`Domains/` below &mdash; only for truly
   project-specific logic, kept thin.

Re-implementing an existing behavior inline is the #1 source of drift in
gc projects.

## Safe to edit

- `src/App/Domains/`                  &mdash; your business logic
- `src/App/Services/*ServiceWrapper.php` &mdash; per-table service extensions (custom actions, response shaping)
- `src/App/Interfaces/*FormWrapper.php`  &mdash; form hooks (beforeSave, afterFormObj, validators, &hellip;)
- `src/App/Models/` (non-`Built/`)     &mdash; model overrides
- `tests/Custom/`                      &mdash; hand-written tests for your code (**required &mdash; see Testing**)
- `../schema/*.hjson`                  &mdash; schema definitions, in the project-root `schema/` folder (regenerate via `gc build`)
- `public/css/_variables.scss`, `public/css/project.scss`, `public/css/print.scss` &mdash; the per-project style hooks (never drift-synced)
- `tmp/custom*.sql`                    &mdash; runs after build; use for static seed data

> **Heads-up on CSS/JS:** only the three SCSS files above are yours. Other
> files under `public/css/` and `public/js/` (including `public/js/app/`)
> come from the **template** and get overwritten on sync &mdash; see
> [The Template layer](#the-template-layer) before editing them.

## Do NOT edit (regenerated on every build)

- `.build/classes/`                   &mdash; Propel ORM classes
- `config/Built/`                     &mdash; generated config (routes, menus, permissions, &hellip;)
- `src/App/Models/Built/`             &mdash; Propel base classes
- `src/App/Services/Built/`           &mdash; generated service bases
- `src/App/Interfaces/Built/`         &mdash; generated form bases
- `tests/Builder/Generated/`          &mdash; auto-generated shape tests (catch emitter regressions)
- `vendor/`                           &mdash; Composer-managed
- `public/css/min/`, `public/js/min/` &mdash; minified asset cache

If you need to change behavior in a `Built/` file, edit the corresponding
`*Wrapper.php` instead &mdash; the wrapper extends the built class and is
yours to own.

## The Template layer

Some files in this project did **not** come from your schema and are **not**
regenerated on a plain `gc build`. They were copied from the shared
`apigoat/template` at `gc create` time and are kept current by **drift-sync**:
when the template improves (a security fix in a JS widget, a new PWA asset, a
dependency bump), `gc upgrade` and `gc build --sync-template` re-copy the
template version over your project copy.

**A plain `gc build` only *reports* template drift; it never overwrites.**
Overwrites happen only when you opt in:

- `gc build --sync-template` &mdash; apply every template change to this project
- `gc upgrade` / `gc upgrade --all` &mdash; update deps **and** sync template drift

### Template-managed files (overwritten on sync)

Editing these locally works until the next sync, then your change is silently
replaced by the template version. If the change belongs in every project, make
it in the **template** repo; if it's project-specific, find a non-template hook.

- `public/js/*.js`, `public/js/app/*.js` &mdash; framework client (the `gcUpload`, list, form widgets)
- `public/css/*.scss`, `public/css/*.css` &mdash; **except** the three per-project files listed under "Safe to edit"
- `public/*.php`, `sw.js`, `manifest.webmanifest`, `public/img/ios/*.png`, `public/img/android/*.png` &mdash; entrypoints + PWA shell
- `public/view/notifications-settings.php` &mdash; tracked by exact path
- `config/assets.php` &mdash; the asset bundle manifest. **Union-merged**, not blindly overwritten: your extra `->add()` lines survive a sync; template-side *removals* (retired assets) are dropped.
- `composer.json` &mdash; **union-merged**: the template can only *add* require/repositories you lack; your deps, pins, autoload, and scripts all survive.
- `tests/Builder/**` &mdash; framework + connectivity tests (placeholder-substituted)

### Template-derived but report-only (drift shown, never overwritten)

These started from the template but you extend them per-project, so sync
surfaces divergence as `[report-only]` and **never** offers to overwrite:

- `src/App/Domains/Template/Variables.php` &mdash; this project's table-variable defs
- `src/App/Interfaces/*FormWrapper.php` skeletons &mdash; your form hooks live here

### Seeded once, never synced

A few features are seeded by `gc create` and deliberately left per-project
(so `gc upgrade` can't clobber them): `public/view/account.php`,
`src/App/Services/AccountServiceWrapper.php`, the `Account` routes, and the
`authy.theme` schema column. If an older project is missing these, `gc build`
prints exactly which pieces to copy from the template.

## gc commands

This project has a `gc` symlink at its root; run commands from `.admin/` as
`../gc <cmd>`, or from anywhere as `gc <cmd> <project>`. The lifecycle-relevant
ones (aliases in parentheses):

| Command | What it does | Touches DB? |
|---------|--------------|-------------|
| `gc build` (`b`) | Regenerate from schema, apply SQL, reset admin user; reports template drift | Yes |
| `gc build --fileonly` | Codegen only &mdash; regenerate `Built/`, no DB side effects | No |
| `gc build --sync-template` | Regenerate **and** overwrite template-managed files with the template version | Yes |
| `gc build --skip-dump` / `--dumpfirst` | Skip the pre-build backup / force one first | Yes |
| `gc upgrade` (`up`) | Update `apigoat/runtime` + Propel, then sync template drift into this project | Yes |
| `gc doctor` (`doc`) | Pre-flight validation (`.env`, required files, HJSON parse). CI gate &mdash; non-zero exit on problems | No |
| `gc verify` (`v`) | Headless Playwright smoke test (login + menus); `-u`/`-p` or `GC_VERIFY_PASSWORD` | No |
| `gc dump` (`u`) | Timestamped DB backup into `.admin/tmp/` | Read-only |
| `gc password <user>` (`pw`) | Set/rotate an admin user's bcrypt password (user arg first) | Yes |
| `gc resetdata` (`rd`) | **Destructive** &mdash; wipe all data, reseed the baseline (skips `gc:dev-only` seeds); `--force`, `-p <pw>` | Yes |
| `gc list` (`l`) | List every project: file integrity, DB name, pinned runtime version | No |

**The build-verify loop for a schema or wrapper change:** edit &rarr;
`gc build` &rarr; check `tmp/logs/build-raw.log` &rarr; `vendor/bin/phpunit`.
`gc doctor` and `gc verify` propagate their exit code, so both work as CI gates.

## How to add a new table

1. Add an entry to a `*.hjson` file in the project-root `schema/` folder (legacy projects: `.admin/` until the next `gc build` migrates them).
2. Run `gc build` from `.admin/` (or `gc build <name>` from anywhere).
3. New `*FormWrapper.php` and `*ServiceWrapper.php` files appear under `src/App/` &mdash; extend those.
4. Routes, menus, permissions auto-emit to `config/Built/*.php`.
5. **Add tests for any custom behavior under `tests/Custom/`** (see Testing).
6. Re-read this directory's `SCHEMA-OVERVIEW.md` and `ENTITIES.md` to confirm.

## How to add custom logic

### Custom action on a table

Register the action in the constructor of the matching `*ServiceWrapper`,
then implement the handler. The same `customActions` map is consulted by
both the HTML dispatcher and the JSON dispatcher:

```php
namespace App;

class ClientServiceWrapper extends ClientService {
    public function __construct($request, $response, $args) {
        parent::__construct($request, $response, $args);
        $this->customActions['exportCsv'] = 'exportClientsCsv';
    }

    public function exportClientsCsv($Api) {
        // ... build response
        return ['status' => 'success', 'data' => [/* ... */]];
    }
}
```

After this, `GET /Client/exportCsv` and `GET /api/v1/Client/exportCsv`
both dispatch to `exportClientsCsv()`. See `API.md` for the full
request/response envelope.

### Form hooks

Hooks live on `*FormWrapper.php`. The most common ones:

- `beforeSave($obj, $data)`  &mdash; mutate `$obj` or veto save by throwing
- `afterFormObj($obj, $data)` &mdash; tweak the object after the form has populated it
- `afterSave($obj, $data)`   &mdash; trigger side effects (emails, audit, &hellip;)

Don't write to `id_creation`, `id_group_creation`, `date_creation`, or
`date_modification` directly &mdash; the framework manages them.

## Testing (required)

Every change that adds or alters behavior must ship with tests. The
auto-generated tests under `tests/Builder/Generated/` only verify the
emitter's shape contract; they do **not** cover your custom logic.

### Where to put tests

| Path                        | What lives here                                              | Editable? |
|-----------------------------|--------------------------------------------------------------|-----------|
| `tests/Builder/Generated/`  | Auto-emitted shape tests (one pair per table)                | No &mdash; regenerated |
| `tests/Builder/Auth/`       | Framework-provided base + connectivity tests (Propel/Authy)  | No &mdash; template-managed |
| `tests/Builder/Smoke/`      | Framework smoke tests                                        | No &mdash; template-managed |
| `tests/Custom/`             | **Your tests for hooks, custom actions, domain logic**       | **Yes &mdash; add here** |

### When to add a test

- New custom action on a `*ServiceWrapper` &rarr; test the handler's response envelope and any side effects.
- New / changed form hook on a `*FormWrapper` &rarr; test that `beforeSave`/`afterSave` does what you intend (or vetoes correctly).
- New domain class under `src/App/Domains/` &rarr; unit-test it.
- Bug fix &rarr; regression test that fails before the fix and passes after.

If you need a real database, extend `Tests\Builder\Auth\AuthyTestCase`
(it boots Propel + the runtime constants and rolls back every test in a
transaction). For pure-logic tests, use `PHPUnit\Framework\TestCase`
directly &mdash; `tests/bootstrap.php` just loads Composer's autoloader.

### Running tests

```
vendor/bin/phpunit --testsuite custom    # your tests
vendor/bin/phpunit --testsuite builder   # generated shape tests + framework tests
vendor/bin/phpunit                       # all suites
```

The `custom` suite must be green before any commit. The `builder` suite
must be green before any `gc build` is considered finished.

## Common pitfalls

- **Don't edit `Built/` files.** They are rewritten on the next build and your change vanishes silently. Edit the matching `*Wrapper.php` instead.
- **Don't edit `schema.xml` by hand.** It is regenerated from the `*.hjson` files. Edit the HJSON; old XML schemas get rotated to `*.schema.xml.1`, `.2`, &hellip;
- **Don't bypass the wrapper.** If `ClientService` doesn't have the method you need, add it to `ClientServiceWrapper`, not to `ClientService`.
- **Don't write audit columns directly.** `id_creation`, `id_group_creation`, `date_creation`, `date_modification` are managed automatically &mdash; setting them by hand breaks RBAC row-filtering.
- **Don't skip the tests.** Auto-generated shape tests catch emitter regressions, not your bugs. New behavior &rarr; new test under `tests/Custom/`.
- **Don't edit template-managed CSS/JS in place.** Most of `public/js/` and `public/css/` is the template; a `gc upgrade` / `gc build --sync-template` re-copies the template version over your edit. See [The Template layer](#the-template-layer).

## Other docs

- `SCHEMA-OVERVIEW.md` &mdash; every table, every column, types, foreign keys
- `ENTITIES.md`        &mdash; menu placement, child tables, behavior summary
- `INDEX.md`           &mdash; what each file in this directory contains
- `API.md`            &mdash; HTTP API: auth, RBAC, routes, request/response envelopes
