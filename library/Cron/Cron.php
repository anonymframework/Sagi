<?php
/**
 * This file belongs to the AnoynmFramework
 *
 * @author vahitserifsaglam <vahit.serif119@gmail.com>
 * @see http://gemframework.com
 *
 * Thanks for using
 */


namespace Sagi\Cron;

use Sagi\Cron\Task\TaskReposity;
use Closure;

/**
 * Class Cron
 * @package Sagi\Cron
 */
class Cron
{

    /**
     * the cache for events
     *
     * @var array
     */
    protected $cache;

    /**
     * the instance of BasicCron Class
     *
     * @var BasicCron
     */
    protected $basic;

    /**
     * create a new instance and install the cron job of php anonym schedule:run command
     *
     */
    public function __construct()
    {

        $this->setBasic(new BasicCron());
    }

    /**
     * install the default cron
     *
     *
     * @param TaskReposity $job
     */
    public function install(TaskReposity $job){

        // install the default schedule
        if (!$this->getBasic()->jobExists($job->buildCommandWithExpression())) {
            $this->getBasic()->event(
                function () use ($job) {
                    return $job;
                }
            );

            $this->getBasic()->run();
        }
    }

    /**
     *  remove a job on crontab
     *
     * @param string $job
     * @return $this
     */
    public function removeJob($job = ''){
        $this->getBasic()->removeJob($job);
        return $this;
    }

    /**
     * clean the saved crontabs
     *
     * @return $this
     */
    public function clean(){
        $this->getBasic()->clean();
        return $this;
    }
    /**
     * @return BasicCron
     */
    public function getBasic()
    {
        return $this->basic;
    }

    /**
     * @param BasicCron $basic
     * @return Cron
     */
    public function setBasic(BasicCron $basic)
    {
        $this->basic = $basic;
        return $this;
    }


    /**
     * add a new event to reposity
     *
     * Note: $command must be a closure
     *
     * @param Closure $command
     */
    public function event(Closure $command)
    {
        $response = $command();

        if ($this->resolveCommandResponse($response)) {
            EventReposity::add($response);
        }
    }

    /**
     * resolve the response
     *
     * @param mixed $response
     * @return bool
     */
    private function resolveCommandResponse($response)
    {
        return ($response !== null & $response instanceof TaskReposity) ? true : false;
    }

    /**
     *  run the all commands
     *
     */
    public function run()
    {
        $events = $this->dueEvents(EventReposity::getEvents());

        foreach ($events as $event) {
            $event->execute();
        }
    }

    /**
     * Get all of the events on the schedule that are due.
     *
     * @param array $events
     * @return array
     */
    public function dueEvents(array $events)
    {
        return array_filter(
            $events,
            function ($event) {
                return $event->isDue();
            }
        );
    }

    /**
     * get the events
     *
     * @return array
     */
    public function getEvents()
    {
        return null !== $this->getCache() ? $this->getCache() : EventReposity::getEvents();
    }

    /**
     * @return array
     */
    public function getCache()
    {
        return $this->cache;
    }

    /**
     * @param array $cache
     * @return Cron
     */
    public function setCache($cache)
    {
        $this->cache = $cache;
        return $this;
    }
}
