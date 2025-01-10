<?php

declare(strict_types=1);

namespace Webman\Coroutine\Channel;

/**
 * ChannelInterface
 */
interface ChannelInterface
{
    /**
     * Push data to channel.
     *
     * @param mixed $data
     * @param float $timeout
     * @return bool
     */
    public function push(mixed $data, float $timeout = -1): bool;

    /**
     * Pop data from channel.
     *
     * @param float $timeout
     * @return mixed
     */
    public function pop(float $timeout = -1): mixed;

    /**
     * Get the length of channel.
     *
     * @return int
     */
    public function length(): int;

    /**
     * Get the capacity of channel.
     *
     * @return int
     */
    public function getCapacity(): int;

    /**
     * Close the channel.
     *
     * @return void
     */
    public function close(): void;

}
