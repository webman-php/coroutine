<?php

declare(strict_types=1);

namespace Webman\Coroutine;

use Webman\Coroutine\Channel\ChannelInterface;
use Webman\Coroutine\Channel\Memory as ChannelMemory;
use Webman\Coroutine\Channel\Swoole as ChannelSwoole;
use Webman\Coroutine\Channel\Swow as ChannelSwow;
use Workerman\Events\Swoole;
use Workerman\Events\Swow;
use Workerman\Worker;

class Channel implements ChannelInterface
{
    protected ChannelInterface $driver;

    public function __construct(int $capacity)
    {
        $this->driver = match (Worker::$eventLoopClass) {
            Swoole::class => new ChannelSwoole($capacity),
            Swow::class => new ChannelSwow($capacity),
            default => new ChannelMemory($capacity),
        };
    }

    public function push(mixed $data, float $timeout = -1): bool
    {
        return $this->driver->push($data, $timeout);
    }

    public function pop(float $timeout = -1): mixed
    {
        return $this->driver->pop($timeout);
    }

    public function length(): int
    {
        return $this->driver->length();
    }

    public function getCapacity(): int
    {
        return $this->driver->getCapacity();
    }

    public function close(): void
    {
        $this->driver->close();
    }
}
