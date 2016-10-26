<?php

namespace Sagi\Cron\Crontab;


/**
 * Represent a cron job
 *
 * @author Benjamin Laugueux <benjamin@yzalis.com>
 */
class Job extends BaseJob
{
    /**
     * To string
     *
     * @return string
     */
    public function __toString()
    {
        try {
            return $this->render();
        } catch (\Exception $e) {
            return '# '.$e;
        }
    }

    /**
     * Parse crontab line into Job object
     *
     * @param $jobLine
     *
     * @return Job
     * @throws \InvalidArgumentException
     */
    static function parse($jobLine)
    {

        // split the line
        $parts = preg_split('@ @', $jobLine, NULL, PREG_SPLIT_NO_EMPTY);

        // check the number of part
        if (count($parts) < 5) {
            throw new \InvalidArgumentException('Wrong job number of arguments.');
        }

        // analyse command
        $command = implode(' ', array_slice($parts, 5));

        // prepare variables
        $lastRunTime = $logFile = $logSize = $errorFile = $errorSize = $comments = null;

        // extract comment
        if (strpos($command, '#')) {
            list($command, $comment) = explode('#', $command);
            $comments = trim($comment);
        }

        // extract error file
        if (strpos($command, '2>>')) {
            list($command, $errorFile) = explode('2>>', $command);
            $errorFile = trim($errorFile);
        }

        // extract log file
        if (strpos($command, '>>')) {
            list($command, $logPart) = explode('>>', $command);
            $logPart = explode(' ', trim($logPart));
            $logFile = trim($logPart[0]);
        }

        // compute last run time, and file size
        if (isset($logFile) && file_exists($logFile)) {
            $lastRunTime = filemtime($logFile);
            $logSize = filesize($logFile);
        }
        if (isset($errorFile) && file_exists($errorFile)) {
            $lastRunTime = max($lastRunTime ? : 0, filemtime($errorFile));
            $errorSize = filesize($errorFile);
        }

        $command = trim($command);

        // compute status
        $status = 'error';
        if ($logSize === null && $errorSize === null) {
            $status = 'unknown';
        } else if ($errorSize === null || $errorSize == 0) {
            $status =  'success';
        }

        // set the Job object
        $job = new Job();
        $job
            ->setMinute($parts[0])
            ->setHour($parts[1])
            ->setDayOfMonth($parts[2])
            ->setMonth($parts[3])
            ->setDayOfWeek($parts[4])
            ->setCommand($command)
            ->setErrorFile($errorFile)
            ->setErrorSize($errorSize)
            ->setLogFile($logFile)
            ->setLogSize($logSize)
            ->setComments($comments)
            ->setLastRunTime($lastRunTime)
            ->setStatus($status)
        ;

        return $job;
    }

    /**
     * Generate a unique hash related to the job entries
     *
     * @return Job
     */
    private function generateHash()
    {
        $this->hash = hash('md5', serialize(array(
            strval($this->getMinute()),
            strval($this->getHour()),
            strval($this->getDayOfMonth()),
            strval($this->getMonth()),
            strval($this->getDayOfWeek()),
            strval($this->getCommand()),
        )));

        return $this;
    }

    /**
     * Get an array of job entries
     *
     * @return array
     */
    public function getEntries()
    {
        return array(
            $this->getMinute(),
            $this->getHour(),
            $this->getDayOfMonth(),
            $this->getMonth(),
            $this->getDayOfWeek(),
            $this->getCommand(),
            $this->prepareLog(),
            $this->prepareError(),
            $this->prepareComments(),
        );
    }

    /**
     * Render the job for crontab
     *
     * @return string
     */
    public function render()
    {
        if (null === $this->getCommand()) {
            throw new \InvalidArgumentException('You must specify a command to run.');
        }

        // Create / Recreate a line in the crontab
        $line = trim(implode(" ", $this->getEntries()));

        return $line;
    }

    /**
     * Prepare comments
     *
     * @return string or null
     */
    public function prepareComments()
    {
        if (null !== $this->getComments()) {
            return '# ' . $this->getComments();
        } else {
            return null;
        }
    }

    /**
     * Prepare log
     *
     * @return string or null
     */
    public function prepareLog()
    {
        if (null !== $this->getLogFile()) {
            return '>> ' . $this->getLogFile();
        } else {
            return null;
        }
    }

