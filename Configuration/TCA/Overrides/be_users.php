<?php

$GLOBALS['TCA']['be_users']['columns']['createdByAction'] = [
    'config' => [
        'type' => 'passthrough'
    ]
];

// By default, the field "disable" is not settable in the BE for an admin.
// Remove exclude flag so that regular user can edit this field in a sys_action
unset($GLOBALS['TCA']['be_users']['columns']['disable']['exclude']);
