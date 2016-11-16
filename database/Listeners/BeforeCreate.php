<?php
/**
 *  SAGI DATABASE ORM FILE
 *
 */

namespace Sagi\Database\Listeners;


use Sagi\Event\EventListener;
use Sagi\Event\SubscriberInterface;

class BeforeCreate extends EventListener implements SubscriberInterface
{

    public function getSubscribedEvents()
    {
        return array(
            'timestamps' => 'createTimestamps',
            'policy_check' => 'policyCheck',
        );
    }

    public function createTimestamps()
    {

    }

    public function policyCheck()
    {

    }
}