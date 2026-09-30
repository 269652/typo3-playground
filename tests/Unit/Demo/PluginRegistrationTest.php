<?php

declare(strict_types=1);

namespace Playground\Tests\Unit\Demo;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Yaml\Yaml;

/**
 * The demo plugin must be registered for FE rendering, in the backend
 * (CType + FlexForm), and shipped with its own site set.
 */
final class PluginRegistrationTest extends TestCase
{
    private const EXT = __DIR__ . '/../../../packages/playground_demo';

    #[Test]
    public function pluginIsConfiguredForFrontend(): void
    {
        $localconf = (string)file_get_contents(self::EXT . '/ext_localconf.php');

        self::assertStringContainsString('configurePlugin', $localconf);
        self::assertStringContainsString("'Greeting'", $localconf);
        self::assertStringContainsString('GreetingController::class', $localconf);
    }

    #[Test]
    public function pluginIsRegisteredAsContentTypeWithFlexForm(): void
    {
        $override = (string)file_get_contents(self::EXT . '/Configuration/TCA/Overrides/tt_content.php');

        self::assertStringContainsString('registerPlugin', $override);
        self::assertStringContainsString("'Greeting'", $override);
        self::assertStringContainsString('FILE:EXT:playground_demo/Configuration/FlexForms/Greeting.xml', $override);
    }

    #[Test]
    public function flexFormIsValidXmlAndExposesNameSetting(): void
    {
        $xml = simplexml_load_file(self::EXT . '/Configuration/FlexForms/Greeting.xml');

        self::assertNotFalse($xml);
        self::assertNotEmpty($xml->xpath('//settings.name'), 'settings.name field present');
    }

    #[Test]
    public function demoSetIsDeclaredAndRegistersTypoScript(): void
    {
        $config = Yaml::parseFile(self::EXT . '/Configuration/Sets/Demo/config.yaml');

        self::assertSame('playground/demo', $config['name']);
        self::assertFileExists(self::EXT . '/Configuration/Sets/Demo/setup.typoscript');
        self::assertStringContainsString(
            'plugin.tx_playgrounddemo_greeting',
            (string)file_get_contents(self::EXT . '/Configuration/Sets/Demo/setup.typoscript')
        );
    }

    #[Test]
    public function fluidTemplateAndLanguageFileExist(): void
    {
        self::assertFileExists(self::EXT . '/Resources/Private/Templates/Greeting/Show.html');
        self::assertFileExists(self::EXT . '/Resources/Private/Language/locallang_db.xlf');
    }

    #[Test]
    public function extensionIsRegisteredAsPackage(): void
    {
        $composer = json_decode((string)file_get_contents(self::EXT . '/composer.json'), true, 512, JSON_THROW_ON_ERROR);

        self::assertSame('playground_demo', $composer['extra']['typo3/cms']['extension-key']);
        self::assertFileExists(self::EXT . '/ext_emconf.php');
    }
}
