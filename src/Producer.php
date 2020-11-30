<?php


namespace EasySwoole\RabbitMq;


use Swoole\Atomic\Long;

class Producer
{
    private $atomic;
    private $driver;
    private $nodeId;
    private $exchange = '';
    private $routingKey = '';
    private $writeExchange = false;

    function __construct(RabbitMqQueueDriver $driver, Long $atomic, ?string $nodeId = null)
    {
        $this->atomic = $atomic;
        $this->driver = $driver;
        $this->nodeId = $nodeId;
    }

    /**
     * 初始化监听队列名
     * @param $exchange //交换机名称
     * @param $routingKey
     * @return $this
     */
    public function setConfig($exchange, $routingKey)
    {
        $this->exchange = $exchange;
        $this->routingKey = $routingKey;
        $this->writeExchange = true;
        return $this;
    }

    function push(MqJob $job, bool $init = true)
    {
        if ($this->writeExchange) {
            $job->setExchange($this->exchange);
            $job->setRoutingKey($this->routingKey);
            $this->writeExchange = false;
        }
        $id = $this->atomic->add(1);
        if ($id > 0) {
            if ($init) {
                $job->setJobId($id);
                $job->setNodeId($this->nodeId);
            }
            $ret = $this->driver->push($job);
            if ($ret) {
                return $id;
            }
        }
        return 0;
    }
}