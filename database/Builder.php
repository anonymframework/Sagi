<?php

namespace Sagi\Database;

use League\Pipeline\PipelineBuilder;
use Sagi\Database\Driver\Connection\Interfaces\DriverInterface;
use Sagi\Database\Driver\DriverManager;
use Sagi\Database\Driver\Expectation\ExpectInstanceOf;
use Sagi\Database\Exceptions\ExtensionNotAsExpectedException;
use Sagi\Database\Exceptions\ExtensionNotFoundException;
use Sagi\Database\Driver\Grammer\Sql\SqlReaderGrammerInterface;
use Sagi\Database\Extension\Interfaces\ExtensionInterface;
use Sagi\Database\Mapping\Join;
use Sagi\Database\Mapping\Limit;
use Sagi\Database\Mapping\SubWhere;
use Sagi\Database\Mapping\Where;
use Sagi\Database\Interfaces\ConnectorInterface;

class Builder
{
    /**
     * @var select query
     */
    private $select;

    /**
     * @var string
     */
    private $table;

    /**
     *
     * @var array
     */
    private $limit;

    /**
     * @var string
     */
    private $groupBy;

    /**
     * where query
     *
     * @var array
     */
    protected $where = [];

    /**
     * @var array
     */
    private $order;

    /**
     * @var array
     */
    private $join = [];

    /**
     * @var string
     */
    private $having = '';

    /**
     * @var array
     */
    private $args = [];


    /**
     * @var string
     */
    private $as;

    /**
     * @var array
     */
    protected static $operators = [
        '=' => 'equal',
        '>' => 'bigger',
        '<' => 'smaller',
        '!=' => 'diffrent',
        '>=' => 'ebigger',
        '=<' => 'esmaller',
        'IN' => 'in',
        'NOT IN' => 'notin',
    ];

    /**
     * @var SqlReaderGrammerInterface;
     */
    private $grammer;

    /**
     * @var bool
     */
    private $connected = false;

    /**
     * @var ConnectorInterface
     */
    private $connector;


    /**
     * @var DriverInterface
     */
    private $driver;

    /**
     * @var DriverManager
     */
    private $driverManager;


    public function __construct($table = null)
    {
        $this->setTable($table);

        $this
            ->getDriverManager()
            ->expect(
                'connector',
                new ExpectInstanceOf('Sagi\Database\Interfaces\ConnectorInterface')
            );
    }

    public function installExtensions($extensions)
    {

        $builder = new PipelineBuilder();

        foreach ($extensions as $extension) {

            $this->checkExtensionExists($extension);
            $extension = new $extension;
            $this->determineExtensionIsAsExpected($extension);
            $builder->add(
                [
                    $extension,
                    'install',
                ]
            );

        }

        return $builder
            ->build()
            ->process(
                $this->getDriverManager()
            );

    }

    /**
     * @param object $extension
     * @throws ExtensionNotAsExpectedException
     */
    private function determineExtensionIsAsExpected($extension)
    {
        if ( ! $extension instanceof ExtensionInterface) {
            throw new ExtensionNotAsExpectedException(
                sprintf(
                    '%s extension is not as expected',
                    get_class($extension)
                )
            );
        }
    }

    /**
     * @param string $extension
     * @throws ExtensionNotFoundException
     */
    private function checkExtensionExists($extension)
    {
        if ( ! class_exists($extension, true)) {
            throw new ExtensionNotFoundException(
                sprintf(
                    '%s extension is not found',
                    $extension
                )
            );
        }
    }

    /**
     * @return DriverManager
     */
    public function getDriverManager()
    {
        if (null === $this->driverManager) {
            $this->driverManager = new DriverManager();
        }

        return $this->driverManager;
    }

    /**
     * @param DriverManager $driverManager
     * @return Builder
     */
    public function setDriverManager($driverManager)
    {
        $this->driverManager = $driverManager;

        return $this;
    }

    /**
     * @param string|null $db
     * @return $this
     */
    public function connect($db = null)
    {
        if ( ! $this->isConnected()) {
            $connector = $this->getConnector();

            $this->connected = true;
        }

        $this->driver = $connector->connect($db);

        return $this;
    }

    /**
     * @return DriverInterface
     */
    public function getDriver()
    {
        return $this->driver;
    }

