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
    private $mqType = 'direct';
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
     * @param $routingKey  //绑定路由和队列名称
     * @param $mqType  //交换机类型
     * @return $this
     */
    public function setConfig($exchange, $routingKey ,$mqType = 'direct')
    {
        $this->exchange = $exchange;
        $this->routingKey = $routingKey;
        $this->mqType = $mqType;
        $this->writeExchange = true;
        return $this;
    }

    function push(MqJob $job, bool $init = true)
    {
        if ($this->writeExchange) {
            $job->setExchange($this->exchange);
            $job->setRoutingKey($this->routingKey);
            $job->setMqType($this->mqType);
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