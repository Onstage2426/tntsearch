<?php

namespace TeamTNT\TNTSearch\Connectors;

use PDO;

class SqlServerConnector extends Connector implements ConnectorInterface
{
    /**
     * The PDO connection options.
     *
     * @var array
     */
    protected array $options = [
        PDO::ATTR_CASE => PDO::CASE_NATURAL,
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_ORACLE_NULLS => PDO::NULL_NATURAL,
        PDO::ATTR_STRINGIFY_FETCHES => false,
    ];

    /**
     * Establish a database connection.
     *
     * @param  array  $config
     * @return PDO
     */
    public function connect(array $config)
    {
        return $this->createConnection(
            $this->getDsn($config),
            $config,
            $this->getOptions($config),
        );
    }

    /**
     * Create a DSN string from a configuration.
     *
     * @param  array   $config
     * @return string
     */
    protected function getDsn(array $config): ?string
    {
        $host = $config["host"] ?? "localhost";
        $port = $config["port"] ?? "1433";
        $database = $config["database"] ?? "";

        $availableDrivers = $this->getAvailableDrivers();

        if (
            !in_array("sqlsrv", $availableDrivers) &&
            in_array("dblib", $availableDrivers)
        ) {
            return "dblib:host={$host}:{$port};dbname={$database}";
        }

        return "sqlsrv:Server={$host},{$port};Database={$database}";
    }

    /**
     * Get the available PDO drivers.
     *
     * @return array
     */
    protected function getAvailableDrivers()
    {
        return PDO::getAvailableDrivers();
    }
}
