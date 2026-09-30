<?php

declare(strict_types=1);

namespace Playground\Demo\Service;

/**
 * Builds a time-of-day dependent greeting. Pure and clock-free: the caller
 * passes the point in time, which keeps it trivially unit-testable.
 */
final class GreetingService
{
    private const FALLBACK_NAME = 'friend';

    public function greet(string $name, \DateTimeInterface $now): string
    {
        $name = trim($name);

        return sprintf('%s, %s!', $this->salutation((int)$now->format('G')), $name !== '' ? $name : self::FALLBACK_NAME);
    }

    private function salutation(int $hour): string
    {
        return match (true) {
            $hour >= 5 && $hour < 12 => 'Good morning',
            $hour >= 12 && $hour < 18 => 'Good afternoon',
            $hour >= 18 && $hour < 22 => 'Good evening',
            default => 'Good night',
        };
    }
}
