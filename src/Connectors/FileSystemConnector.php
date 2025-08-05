<?php

namespace TeamTNT\TNTSearch\Connectors;

class FileSystemConnector extends Connector implements ConnectorInterface
{
    /**
     * Establish a database connection.
     *
     * @param array $config
     *
     * @throws \InvalidArgumentException
     *
     * @return null
     */
    public function connect(array $config): null
    {
        return null;
    }
}
