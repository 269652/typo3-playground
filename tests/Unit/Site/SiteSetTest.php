<?php

declare(strict_types=1);

namespace Playground\Tests\Unit\Site;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Yaml\Yaml;

/**
 * Structural contract of the `playground/site` site set.
 */
final class SiteSetTest extends TestCase
{
    private const SET = __DIR__ . '/../../../packages/playground_site/Configuration/Sets/Playground';

    #[Test]
    public function setDeclaresNameLabelAndCoreDependencies(): void
    {
        $config = Yaml::parseFile(self::SET . '/config.yaml');

        self::assertSame('playground/site', $config['name']);
        self::assertNotEmpty($config['label']);
        self::assertContains('typo3/fluid-styled-content', $config['dependencies']);
        self::assertContains('playground/demo', $config['dependencies'], 'site set pulls in the demo plugin set');
    }

    #[Test]
    public function setShipsTypoScriptAndPageTsConfig(): void
    {
        self::assertFileExists(self::SET . '/setup.typoscript');
        self::assertFileExists(self::SET . '/page.tsconfig');
        self::assertStringContainsString('PAGE', (string)file_get_contents(self::SET . '/setup.typoscript'));
    }

    #[Test]
    public function settingsDefinitionsDeclareTypedSettingsWithDefaults(): void
    {
        $definitions = Yaml::parseFile(self::SET . '/settings.definitions.yaml')['settings'];

        foreach (['playground.site.name', 'playground.site.accentColor'] as $key) {
            self::assertArrayHasKey($key, $definitions);
            self::assertArrayHasKey('type', $definitions[$key]);
            self::assertArrayHasKey('default', $definitions[$key]);
            self::assertArrayHasKey('label', $definitions[$key]);
        }
    }

    #[Test]
    public function siteConfigurationActivatesTheSet(): void
    {
        $site = Yaml::parseFile(__DIR__ . '/../../../config/sites/playground/config.yaml');

        self::assertSame(1, $site['rootPageId']);
        self::assertContains('playground/site', $site['dependencies']);
        self::assertNotEmpty($site['base']);
        self::assertNotEmpty($site['languages']);
        self::assertSame(0, $site['languages'][0]['languageId']);
    }

    #[Test]
    public function extensionIsRegisteredAsPackage(): void
    {
        $composer = json_decode((string)file_get_contents(__DIR__ . '/../../../packages/playground_site/composer.json'), true, 512, JSON_THROW_ON_ERROR);

        self::assertSame('typo3-cms-extension', $composer['type']);
        self::assertSame('playground_site', $composer['extra']['typo3/cms']['extension-key']);
        self::assertFileExists(__DIR__ . '/../../../packages/playground_site/ext_emconf.php');
    }
}
