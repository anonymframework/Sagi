<?php

namespace Sagi\Cron\Crontab;
use Symfony\Component\Process\Process;


/**
 * Represent a crontab
 *
 * @author Benjamin Laugueux <benjamin@yzalis.com>
 */
class Crontab
{
    /**
     * @var CrontabFileHandler
     */
    protected $crontabFileHandler;

    /**
     * A collection of jobs
     *
     * @var Job[] $jobs
     */
    private $jobs = array();

    /**
     * The user executing the comment 'crontab'
     *
     * @var string
     */
    protected $user = null;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->getCrontabFileHandler()->parseExistingCrontab($this);
    }

    /**
     * Parse an existing crontab
     *
     * @return Crontab
     *
     * @deprecated Please use {@see CrontabFileHandler::parseExistingCrontab()}
     */
    public function parseExistingCrontab()
    {
        $this->getCrontabFileHandler()->parseExistingCrontab($this);

        return $this;
    }

    /**
     * resolve the linux system path variable
     *
     * @return string
     */
    private function resolvePathVariable()
    {
        return exec('echo $PATH');
    }

    /**
     * Render the crontab and associated jobs
     *
     * @return string
     */
    public function render()
    {
        $path = $this->resolvePathVariable();
        $content =  "PATH=$path \n". implode(PHP_EOL, $this->getJobs());
        return $content;
    }

    /**
     * Write the current crons in the cron table
     *
     * @deprecated Please use {@see CrontabFileHandler::write()}
     */
    public function write()
    {
        $this->getCrontabFileHandler()->write($this);

        return $this;
    }

    /**
     * Remove all crontab content
     *
     * @return Crontab
     */
    public function flush()
    {
        $this->removeAllJobs();
        $this->getCrontabFileHandler()->write($this);
    }

    /**
     * Get unix user to add crontab
     *
     * @return string
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set unix user to add crontab
     *
     * @param string $user
     *
     * @return Crontab
     */
    public function setUser($user)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get crontab executable location
     *
     * @return string
     *
     * @deprecated Please use {@see CrontabFileHandler::getCrontabExecutable()}
     */
    public function getCrontabExecutable()
    {
        return $this->getCrontabFileHandler()->getCrontabExecutable();
    }

    /**
     * Set unix user to add crontab
     *
     * @param string $crontabExecutable
     *
     * @return Crontab
     *
     * @deprecated Please use {@see CrontabFileHandler::setCrontabExecutable()}
     */
    public function setCrontabExecutable($crontabExecutable)
    {
        $this->getCrontabFileHandler()->setCrontabExecutable($crontabExecutable);

        return $this;
    }

    /**
     * Get all crontab jobs
     *
     * @return Job[] An array of Job
     */
    public function getJobs()
    {
        return $this->jobs;
    }

    /**
     * Get crontab error
     *
     * @return string
     *
     * @deprecated Please use {@see CrontabFileHandler::getError()}
     */
    public function getError()
    {
        return $this->getCrontabFileHandler()->getError();
    }

    /**
     * Get crontab output
     *
     * @return string
     *
     * @deprecated Please use {@see CrontabFileHandler::getOutput()}
     */
    public function getOutput()
    {
        return $this->getCrontabFileHandler()->getOutput();
    }

    /**
     * Add a new job to the crontab
     *
     * @param Job $job
     *
     * @return Crontab
     */
    public function addJob(Job $job)
    {
        $this->jobs[$job->getHash()] = $job;

        return $this;
    }

    /**
     * Adda new job to the crontab
     *
     * @param array $jobs
     *
     * @return Crontab
     */
    public function setJobs(array $jobs)
    {
        foreach ($jobs as $job) {
            $this->addJob($job);
        }

        return $this;
    }


    /**
     * search job in jobs
     *
     * @param string $job
     * @return mixed
     */
    public function jobExists($job = '')
    {
        $process = new Process('crontab -l');
        $process->run();

        $output = ($process->getIncrementalOutput());

        $jobs = array_filter(explode(PHP_EOL, $output), function($line) {
            return '' != trim($line);
        });

        return is_array($jobs) ?  array_search($job, $jobs) : false;
    }
    /**
     * Remove all job in the current crontab
     *
     * @return Crontab
     */
    public function removeAllJobs()
    {
        $this->jobs = array();

        return $this;
    }

    /**
     * Remove a specified job in the current crontab
     *
     * @param Job $job
     *
     * @return Crontab
     */
    public function removeJob(Job $job)
    {
        unset($this->jobs[$job->getHash()]);

        return $this;
    }

    /**
     * Returns a Crontab File Handler
     *
     * @return CrontabFileHandler
     */
    public function getCrontabFileHandler()
    {
        if (!$this->crontabFileHandler instanceof CrontabFileHandler) {
            $this->crontabFileHandler = new CrontabFileHandler();
        }

        return $this->crontabFileHandler;
    }

    /**
     * Set the Crontab File Handler
     *
     * @param CrontabFileHandler $command
     *
     * @return $this
     */
    public function setCrontabFileHandler(CrontabFileHandler $command)
    {
        $this->crontabFileHandler = $command;

        return $this;
    }
}
