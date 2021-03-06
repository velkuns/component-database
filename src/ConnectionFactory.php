<?php

/*
 * Copyright (c) Romain Cottard
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Eureka\Component\Database;

use Eureka\Component\Database\Exception\UnknownConfigurationException;

/**
 * Class ConnectionHandler
 *
 * @author  Romain Cottard
 */
class ConnectionFactory
{
    /** @var Connection[] $connections */
    private static array $connections = [];

    /** @var array $configs */
    private array $configs;

    /**
     * Class constructor.
     *
     * @param array $configs
     */
    public function __construct(array $configs)
    {
        $this->configs = $configs;
    }

    /**
     * @return void
     *
     * @codeCoverageIgnore
     */
    public function __destroy()
    {
        foreach (self::$connections as $name => $connection) {
            unset(self::$connections[$name]);
        }

        self::$connections = [];
    }

    /**
     * @param string $name
     * @param bool $forceReconnection
     * @return Connection
     *
     * @codeCoverageIgnore
     */
    public function getConnection(string $name, bool $forceReconnection = false): Connection
    {
        if (!isset($this->configs[$name])) {
            throw new UnknownConfigurationException('Configuration with name "' . $name . '" is unknown', 1000);
        }

        //~ Force unset to close existing connection & destroy instance if required
        if ($forceReconnection) {
            $this->closeConnection($name);
        }

        //~ Connection already set & id alive
        if (isset(self::$connections[$name])) {
            return self::$connections[$name];
        }

        //~ Create & store connection
        self::$connections[$name] = new Connection(
            $this->configs[$name]['dsn'],
            $this->configs[$name]['username'] ?? null,
            $this->configs[$name]['password'] ?? null,
            $this->configs[$name]['options'] ?? null,
            $name,
        );

        return self::$connections[$name];
    }

    /**
     * @param string $name
     * @return ConnectionFactory
     *
     * @codeCoverageIgnore
     */
    public function closeConnection(string $name): ConnectionFactory
    {
        //~ Connection already set & id alive
        if (isset(self::$connections[$name])) {
            unset(self::$connections[$name]);
        }

        return $this;
    }
}
