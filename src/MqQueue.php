<?php

namespace EasySwoole\RabbitMq;

use EasySwoole\Component\Singleton;
use EasySwoole\Utility\Random;
use Swoole\Atomic\Long;

class MqQueue
{
    use Singleton;
    /**
     * @var RabbitMqQueueDriver $driver
     */
    private $driver;
    private $atomic;
    private $nodeId;

    function __construct(RabbitMqQueueDriver $driver)
    {
        $this->driver = $driver;
        $this->atomic = new Long(0);
        $this->nodeId = Random::character(6);
    }

    /**
     * 刷新重建一个链接
     * @return MqQueue
     */
    function refreshConnect()
    {
        return new static($this->driver->refreshConnect());
    }

    /**
     * 主动关闭链接
     * @param callable|null $callback //关闭链接异常捕获回调函数
     * @return bool
     */
    function closeConnection(callable $callback = null)
    {
        return $this->driver->closeConnection($callback);
    }

    function queueDriver()
    {
        return $this->driver;
    }

    function producer(): Producer
    {
        return new Producer($this->driver, $this->atomic, $this->nodeId);
    }

    function consumer(): Consumer
    {
        return new Consumer($this->driver);
    }


    function size(): ?int
    {
        return $this->driver->size();
    }

    function currentJobId(): int
    {
        return $this->atomic->get();
    }

    function setJobStartId(int $id): MqQueue
    {
        $this->atomic->set($id);
        return $this;
    }

    /**
     * @return bool|string
     */
    public function getNodeId()
    {
        return $this->nodeId;
    }

    /**
     * @param bool|string $nodeId
     */
    public function setNodeId($nodeId): void
    {
        $this->nodeId = $nodeId;
    }
}