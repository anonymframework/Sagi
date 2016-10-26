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

/**
 * Class ConsoleTask
 * @package Sagi\Cron\Task
 */
class ConsoleTask extends PhpTask
{
    /**
     * register the command
     *
     * @param string $command
     * @param string $base
     * @return $this
     */
    public function setCommand($command, $base = null)
    {

        $base = ($base === null) ? BASE : $base;

        parent::setCommand($this->resolveBase($base).'anonym '.$command);
        return $this;
    }

    /**
     * resolve the base string
     *
     * @param string $base
     * @return string
     */
    private function resolveBase($base)
    {
        $last = substr($base, -1);

        if ($last !== '/') {
            return $base.'/';
        }

        return $base;
    }
}
