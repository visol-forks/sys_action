<?php

if (!defined('TYPO3')) {
    die('Access denied.');
}

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns(
    'be_users',
    [
        'createdByAction' => [
            'config' => [
                'type' => 'passthrough',
            ],
        ],
        // The owner of the be_user
        'cruser_id' => [
            'config' => [
                'type' => 'passthrough',
            ],
        ],
    ]
);
