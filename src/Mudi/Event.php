<?php

namespace Mudi;

use Symfony\Component\EventDispatcher\Event as BaseEvent;

class Event extends BaseEvent
{

    public function __construct($proxy)
    {
    	$this->proxy = $proxy;
    }

    public function getResults()
    {
        return $this->proxy->getResults();
    }

    public function getResource()
    {
    	return $this->proxy->getResource();
    }

    public function getResourceName()
    {
        return $this->proxy->getResource()->name;
    }

    public function getService()
    {
        return $this->proxy->getService();
 	}


    public function getServiceName()
    {
        return $this->proxy->getService()->name;
 	}

}