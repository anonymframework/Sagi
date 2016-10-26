<?php
/**
 * This file belongs to the AnoynmFramework
 *
 * @author vahitserifsaglam <vahit.serif119@gmail.com>
 * @see http://gemframework.com
 *
 * Thanks for using
 */


namespace Sagi\Cron\Schedule;
use Closure;
/**
 * Class Schedule
 * @package Sagi\Cron\Job
 */
class Schedule
{

    /**
     * the minute of cronjob
     *
     * @var string
     */
    private $minute = '*';

    /**
     * the hour of cronjob
     *
     * @var string
     */
    private $hour = '*';

    /**
     * the day of mount
     *
     * @var string
     */
    private $dayOfMounth = '*';

    /**
     * the mounth of cronjob
     *
     * @var string
     */
    private $mounth = '*';

    /**
     * the day of week
     *
     * @var string
     */
    private $dayOfWeek = '*';

    /**
     * the year of cronjob
     *
     * @var string
     */
    private $year;

    /**
     * the full time string
     *
     * @var string
     */
    private $pattern = '* * * * * *';

    /**
     * the instance of closure
     *
     * @var Closure
     */
    protected $when;

    /**
     * before callbacks
     *
     * @var array
     */
    protected $before;


    /**
     * after callbacks
     *
     * @var array
     */
    protected $after;

    /**
     * add a before callback
     *
     * @param Closure $closure
     *
     * @return $this
     */
    public function before(Closure $closure){
        $this->before[] = $closure;
    }

    /**
     * add an after callback
     *
     * @param Closure $closure
     *
     * @return $this
     */
    public function after(Closure $closure){
        $this->after[] = $closure;
    }

    /**
     * register the when instance
     *
     * @param Closure $when
     * @return $this
     */
    public function when(Closure $when)
    {
        $this->when = $when;
        return $this;
    }

    /**
     * @return string
     */
    public function getMinute()
    {
        return $this->minute;
    }

    /**
     * @param string $minute
     * @return Job
     */
    public function setMinute($minute)
    {
        $this->minute = $minute;

        return $this;
    }

    /**
     * @return string
     */
    public function getHour()
    {
        return $this->hour;
    }

    /**
     * @param string $hour
     * @return Schedule
     */
    public function setHour($hour)
    {
        $this->hour = $hour;

        return $this;
    }

    /**
     * @return string
     */
    public function getDayOfMounth()
    {
        return $this->dayOfMounth;
    }

    /**
     * @param string $dayOfMounth
     * @return Schedule
     */
    public function setDayOfMounth($dayOfMounth)
    {
        $this->dayOfMounth = $dayOfMounth;

        return $this;
    }

    /**
     * @return string
     */
    public function getMounth()
    {
        return $this->mounth;
    }

    /**
     * @param string $mounth
     * @return Schedule
     */
    public function setMounth($mounth)
    {
        $this->mounth = $mounth;

        return $this;
    }

    /**
     * @return string
     */
    public function getDayOfWeek()
    {
        return $this->dayOfWeek;
    }

    /**
     * @param string $dayOfWeek
     * @return Schedule
     */
    public function setDayOfWeek($dayOfWeek)
    {
        $this->dayOfWeek = $dayOfWeek;

        return $this;
    }

    /**
     * @return string
     */
    public function getYear()
    {
        return $this->year;
    }

    /**
     * @param string $year
     * @return Schedule
     */
    public function setYear($year)
    {
        $this->year = $year;

        return $this;
    }

    /**
     * @return string
     */
    public function getPattern()
    {
        return $this->pattern;
    }

    /**
     * @param string $pattern
     * @return Schedule
     */
    public function setPattern($pattern)
    {
        $this->pattern = $pattern;

        return $this;
    }


    /**
     * Splice the given value into the given position of the expression.
     *
     * @param  int $position
     * @param  string $value
     * @return $this
     */
    protected function spliceIntoPosition($position, $value)
    {
        $segments = explode(' ', $this->pattern);
        $segments[$position - 1] = $value;

        return $this->cron(implode(' ', $segments));
    }

    /**
     * set the cron pattern
     *
     * @param string $expression
     * @return Schedule
     */
    public function cron($expression)
    {
        return $this->setPattern($expression);
    }


    /**
     * Schedule the event to run hourly.
     *
     * @return $this
     */
    public function hourly()
    {
        return $this->cron('0 * * * * *');
    }

    /**
     * Schedule the event to run daily.
     *
     * @return $this
     */
    public function daily()
    {
        return $this->cron('0 0 * * * *');
    }


    /**
     * Schedule the event to run monthly.
     *
     * @return $this
     */
    public function monthly()
    {
        return $this->cron('0 0 1 * * *');
    }

