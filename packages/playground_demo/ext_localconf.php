<?php

declare(strict_types=1);

use Playground\Demo\Controller\GreetingController;
use TYPO3\CMS\Extbase\Utility\ExtensionUtility;

defined('TYPO3') or die();

ExtensionUtility::configurePlugin(
    'PlaygroundDemo',
    'Greeting',
    [GreetingController::class => 'show'],
    // The greeting depends on the time of day, so the plugin must not be page-cached.
    [GreetingController::class => 'show'],
);
