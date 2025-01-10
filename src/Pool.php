<?php

declare(strict_types=1);

namespace Webman\Coroutine;

use Closure;
use Psr\Log\LoggerInterface;
use RuntimeException;
use support\Log;
use Throwable;
use WeakMap;
use Workerman\Timer;

/**
 * Class Pool
 */
class Pool implements PoolInterface
{
    /**
     * @var Channel
     */
    protected Channel $channel;

    /**
     * @var int
     */
    protected int $currentConnections = 0;

    /**
     * @var int
     */
    protected int $minConnections = 1;

    /**
     * @var WeakMap
     */
    protected WeakMap $lastUsedTimes;

    /**
     * @var WeakMap
     */
    protected WeakMap $lastHeartbeatTimes;

    /**
     * @var Closure|null
     */
    protected ?Closure $connectionCreateHandler = null;

    /**
     * @var Closure|null
     */
    protected ?Closure $connectionDestroyHandler = null;

    /**
     * @var Closure|null
     */
    protected ?Closure $connectionHeartbeatHandler = null;

    /**
     * @var float
     */
    protected float $idleTimeout = 60;

    /**
     * @var float
     */
    protected float $heartbeatInterval = 50;

    /**
     * @var float
     */
    protected float $waitTimeout = 10;

    /**
     * @var LoggerInterface|null
     */
    protected ?LoggerInterface $logger = null;

    /**
     * Constructor.
     *
     * @param int $maxConnections
     * @param array $config
     */
    public function __construct(protected int $maxConnections = 1, protected array $config = [])
    {
        foreach ($config as $key => $value) {
            $camelCaseKey = lcfirst(str_replace(' ', '', ucwords(str_replace('_', ' ', $key))));
            if (property_exists($this, $camelCaseKey)) {
                $this->$camelCaseKey = $value;
            }
        }

        $this->channel = new Channel($maxConnections);
        $this->lastUsedTimes = new WeakMap();
        $this->lastHeartbeatTimes = new WeakMap();

        Timer::repeat(1, function () {
            $this->checkConnections();
        });
    }

    /**
     * Set the connection creator.
     *
     * @param Closure $connectionCreateHandler
     * @return $this
     */
    public function setConnectionCreator(Closure $connectionCreateHandler): self
    {
        $this->connectionCreateHandler = $connectionCreateHandler;
        return $this;
    }

    /**
     * Set the connection closer.
     *
     * @param Closure $connectionDestroyHandler
     * @return $this
     */
    public function setConnectionCloser(Closure $connectionDestroyHandler): self
    {
        $this->connectionDestroyHandler = $connectionDestroyHandler;
        return $this;
    }

    /**
     * Set the connection heartbeat checker.
     *
     * @param Closure $connectionHeartbeatHandler
     * @return $this
     */
    public function setHeartbeatChecker(Closure $connectionHeartbeatHandler): self
    {
        $this->connectionHeartbeatHandler = $connectionHeartbeatHandler;
        return $this;
    }

    /**
     * Get connection.
     *
     * @return mixed
     * @throws Throwable
     */
    public function get(): mixed
    {
        $num = $this->channel->length();
        if ($num === 0 && $this->currentConnections < $this->maxConnections) {
            $this->createConnection();
        }
        $connection = $this->channel->pop($this->waitTimeout);
        if (!$connection) {
            throw new RuntimeException("Connection pool exhausted and unable to acquire a connection within wait timeout($this->waitTimeout seconds).");
        }
        $this->lastUsedTimes[$connection] = time();
        return $connection;
    }

    /**
     * Put connection to pool.
     *
     * @param $connection
     * @return void
     * @throws Throwable
     */
    public function put($connection): void
    {
        $this->checkValidateConnection($connection);

        // This connection does not belong to the connection pool.
        // It may have been closed by $this->closeConnection($connection).
        if (!isset($this->lastUsedTimes[$connection])) {
            throw new RuntimeException('The connection does not belong to the connection pool.');
        }
        try {
            $this->channel->push($connection);
        } catch (Throwable $throwable) {
            $this->closeConnection($connection);
            throw $throwable;
        }
    }

    /**
     * Check if the connection is valid.
     *
     * @param $connection
     * @return bool
     */
    protected function isValidateConnection($connection): bool
    {
        return is_object($connection);
    }

    /**
     * Check if the connection is valid.
     *
     * @param $connection
     * @return void
     * @throws RuntimeException
     */
    protected function checkValidateConnection($connection): void
    {
        if (!$this->isValidateConnection($connection)) {
            throw new RuntimeException('The connection is invalid. Expected a valid connection object, but received a ' . gettype($connection) . '.');
        }
    }

    /**
     * Create connection.
     *
     * @return mixed
     * @throws Throwable
     */
    public function createConnection(): mixed
    {
        try {
            ++$this->currentConnections;
            $connection = call_user_func($this->connectionCreateHandler);
            $this->checkValidateConnection($connection);
            $this->channel->push($connection);
            $this->lastUsedTimes[$connection] = $this->lastHeartbeatTimes[$connection] = time();
        } catch (Throwable $throwable) {
            --$this->currentConnections;
            throw $throwable;
        }
        return $connection;
    }

    /**
     * Close the connection and remove the connection from the connection pool.
     *
     * @param mixed $connection
     * @return void
     */
    public function closeConnection(mixed $connection): void
    {
        if (!is_object($connection)) {
            return;
        }
        --$this->currentConnections;
        // Mark this connection as no longer belonging to the connection pool.
        unset($this->lastUsedTimes[$connection]);
        try {
            if (!$this->connectionDestroyHandler) {
                foreach (['close', 'disconnect', 'release', 'destroy', 'free'] as $method) {
                    if (method_exists($connection, $method)) {
                        $connection->$method();
                        return;
                    }
                }
                return;
            }
            call_user_func($this->connectionDestroyHandler, $connection);
        } catch (Throwable $throwable) {
            $this->log($throwable);
        }
    }
    

    /**
     * Cleanup idle connections.
     *
     * @return void
     */
    protected function checkConnections(): void
    {
        $num = $this->channel->length();
        $time = time();
        for($i = $num; $i > 0; $i--) {
            $connection = $this->channel->pop(0.001);
            if (!$connection) {
                return;
            }
            $lastUsedTime = $this->lastUsedTimes[$connection];
            if ($time - $lastUsedTime > $this->idleTimeout && $this->channel->length() >= $this->minConnections) {
                $this->closeConnection($connection);
                continue;
            }
            $lastHeartbeatTime = $this->lastHeartbeatTimes[$connection];
            if ($this->connectionHeartbeatHandler && $time - $lastHeartbeatTime >= $this->heartbeatInterval) {
                try {
                    call_user_func($this->connectionHeartbeatHandler, $connection);
                    $this->lastHeartbeatTimes[$connection] = time();
                } catch (Throwable $throwable) {
                    $this->log($throwable);
                    $this->closeConnection($connection);
                    continue;
                }
            }
            $this->channel->push($connection);
        }
    }

    /**
     * Log.
     *
     * @param $message
     * @return void
     */
    protected function log($message): void
    {
        if (!$this->logger) {
            $this->logger = Log::channel();
        }
        $this->logger->info((string)$message);
    }
}

