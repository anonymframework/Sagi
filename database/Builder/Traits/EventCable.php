<?php
/**
 * Created by PhpStorm.
 * User: My
 * Date: 05/08/2017
 * Time: 01:54
 */

namespace Sagi\Database\Builder\Traits;


use Sagi\Database\Event\EventDispatcher;

trait EventCable
{
    /**
     * @var EventDispatcher
     */
    protected $eventManager;

    /**
     * Boots traits
     */
    public function bootEventCable()
    {
        $this->eventManager = new EventDispatcher();
    }

    /**
     * @param string $event
     * @param array $arguments
     */
    public function callEvent($event, $arguments = [])
    {
        if ($this->getEventManager()->hasListiner($event)) {
            $this->getEventManager()->fire($event, $arguments);
        }
    }

    /**
     * @return EventDispatcher
     */
    public function getEventManager()
    {
        if ( ! $this->eventManager instanceof EventDispatcher) {
            $this->eventManager = new EventDispatcher();
        }

        return $this->eventManager;
    }

}