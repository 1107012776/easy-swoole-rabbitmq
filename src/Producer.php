<?php


namespace EasySwoole\RabbitMq;


use Swoole\Atomic\Long;

class Producer
{
    protected $atomic;
    protected $driver;
    protected $nodeId;
    protected $exchange = '';
    protected $routingKey = '';
    protected $mqType = 'direct';
    protected $queueName = '';
    protected $mqTable = [];  //headers  模式键值对
    protected $writeExchange = false;

    function __construct(RabbitMqQueueDriver $driver, Long $atomic, ?string $nodeId = null)
    {
        $this->atomic = $atomic;
        $this->driver = $driver;
        $this->nodeId = $nodeId;
    }

    /**
     * 初始化监听队列名
     * @param $exchange //交换器名称
     * @param $routingKey  //绑定路由和队列名称
     * @param $mqType  //交换器类型
     * @param $queueName  //队列名称
     * @param $mqTable  //headers模式键值对
     * @return $this
     */
    public function setConfig($exchange, $routingKey ,$mqType = 'direct' ,$queueName = '', $mqTable = [])
    {
        $this->exchange = $exchange;
        $this->routingKey = $routingKey;
        $this->mqType = $mqType;
        $this->queueName = $queueName;
        $this->mqTable = $mqTable;
        $this->writeExchange = true;
        return $this;
    }

    function push(MqJob $job, bool $init = true)
    {
        if ($this->writeExchange) {
            $job->setExchange($this->exchange);
            $job->setRoutingKey($this->routingKey);
            $job->setMqType($this->mqType);
            $job->setQueueName($this->queueName);
            $job->setMqTable($this->mqTable);
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