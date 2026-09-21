<?php
/**
 * MCP custom-tool manifest (editable; created once, never overwritten by gc build).
 * Drop App\Mcp\Tools\*Tool classes (implementing ApiGoat\Mcp\McpTool) into
 * src/App/Mcp/Tools/ — they are auto-discovered. Use this file only to register
 * tools that live elsewhere, fix ordering, or disable a discovered/built-in tool.
 * Custom tool names must NOT start with 'crm_' (reserved for built-ins).
 *
 * 'instructions' (string) is returned in the MCP initialize response and shown
 * to the model at the start of every conversation — describe your entities,
 * filter syntax and canonical tool workflows here so clients use the tools
 * without exploratory calls. Omitted/empty => a generic built-in default.
 */
return [
    // serverInfo shown to MCP clients (runtime 0dbba9b: per-project, no longer the
    // hardcoded 'apicrm-mcp'); name is an identifier, title is for humans
    'name' => 'apigtbot',
    'title' => 'apigtbot trading bot (grid + trend runs, PnL, refit)',
    'tools' => [],     // e.g. \App\Mcp\Tools\LlteqConvertQuote::class
    'disabled' => [],  // e.g. 'crm_delete' to turn off a built-in
    // 'instructions' => 'MyApp CRM. Start with crm_describe; ...',
];