    /**
     * @param DriverInterface $driver
     * @return Builder
     */
    public function setDriver(DriverInterface $driver)
    {
        $this->driver = $driver;

        return $this;
    }

    /**
     * @param string $driver
     * @return SqlReaderGrammerInterface
     */
    public function getGrammer($driver = 'standart')
    {
        return $this
            ->getConnector()
            ->$driver();
    }


    /**
     * @return string
     */
    public function getTable()
    {
        return $this->table;
    }

    /**
     * @param string $table
     * @return Builder
     */
    public function setTable($table)
    {
        $this->table = $table;

        return $this;
    }


    public function getSelect()
    {
        return $this->select;
    }

    /**
     * @param select $select
     * @return QueryBuilder
     */
    public function setSelect($select)
    {
        $this->select = $select;

        return $this;
    }


    /**
     * @param Where|SubWhere $where
     * @param string|null $mark
     * @return $this
     */
    public function addWhere($where, $mark = null)
    {

        if (null === $mark) {
            $this->where[] = $where;
        } else {
            $this->where[$mark] = $where;
        }

        return $this;
    }


    /**
     * @return array
     */
    public function getLimit()
    {
        return $this->limit;
    }

    /**
     * @param Limit $limit
     * @return Builder
     */
    public function setLimit(Limit $limit)
    {
        $this->limit = $limit;

        return $this;
    }

    /**
     * @return string
     */
    public function getGroupBy()
    {
        return $this->groupBy;
    }

    /**
     * @param string $groupBy
     * @return Builder
     */
    public function setGroupBy($groupBy)
    {
        $this->groupBy = $groupBy;

        return $this;
    }

    /**
     * @return bool
     */
    public function isConnected()
    {
        return $this->connected;
    }

    /**
     * @param bool $connected
     * @return Builder
     */
    public function setConnected($connected)
    {
        $this->connected = $connected;

        return $this;
    }

    /**
     * @return ConnectorInterface
     */
    public function getConnector()
    {
        if (null === $this->connector) {
            $this->connector = new Connector(
                $this->getDriverManager()
            );
        }

        return $this->connector;
    }

    /**
     * @param ConnectorInterface $connector
     * @return Builder
     */
    public function setConnector($connector)
    {
        $this->connector = $connector;

        return $this;
    }

    /**
     * @return array
     */
    public function getWhere()
    {
        return $this->where;
    }

    /**
     * @param array $where
     * @return Builder
     */
    public function setWhere($where)
    {
        $this->where = $where;

        return $this;
    }

    /**
     * @return array
     */
    public function getOrder()
    {
        return $this->order;
    }

    /**
     * @param array $order
     * @return Builder
     */
    public function setOrder($order)
    {
        $this->order = $order;

        return $this;
    }

    /**
     * @return array
     */
    public function getJoin()
    {
        return $this->join;
    }

    /**
     * @param array $join
     * @return Builder
     */
    public function setJoin($join)
    {
        $this->join = $join;

        return $this;
    }

    /**
     * @return string
     */
    public function getHaving()
    {
        return $this->having;
    }

    /**
     * @param Join $join
     * @return $this
     */
    public function addJoin(Join $join)
    {
        $this->join[] = $join;

        return $this;
    }

    /**
     * @param string $having
     * @return Builder
     */
    public function setHaving($having)
    {
        $this->having = $having;

        return $this;
    }


    public function hasAs()
    {
        return ! empty($this->as);
    }


    /**
     * @return array
     */
    public function getArgs()
    {
        return $this->args;
    }

    /**
     * @param array $args
     * @return Builder
     */
    public function setArgs($args)
    {
        $this->args = $args;

        return $this;
    }

    /**
     * @return string
     */
    public function getAs()
    {
        return $this->as;
    }

    /**
     * @param string $as
     * @return Builder
     */
    public function setAs($as)
    {
        $this->as = $as;

        return $this;
    }

    /**
     * @return array
     */
    public static function getOperators()
    {
        return self::$operators;
    }

    /**
     * @param array $operators
     */
    public static function setOperators($operators)
    {
        self::$operators = $operators;
    }

    /**
     * @return string
     */
    public static function getClassName()
    {
        return get_called_class();
    }

}

