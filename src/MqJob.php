<?php

namespace EasySwoole\RabbitMq;

use EasySwoole\Queue\Job;

class MqJob extends Job
{
    protected $exchange = '';  //交换器名称
    protected $routingKey = '';  //路由和绑定队列名称
    protected $mqType = 'direct';  //交换器类型
    protected $queueName = '';  //队列名称
    protected $mqTable = [];  //headers  模式键值对

    public function __construct($exchange = '', $routingKey = '', $mqType = 'direct', $queueName = '', $mqTable = [])
    {
        $this->exchange = $exchange;
        $this->routingKey = $routingKey;
        $this->mqType = $mqType;
        $this->queueName = $queueName;
        $this->mqTable = $mqTable;
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

    public function setQueueName($queueName)
    {
        return $this->queueName = $queueName;
    }

    public function setMqTable($mqTable)
    {
        return $this->mqTable = $mqTable;
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

    public function getQueueName()
    {
        return $this->queueName;
    }

    public function getMqTable()
    {
        return $this->mqTable;
    }
}