    /**
     * Schedule the event to run yearly.
     *
     * @return $this
     */
    public function yearly()
    {
        return $this->cron('0 0 1 1 * *');
    }

    /**
     * Schedule the event to run every minute.
     *
     * @return $this
     */
    public function everyMinute()
    {
        return $this->cron('* * * * * *');
    }

    /**
     * Schedule the event to run every five minutes.
     *
     * @return $this
     */
    public function everyFiveMinutes()
    {
        return $this->cron('*/5 * * * * *');
    }

    /**
     * Schedule the event to run every ten minutes.
     *
     * @return $this
     */
    public function everyTenMinutes()
    {
        return $this->cron('*/10 * * * * *');
    }

    /**
     * Schedule the event to run every thirty minutes.
     *
     * @return $this
     */
    public function everyThirtyMinutes()
    {
        return $this->cron('0,30 * * * * *');
    }

    /**
     * Set the days of the week the command should run on.
     *
     * @param  array|dynamic $days
     * @return $this
     */
    public function days($days)
    {
        $days = is_array($days) ? $days : func_get_args();

        return $this->spliceIntoPosition(5, implode(',', $days));
    }

    /**
     * Schedule the command at a given time.
     *
     * @param  string $time
     * @return $this
     */
    public function at($time)
    {
        return $this->dailyAt($time);
    }

    /**
     * Schedule the event to run daily at a given time (10:00, 19:30, etc).
     *
     * @param  string $time
     * @return $this
     */
    public function dailyAt($time)
    {
        $segments = explode(':', $time);

        return $this->spliceIntoPosition(2, (int)$segments[0])
            ->spliceIntoPosition(1, count($segments) == 2 ? (int)$segments[1] : '0');
    }

    /**
     * Schedule the event to run twice daily.
     *
     * @param  int $first
     * @param  int $second
     * @return $this
     */
    public function twiceDaily($first = 1, $second = 13)
    {
        $hours = $first.','.$second;

        return $this->spliceIntoPosition(1, 0)
            ->spliceIntoPosition(2, $hours);
    }

    /**
     * Schedule the event to run only on weekdays.
     *
     * @return $this
     */
    public function weekdays()
    {
        return $this->spliceIntoPosition(5, '1-5');
    }

    /**
     * Schedule the event to run only on Mondays.
     *
     * @return $this
     */
    public function mondays()
    {
        return $this->days(1);
    }

    /**
     * Schedule the event to run only on Tuesdays.
     *
     * @return $this
     */
    public function tuesdays()
    {
        return $this->days(2);
    }

    /**
     * Schedule the event to run only on Wednesdays.
     *
     * @return $this
     */
    public function wednesdays()
    {
        return $this->days(3);
    }

    /**
     * Schedule the event to run only on Thursdays.
     *
     * @return $this
     */
    public function thursdays()
    {
        return $this->days(4);
    }

    /**
     * Schedule the event to run only on Fridays.
     *
     * @return $this
     */
    public function fridays()
    {
        return $this->days(5);
    }

    /**
     * Schedule the event to run only on Saturdays.
     *
     * @return $this
     */
    public function saturdays()
    {
        return $this->days(6);
    }

    /**
     * Schedule the event to run only on Sundays.
     *
     * @return $this
     */
    public function sundays()
    {
        return $this->days(0);
    }

    /**
     * Schedule the event to run weekly.
     *
     * @return $this
     */
    public function weekly()
    {
        return $this->cron('0 0 * * 0 *');
    }

    /**
     * Schedule the event to run weekly on a given day and time.
     *
     * @param  int $day
     * @param  string $time
     * @return $this
     */
    public function weeklyOn($day, $time = '0:0')
    {
        $this->dailyAt($time);

        return $this->spliceIntoPosition(5, $day);
    }

    /**
     * if pattern is null, create patten with private variables
     *
     * @return string
     */
    private function createPatternWithVariables()
    {
        $variables = [
            $this->getMinute(),
            $this->getHour(),
            $this->getDayOfMounth(),
            $this->getMounth(),
            $this->getDayOfWeek(),
            $this->getYear(),
        ];

        return join(' ', $variables);
    }

    /**
     * resolve the pattern
     *
     * @return string
     */
    private function resolvePattern()
    {
        if (null !== $pattern = $this->pattern) {
            return $pattern;
        }

        return $this->createPatternWithVariables();
    }

    /**
     * use the class to string
     *
     * @return string
     */
    protected function getPatternString()
    {
        if (false !== $pattern = $this->resolvePattern()) {
            return $pattern;
        }

        return '';
    }
}
