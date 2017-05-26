<?php
namespace Sagi\Database\Extension;

use Sagi\Database\Driver\DriverManager;
use Sagi\Database\Extension\Interfaces\ExtensionInterface;

class Extension
{

    /**
     * @var DriverManager
     */
    private $manager;

    /**
     * BuilderExtension constructor.
     * @param DriverManager $manager
     */
    public function __construct(DriverManager $manager)
    {
        $this->manager = $manager;
    }

    /**
     * @param string $name
     * @return \Sagi\Database\Driver\Driver
     */
    public function connector($name)
    {
        return $this->manager->driver('connector')->name($name);
    }

    /**
     * @param string $name the name of driver
     * @return \Sagi\Database\Driver\Driver
     */
    public function create($name)
    {
        return $this->manager
            ->driver('create')
            ->name($name);
    }

    /**
     * @param string $name the name of driver
     * @return \Sagi\Database\Driver\Driver
     */
    public function blueprint($name)
    {
        return $this->manager
            ->driver('blueprint')
            ->name($name);
    }

}
