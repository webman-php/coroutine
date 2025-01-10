<?php

declare(strict_types=1);

namespace Webman\Coroutine\Channel;

use Swow\Channel;
use Throwable;

/**
 * Class Swow
 */
class Swow implements ChannelInterface
{

    /**
     * @var Channel
     */
    protected Channel $channel;

    /**
     * Swow constructor.
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
        try {
            $this->channel->push($data, $timeout === -1 ? -1 : (int)$timeout * 1000);
        } catch (Throwable $e) {
            return false;
        }
        return true;
    }

    /**
     * Pop data from channel.
     *
     * @param float $timeout
     * @return mixed
     */
    public function pop(float $timeout = -1): mixed
    {
        try {
            return $this->channel->pop($timeout === -1 ? -1 : (int)$timeout * 1000);
        } catch (Throwable $e) {
            return false;
        }
    }

    /**
     * Get the length of channel.
     *
     * @return int
     */
    public function length(): int
    {
        return $this->channel->getLength();
    }

    /**
     * Get the capacity of channel.
     *
     * @return int
     */
    public function getCapacity(): int
    {
        return $this->channel->getCapacity();
    }

    /**
     * Close the channel.
     */
    public function close(): void
    {
        $this->channel->close();
    }

}
