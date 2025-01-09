<?php

declare(strict_types=1);

namespace Webman\Coroutine\Channel;


class Memory implements ChannelInterface
{

    protected array $data = [];

    public function __construct(protected int $capacity)
    {
    }

    public function push(mixed $data, float $timeout = -1): bool
    {
        if ($this->length() >= $this->capacity) {
            return false;
        }
        $this->data[] = $data;
        return true;
    }

    public function pop(float $timeout = -1): mixed
    {
        if ($this->length() === 0) {
            return false;
        }
        return array_shift($this->data);
    }

    public function length(): int
    {
        return count($this->data);
    }

    public function getCapacity(): int
    {
        return $this->capacity;
    }

    public function close(): void
    {
        $this->data = [];
    }

}
