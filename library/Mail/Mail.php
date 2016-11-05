<?php
/**
 * This file belongs to the AnoynmFramework
 *
 * @author vahitserifsaglam <vahit.serif119@gmail.com>
 * @see http://gemframework.com
 *
 * Thanks for using
 */

namespace Sagi\Mail;
use Sagi\Database\ConfigManager as Config;
/**
 * the component of mail
 *
 * Class Mail
 * @package Sagi\Mail
 */
class Mail
{

    /**
     * the instance of mail driver
     *
     * @var DriverInterface
     */
    private $driver;

    /**
     * the list of driver list
     *
     * @var array
     */
    private $defaultDriverList;

    /**
     * create a new instance and register the default driver list
     *
     */
    public function __construct()
    {
        $this->defaultDriverList = [
            'swift'     => SwiftMailerDriver::class,
            'phpmailer' => PhpMailerDriver::class
        ];
    }

    /**
     * select a driver
     *
     * @param string $driver the name of driver
     * @param array $configs the configs for driver
     * @throws DriverException
     * @throws DriverNotInstalledException
     * @return DriverInterface if driver name isset in driver list, return the driver instance, else return false
     */
    public function driver($driver = '', array $configs = [])
    {
        $driverList = $this->defaultDriverList;
        if (isset($driverList[$driver])) {
            $driver = $driverList[$driver];
            $driver = new $driver($configs);

            if ($driver instanceof DriverInterface) {
                return $driver;
            } else {
                throw new DriverException(sprintf('your %s driver has not Driver Interface', get_class($driver)));
            }
        } else {
            throw new DriverNotInstalledException(sprintf('your %s driver is not installed', $driver));
        }

    }

    /**
     * add a driver
     *
     * @param string $name the name of driver
     * @param string $class the class path of driver
     * @return $this
     */
    public function add($name, $class)
    {
        $this->defaultDriverList[$name] = $class;
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
     * @return Mail
     */
    public function setDriver(DriverInterface $driver)
    {
        $this->driver = $driver;
        return $this;
    }

    /**
     * send the mail with config name and closure callback
     *
     * @param string $name
     * @param callable $callback
     * @return mixed
     * @throws DriverException
     * @throws DriverNotInstalledException
     */
    public function send($name = '', callable $callback)
    {
        $configs = Config::get($name);
        $driver = $this->driver(isset($configs['driver']) ? $configs['driver']: 'swift', $configs);

        return $callback($driver);
    }
}
