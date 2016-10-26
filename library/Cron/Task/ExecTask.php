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
 * Class ExecTask
 * @package Sagi\Cron\Task
 */
class ExecTask extends TaskReposity
{
    /**
     * register the command
     *
     * @param string $command
     * @return ExecTask
     */
    public function setCommand($command)
    {
        parent::setCommand($command);

        return $this;
    }

    /**
     * @param array $parameters
     * @return ExecTask
     */
    public function setParameters(array $parameters = [])
    {
        $content = '';
        foreach($parameters as $key => $value)
        {
            $content .= is_numeric($key) ? $value : $key.'="'.addslashes($value).'"';
        }

        $this->setCommand($this->getCommand().' '. $content);
        return $this;
    }
}

