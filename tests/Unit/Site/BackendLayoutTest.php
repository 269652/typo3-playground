<?php

declare(strict_types=1);

namespace Playground\Tests\Unit\Site;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * Backend layouts are file-based page TSconfig in the site set.
 * Each layout must have a unique identifier, a title, and a grid whose
 * colPos values are declared and unique.
 */
final class BackendLayoutTest extends TestCase
{
    private const SET = __DIR__ . '/../../../packages/playground_site/Configuration/Sets/Playground';

    /** @return array<string, array{string, list<int>}> */
    public static function layouts(): array
    {
        return [
            'default' => ['Default', [0]],
            'two column' => ['TwoColumn', [0, 1]],
            'landing' => ['Landing', [10, 0, 20]],
        ];
    }

    #[Test]
    public function pageTsConfigImportsAllBackendLayouts(): void
    {
        $pageTs = (string)file_get_contents(self::SET . '/page.tsconfig');

        self::assertStringContainsString('BackendLayouts/*.tsconfig', $pageTs);
    }

    /** @param list<int> $expectedColPos */
    #[Test]
    #[DataProvider('layouts')]
    public function layoutDefinesGridWithExpectedColumns(string $file, array $expectedColPos): void
    {
        $path = self::SET . '/BackendLayouts/' . $file . '.tsconfig';
        self::assertFileExists($path);
        $ts = (string)file_get_contents($path);

        self::assertMatchesRegularExpression('/mod\.web_layout\.BackendLayouts\.Playground[A-Za-z]+\s*\{/', $ts);
        self::assertMatchesRegularExpression('/title\s*=\s*\S+/', $ts);

        preg_match_all('/colPos\s*=\s*(\d+)/', $ts, $m);
        $found = array_map('intval', $m[1]);
        sort($found);
        $expected = $expectedColPos;
        sort($expected);
        self::assertSame($expected, $found);
    }

    #[Test]
    public function layoutIdentifiersAreUnique(): void
    {
        $ids = [];
        foreach (glob(self::SET . '/BackendLayouts/*.tsconfig') ?: [] as $file) {
            preg_match('/BackendLayouts\.(Playground[A-Za-z]+)\s*\{/', (string)file_get_contents($file), $m);
            self::assertNotEmpty($m, basename($file) . ' declares an identifier');
            $ids[] = $m[1];
        }

        self::assertCount(3, $ids);
        self::assertSame($ids, array_values(array_unique($ids)));
    }

    /** @return array<string, array{string, string, list<string>}> */
    public static function columnIdentifiers(): array
    {
        return [
            'default' => ['Default', 'PlaygroundDefault', ['main']],
            'two column' => ['TwoColumn', 'PlaygroundTwoColumn', ['main', 'sidebar']],
            'landing' => ['Landing', 'PlaygroundLanding', ['hero', 'main', 'footer']],
        ];
    }

    /** @param list<string> $identifiers */
    #[Test]
    #[DataProvider('columnIdentifiers')]
    public function everyColumnHasIdentifierRenderedByItsTemplate(string $file, string $layout, array $identifiers): void
    {
        $ts = (string)file_get_contents(self::SET . '/BackendLayouts/' . $file . '.tsconfig');
        $template = (string)file_get_contents(
            __DIR__ . '/../../../packages/playground_site/Resources/Private/PageView/Pages/' . $layout . '.fluid.html'
        );

        preg_match_all('/identifier\s*=\s*(\w+)/', $ts, $m);
        $declared = $m[1];
        sort($declared);
        $expected = $identifiers;
        sort($expected);
        self::assertSame($expected, $declared, 'column identifiers in backend layout');

        foreach ($identifiers as $identifier) {
            self::assertStringContainsString('{content.' . $identifier . '}', $template, "template renders area $identifier");
        }
    }

    #[Test]
    public function typoScriptUsesPageviewWithSinglePageContentProcessor(): void
    {
        $setup = (string)file_get_contents(self::SET . '/setup.typoscript');

        self::assertStringContainsString('PAGEVIEW', $setup);
        self::assertSame(1, preg_match_all('/=\s*page-content\b/', $setup), 'exactly one page-content processor');
        self::assertMatchesRegularExpression('/as\s*=\s*content\b/', $setup);
        self::assertStringContainsString('EXT:playground_site/Resources/Private/PageView/', $setup);
    }

    #[Test]
    public function fluidTemplatesExistForEveryLayout(): void
    {
        // PAGEVIEW resolves Pages/<layout identifier>.fluid.html
        foreach (['PlaygroundDefault', 'PlaygroundTwoColumn', 'PlaygroundLanding'] as $name) {
            self::assertFileExists(
                __DIR__ . '/../../../packages/playground_site/Resources/Private/PageView/Pages/' . $name . '.fluid.html'
            );
        }
    }

    #[Test]
    public function newPagesDefaultToPlaygroundDefaultLayoutAndFallbackTemplateExists(): void
    {
        $pageTs = (string)file_get_contents(self::SET . '/page.tsconfig');

        self::assertMatchesRegularExpression('/TCAdefaults\.pages\.(backend_layout|backend_layout_next_level)\s*=\s*pagets__PlaygroundDefault/', $pageTs);
        self::assertFileExists(
            __DIR__ . '/../../../packages/playground_site/Resources/Private/PageView/Pages/Default.fluid.html',
            'PAGEVIEW falls back to "default" when no layout is selected'
        );
    }
}
