<?php

namespace EasySwoole\RabbitMq;

use EasySwoole\Queue\Job;

class MqJob extends Job
{
    protected $exchange = '';
    protected $routingKey = '';

    public function __construct($exchange = '', $routingKey = '')
    {
        $this->exchange = $exchange;
        $this->routingKey = $routingKey;
    }

    public function setExchange($exchange)
    {
        return $this->exchange = $exchange;
    }

    public function setRoutingKey($routingKey)
    {
        return $this->routingKey = $routingKey;
    }

    public function getExchange()
    {
        return $this->exchange;
    }

    public function getRoutingKey()
    {
        return $this->routingKey;
    }
}