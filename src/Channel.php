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

/**
 * Class Channel
 */
class Channel implements ChannelInterface
{

    /**
     * @var ChannelInterface
     */
    protected ChannelInterface $driver;

    /**
     * Channel constructor.
     *
     * @param int $capacity
     */
    public function __construct(int $capacity)
    {
        $this->driver = match (Worker::$eventLoopClass) {
            Swoole::class => new ChannelSwoole($capacity),
            Swow::class => new ChannelSwow($capacity),
            default => new ChannelMemory($capacity),
        };
    }

    /**
     * Push data to channel.
     *
     * @param mixed $data
     * @param float $timeout
     * @return bool
     */
    public function push(mixed $data, float $timeout = -1): bool
    {
        return $this->driver->push($data, $timeout);
    }

    /**
     * Pop data from channel.
     *
     * @param float $timeout
     * @return mixed
     */
    public function pop(float $timeout = -1): mixed
    {
        return $this->driver->pop($timeout);
    }

    /**
     * Get length of channel.
     *
     * @return int
     */
    public function length(): int
    {
        return $this->driver->length();
    }

    /**
     * Get capacity of channel.
     *
     * @return int
     */
    public function getCapacity(): int
    {
        return $this->driver->getCapacity();
    }

    /**
     * Close channel.
     *
     * @return void
     */
    public function close(): void
    {
        $this->driver->close();
    }
}
