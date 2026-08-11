<?php

/**
 *  Defaults action to privileges mapping
 *  Valid actions
 *  Privileges Excluded routes
 */

return [
    "action" =>
    [
        "list" => 'r',
        "view" => 'r',
        "create" => 'a',
        "update" => 'w',
        "delete" => 'd',
        "file" => 'a',
        "upload" => 'a'
    ],
    "exclude" => [
        "Authy/auth",
        "stripe/return",
        "stripe/pay",
        "stripe/webhook",
        "Authy/resetConfirm",
        "Authy/refresh",
        "Authy/google",
        "Authy/forgotten",
        "Authy/confirm",
        "Authy/reset",
        "Authy/login",
        "Authy/register",
        "Authy/logout",
        "GuiManager"
    ]
];