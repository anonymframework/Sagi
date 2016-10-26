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

use Sagi\Cron\Crontab\Crontab as CrontabManager;
use Sagi\Cron\Exception\CronInstanceException;
use Sagi\Cron\Crontab\Job as CronEntry;
use Sagi\Cron\Task\TaskReposity;
use Sagi\Cron\Task\ClosureTask;
use Sagi\Cron\Task\ExecTask;
use Symfony\Component\Process\Process;
use Closure;

/**
 * Class BasicCron
 * @package Sagi\Cron
 */
class BasicCron
{

    /**
     * the instance of cron manager
     *
     * @var CrontabManager
     */
    private $manager;

    /**
     * the instance of job class
     *
     * @var CronEnty
     */
    private $job;

    /**
     *
     * create a new instance and register cron tab manager
     */
    public function __construct()
    {
        $this->setManager(new CrontabManager());
        $this->setJob(new CronEntry());
    }

    /**
     * @return CronEntry
     */
    public function getJob()
    {
        return $this->job;
    }

    /**
     * @param CronEntry $job
     * @return BasicCron
     */
    public function setJob($job)
    {
        $this->job = $job;

        return $this;
    }


    /**
     * @return CrontabManager
     */
    public function getManager()
    {
        return $this->manager;
    }

    /**
     * add a event
     *
     * @param Closure $event
     * @return $this
     */
    public function event(Closure $event)
    {
        if (null !== $response = $event()) {
            $this->resolveEventResponse($response);
        }

        return $this;
    }

    /**
     * resolve the response from add event method
     *
     * @param mixed $response
     * @throws CronInstanceException
     */
    private function resolveEventResponse($response)
    {
        if (is_string($response)) {
            $response = new ExecTask($response);
        }

        if ($response instanceof TaskReposity && !$response instanceof ClosureTask) {
            EventReposity::addBasic($response);
        } else {
            throw new CronInstanceException(
                sprintf(
                    'Event Response must be an instance of %s and cant be an instance of %s',
                    TaskReposity::class,
                    ClosureTask::class
                )
            );
        }
    }

    /**
     * @param CrontabManager $manager
     * @return BasicCron
     */
    public function setManager(CrontabManager $manager)
    {
        $this->manager = $manager;

        return $this;
    }

    /**
     * clean the saved crontabs
     *
     * @return $this
     */
    public function clean()
    {
        $process = new Process($this->getManager()->getCrontabExecutable().' -r');
        $process->run();

        return $this;
    }

    /**
     * run events
     *
     */
    public function run()
    {
        $events = EventReposity::getBasicEvents();

        $job = $this->getJob();
        $manager = $this->getManager();

        if (count($events)) {
            foreach ($events as $event) {

                if ($event instanceof TaskReposity) {
                    $time = $event->getPattern();
                    list($min, $hour, $dayOfMonth, $month, $dayOfWeek) = explode(' ', $time);

                    $job->setMinute($min)->setHour($hour)->setDayOfMonth($dayOfMonth)->setMonth($month)->setDayOfWeek(
                        $dayOfWeek
                    );

                    $job->setCommand($event->buildCommand());
                    $manager->addJob($job);
                }
            }

            $manager->write();
        }
    }

    /**
     * remove a job
     *
     * @param string $job
     * @return $this
     */
    public function removeJob($job = '')
    {

        if ($job instanceof TaskReposity) {
            $job = $job->buildCommandWithExpression();
        }

        $refrector = new CronEntry();
        $job = $refrector->parse($job);

        $this->getManager()->removeJob($job);

        return $this;
    }

    /**
     * search the job in jobs
     *
     * @param string $job
     * @return mixed
     */
    public function jobExists($job)
    {
        return $this->getManager()->jobExists($job);
    }
}