    /**
     * Prepare log
     *
     * @return string or null
     */
    public function prepareError()
    {
        if (null !== $this->getErrorFile()) {
            return '2>> ' . $this->getErrorFile();
        } else if ($this->prepareLog()) {
            return '2>&1';
        } else {
            return null;
        }
    }
    /**
     * Return the error file content
     *
     * @return string
     */
    public function getErrorContent()
    {
        if ($this->getErrorFile() && file_exists($this->getErrorFile())) {
            return file_get_contents($this->getErrorFile());
        } else {
            return null;
        }
    }

    /**
     * Return the log file content
     *
     * @return string
     */
    public function getLogContent()
    {
        if ($this->getLogFile() && file_exists($this->getLogFile())) {
            return file_get_contents($this->getLogFile());
        } else {
            return null;
        }
    }

    /**
     * Return the last job run time
     *
     * @return DateTime|null
     */
    public function getLastRunTime()
    {
        return $this->lastRunTime;
    }

    /**
     * Return the job unique hash
     *
     * @return Job
     */
    public function getHash()
    {
        if (null === $this->hash) {
            $this->generateHash();
        }

        return $this->hash;
    }

    /**
     * Set the minute (* 1 1-10,11-20,30-59 1-59 *\/1)
     *
     * @param string
     *
     * @return Job
     */
    public function setMinute($minute)
    {
        if (!preg_match(self::$_regex['minute'], $minute)) {
            throw new \InvalidArgumentException(sprintf('Minute "%s" is incorect', $minute));
        }

        $this->minute = $minute;

        return $this->generateHash();
    }

    /**
     * Set the hour
     *
     * @param string
     *
     * @return Job
     */
    public function setHour($hour)
    {
        if (!preg_match(self::$_regex['hour'], $hour)) {
            throw new \InvalidArgumentException(sprintf('Hour "%s" is incorect', $hour));
        }

        $this->hour = $hour;

        return $this->generateHash();
    }

    /**
     * Set the day of month
     *
     * @param string
     *
     * @return Job
     */
    public function setDayOfMonth($dayOfMonth)
    {
        if (!preg_match(self::$_regex['dayOfMonth'], $dayOfMonth)) {
            throw new \InvalidArgumentException(sprintf('DayOfMonth "%s" is incorect', $dayOfMonth));
        }

        $this->dayOfMonth = $dayOfMonth;

        return $this->generateHash();
    }

    /**
     * Set the month
     *
     * @param string
     *
     * @return Job
     */
    public function setMonth($month)
    {
        if (!preg_match(self::$_regex['month'], $month)) {
            throw new \InvalidArgumentException(sprintf('Month "%s" is incorect', $month));
        }

        $this->month = $month;

        return $this->generateHash();
    }

    /**
     * Set the day of week
     *
     * @param string
     *
     * @return Job
     */
    public function setDayOfWeek($dayOfWeek)
    {

        if (!preg_match(self::$_regex['dayOfWeek'], $dayOfWeek)) {
            throw new \InvalidArgumentException(sprintf('DayOfWeek "%s" is incorect', $dayOfWeek));
        }

        $this->dayOfWeek = $dayOfWeek;

        return $this->generateHash();
    }

    /**
     * Set the command
     *
     * @param string
     *
     * @return Job
     */
    public function setCommand($command)
    {
        if (!preg_match(self::$_regex['command'], $command)) {
            throw new \InvalidArgumentException(sprintf('Command "%s" is incorect', $command));
        }

        $this->command = $command;

        return $this->generateHash();
    }

    /**
     * Set the last job run time
     *
     * @param int
     *
     * @return Job
     */
    public function setLastRunTime($lastRunTime)
    {
        $this->lastRunTime = \DateTime::createFromFormat('U', $lastRunTime);

        return $this;
    }

    /**
     * Set the comments
     *
     * @param string
     *
     * @return Job
     */
    public function setComments($comments)
    {
        if (is_array($comments)) {
            $comments = implode($comments, ' ');
        }

        $this->comments = $comments;

        return $this;
    }

    /**
     * Set the log file
     *
     * @param string
     *
     * @return Job
     */
    public function setLogFile($logFile)
    {
        $this->logFile = $logFile;

        return $this->generateHash();
    }

    /**
     * Set the error file
     *
     * @param string
     *
     * @return Job
     */
    public function setErrorFile($errorFile)
    {
        $this->errorFile = $errorFile;

        return $this->generateHash();
    }
}
