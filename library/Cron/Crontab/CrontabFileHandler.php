<?php

namespace Sagi\Cron\Crontab;
use Symfony\Component\Process\Process;

/**
 * Logic for reading and writing crontab files.
 *
 * @author Jacob Kiers <jacob@alphacomm.nl>
 */
class CrontabFileHandler
{
    /**
     * Location of the crontab executable
     *
     * @var string
     */
    public $crontabExecutable = '/usr/bin/crontab';

    /**
     * The error when using the command 'crontab'
     *
     * @var string
     */
    protected $error;

    /**
     * The output when using the command 'crontab'
     *
     * @var string
     */
    protected $output;

    /**
     * list the current jobs
     *
     * @param Crontab $crontab
     * @return array
     */
    public function listJobs($crontab){

        $process = new Process($this->crontabCommand($crontab).' -l');
        $process->run();

        $this->error = $process->getErrorOutput();
        return $process->getOutput();

    }

    /**
     * Parse an existing crontab
     *
     * @param Crontab $crontab
     *
     * @return CrontabFileHandler
     */
    public function parseExistingCrontab(Crontab $crontab)
    {

        foreach ($this->parseString($this->listJobs($crontab)) as $job) {
            $crontab->addJob($job);
        }

        return $this;
    }

    /**
     * Reads cron jobs from a file.
     *
     * @param Crontab $crontab
     * @param string  $filename
     *
     * @return CrontabFileHandler
     * @throws \InvalidArgumentException
     */
    public function parseFromFile(Crontab $crontab, $filename)
    {
        if (!is_readable($filename)) {
            throw new \InvalidArgumentException('File '.$filename.' is not readable.');
        }

        $file = file_get_contents($filename);

        foreach ($this->parseString($file) as $job) {
            $crontab->addJob($job);
        }

        return $this;
    }

    /**
     * Returns an array of Cron Jobs based on the contents of a file.
     *
     * @param string $input
     *
     * @return Job[]
     */
    protected function parseString($input)
    {
        $jobs = array();

        $lines = array_filter(explode(PHP_EOL, $input), function($line) {
            return '' != trim($line);
        });

        foreach ($lines as $line) {
            $trimmed = trim($line);

            if (strstr($line, 'PATH=') || strstr($line,'MAILTO=')) {
                continue;
            }
            // if line is not a comment, convert it to a cron
            if (0 !== \strpos($trimmed, '#')) {
                $jobs[] = Job::parse($line);
            }
        }

        return $jobs;
    }

    /**
     * Calcuates crontab command
     *
     * @param Crontab $crontab
     *
     * @return string
     */
    protected function crontabCommand(Crontab $crontab)
    {
        $cmd = $this->getCrontabExecutable();
        if ($crontab->getUser()) {
            $cmd .= sprintf(' -u %s ', $crontab->getUser());
        }

        return $cmd;
    }

    /**
     * Get crontab error
     *
     * @return string
     */
    public function getError()
    {
        return $this->error;
    }

    /**
     * Get crontab output
     *
     * @return string
     */
    public function getOutput()
    {
        return $this->output;
    }

    /**
     * Write the current crons in the cron table
     *
     * @param Crontab $crontab
     *
     * @return CrontabFileHandler
     */
    public function write(Crontab $crontab)
    {
        $tmpFile = tempnam(sys_get_temp_dir(), 'cron');

        $this->writeToFile($crontab, $tmpFile);

        $process = new Process($this->crontabCommand($crontab).' '.$tmpFile);
        $process->run();

        $this->error  = $process->getErrorOutput();
        $this->output = $process->getOutput();

        return $this;
    }

    /**
     * Write the current crons to a file.
     *
     * @param Crontab $crontab
     * @param string  $filename
     *
     * @return CrontabFileHandler
     * @throws \InvalidArgumentException
     */
    public function writeToFile(Crontab $crontab, $filename)
    {
        if (!is_writable($filename)) {
            throw new \InvalidArgumentException('File '.$filename.' is not writable.');
        }

        file_put_contents($filename, $crontab->render().PHP_EOL);

        return $this;
    }

    /**
     * Get crontab executable location
     *
     * @return string
     */
    public function getCrontabExecutable()
    {
        return $this->crontabExecutable;
    }

    /**
     * Set unix user to add crontab
     *
     * @param string $crontabExecutable
     *
     * @return Crontab
     */
    public function setCrontabExecutable($crontabExecutable)
    {
        $this->crontabExecutable = $crontabExecutable;

        return $this;
    }
}
