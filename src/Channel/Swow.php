<?php

declare(strict_types=1);

namespace Webman\Coroutine\Channel;

use Swow\Channel;
use Throwable;

class Swow implements ChannelInterface
{
    protected Channel $channel;

    public function __construct(protected int $capacity)
    {
        $this->channel = new Channel($capacity);
    }

    public function push(mixed $data, float $timeout = -1): bool
    {
        try {
            $this->channel->push($data, $timeout === -1 ? -1 : (int)$timeout * 1000);
        } catch (Throwable $e) {
            return false;
        }
        return true;
    }

    public function pop(float $timeout = -1): mixed
    {
        try {
            return $this->channel->pop($timeout === -1 ? -1 : (int)$timeout * 1000);
        } catch (Throwable $e) {
            return false;
        }
    }

    public function length(): int
    {
        return $this->channel->getLength();
    }

    public function getCapacity(): int
    {
        return $this->channel->getCapacity();
    }

    public function close(): void
    {
        $this->channel->close();
    }

}
