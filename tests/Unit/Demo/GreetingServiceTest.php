<?php

declare(strict_types=1);

namespace Playground\Tests\Unit\Demo;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Playground\Demo\Service\GreetingService;

final class GreetingServiceTest extends TestCase
{
    /** @return array<string, array{string, string}> */
    public static function timesOfDay(): array
    {
        return [
            'early morning' => ['2026-01-01 05:00', 'Good morning'],
            'late morning' => ['2026-01-01 11:59', 'Good morning'],
            'noon' => ['2026-01-01 12:00', 'Good afternoon'],
            'late afternoon' => ['2026-01-01 17:59', 'Good afternoon'],
            'evening' => ['2026-01-01 18:00', 'Good evening'],
            'late evening' => ['2026-01-01 21:59', 'Good evening'],
            'night' => ['2026-01-01 22:00', 'Good night'],
            'after midnight' => ['2026-01-01 04:59', 'Good night'],
        ];
    }

    #[Test]
    #[DataProvider('timesOfDay')]
    public function greetsAccordingToTimeOfDay(string $time, string $expectedSalutation): void
    {
        $subject = new GreetingService();

        self::assertSame($expectedSalutation . ', Ada!', $subject->greet('Ada', new \DateTimeImmutable($time)));
    }

    #[Test]
    public function fallsBackToFriendForBlankName(): void
    {
        $subject = new GreetingService();

        self::assertSame('Good morning, friend!', $subject->greet("  \t", new \DateTimeImmutable('2026-01-01 08:00')));
    }

    #[Test]
    public function trimsName(): void
    {
        $subject = new GreetingService();

        self::assertSame('Good evening, Grace!', $subject->greet('  Grace ', new \DateTimeImmutable('2026-01-01 19:00')));
    }
}
