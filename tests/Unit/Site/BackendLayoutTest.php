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

        self::assertMatchesRegularExpression('/mod\.web_layout\.BackendLayouts\.playground_[a-z_]+\s*\{/', $ts);
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
            preg_match('/BackendLayouts\.(playground_[a-z_]+)\s*\{/', (string)file_get_contents($file), $m);
            self::assertNotEmpty($m, basename($file) . ' declares an identifier');
            $ids[] = $m[1];
        }

        self::assertCount(3, $ids);
        self::assertSame($ids, array_values(array_unique($ids)));
    }

    #[Test]
    public function typoScriptRendersEveryColumnOfEveryLayout(): void
    {
        $setup = (string)file_get_contents(self::SET . '/setup.typoscript');

        foreach ([0, 1, 10, 20] as $colPos) {
            self::assertMatchesRegularExpression('/colPos\s*=\s*' . $colPos . '\b/', $setup, "colPos $colPos rendered");
        }
        foreach (['playground_default', 'playground_two_col', 'playground_landing'] as $id) {
            self::assertStringContainsString($id, $setup, "template selection covers $id");
        }
    }

    #[Test]
    public function fluidTemplatesExistForEveryLayout(): void
    {
        foreach (['Default', 'TwoColumn', 'Landing'] as $name) {
            self::assertFileExists(
                __DIR__ . '/../../../packages/playground_site/Resources/Private/PageView/Pages/' . $name . '.fluid.html'
            );
        }
    }
}
