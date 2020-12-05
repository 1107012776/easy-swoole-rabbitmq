<?php

namespace EasySwoole\RabbitMq;

use EasySwoole\Queue\Job;

class MqJob extends Job
{
    protected $exchange = '';  //交换机名称
    protected $routingKey = '';  //路由和绑定队列名称
    protected $mqType = 'direct';  //交换机类型

    public function __construct($exchange = '', $routingKey = '', $mqType = 'direct')
    {
        $this->exchange = $exchange;
        $this->routingKey = $routingKey;
        $this->mqType = $mqType;
    }


    public function setExchange($exchange)
    {
        return $this->exchange = $exchange;
    }

    public function setRoutingKey($routingKey)
    {
        return $this->routingKey = $routingKey;
    }

    public function setMqType($mqType)
    {
        return $this->mqType = $mqType;
    }


    public function getExchange()
    {
        return $this->exchange;
    }

    public function getRoutingKey()
    {
        return $this->routingKey;
    }

    public function getMqType()
    {
        return $this->mqType;
    }
}