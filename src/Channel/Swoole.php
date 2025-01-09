<?php

declare(strict_types=1);

namespace Webman\Coroutine\Channel;

use Swoole\Coroutine\Channel;

class Swoole implements ChannelInterface
{
    protected Channel $channel;

    public function __construct(protected int $capacity)
    {
        $this->channel = new Channel($capacity);
    }

    public function push(mixed $data, float $timeout = -1): bool
    {
        return $this->channel->push($data, $timeout);
    }

    public function pop(float $timeout = -1): mixed
    {
        return $this->channel->pop($timeout);
    }

    public function length(): int
    {
        return $this->channel->length();
    }

    public function getCapacity(): int
    {
        return $this->channel->capacity;
    }

    public function close(): void
    {
        $this->channel->close();
    }

}
