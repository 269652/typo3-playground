<?php

declare(strict_types=1);

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Extbase\Utility\ExtensionUtility;

defined('TYPO3') or die();

$pluginSignature = ExtensionUtility::registerPlugin(
    'PlaygroundDemo',
    'Greeting',
    'LLL:EXT:playground_demo/Resources/Private/Language/locallang_db.xlf:plugin.greeting.title',
    'content-plugin',
    'plugins',
    'LLL:EXT:playground_demo/Resources/Private/Language/locallang_db.xlf:plugin.greeting.description',
    'FILE:EXT:playground_demo/Configuration/FlexForms/Greeting.xml',
);

ExtensionManagementUtility::addToAllTCAtypes(
    'tt_content',
    '--div--;core.form.tabs:plugin,pi_flexform',
    $pluginSignature,
    'after:subheader',
);
