<?php

declare(strict_types=1);

namespace Webman\Coroutine\Channel;

/**
 * Class Memory
 */
class Memory implements ChannelInterface
{

    /**
     * @var array
     */
    protected array $data = [];

    /**
     * Memory constructor.
     *
     * @param int $capacity
     */
    public function __construct(protected int $capacity)
    {
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
        if ($this->length() >= $this->capacity) {
            return false;
        }
        $this->data[] = $data;
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
        if ($this->length() === 0) {
            return false;
        }
        return array_shift($this->data);
    }

    /**
     * Get the length of channel.
     *
     * @return int
     */
    public function length(): int
    {
        return count($this->data);
    }

    /**
     * Get the capacity of channel.
     *
     * @return int
     */
    public function getCapacity(): int
    {
        return $this->capacity;
    }

    /**
     * Close the channel.
     */
    public function close(): void
    {
        $this->data = [];
    }

}
