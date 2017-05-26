<?php

namespace Sagi\Database;

use PDO;
use Sagi\Database\Driver\DriverManager;
use Sagi\Database\Exceptions\ConnectionException;
use Sagi\Database\Exceptions\DriverNotFoundException;
use Sagi\Database\Interfaces\ConnectorInterface;

class Connector
{

    /**
     * @var string
     */
    protected $db;
    /**
     * @var PDO
     *
     */
    protected $connection;

    /**
     * @var DriverManager
     */
    protected $driverManager;

    /**
     * Connector constructor.
     * @param DriverManager $driverManager
     * @param null $db
     */
    public function __construct(DriverManager $driverManager, $db = null)
    {
        $this->driverManager = $driverManager;
        $this->db = $db;
    }

    /**
     * @param string $connection
     */
    public function connect(){
        $configs = $this->findConnectionConfig(
            $this->db
        );

        $driver = $configs['driver'];

        $driver = $this->driverManager->resolve('connector', $driver);

        return $driver->connect($driver, $configs);
    }

    /**
     * @param string $db
     * @return mixed
     */
    private function findDriver()
    {
        $configs = $this->findConnectionConfig(
            $this->db
        );
        $driver = $configs['driver'];

        return $driver;
    }

    public function grammer()
    {

    }


    /**
     * @return mixed
     * @throws \Sagi\Database\Exceptions\DriverNotFoundException
     */
    public function create()
    {
        $driver = $this->findDriver(
            $this->db
        );

        return $this->driverManager
            ->resolve('create', $driver);
    }

    /**
     * @return mixed
     * @throws \Sagi\Database\Exceptions\DriverNotFoundException
     */
    public function blueprint()
    {
        $driver = $this->findDriver(
            $this->db
        );

        return $this->driverManager
            ->resolve('blueprint', $driver);
    }

    /**
     * @return mixed
     * @throws \Sagi\Database\Exceptions\DriverNotFoundException
     */
    public function modify()
    {
        $driver = $this->findDriver(
            $this->db
        );

        return $this->driverManager
            ->resolve('modify', $driver);
    }


    /**
     * @param $connection
     * @return array
     */
    private function findConnectionConfig($connection)
    {
        if ($connection === null) {
            $configs = Config::get('connections.default', 'localhost');
        } else {
            $configs = Config::get('connections.' . $connection, []);
        }


        return $configs;
    }
}
