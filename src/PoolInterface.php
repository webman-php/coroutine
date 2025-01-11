<?php

declare(strict_types=1);

namespace Webman\Coroutine;

interface PoolInterface
{

    /**
     * Get a connection from the pool.
     *
     * @return mixed
     */
    public function get(): mixed;

    /**
     * Put a connection back to the pool.
     *
     * @param object $connection
     * @return void
     */
    public function put(object $connection): void;

    /**
     * Create a connection.
     *
     * @return object
     */
    public function createConnection(): object;

    /**
     * Close the connection and remove the connection from the connection pool.
     *
     * @param mixed $connection
     * @return void
     */
    public function closeConnection(object $connection): void;

}
