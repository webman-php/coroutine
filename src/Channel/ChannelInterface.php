<?php

declare(strict_types=1);

namespace Webman\Coroutine\Channel;

interface ChannelInterface
{
    public function push(mixed $data, float $timeout = -1): bool;

    public function pop(float $timeout = -1): mixed;

    public function length(): int;

    public function getCapacity(): int;

    public function close(): void;

}
