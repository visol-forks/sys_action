<?php

defined('TYPO3') or die();

$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['taskcenter']['sys_action'][\TYPO3\CMS\SysAction\ActionTask::class] = [
    'title' => 'LLL:EXT:sys_action/Resources/Private/Language/locallang_tca.xlf:sys_action',
    'description' => 'LLL:EXT:sys_action/Resources/Private/Language/locallang_csh_sysaction.xlf:.description',
    'icon' => 'task-sys-action',
];

// Fill the "owner" field of a sys_action with the user who created it
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processDatamapClass'][] = \TYPO3\CMS\SysAction\Persistence\BeUserCreationEnricher::class;