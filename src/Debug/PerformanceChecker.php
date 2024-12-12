<?php

declare(strict_types=1);

namespace Temkaa\Container\Debug;

use function array_key_last;
use function array_map;
use function array_sum;
use function array_values;
use function microtime;

/**
 * @internal
 * @psalm-suppress all
 * @codeCoverageIgnore
 */
final class PerformanceChecker
{
    private array $events = [];

    private array $startedEvents = [];

    public function end(string $eventName): void
    {
        $end = microtime(true);

        $events = $this->startedEvents[$eventName];

        $lastIndex = array_key_last($events);
        $lastEvent = $events[$lastIndex];
        $lastEvent['end'] = $end;

        unset($events[$lastIndex]);
        $events = array_values($events);

        if (!$events) {
            unset($this->startedEvents[$eventName]);
        } else {
            $this->startedEvents[$eventName] = $events;
        }

        $this->events[$eventName] ??= [];
        $this->events[$eventName][] = $lastEvent;
    }

    public function print(string $eventName): void
    {
        $consumed = array_sum(
            array_map(static fn (array $event): float => $event['end'] - $event['start'], $this->events[$eventName]),
        );

        echo "Event: [$eventName], consumed: $consumed\n";
    }

    public function start(string $eventName): void
    {
        $this->startedEvents[$eventName] ??= [];
        $this->startedEvents[$eventName][] = ['name' => $eventName, 'start' => microtime(true)];
    }
}
