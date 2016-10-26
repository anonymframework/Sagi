<?php
/**
 * This file belongs to the AnoynmFramework
 *
 * @author vahitserifsaglam <vahit.serif119@gmail.com>
 * @see http://gemframework.com
 *
 * Thanks for using
 */


namespace Sagi\Cron\Task;

use Sagi\Cron\Schedule\Schedule;
use Carbon\Carbon;
use Cron\CronExpression;
use Symfony\Component\Process\Process;
use Closure;

/**
 * Class TaskReposity
 * @package Sagi\Cron\Task
 */
class TaskReposity extends Schedule
{

    /**
     * the timezone
     *
     * @var string
     */
    public $timezone;
    /**
     * the command for job
     *
     * @var string
     */
    protected $command;

    /**
     * the output
     *
     * @var string
     */
    private $output;

    /**
     * set the command and create a new instance
     *
     * @param mixed $command
     */
    public function __construct($command)
    {
        call_user_func_array([$this, 'setCommand'], func_get_args());
    }

    /**
     * Get the default output depending on the OS.
     *
     * @return string
     */
    protected function getDefaultOutput()
    {
        return (strpos(strtoupper(PHP_OS), 'WIN') === 0) ? 'NUL' : '/dev/null';
    }

    /**
     * build the exec command
     *
     * @return string
     */
    public function buildCommand()
    {
        $command = $this->getCommand();
        $output = $this->output !== null ? $this->output : $this->getDefaultOutput();

        return $command.' > '.$output.' 2>&1';
    }


    /**
     * build command with time expression and command line
     *
     * @return string
     */
    public function buildCommandWithExpression()
    {
        return sprintf('%s %s', $this->getPattern(), $this->buildCommand());
    }

    /**
     * @return string
     */
    public function getCommand()
    {
        return $this->command;
    }

    /**
     * register the command
     *
     * @param string $command
     * @return $this
     */
    public function setCommand($command)
    {
        $this->command = $command;

        return $this;
    }

    /**
     * check the event for call
     *
     *
     * @return bool
     */
    public function isDue()
    {

        $date = Carbon::now();

        if ($this->timezone) {
            $date->setTimezone($this->timezone);
        }

        $response = CronExpression::factory($this->getPatternString())->isDue($date->toDateTimeString());

        return $response && $this->resolveWhen();
    }


    /**
     * @return bool
     */
    private function resolveWhen()
    {
        $when = $this->when;

        if (null === $when) {
            return true;
        }

        return $when() === true ? true : false;
    }

    /**
     * get the base path
     *
     * @return string
     */
    private function resolveBasePath()
    {
        return defined('BASE') ? BASE : __DIR__;
    }

    /**
     * execute the commands
     *
     */
    public function execute()
    {
        if ($this->command instanceof Closure) {
            $this->runClosureTask();
        } else {
            $this->runExecTask();
        }
    }

    /**
     * get the description
     *
     *
     * @return string
     */
    public function getSummaryForDescription()
    {
        return $this->command instanceof Closure ? 'Closure' : $this->getCommand();
    }

    /**
     * run the closure
     *
     */
    private function runClosureTask()
    {

        if ($this->before !== null) {
            $this->resolveBeforeCallbacks();
        }

        call_user_func($this->command);

        if ($this->after !== null) {
            $this->resolveAfterCallbacks();
        }
    }

    /**
     * run the exec task
     */
    private function runExecTask()
    {
        chdir($this->resolveBasePath());
        $process = new Process($this->getCommand());
        $process->run();
    }

    /**
     * resolve the before callbacks
     */
    private function resolveBeforeCallbacks()
    {

        foreach ($this->before as $callback) {
            if ($callback instanceof Closure) {
                $callback();
            }
        }
    }

    /**
     * resolve the after callbacks
     */
    private function resolveAfterCallbacks()
    {
        foreach ($this->after as $callback) {
            if ($callback instanceof Closure) {
                $callback();
            }
        }
    }
}
