<?php
namespace Sagi\Database;

use PDOException;
use PDO;
use Sagi\Database\Exceptions\ErrorException;
use Sagi\Database\Interfaces\ConnectionInferface;

class Connector
{
    /**
     * @var PDO
     */
    protected $connection;


    private  $callbacks = [
        'default' => 'buildDefaultConnection',
        'mysql' => 'buildMysqlConnection'
    ];

    /**
     * @param string|null $connection
     */
    public  function madeConnection($connection = null)
    {

        $configs = $this->findConnectionConfig($connection);
        $driver = $configs['driver'];
        $called = $this->callDriver($driver, $configs);


        if (!$called instanceof ConnectionInferface) {
            throw new ConnectionException('%s driver must return an instance of ConnectionInterface', $driver);
        }

        $this->$connection = $called->getConnection();

    }

    /**
     * @param $configs
     * @return Connection
     */
    protected  function buildDefaultConnection($configs)
    {
        try {
            list($username, $password) = $this->getUsernameAndPassword($configs);

            $attributes = isset($configs['attr']) ? $configs['attr'] : [];

            $pdo = $this->preparePdoInstance($configs, $username, $password, $attributes);
        } catch (PDOException $p) {
            throw new PDOException("Something went wrong, message: " . $p->getMessage());
        }


        return new Connection($pdo);
    }

    /**
     * @param $connection
     * @return array
     */
    private  function findConnectionConfig($connection)
    {
        if ($connection === null) {
            $configs = ConfigManager::get('connections.default', 'localhost');
        } else {
            $configs = ConfigManager::get('connections.' . $connection, []);
        }

        return $configs;
    }

    /**
     * @param string $driver
     * @param callable $callback
     * @return Connector
     */
    public  function driver($driver, $callback)
    {
        $this->callbacks[$driver] = $callback;

        return $this;
    }

    /**
     * @param string $driver
     * @param array $configs
     * @return mixed
     */
    private  function callDriver($driver,array $configs){
        $callback = $this->callbacks[$driver];


        if (is_string($callback)) {
            return $this->$callback($configs);
        }

        throw new ErrorException(sprintf('%s driver not found', $driver));
    }
    /**
     * @param array $configs
     * @return Connection
     */
    protected function buildMysqlConnection($configs)
    {
        return $this->buildDefaultConnection($configs);
    }

    /**
     * @param $configs
     * @param $username
     * @param $password
     * @param $attributes
     * @return PDO
     */
    private  function preparePdoInstance($configs, $username, $password, $attributes)
    {
        $pdo = new PDO($configs['dsn'], $username, $password, $attributes);
        $command = sprintf('SET CHARACTER SET %s',
            isset($configs['charset']) ? $configs['charset'] : 'utf8'
        );

        $pdo->exec($command);

        return $pdo;
    }

    /**
     * @param array $configs
     * @return array
     */
    private  function getUsernameAndPassword($configs)
    {

        $username = isset($configs['username']) ? $configs['username'] : null;
        $password = isset($configs['password']) ? $configs['password'] : null;

        return array($username, $password);
    }


    /**
     *
     * @param string $database
     * @return PDO
     */
    public  function getConnection($database = null)
    {
        if (!$this->connection) {
            $this->madeConnection($database);
        }

        return $this->connection;
    }

   
}
