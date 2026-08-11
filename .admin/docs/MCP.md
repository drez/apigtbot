# Connect this MCP server to Claude

_Auto-generated on `gc build` because the `with_mcp` behavior is enabled.
Do not edit by hand — this file is overwritten on every build._

This project ships a built-in **Model Context Protocol (MCP)** server that lets
AI assistants read and write its CRM data. It is protected by an OAuth 2.1
authorization server, so Claude connects as a remote connector and acts **as
you** — every request is scoped to your login, tenant and owner/group ACL.

## Endpoints

| What | URL |
| ---- | --- |
| MCP endpoint (JSON-RPC over HTTP POST) | `https://your-domain.example/api/v1/mcp` |
| OAuth authorization-server metadata | `https://your-domain.example/.well-known/oauth-authorization-server` |
| OAuth protected-resource metadata | `https://your-domain.example/.well-known/oauth-protected-resource` |

> These URLs point at the deploy target (`DEPLOY_HOST`). If the server is reachable at a different
> public host, substitute it — the paths stay the same.

## Connect from Claude (web or desktop)

1. Open Claude → **Settings → Connectors → Add custom connector**.
2. Paste the **MCP endpoint** URL:
   `https://your-domain.example/api/v1/mcp`
3. Click **Connect**. Claude discovers the OAuth server automatically via the
   metadata URLs above and registers itself (dynamic client registration —
   there is no client ID or secret to copy).
4. You are redirected to this app's sign-in page. Log in with your normal CRM
   credentials and **approve** the requested scopes.
5. The connector shows as connected and the `crm_*` tools become available in
   your chats.

## Connect from Claude Code (CLI)

```
claude mcp add --transport http crm https://your-domain.example/api/v1/mcp
```

Then run `/mcp` inside Claude Code and choose **Authenticate** to complete the
same browser OAuth login.

## Scopes

| Scope | Grants |
| ----- | ------ |
| `crm:read` | List / read records (`crm_describe`, `crm_list`, `crm_get`) |
| `crm:write` | Create / update / delete records (`crm_create`, `crm_update`, `crm_delete`) |
| `offline_access` | A refresh token so Claude stays connected without re-login |

## Built-in tools

All built-ins use the reserved `crm_` prefix. Start with **`crm_describe`** to
discover which entities and fields your account may access.

| Tool | Right | Description |
| ---- | ----- | ----------- |
| `crm_describe` | read | Describe accessible entities and fields (type, required, writable, enum, relations). Omit `entity` to list all. |
| `crm_list` | read | List/search rows of an entity with filter, order, select and pagination. |
| `crm_get` | read | Fetch a single row by id. |
| `crm_create` | write | Create a new row (`data` = writable column → value). |
| `crm_update` | write | Update an existing row by id. |
| `crm_delete` | write | Delete a row by id (pass `confirm: true` to execute). |

## Custom tools

Project-specific tools are auto-discovered from `src/App/Mcp/Tools/` — any class
implementing `ApiGoat\Mcp\McpTool`. The `crm_` prefix is reserved for the
built-ins, so name custom tools anything else. Enable, disable or override tools
in `config/mcp.php` (created once, never overwritten by `gc build`).

## Protocol details

- JSON-RPC 2.0 over HTTP POST; MCP protocol version `2025-06-18`.
- Methods: `initialize`, `notifications/initialized`, `tools/list`, `tools/call`.
- Every call needs `Authorization: Bearer <token>`. An unauthenticated request
  gets `401` with a `WWW-Authenticate` header pointing at the protected-resource
  metadata above — that is how Claude bootstraps the OAuth flow.

## Troubleshooting

- **Won't connect / repeated 401:** the server must be reachable over **HTTPS**
  with a valid certificate — Claude refuses OAuth over plain HTTP.
- **"tool not allowed" / empty results:** your account lacks the
  `crm:read`/`crm:write` right on that entity; tools are filtered by your ACL.
- **404 on the endpoints:** confirm the build emitted the OAuth/MCP routes
  (`with_mcp` set on the auth table) and that the seeded `mcp` `api_rbac` rule
  is present in the database.