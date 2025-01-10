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
     * @param mixed $connection
     * @return void
     */
    public function put(mixed $connection): void;

    /**
     * Create a connection.
     *
     * @return mixed
     */
    public function createConnection(): mixed;


    /**
     * Close the connection and remove the connection from the connection pool.
     *
     * @param mixed $connection
     * @return void
     */
    public function closeConnection(mixed $connection): void;

}
