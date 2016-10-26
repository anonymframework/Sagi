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
use Closure;
/**
 * Class Task
 * @package Sagi\Cron\Task
 */
class Task
{

    /**
     * call the command, command must be a Closure or string
     *
     * @param string|Closure $command
     * @return ClosureTask|ExecTask
     */
    public static function call($command)
    {
        return $command instanceof Closure ? new ClosureTask($command) : new ExecTask($command);
    }

    /**
     * create a new exec task instance
     *
     * @param string $command
     * @param array $parameters
     * @return ExecTask
     */
    public static function exec($command, $parameters = [])
    {
        return (new ExecTask($command))->setParameters($parameters);
    }
    /**
     * call the file command
     *
     * @param string $command
     * @return PhpFileTask
     */
    public static function php($command)
    {
        return new PhpFileTask($command);
    }

    /**
     * call the a console task
     *
     * @param string $command
     * @param string $base
     * @return ConsoleTask
     */
    public static function console($command, $base = null)
    {
        return new ConsoleTask($command, $base);
    }

}
