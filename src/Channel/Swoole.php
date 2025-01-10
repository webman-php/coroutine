<?php

declare(strict_types=1);

namespace Webman\Coroutine\Channel;

use Swoole\Coroutine\Channel;

/**
 * Class Swoole
 */
class Swoole implements ChannelInterface
{

    /**
     * @var Channel
     */
    protected Channel $channel;

    /**
     * Swoole constructor.
     *
     * @param int $capacity
     */
    public function __construct(protected int $capacity)
    {
        $this->channel = new Channel($capacity);
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
        return $this->channel->push($data, $timeout);
    }

    /**
     * Pop data from channel.
     *
     * @param float $timeout
     * @return mixed
     */
    public function pop(float $timeout = -1): mixed
    {
        return $this->channel->pop($timeout);
    }

    /**
     * Get the length of channel.
     *
     * @return int
     */
    public function length(): int
    {
        return $this->channel->length();
    }

    /**
     * Get the capacity of channel.
     *
     * @return int
     */
    public function getCapacity(): int
    {
        return $this->channel->capacity;
    }

    /**
     * Close the channel.
     *
     * @return void
     */
    public function close(): void
    {
        $this->channel->close();
    }

}
