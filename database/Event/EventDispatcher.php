<?php
/**
 * This file belongs to the AnoynmFramework
 *
 * @author vahitserifsaglam <vahit.serif119@gmail.com>
 * @see http://gemframework.com
 *
 * Thanks for using
 */

namespace Sagi\Database\Event;

use Closure;
use Sagi\Database\Event\Event as EventDispatch;
use Sagi\Database\Event\EventListener;
use Sagi\Database\Exceptions\EventNameException;

/**
 *
 * Class Event
 * @package Anonym
 */
class EventDispatcher
{

    /**
     * store the list of fired events
     *
     * @var EventDispatcher
     */
    private $firing;


    /**
     * create a new instance and registerde event collector
     *
     */
    public function __construct()
    {

        $this->listeners = EventCollector::getListeners();
    }

    /**
     * execute the event
     *
     * @param string|EventDispatch $event the name of event.
     * @param array $parameters the parameters for closure events
     * @return array the response
     * @throws EventException
     * @throws EventListenerException
     * @throws EventNameException
     * @throws EventNotFoundException
     */
    public function fire($event = null, array $parameters = [])
    {

        $response = [];
        list($listeners, $event) = $this->resolveEventAndListeners($event);

        foreach ($listeners as $listener) {

            $response[] = $listener instanceof Closure ? $this->resolveClosureListener(
                $listener,
                $parameters
            ) : $this->resolveObjectListener(
                $listener,
                $event
            );
        }


        return $this->resolveResponseArray($response);
    }

    /**
     * resolve the return parameter
     *
     * @param array $response
     * @return mixed
     */
    private function resolveResponseArray(array $response)
    {
        if ($count = count($response)) {
            if ($count === 1) {
                $response = $response[0];
            }
        }
        $this->firing[] = $response;

        return $response;
    }

    /**
     * resolve the object listener
     *
     * @param \Sagi\Database\Event\EventListener $listener
     * @param Event $event
     * @return mixed
     */
    private function resolveObjectListener(EventListener $listener, EventDispatch $event)
    {
        return call_user_func_array([$listener, 'handle'], [$event]);
    }

    /**
     * resolve the callable listener
     *
     * @param Closure $listener
     * @param array $parameters
     * @return mixed
     */
    private function resolveClosureListener(Closure $listener, array $parameters)
    {
        return call_user_func_array($listener, $parameters);
    }

    /**
     * resolve the event and listener
     *
     * @param mixed $event
     * @throws EventListenerException
     * @return null|string|EventDispatch
     */
    private function resolveEventAndListeners($event)
    {

        if (is_object($event) && $event instanceof EventDispatch) {
            $name = get_class($event);
        } else {
            $name = $event;
        }

        if (is_string($name)) {
            if ($this->hasListiner($name) && $listeners = $this->getListeners($name)) {
                if (count($listeners) === 1) {
                    $listeners = $listeners[0];
                    $listeners = [$listeners instanceof Closure ? $listeners : (new $listeners)];
                }
            } else {
                throw new EventListenerException(sprintf('Your %s event havent got listener', $event));
            }
        }

        return [$listeners, $event];
    }

    /**
     * register a new listener
     *
     * @param string|Event $name the name or instance of event
     * @param string|EventListener $listener the name or instance of event listener
     * @return $this
     */
    public function listen($name, $listener)
    {
        EventCollector::addListener($name, $listener);

        return $this;
    }


    /**
     * return the registered listeners
     *
     * @param string $eventName get the event listeners
     * @return mixed
     * @throws EventNameException
     */
    public function getListeners($eventName = '')
    {
        if ( ! is_string($eventName)) {
            throw new EventNameException('Event adı geçerli bir string değeri olmalıdır');
        }

        return EventCollector::getListeners()[$eventName];
    }

    /**
     * check the isset any listener
     *
     * @param string $eventName the name of event
     * @return bool
     */
    public function hasListiner($eventName = '')
    {
        $listeners = EventCollector::getListeners();

        return isset($listeners[$eventName]);
    }

    /**
     * get the last fired event response
     *
     * @return mixed
     */
    public function firing()
    {
        return end($this->firing);
    }
}
