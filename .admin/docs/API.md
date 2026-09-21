# API Reference &mdash; apigtbot

_Auto-generated on every `gc build`. Do not edit by hand._

This project exposes an HTTP API automatically derived from the `*.hjson`
schema. Every table is reachable two ways:

- **Browser / XHR mode** &mdash; cookie session, returns HTML fragments. URL: `/<Table>/...`
- **JSON API mode** &mdash; **JWT bearer token** (or cookie session), returns JSON. URL: `/api/v1/<Table>/...`

A request is treated as API mode when the URL path matches the regex
`^/?api/v[0-9]+/` (see `RouteParser::decodePath()` &mdash; the matcher is literally
`preg_match('#^/?api/v[0-9]+/#', $path)`). The match is anchored at the start and
requires `api/v` followed by one or more digits and a trailing slash; the matched
version segment is stripped before dispatch. A path like `/something/api/foo` does
**not** flag as API. JWT authentication is enforced only on routes flagged this way.

## Table of contents

- [Quick start](#quick-start)
- [Authentication modes](#authentication-modes)
- [JWT (bearer token)](#jwt-bearer-token)
- [Session (cookie)](#session-cookie)
- [Session model](#session-model)
- [RBAC](#rbac)
- [`app_status` (dev vs prod)](#app_status)
- [Routes & dispatch](#routes--dispatch)
- [Request headers](#request-headers)
- [Request envelope](#request-envelope)
- [Response envelope](#response-envelope)
- [Custom actions (extension point)](#custom-actions)
- [Constants](#constants)
- [Resources in this project](#resources)
- [Examples](#examples)
- [Source-of-truth files](#source-of-truth-files)

## Quick start

Get a JWT and call the API:

```http
POST /api/v1/Authy/auth
Content-Type: application/json

{ "u": "you", "p": "secret" }
```

Response:

```json
{ "status": "success", "token": "eyJhbGciOiJIUzI1NiIs...", "expires": 1735776000 }
```

Send the token on every subsequent API call:

```http
GET /api/v1/Client
Authorization: Bearer eyJhbGciOiJIUzI1NiIs...
```

For a browser session instead of JWT, `POST /Authy/auth` sets a session cookie
and the same routes (without `/api/v1/`) return HTML / XHR fragments.

## Authentication modes

Two parallel authentication paths share one identity store (the `authy` table);
each request carries credentials via exactly one mechanism:

| Mode | URL prefix | Credential | Storage | Used by |
| --- | --- | --- | --- | --- |
| **JWT** | `/api/v1/` | `Authorization: Bearer <token>` | Stateless &mdash; claims in token | Programmatic clients, mobile, SPA, integrations |
| **Session** | `/` | `Cookie: <session-id>` | `$_SESSION[_AUTH_VAR]` (server) | Browser UI, server-rendered XHR |

Routing rule (in `vendor/apigoat/runtime/src/Middlewares/RouteParser.php::decodePath()`):
a path matching the regex `^/?api/v[0-9]+/` flags the request as `is_api = true`, and
the matched version segment is stripped before dispatch
(via `preg_replace('#^/?api/v[0-9]+/#', '', $path, 1)`). The match is a strict prefix
&mdash; anchored at the start, requires `api/v` followed by one or more digits and a
trailing slash &mdash; so a path like `/something/api/foo` does **not** flag as API.

Note: `RbacMiddleware.php:51` uses a different, looser substring check
(`strstr($path, '/api/v')`) to gate RBAC processing. The two middlewares do not share
the same matching rule, so a contrived path containing `/api/v` but not anchored at the
start can pass RBAC's check without setting `is_api`.

The JWT middleware (`config/container.php` `JwtAuthentication::class`
factory) runs based on a multi-clause predicate (`config/container.php:145-171`),
evaluated in order:

1. **Skip** if `is_api` is false (browser-mode route &mdash; cookie session handles auth).
2. **Skip** if `$_SESSION[_AUTH_VAR]->isConnected === 'YES'` (already session-authenticated; nothing to hydrate).
3. **Run** if a Bearer token is present in `Authorization` or `X-Authorization` &mdash; *regardless of `rbac_public`*. This clause is first deliberately, so Bearer-bearing requests always hydrate the session even on public routes.
4. **Skip** if `$request->getAttribute('rbac_public') === 'passed'` (public route + no Bearer &mdash; see [Public routes & session hydration](#public-routes-session) below).
5. Otherwise **run** (private route, no Bearer &rarr; the JWT decoder will reject the missing token and `JwtAuthentication` returns `401`).

Routes that **never require authentication** (in either mode) are listed in
`config/Built/privileges.map.defaults.php` under the `exclude` key. Typical excludes:
`Authy/auth`, `Authy/google`, `Authy/login`, `Authy/register`, `Authy/forgotten`, `Authy/confirm`,
`Authy/reset`, `Authy/logout`, `GuiManager`.

When auth is required and absent, the middleware redirects browser requests to
`/Authy/login`, and returns `401` JSON for API requests.

### Google sign-in (GIS)

`Authy/google` verifies a Google Identity Services ID token (`GOOGLE_CLIENT_ID` in `.env`;
the login button is only emitted when it is set). It signs in the user whose `google_sub`
matches, else auto-links an active user whose verified email matches. It never creates users
unless **self-registration** is switched on in `.env` (OFF by default):

| `.env` key | Meaning |
|---|---|
| `GOOGLE_AUTO_REGISTER="1"` | create an `authy` row on first Google sign-in (`1`/`true`/`yes`/`on`) |
| `GOOGLE_AUTO_REGISTER_GROUP="Learner"` | `authy_group` **name** for new users; unset or unknown &rarr; the seeded default group (`default_group=Yes`) |
| `GOOGLE_AUTO_REGISTER_DOMAINS="a.com,b.org"` | exact email-domain allowlist; empty &rarr; any verified Google account |

A self-registered user gets `is_root=No`, `is_system=No`, `deactivate=No`, the column-default
`id_tenant`, and a random bcrypt `passwd_hash` (password login only after a normal reset). A
deactivated user with the same email is not revived (the unique-email validator refuses the
create). Policy parsing lives in the runtime `ApiGoat\Auth\GoogleRegistration`.

## JWT (bearer token)

Implemented via `jimtools/jwt-auth` (Slim middleware) + `firebase/php-jwt`
(encode/decode), wired in `config/container.php` under `JwtAuthentication::class`.

### Issuing a token

Three endpoints mint JWTs:

| Endpoint | Method | Purpose |
| --- | --- | --- |
| `/api/v1/Authy/auth` | `POST` | Username/password login. Body fields: `u` (username), `p` or `pw` (password). Returns `{status, token, expires}` &mdash; plus `refresh_token` when the optional `authy_refresh_token` table exists (see [Refreshing tokens](#refreshing-tokens)). |
| `/api/v1/Authy/renew` | `POST` | Issue a fresh token using an existing valid session/JWT. Useful before expiry. Shares the route binding with `auth` (`Authy/{a:auth\|renew}`), so it is **POST** even though it takes no body. |
| `/api/v1/oauth/<provider>/callback` | `GET` | OAuth callback for Google/Facebook/etc. (via Opauth). On success, `303 See Other` redirect to `oauth.frontend_callback_url` with the result encoded as a query string and `Cache-Control: no-store` (non-cacheable; carries result/error data in the URL). **Shape varies by branch** &mdash; see below. |

`Authy/auth` is in the `privileges.map.exclude` list, so it is reachable without
an existing session or token. `Authy/renew` requires either a connected session
or a still-valid JWT.

#### OAuth callback redirect &mdash; query-string shape

The redirect URL is `frontend_callback_url . "?" . $params` where `$params =
http_build_query($body)` over the full `ApiResponse` envelope
(`OauthService.php:122-123`, `ApiResponse.php::getQueryParam()`). The body returned
from `process()`/`authenticate()` has no `data` key, so `ApiResponse::setBody()`
takes the flat-shape branch (`ApiResponse.php:106-116`) and lifts only the reserved
keys (`status`, `data`, `messages`, `errors`) to the envelope &mdash; **everything else
is nested under `data[]`**, and `http_build_query` then encodes it with bracket
notation (`data%5Bjwt%5D%5Btoken%5D=...`).

Resulting query string by branch (`OauthService.php::authenticate()`), brackets shown
un-encoded for readability:

| Branch | Source line | Query string |
| --- | --- | --- |
| Existing user, provider already linked | `:171` | `?status=success&data[jwt][token]=<jwt>&data[jwt][expires]=<unix-ts>&data[jwt][status]=success&data[id]=<authyId>&data[action]=login` |
| Auto-register, existing email + matching provider | `:188` | `?status=success&data[jwt][token]=<jwt>&data[jwt][expires]=<unix-ts>&data[jwt][status]=success&data[id]=<authyId>&data[action]=login` |
| Auto-register, new user | `:214` | `?status=success&data[jwt][token]=<jwt>&data[jwt][expires]=<unix-ts>&data[jwt][status]=success&data[id]=<authyId>&data[action]=register` |
| Existing email, mismatched provider | `:195` | `?status=failure&data[error]=This+email+is+already+in+use...` |
| Auto-register failure (validation) | `:223` | `?status=failure&data[error]=Cannot+add...` |
| `auto_register=false` and no linked account | `:229` | `?status=failure&data[error]=You+do+not+have+a+linked...` |

Notes on the success shape:

- `data[jwt]` is itself a three-key sub-array &mdash; `token`, `expires`, `status` &mdash; because `AuthyService::getToken()` returns the full `{token, expires, status}` envelope rather than just the token string. The bearer credential is `data[jwt][token]`, **not** `data[jwt]`.
- Two `status` values appear in the URL: top-level `status=success` (overall OAuth flow) and nested `data[jwt][status]=success` (success of the token-mint step). They are distinct &mdash; branch on the top-level one.
- Empty default envelope keys (`messages=[]`, `errors=[]`) are skipped by `http_build_query` and do not appear in the URL.

Failure-path shape: top-level `status=failure` plus `data[error]=<string>`. The
`unset($result['errors'])` on `OauthService.php:112` keeps the indexed-array form
out of the URL; `error` is always a single string.

Frontend implications:

- The query string uses PHP-style bracket nesting. `URLSearchParams` will parse `data[jwt][token]` as the literal flat key `"data[jwt][token]"` &mdash; either treat each bracket-key as a flat string, or reconstitute the nested object yourself (e.g. with the `qs` library's parser).
- Check top-level `status` first; success and failure are distinguishable on that key alone.
- On success: read `data[jwt][token]` for the bearer; `data[action]` is always either `"login"` or `"register"`.
- On failure: read `data[error]` (a string). The legacy `errors[0]` indexed-array form has been dropped from the redirect.
- Forward-compatible: any extra keys `authenticate()` returns in future revisions will land under `data[]` too, with the same bracket encoding.

### Token claims

Tokens are signed with `HS256` (`Firebase\JWT\JWT::encode($payload, $secret, "HS256")`)
in `src/App/Services/Built/AuthyService.php::getToken()`. Effective default lifetime is
`"now +15 minutes"`, set in `config/settings.defaults.php` under `jwt_middleware.expire`
and overridable in `config/settings.php`. (The `+2 hours` default on the `getToken()`
parameter signature is a fallback that is never used &mdash; every caller passes
`$jwt_settings['expire']` from config.)

| Claim | Type | Purpose |
| --- | --- | --- |
| `iat` | unix ts | Issued at |
| `exp` | unix ts | Expires at |
| `jti` | string | JWT ID &mdash; base62-encoded random_bytes(16); use to revoke a single token if you build a denylist |
| `sub` | string | Subject &mdash; the Authy primary key (`$pmpoData->getIdAuthy()`) cast to string |
| `scope` | string[] | Reserved for finer-grained scopes; emitted as `[]` |
| `username` | string | Authy.username |
| `authyId` | int | Authy.id_authy &mdash; preferred lookup key on verify |
| `group` | int | Authy.id_authy_group |
| `isRoot` | string | Authy.is_root (`Yes` / `No`). **Type changes across the round-trip:** the JWT carries the raw ENUM string, but `AuthyService::setSession()` (line 888) converts it to a real bool (`($pmpoData->getIsRoot() == 'Yes') ? true : false`) when hydrating the server-side session. Clients decoding the token see `"Yes"` / `"No"`; the server's `AuthySession->isRoot` is `true` / `false`. |

### Token transmission

Either header is accepted (`vendor/apigoat/runtime` ConditionalMiddleware in
`config/container.php`):

- `Authorization: Bearer <token>` &mdash; standard
- `X-Authorization: Bearer <token>` &mdash; fallback for environments that strip `Authorization`

### Verification & session hydration

On every protected request the middleware does:

1. Reads the bearer header and decodes via `FirebaseDecoder(new Secret($secret, $algorithm))`. Invalid / expired tokens cause a `401`.
2. Deep-normalizes claims (Firebase returns nested `stdClass`; the runtime casts via `json_decode(json_encode(...), true)`).
3. Stores decoded claims in the DI container under `'token'` (also passed to handlers as `$args['decoded']`).
4. Looks up the user, in priority order:
   - `decoded['authyId']` &rarr; `\App\AuthyQuery::findPk($id)`
   - `decoded['username']` &rarr; `\App\AuthyQuery::filterByUsername($u)->findOne()`
   - `decoded['user'] === 'web'` &rarr; service-token shortcut, looks up the user named `web`
5. Calls `\App\AuthyServiceWrapper::setSession($Authy)` to hydrate `AuthySession` (groups, rights, identity).
6. **Fail closed.** If `setSession()` throws, or returns without setting `isConnected=YES` (expired user, deactivate / lockout branches, corrupted `rights_*` JSON, etc.), the request continues with `isConnected=NO` and `AuthyMiddleware` returns `401`. There is no minimal-session fallback &mdash; a JWT cannot admit any user that `setSession()` would have refused. Exceptions are still logged: grep server error log for `[JwtAuthentication before] setSession failed:`.

When `setSession()` succeeds, the rest of the stack treats the JWT-authenticated
request identically to a session-authenticated one &mdash; the same RBAC checks, the
same row filtering, the same `customActions` dispatch.

<a id="public-routes-session"></a>
### Public routes & session hydration

Some routes are marked **public** by `ApiRbac`, which sets the request attribute
`rbac_public = "passed"`. Public is *not* the same as `privileges.map.exclude`:
`exclude` is a static config list (auth/login/register/&hellip;); `rbac_public` is a
runtime attribute set by the `ApiRbac` middleware based on the per-route ACL data.

On a public route, two things happen in this order:

- `AuthyMiddleware` lets the request through without requiring `isConnected=YES`.
- The JWT middleware predicate (clauses 3 + 4 above) **only decodes the token if a Bearer header is present**. With no Bearer, `setSession()` is never called &mdash; `$_SESSION[_AUTH_VAR]->isConnected` stays `NO`.

**The trap:** any handler that *explicitly* checks the session past `AuthyMiddleware`
will still reject the call. The reference example is
`SchedulingServiceWrapper::assertSchedulingAuthyAcl()` &mdash; even on a public route,
if the caller didn't send a Bearer token, that handler raises "Authentication required"
because `$_SESSION[_AUTH_VAR]->isConnected !== 'YES'`. The inline comment in
`config/container.php:155-157` documents exactly this asymmetry &mdash; the Bearer-first
clause was added so Bearer requests always hydrate the session, even when the route
is public.

Two ways to avoid the trap:

1. **Call the public route with a Bearer token** &mdash; the predicate runs JWT first, hydrates `AuthySession`, and any session check downstream sees `isConnected=YES`.
2. **Write the handler to tolerate `isConnected=NO`** &mdash; pull the user identity from request body / args, or take the unauthenticated path deliberately. Don't reach for `$_SESSION[_AUTH_VAR]->getIdAuthy()` and expect a value.

### Configuration (`config/settings.php` &rarr; `jwt_middleware`)

| Key | Type | Purpose |
| --- | --- | --- |
| `secret` | string | HS256 signing key. **Keep out of source control.** |
| `algorithm` | string | Typically `HS256` |
| `secure` | bool | `true` requires HTTPS for token transmission |
| `path` | string\|array | Path prefixes the middleware protects. `/` is always added defensively so a typo cannot disable JWT entirely. |
| `ignore` | string[] | Path prefixes to skip (in addition to `privileges.map.exclude`) |
| `expire` | string | `DateTime`-parseable expiry (default `"now +15 minutes"`) |
| `refresh_expire` | string | Per-token TTL for opaque refresh tokens (default `"now +30 days"`). Sliding &mdash; each successful rotation issues a new token valid for this duration. Accepts `DateTime`-parseable strings or integer seconds. Has no effect unless `with_refresh_tokens` is enabled. |
| `refresh_family_expire` | string | Absolute cap for a refresh-token family (default `"now +90 days"`). Set once at login and **not** extended by rotation &mdash; after this point the client must re-authenticate with credentials. Same format as `refresh_expire`. |

### Refreshing tokens

There is no automatic refresh. Two mechanisms exist, depending on what the
project has enabled.

#### 1. `Authy/renew` &mdash; re-mint while still authenticated (always available)

Call `POST /api/v1/Authy/renew` while the current token is still valid (or
before the user-perceived idle window) to mint a fresh JWT. The request needs
no body &mdash; the user is read from the session/JWT &mdash; but must carry a
still-valid `Authorization: Bearer <jwt>` (or a connected cookie session);
`renew` is **not** in `privileges.map.exclude`, so an expired token cannot
renew itself. Returns the same `{status, token, expires}` envelope as `auth`.
After expiry, fall back to `POST /api/v1/Authy/auth` with credentials.

#### 2. `POST /api/v1/Authy/refresh` &mdash; opaque refresh token (opt-in; survives access-token expiry)

Enabled by the **`with_refresh_tokens: true`** GoatCheese parameter on the auth
table (which emits the `authy_refresh_token` backing table). When enabled, a
successful `Authy/auth` login returns a `refresh_token` alongside the JWT:

```json
{ "status": "success", "token": "<jwt>", "expires": <unix-ts>, "refresh_token": "<opaque>" }
```

`Authy/auth` and `Authy/refresh` are both in `privileges.map.exclude` and
`Authy/refresh` is also in `jwt_middleware.ignore`, so they are reachable
without a valid `Authorization` header.

**Redeeming a refresh token** — exchange it for a new access JWT:

```
POST /api/v1/Authy/refresh
Content-Type: application/json

{ "refresh_token": "<opaque>" }
```

Response:

```json
{ "status": "success", "token": "<new-jwt>", "expires": <unix-ts>, "refresh_token": "<new-opaque>" }
```

**Token rotation.** Refresh tokens are **single-use** — each successful redeem
revokes the presented token and issues a new one within the same family.
Store the `refresh_token` from each response and discard the previous one.

**Reuse detection.** Presenting an already-revoked refresh token returns an error
**and revokes the entire family** — all outstanding tokens for that login session
are killed; the client must re-authenticate with credentials.

**Error codes** (all `{"status":"error","message":"<code>"}`)

| Code | Meaning |
| --- | --- |
| `invalid_token` | Token unknown or malformed |
| `token_reuse` | Revoked token replayed — entire family killed; must re-authenticate |
| `expired` | Token past its TTL (`refresh_expire`), or the family cap (`refresh_family_expire`) reached |
| `rate_limited` | Per-IP / per-family throttle tripped |
| `unsupported` | `with_refresh_tokens` not enabled on this deployment |

**Configuration TTLs** (in `jwt_middleware` settings):

- `refresh_expire` (default `"now +30 days"`) — sliding per-token TTL; each
  rotation issues a new token valid for this duration, capped by the family expiry.
- `refresh_family_expire` (default `"now +90 days"`) — absolute cap set at first
  login and **not** extended by rotation. After the cap, re-authenticate with credentials.

**Opt-in / no-op-safe** — projects without the `authy_refresh_token` table never
receive `refresh_token` in responses and `Authy/refresh` returns `unsupported`.

### Revoking tokens

JWTs are stateless: there is no server-side revocation list out of the box.
For sensitive deployments, implement a denylist keyed on `jti` and check it in
an additional middleware before `JwtAuthentication`. Rotating `jwt_middleware.secret`
invalidates every outstanding token.

## Session (cookie)

| Step | Where | Notes |
| --- | --- | --- |
| 1. Submit credentials | `POST /Authy/login` (UI) or `POST /Authy/auth` (XHR) | Username + password |
| 2. Server validates | `AuthyService::tryLog()` &rarr; `queryUser()` | Password verified via `password_verify()` against `authy.passwd_hash` |
| 3. Session populated | `AuthyService::setSession()` | Identity fields set on `AuthySession` |
| 4. Group memberships loaded | `AuthySession::setGroups()` | `group = Admin` if any joined `AuthyGroup.admin == 1` |
| 5. Rights loaded | `AuthySession::resetRights()` | Reads `authy.rights_all`, `rights_group`, `rights_owner` JSON columns |

**Brute-force throttling.** `AuthyService::tryLog()` calls `checkAttemptsOk($username)`
(`AuthyService.php:774, 926`) before verifying the password. The check counts
`authy_log` rows with `result='w'` (failed-write attempt) for the current
`(REMOTE_ADDR, login)` pair within the **last 1 minute**, and rejects if `count >= 5`
(note: the inline doc-comment says "5 attempts in 5 minutes" but the SQL window
is `1 min ago` &mdash; trust the code). On rejection the response includes
`"Too many attempts, try again later"` and the password is never checked. The
gate is per IP+username, so attackers spreading across IPs are not throttled and
a legitimate user behind a NAT cannot lock out other users on the same IP.

**CSRF.** Each session has a token at `$_SESSION[_AUTH_VAR]->csrf`. Pages inject
it as `<meta name="csrf-token">`. Browser-side JS reads the meta tag and echoes
the token on POST/PATCH/DELETE. Pure-cookie API consumers should do the same.
JWT-authenticated requests do not need CSRF (the token itself is the credential).

Logout: `GET /Authy/logout` destroys the session.

## Session model

The session object is `\ApiGoat\Sessions\AuthySession` stored at
`$_SESSION[_AUTH_VAR]` (key value defined in `config/Built/config.php`).

| Property | Type | Description |
| --- | --- | --- |
| `isConnected` | `YES` / `NO` | Authentication state |
| `firstname`, `lastname`, `username`, `email` | string | Identity |
| `authyId` | int | PK in `authy` table |
| `group` | `Admin` / `User` | Effective group; `Admin` elevation if any group has `admin=1` |
| `Groups` | int[] | Member group IDs |
| `accessControl[model][group]` | string | ACL right string, e.g. `rwda` |
| `csrf` | string | Per-session CSRF token |
| `lang` | string | Locale (drives i18n output) |
| `isRoot` | bool | Root-user flag |

Initialization happens in `vendor/apigoat/runtime/src/Middlewares/AuthyMiddleware.php`
(blank `AuthySession` on first request) and is populated by `AuthyService` on login.

## RBAC

Two independent access-control layers, applied in this order on every API request:

1. **Route-level RBAC** &mdash; `api_rbac` table + `RbacMiddleware` &mdash; decides whether the (model, action, method, body) tuple is reachable at all. Acts before `AuthyMiddleware`.
2. **Record-level RBAC** &mdash; `AuthyACL` + `accessControl[model][group]` &mdash; decides which rows the authenticated user can read / modify within an allowed endpoint.

A request that survives both layers reaches the Service handler.

### Route-level RBAC: the `api_rbac` table

Source: `vendor/apigoat/runtime/src/Middlewares/RbacMiddleware.php`. Triggers only on
paths containing `/api/v` (and not `OPTIONS`). The behavior is **a learning RBAC**:
every distinct request fingerprint gets a row in `api_rbac` on first hit, then
subsequent calls match against stored rules.

#### `api_rbac` columns that drive the decision

| Column | Purpose |
| --- | --- |
| `model` | Resource (table name) |
| `action` | `list` / `edit` / `create` / `update` / `delete` / custom |
| `method` | HTTP method |
| `body` | JSON template of the request body, with `*` as a wildcard for any value |
| `scope` | `Public` (passes without session) / `Private` (requires session) |
| `rule` | `Allow` / `Deny` (`Deny` is enforced even on a hard match) |
| `role` | Optional role gate; if set, only `$_SESSION[_AUTH_VAR]->SessVar['IdRole']` matching this value passes |
| `count` | Invocation counter (incremented per call) |

#### Two-pass evaluation

1. **First pass** (`authorizePublicRequest()`):
   - Look up by `(model, action, method, body)` &mdash; body is matched via wildcards (see below).
   - **No match found**: insert a new row with rule = `Allow` if `app_status == 'dev'`, else `Deny`. Set `rbac_public = 'passed'` in dev, `'failed'` in prod.
   - **Match + scope=Public + rule!=Deny**: increment count, set `rbac_public = 'passed'`. Request flows through without auth.
   - **Otherwise** (private route, or scope=Public with rule=Deny): set `rbac_public = 'failed'`, hand off to second pass.
2. **Second pass** (`authorizePrivateRequest()`, only when first pass marked failed):
   - Require `$_SESSION[_AUTH_VAR]->isConnected == 'YES'` (cookie session, or JWT-hydrated). If not connected: `401` "Route denied, private route require authentication."
   - If `rule == 'Deny'` or role mismatch: `401` "Route denied. Check your API access control."
   - Otherwise: increment count, log to `api_log`, allow through.

Every call &mdash; passed or denied &mdash; is logged to `api_log` (linked to the `api_rbac` row,
with `id_authy` if connected, raw query string + body in `raw_parameters`).

#### Body wildcard matching

Stored `body` templates can use `*` to match any value at a given JSON path. The
matcher (`findBestMatch()` in `RbacMiddleware.php`) runs a MySQL query that scores
each candidate row against `JSON_VALUE` / `JSON_CONTAINS` of the request body and
picks the highest-scoring match. Variants generated automatically via
`getBodyWildcarded()`:

- The literal request body
- Top-level keys replaced with `*`
- `query.filter[<table>][<i>][1]` (the value field) replaced with `*`

In practice: a stored template `[["IsActive", "*"]]` matches request body filter
`[["IsActive", "1"]]`, `[["IsActive", "0"]]`, etc. A stored `*` for the whole `query`
matches any query.

You can also configure `rbac.excludes` in `config/settings.php` to wipe specific
(method, model, action) tuples' bodies before matching &mdash; useful for routes whose
body varies on every call (e.g. timestamps).

#### Managing rules

The `api_rbac` table is editable via the standard CRUD UI at `/ApiRbac/`. Typical
lifecycle: develop with `app_status=dev` (auto-Allow), audit the table afterward,
flip to `app_status=prod`, then any new endpoint your client tries gets auto-denied
until you add an explicit `Allow`.

#### Public GET response cache

Anonymous requests to a **public** GET route can be served from APCu by
`\ApiGoat\Middlewares\PublicResponseCacheMiddleware` (registered in
`config/middlewares.php` directly before `RouteParser`, so a HIT is answered right
after routing and never reaches RBAC, JWT, Authy or the session). It is inert until
`GC_HTTPCACHE_TTL` > 0 in `.env` **and** the route is declared in `.gc-meta.json`
under a top-level `"cache"` key, keyed exactly like `rbac.public`; `gc build` compiles
it into `config/Built/cache.map.php` (generated &mdash; never edit):

```json
"cache": {
  "Category/list/GET": { "ttl": 600, "tables": ["category", "category_file"],
                         "params": ["id_parent"], "bypass_params": ["nocache"],
                         "vary": ["Accept-Language"] },
  "Setting/list/GET": 300,
  "Page/list/GET": true
}
```

| Field | Meaning |
| --- | --- |
| `ttl` | seconds, integer &ge; 0. An integer value is shorthand for `{ "ttl": n }`; `true` means ttl 0 = use the `GC_HTTPCACHE_TTL` default. Keep it &le; 600 s &mdash; it is only the backstop, invalidation is version-based (below). |
| `tables` | snake_case table names the response is built from; an ORM write to any of them invalidates the entry (`TableVersion` generation). Omitting it means only the TTL and a rebuild expire the entry. |
| `params` | query parameters that are part of the cache key (absent = every parameter, normalised: sorted, decoded, empty values dropped). |
| `bypass_params` | query parameters whose presence skips the cache entirely. |
| `vary` | request headers folded into the key (e.g. `Accept-Language`). |

**Rules `gc build` enforces** (a violation is dropped with a yellow warning, never emitted):
only `GET` tuples; the tuple **must also be listed in `rbac.public`** &mdash; a cache hit
short-circuits RBAC, so caching a private route would hand an anonymous caller a
response only a session may see.

**Anonymity gate.** The middleware only looks at the cache when the request carries
no `Authorization` / `X-Authorization` header **and** the session is not connected
(`$_SESSION[_AUTH_VAR]->isConnected !== 'YES'`). Anything else is `X-GC-Cache: BYPASS`
and runs the normal stack &mdash; a logged-in user never sees an anonymous rendering.

**Key ingredients.** project namespace + build id (`config/.buildid`) + the `api_rbac`
rule generation + the declared tables' `TableVersion` generations + the normalised
query string + the `vary` header values. Any of them moving is a new key, so
**invalidation** is implicit: an ORM save/delete on a declared table bumps its
generation, a rule change bumps the RBAC generation, `gc build` rotates the build id.
The TTL is the backstop for writes that bypass the ORM (raw SQL, `gc pushdata`).

**Opting out from a handler.** Set the request attribute `gc_httpcache_skip` (e.g.
`$request = $request->withAttribute('gc_httpcache_skip', true)` before delegating, or
send `X-GC-Cache: BYPASS` on the response) for a response that must not be stored
&mdash; per-visitor content, errors, anything larger than `GC_HTTPCACHE_MAX_KB`. Only
`200` responses are ever stored.

`.env` knobs: `GC_HTTPCACHE_TTL` (0 = off), `GC_HTTPCACHE_VERIFY` (1 = also run the
handler on a HIT and log a warning when the bodies diverge &mdash; dev only),
`GC_HTTPCACHE_MAX_KB` (512), and `GC_SESSION_DEFER_ANON_API` (1 = no `session_start`,
session file or `Set-Cookie` for anonymous `/api/` GETs). Check with
`curl -sI <app>/api/v1/Category/list | grep -i x-gc-cache` &mdash; `MISS` then `HIT`,
`BYPASS` once a bearer is sent (a non-empty one — Apache's `SetEnvIf Authorization` rule
leaves an empty header on every request, which does not count). An entry past its TTL is
served `STALE` for up to 30 s while ONE request refreshes it, so an expiry under load never
turns into a burst of handler runs. With `GC_HTTPCACHE_VERIFY=1`, `X-GC-Cache-Reason` says
why a declared route was not served from cache.

### Record-level RBAC: `AuthyACL`

Once a request is past `RbacMiddleware`, `AuthyMiddleware` and the per-Service code
apply a second layer of access control: which rows can this user read or modify?

Three-tier rights model: **All > Group > Owner**. Implemented in
`vendor/apigoat/runtime/src/ACL/AuthyACL.php`.

#### Privilege letters

`config/Built/privileges.map.defaults.php` maps each action to a single letter:

| Action | Letter | Meaning |
| --- | --- | --- |
| `list`, `view` | `r` | read |
| `create` | `a` | add |
| `update` | `w` | write |
| `delete` | `d` | delete |
| `file`, `upload` | `a` | add (uploaded asset) |

Compound rights combine letters with `|` (OR) or `&` (AND), parsed in
`AuthyACL::authorize()` (`AuthyACL.php:28-49`):

- `r|w` &mdash; user passes if they have **either** read or write
- `r&w` &mdash; user must have **both** read and write

**Heads-up:** the seeded `privileges.map.defaults.php` only ever stores single
letters, and no first-party Service code passes a compound string &mdash; so grepping
the framework defaults will turn up no `|`/`&` examples. To actually use a
compound right, add a per-model override under `action`, keyed `"{Model}-{action}"`
(checked at `AuthyMiddleware.php:182-183` after the plain-action lookup misses):

```php
// config/Built/privileges.map.defaults.php
return [
    "action" => [
        "list" => "r",
        "create" => "a",
        "update" => "w",
        "delete" => "d",
        // Per-model override: exporting a Document needs read AND write
        "Document-export" => "r&w",
        // Custom 'review' action: either reader or writer can perform it
        "Invoice-review" => "r|w",
    ],
    "exclude" => [ /* ... */ ],
];
```

#### Authorization flow

1. `AuthyMiddleware` maps the HTTP method + action to a privilege letter.
2. Calls `AuthyACL::authorize($modelName, $right)`.
3. Admin (`group == 'Admin'`) bypasses all checks.
4. Otherwise `hasRights()` looks up `accessControl[$modelName][$group]` and returns:
   - `true` if right is granted at the **All** level (no row filter)
   - `'Owner'` if granted only on rows the user created
   - `'Group'` if granted only on rows from the user's groups
   - `false` if denied
5. For row-filtered cases, `setAclFilter($Query)` adds Propel filters before fetch:
   `filterByIdCreation()` (Owner) and/or `filterByIdGroupCreation()` (Group).

#### Per-record audit columns

Every table has these columns (managed automatically; do not write directly):

- `id_creation` &mdash; user who created the row (Owner-level filter)
- `id_group_creation` &mdash; group of the creator (Group-level filter)
- `date_creation`, `date_modification` &mdash; timestamps

<a id="app_status"></a>
## `app_status` (dev vs prod)

`app_status` is a PHP constant (`\app_status`) **defined dynamically** at request
bootstrap from a row in the `config` table. The bootstrap loop is in
`config/Built/config.db.php`:

```php
$Configs = \App\ConfigQuery::create()->find();
foreach ($Configs as $Config) {
    define($Config->getConfig(), $Config->getValue());
}
```

Results are cached on `$_SESSION[_AUTH_VAR]->configdb`; the loop refreshes only when
`config_changed === 'yes'` (set by `Config` save hooks). Every key in `config` becomes
a top-level PHP constant for the duration of the request &mdash; so `app_status` is just
one of many; `api_ips`, others may exist depending on the project's `config` rows.

### What `app_status` controls

| Value | Effect on `RbacMiddleware` (route-level RBAC) |
| --- | --- |
| `'dev'` | New (model, action, method, body) tuples auto-create with `rule = Allow`. First-time hits to undocumented endpoints **pass through**. The current request also passes through (`return false`) so end-to-end tests of new endpoints just work. |
| anything else (default `'prod'`) | New tuples auto-create with `rule = Deny`. First-time hits to undocumented endpoints **return `401`**. Failed-rule second-pass also returns `401` rather than letting the request through. |

The seed value (per `basedata.sql`) is `'dev'`. Existing projects already in
production should explicitly toggle to `'prod'`.

### Toggling

The Welcome / admin landing view (`WelcomeView.php`) renders a checkbox:

- Unchecked &rarr; `Config.app_status = 'dev'`
- Checked &rarr; `Config.app_status = 'prod'`

The change writes via the standard `ConfigService` AJAX save and triggers
`config_changed='yes'`, so the next request picks up the new constant. Direct DB
edits (or migrations) work too &mdash; just `UPDATE config SET value='prod' WHERE config='app_status'`,
no service restart needed.

### Recommended workflow

1. **Develop in `dev`.** Exercise every endpoint your client calls; each one auto-creates an `api_rbac` row with `rule=Allow`.
2. **Audit the `api_rbac` table** at `/ApiRbac/`. For each row decide:
   - Public unauthenticated route &rarr; set `scope=Public`.
   - Authenticated-only &rarr; leave `scope=Private`, optionally pin a `role`.
   - Should never be reachable &rarr; set `rule=Deny`.
3. **Flip to `prod`.** New endpoints your client tries now auto-default to `Deny` &mdash; you must explicitly add an `Allow` row before they work. Deploys cannot accidentally expose new resources.
4. **Watch `api_log`** for `Deny` hits in production &mdash; they show up as failed RBAC events with the offending raw_parameters captured for debugging.

## Routes & dispatch

Actual Slim route registration (`config/Built/routes.php:121`):

```
{HTTP_METHOD} /api/v{N}/{table}[/{a}[/{params:.*}]]
```

The `params:.*` segment is a Slim regex wildcard &mdash; it matches **any number**
of slash-separated segments, so the URL grammar is permissive: `/api/v1/Client/exportCsv/foo/bar/baz`
is a valid match. **What the framework actually reads from the path is narrower**:

| Slot | Set by | Source |
| --- | --- | --- |
| `model` (`p`) | `RouteParser::decodePath()` | `$pathPart[0]` |
| `action` (`a`) | `RouteParser::decodePath()` | `$pathPart[1]` (empty if numeric &mdash; then promoted to `id`) |
| `id` (`i`) | `RouteParser::decodePath()` line 159 | `$pathPart[2]` &mdash; **regardless of how many segments follow** |
| `i` (overlap) | `RouteHelper::get{GET,POST,DELETE}Args()` | `explode('/', $args['params'])[0]` &mdash; first chunk of the wildcard only |

So `/api/v1/Client/exportCsv/foo/bar` resolves to `model=Client`, `action=exportCsv`,
`id=foo`. `bar` is captured by Slim as part of `params` but never lands in `$args`.
Don't design endpoints that rely on segments past the third &mdash; they are silently
dropped on both the parser and the helper side.

Practical contract by method (what `Service::getApiResponse()` dispatches on):

| Method | Action | Effect |
| --- | --- | --- |
| `GET` | (none) or `list` | List resources |
| `GET` | `<id>` | Read one (also `edit` / `view`) |
| `POST` | (none) or `insert` / `update` | Create or update |
| `PATCH` | `<id>` | Partial update |
| `PUT` | `<id>` | Replace / file upload |
| `DELETE` | `<id>` or `delete` | Remove |

Dispatch:

1. `AuthyMiddleware` enforces auth (unless route is in `exclude`).
2. `RouteHelper::getRouteName()` parses URL into the unified `$args` array.
3. `RouteHelper::getService()` instantiates `\App\<Table>ServiceWrapper`.
4. `Service::getResponse()` (HTML/XHR) or `Service::getApiResponse()` (JSON)
   dispatches by `$args['a']`. **Which method runs is decided purely by the
   Slim route binding**: `routes.php:113-126` wires `/api/v{N}/...` paths to
   `getApiResponse()`; everything else falls through to `getResponse()`. Neither
   the `Accept` header nor the HTTP method influences this selection &mdash; see
   "What flips JSON vs HTML response" below for the full decision tree.

### Special routes (not driven by the resource table)

A handful of routes are wired by hand in `config/Built/routes.php` and bypass the
foreach-builder loop. They use the same `RouteHelper` &rarr; service contract but are
bound to specific Service classes rather than `<Table>ServiceWrapper`:

| Route | Method(s) | Service | Source |
| --- | --- | --- | --- |
| `/[admin]` (home) | `GET` | `WelcomeService` (or `public/view/welcome.html` if present) | `routes.php:27-39` |
| `/GuiManager` | `POST` | `GuiManager` | `routes.php:42-46` |
| `/oauth/{p}[/{c}]` | `GET`, `POST` | `OauthService` (browser-mode &mdash; redirects on success) | `routes.php:96-107` |
| `/api/v{N}/Authy/{a:auth\|renew}` | `POST` | `AuthyServiceWrapper` (sets `method=AUTH`) | `routes.php:113-118` |
| `/api/v{N}/ApiGoat/sendEmail[/{i}]` | `GET`, `POST` | `EmailService` | `routes.php:128-132` |
| `/api/v{N}/ApiGoat/account[/{i}]` | `GET`, `POST` | `AccountService` | `routes.php:140-144` |
| `/api/v{N}/oauth/{p}[/{c}]` | `GET`, `POST` | `OauthService` (API-mode JSON envelope) | `routes.php:146-151` |
| `OPTIONS /api/v{N}/{routes:.+}` | `OPTIONS` | empty handler (CORS preflight pass-through) | `routes.php:109-111` |

These do **not** show up in the per-table resource list later in this doc &mdash; they
are framework-provided endpoints, not generated from your schema. Treat them as
always-on; they exist in every project regardless of the schema.

## Request headers

Only a small set of headers actually influences runtime behavior. **Importantly,
`Accept` is NOT one of them** &mdash; whether you get JSON or HTML depends purely on
whether the URL path matches `^/?api/v[0-9]+/` (canonical form: `/api/v{N}/...`),
not on what you ask for.

| Header | Read by | Effect |
| --- | --- | --- |
| `Authorization: Bearer <jwt>` | `JwtAuthentication` (`config/container.php`) | Decode + hydrate session. Required for protected `/api/v1/...` routes when no cookie session is present. |
| `X-Authorization: Bearer <jwt>` | `JwtAuthentication` (`config/container.php`) | Same as `Authorization`; fallback when proxies / API gateways strip the standard header. |
| `X-Requested-With: XMLHttpRequest` | `AuthyMiddleware.php:55` &mdash; **non-API routes only** | When unauthenticated on a browser-mode route, return `401` JSON instead of redirecting to `/Authy/login`. Has no effect on `/api/v1/...` routes (those always return JSON). The same API/browser split also drives **`403` Forbidden** responses (`AuthyMiddleware.php:78-91`): API path emits an `ApiResponse` JSON envelope with status 403; browser path emits the access-denied message as plain text 403 (and sets `authy_access=denied`, `authy_message=...` request attributes for downstream handlers). |
| `Cookie: <session-id>` | PHP session handler | Loads `$_SESSION[_AUTH_VAR]`; selected automatically when JWT is absent. |
| `Content-Type` | `RouteParser::getContentType()` | Soft-checked. Recognized values: `application/json`, `application/x-www-form-urlencoded`, `application/xml`, `text/xml`. Unrecognized values currently warn-only (see `RouteParser.php:87-88`); body parsing is up to the framework / Slim middleware. |
| `Accept` | `ExceptionHandler.php:223` &mdash; **error renderer only** | When the request triggers an unhandled exception, picks `JsonErrorRenderer` for `application/json` else `HtmlErrorRenderer`. **Does not influence routing or normal-path response format.** |
| `Origin`, `Access-Control-Request-*` | `CorsMiddleware` | Standard CORS preflight handling per the `cors` config block. |
| `X-CSRF-Token` (or `csrf` body field) | session-cookie writes | Required by `Authy/auth` and other mutating cookie-mode endpoints. **Not required for JWT-mode requests** &mdash; the bearer token is the credential. The token value is `$_SESSION[_AUTH_VAR]->csrf`, exposed to browser pages via `<meta name="csrf-token">`. |

### What flips JSON vs HTML response

Decision tree (in order, NOT a header check):

1. URL path matches `^/?api/v[0-9]+/` &rarr; `is_api = true` (`RouteParser::decodePath()` line 151); responses go through `ApiResponse` which always sets `Content-Type: application/json` and returns the JSON envelope. The matched `api/v{N}/` segment is stripped before dispatch.
2. Otherwise &rarr; `is_api = false`; the `Service::getResponse()` HTML/XHR path runs.
   - If unauthenticated AND `X-Requested-With: XMLHttpRequest` &rarr; `401` JSON (special-case for AJAX in browser mode).
   - Otherwise unauthenticated &rarr; `303 See Other` redirect to `/Authy/login` with `Cache-Control: no-store` (non-cacheable, so browsers won't pin the protected URL to the login page after the user authenticates).

## Request envelope

Every Service method receives a unified `$args` (also `$this->request`):

| Key | Source | Purpose |
| --- | --- | --- |
| `p` | URL path | Resource (table name) |
| `a` | URL path or `data['a']` | Action: `list`, `edit`, `update`, `delete`, or custom |
| `i` | URL path or query | Primary key |
| `method` | HTTP method | `GET` / `POST` / `PUT` / `PATCH` / `DELETE` |
| `data` | Body + query string | Field values for write; `query` sub-key for list filters |
| `data.query.where` | Body | List filter, format `[Field, op, value]`; ops: `=`, `!=`, `<`, `>`, `ilike`, `in`, &hellip; |
| `ui` | XHR header / param | Container DOM ID for HTML mode |
| `rbac_public` | `ApiRbac` middleware | `"passed"` when the route is marked public by ACL data. Skips `AuthyMiddleware`'s session-required check **and** (if no Bearer) skips JWT decoding &mdash; see [Public routes & session hydration](#public-routes-session). Distinct from `privileges.map.exclude`, which is a static config list. |

Example list with filter:

```http
POST /Client
Content-Type: application/json

{
  "a": "list",
  "data": {
    "query": { "where": ["FirstName", "ilike", "John%"] }
  }
}
```

## Response envelope

### JSON (API mode)

```json
{
  "status": "success" | "failure",
  "data": [],
  "messages": [],
  "errors": []
}
```

Default body shape and key order come from `ApiResponse.php` (lines 23-28).
`messages` and `errors` are always present arrays &mdash; they default to `[]`,
never `null`. A handler that overrides `setBody()` may add or omit keys, but the
four-key shape above is what every code path that uses `ApiResponse` directly
produces.

| HTTP status | When |
| --- | --- |
| `200` | GET / PUT / PATCH / DELETE success |
| `201` | POST create |
| `400` | Bad request / validation error |
| `401` | Unauthenticated |
| `403` | Authenticated but lacks RBAC right |
| `405` | Method not allowed for this route |

Source: `vendor/apigoat/runtime/src/Api/ApiResponse.php`.

### XHR (browser mode)

Service methods return an array merged via `BuilderLayout::renderXHR()`:

```php
[
  'html'      => '...',  // HTML fragment to swap into a UI container
  'js'        => '...',  // Inline JS, executed immediately
  'onReadyJs' => '...',  // JS to run after fragment insertion
  'json'      => null,   // Empty in XHR mode
]
```

<a id="custom-actions"></a>
## Custom actions (extension point)

Add API endpoints without modifying generated code. Extend the
`<Table>ServiceWrapper` in `src/App/Services/`:

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

After registering, `GET /Client/exportCsv` (or `POST /Client/exportCsv`) routes
to `exportClientsCsv()`. The same `customActions` map is consulted by both the
HTML dispatcher (`Service::getResponse()`) and the JSON dispatcher
(`Service::getApiResponse()`).

## Constants

Defined in `config/Built/config.php` (regenerated each build):

| Constant | Purpose |
| --- | --- |
| `_AUTH_VAR` | Session-storage key for `AuthySession` (random per-project) |
| `_SITE_URL` | Absolute base URL (e.g. `https://example.com/`) |
| `_SUB_DIR_URL` | Subdirectory prefix when project is below domain root |
| `_BASE_DIR` | Filesystem path to project root |
| `_PROJECT_NAME` | Project identifier (matches `gc create` argument) |
| `_CRYPT_KEY`, `_CRYPT_IV` | Symmetric encryption for session-stored secrets |
| `API_VERSION` | API version segment (used by `/v{N}/` routing) |

<a id="resources"></a>
## Resources in this project

Each row maps the route prefix to its underlying table. The actual operations
available depend on RBAC rights granted to the calling session.

| Route prefix | DB table | Description | Menu | Child tables |
| --- | --- | --- | --- | --- |
| `/ApiLog` | api_log | API log | Settings |  |
| `/ApiRbac` | api_rbac | API ACL | Settings | ["api_log"] |
| `/AuthyGroupX` | authy_group_x | Group |  |  |
| `/AuthyGroup` | authy_group | Group | Settings |  |
| `/AuthyLog` | authy_log | Login log | Settings |  |
| `/AuthyRefreshToken` | authy_refresh_token |  |  |  |
| `/Authy` | authy | User | Settings | ["authy_group_x","authy_log"] |
| `/BotCommand` | bot_command | Command |  |  |
| `/BotDecision` | bot_decision | Refit Decision |  |  |
| `/BotEvent` | bot_event | Event |  |  |
| `/BotOrder` | bot_order | Order |  |  |
| `/Config` | config | Setting | Settings |  |
| `/Country` | country | Country | Settings |  |
| `/FleetSlot` | fleet_slot | Fleet slot | Trading |  |
| `/GridRunAudit` | grid_run_audit | Change history |  |  |
| `/GridRun` | grid_run | Grid Run | Trading | ["bot_order","trade_cycle","bot_event","bot_command","grid_run_audit"] |
| `/MarketCandle` | market_candle | Candles | Settings |  |
| `/MarketOutlookState` | market_outlook_state | Outlook State | Settings |  |
| `/MarketOutlook` | market_outlook | Market Outlook | Settings |  |
| `/MarketRegime` | market_regime | Regime History | Settings |  |
| `/MarketSummary` | market_summary | Market Data | Settings |  |
| `/MessageI18n` | message_i18n |  |  |  |
| `/Message` | message | Message | Settings |  |
| `/OauthAccessToken` | oauth_access_token |  |  |  |
| `/OauthAuthCode` | oauth_auth_code |  |  |  |
| `/OauthClient` | oauth_client |  |  |  |
| `/OauthRefreshToken` | oauth_refresh_token |  |  |  |
| `/PushDevice` | push_device | Push device | Settings |  |
| `/RegimeEpisode` | regime_episode | Regime episode | Trading |  |
| `/SimWallet` | sim_wallet | Paper Wallet | Settings |  |
| `/TemplateFile` | template_file | File |  |  |
| `/Template` | template | Template | Settings | ["template_file"] |
| `/TradeCycle` | trade_cycle | Trade Cycle |  |  |
| `/WalletNav` | wallet_nav | Wallet NAV | Settings |  |

## Examples

### JWT mode (programmatic clients)

Login (mint a token):

```http
POST /api/v1/Authy/auth
Content-Type: application/json

{ "u": "you", "p": "secret" }
```

Response:

```json
{ "status": "success", "token": "eyJhbGciOiJIUzI1NiIs...", "expires": 1735776000 }
```

Renew before expiry:

```http
GET /api/v1/Authy/renew
Authorization: Bearer eyJhbGciOiJIUzI1NiIs...
```

List with filter:

```http
GET /api/v1/Client?query[where][]=FirstName&query[where][]=ilike&query[where][]=John%
Authorization: Bearer eyJhbGciOiJIUzI1NiIs...
```

Read one:

```http
GET /api/v1/Client/123
Authorization: Bearer eyJhbGciOiJIUzI1NiIs...
```

Create:

```http
POST /api/v1/Client
Authorization: Bearer eyJhbGciOiJIUzI1NiIs...
Content-Type: application/json

{ "data": { "FirstName": "Jane", "Email": "jane@example.com" } }
```

Update (partial):

```http
PATCH /api/v1/Client/123
Authorization: Bearer eyJhbGciOiJIUzI1NiIs...
Content-Type: application/json

{ "data": { "Email": "jane2@example.com" } }
```

Delete:

```http
DELETE /api/v1/Client/123
Authorization: Bearer eyJhbGciOiJIUzI1NiIs...
```

Custom action (after registering in your `ServiceWrapper`):

```http
GET /api/v1/Client/exportCsv
Authorization: Bearer eyJhbGciOiJIUzI1NiIs...
```

### Session mode (browser)

Same routes without the `api/v1/` prefix; auth via the cookie set after
`POST /Authy/login`. Mutating verbs require the CSRF token from the page meta tag:

```http
PATCH /Client/123
Cookie: PHPSESSID=...
X-CSRF-Token: <session.csrf>
Content-Type: application/json

{ "data": { "Email": "jane2@example.com" } }
```

## Source-of-truth files

When in doubt, read these. They are the actual code paths driving the API:

- `vendor/apigoat/runtime/src/Routes/RouteHelper.php` &mdash; URL parsing into `$args`
- `vendor/apigoat/runtime/src/Middlewares/RouteParser.php` &mdash; sets `is_api` via `preg_match('#^/?api/v[0-9]+/#', $path)` (strict regex anchored at the start; requires version digits and trailing slash) and strips the matched segment
- `vendor/apigoat/runtime/src/Middlewares/RbacMiddleware.php` &mdash; uses a different, looser check `strstr($path, '/api/v')` to gate RBAC; not the same rule as `is_api`
- `vendor/apigoat/runtime/src/Middlewares/AuthyMiddleware.php` &mdash; auth gate (session and JWT-hydrated)
- `vendor/apigoat/runtime/src/Sessions/AuthySession.php` &mdash; session object, groups, rights loader
- `vendor/apigoat/runtime/src/ACL/AuthyACL.php` &mdash; `authorize`, `hasRights`, `setAclFilter`
- `vendor/apigoat/runtime/src/Api/ApiResponse.php` &mdash; JSON response envelope
- `vendor/apigoat/runtime/src/Services/OauthService.php` &mdash; OAuth callback &rarr; JWT minting
- `config/container.php` &rarr; `JwtAuthentication::class` &mdash; JWT middleware factory and `BeforeHandler` (token claims &rarr; AuthySession)
- `src/App/Services/Built/AuthyService.php` &mdash; `auth()`, `tryLog()`, `getToken()`, `renew()`
- `config/Built/routes.php` &mdash; Slim route registrations
- `config/Built/permissions.defaults.php` &mdash; default RBAC defaults
- `config/Built/privileges.map.defaults.php` &mdash; action&rarr;letter map and `exclude` list
- `config/settings.php` &rarr; `jwt_middleware` &mdash; secret, algorithm, paths, expire
- `src/App/Services/Built/<Table>Service.php` &mdash; per-table dispatch (regenerated)
- `src/App/Services/<Table>ServiceWrapper.php` &mdash; your extensions (hand-edited)
- `.admin/docs/SCHEMA-OVERVIEW.md`, `ENTITIES.md`, `EDIT-GUIDE.md` &mdash; companion docs
