<?php

/**
 * Created by PhpStorm.
 * User: vahit
 * Date: 24.05.2017
 * Time: 15:12
 */
class DriverTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Sagi\Database\Driver\DriverManager
     */
    private $driver;

    protected function tearDown()
    {
        $this->driver = null;
    }

    protected function setUp()
    {
        $this->driver = new \Sagi\Database\Driver\DriverManager();
    }

    public function testAddAndReturn(){
        $added = $this->driver->driver('executor');

        $this->assertInstanceOf(\Sagi\Database\Driver\Driver::class, $added);
    }

    /**
     * @dataProvider addDriver
     */
    public function testResolveDriverDoestNotExists($driver)
    {
        $this->expectException(\Sagi\Database\Exceptions\DriverNotFoundException::class);

        $mock = new \Sagi\Database\Repositories\ParameterRepository(
            array(
                'test' => 1
            )
        );


        $driver->resolve('executor', 'pdoa', $mock);
    }

    /**
     * @dataProvider addDriver
     */
    public function testDriverAdd($added)
    {

        $this->assertInstanceOf(
            \Sagi\Database\Driver\DriverManager::class,
            $added
        );

        return $added;
    }


    public function addDriver(){
        $expectation =
            new \Sagi\Database\Driver\Expectation\ExpectInstanceOf(
                \Sagi\Database\Executor\Interfaces\DriverInterface::class
            );

        $manager = new \Sagi\Database\Driver\DriverManager();

        $driver = $manager->driver(
            'executor'
        );

        $driver->setName('pdo')
            ->setExpect($expectation)
            ->setCallback(function(){

            });

      return  array(
          array($manager->add($driver))
      );
    }

    /**
     * @dataProvider  addDriver
     */
    public function testResolvedDriverNotAsExpectation($driver){


        $this->expectException(\Sagi\Database\Exceptions\DriverIsNotExpectedException::class);

        $mock = new \Sagi\Database\Repositories\ParameterRepository(
            array(
                'test' => 1
            )
        );

        $driver->resolve('executor', 'pdo', $mock);
    }


    public function testResolvedDriverAsExpected(\Sagi\Database\Driver\DriverManager $manager, $connection)
    {
        $driver = $manager->driver('executor');

        $driver->setName('pdo')
            ->setExpect(
                new \Sagi\Database\Driver\Expectation\ExpectInstanceOf(
                    \Sagi\Database\Executor\Interfaces\DriverInterface::class
                )
            )
            ->setCallback(
                new \Sagi\Database\Connection\Drivers\PdoDriver($connection)
            );

        $manager->add($driver);


    }
}
