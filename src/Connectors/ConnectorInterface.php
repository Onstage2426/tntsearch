<?php

namespace TeamTNT\TNTSearch\Connectors;

use PDO;

interface ConnectorInterface
{
    /**
     * Establish a database connection.
     *
     * @param array $config
     *
     * @return \PDO|null
     */
    public function connect(array $config): ?PDO;
}
