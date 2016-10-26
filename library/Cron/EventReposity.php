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

/**
 * Class EventReposity
 * @package Sagi\Cron
 */
class EventReposity
{

    /**
     * the reposity of events
     *
     * @var array
     */
    private static $events = [];

    /**
     * the reposity of events
     *
     * @var array
     */
    private static $basicEvents = [];

    /**
     * add a new event to reposity
     *
     * @param string|\Closure $event
     */
    public static function add($event)
    {
        static::$events[] = $event;
    }


    /**
     * add a new event to reposity
     *
     * @param string|\Closure $event
     */
    public static function addBasic($event)
    {
        static::$basicEvents[] = $event;
    }

    /**
     * @return array
     */
    public static function getBasicEvents()
    {
        return self::$basicEvents;
    }

    /**
     * @param array $basicEvents
     */
    public static function setBasicEvents($basicEvents)
    {
        self::$basicEvents = $basicEvents;
    }

    /**
     * @return array
     */
    public static function getEvents()
    {
        return self::$events;
    }

    /**
     * @param array $events
     */
    public static function setEvents($events)
    {
        self::$events = $events;
    }

}
