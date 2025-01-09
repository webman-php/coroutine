<?php

declare(strict_types=1);

namespace Webman\Coroutine;

interface PoolInterface
{

    public function get(): mixed;


    public function put(mixed $connection): void;


    public function createConnection(): mixed;


    public function closeConnection(mixed $connection): void;

}
