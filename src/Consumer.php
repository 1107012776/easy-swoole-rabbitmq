<?php


namespace EasySwoole\RabbitMq;


use Swoole\Coroutine;
use Swoole\Exception;

class Consumer
{
    private $driver;
    /**
     * @var MqJob $job
     */
    private $job;

    function __construct(RabbitMqQueueDriver $driver)
    {
        $this->driver = $driver;
    }

    /**
     * 初始化监听队列名
     * @param $exchange //交换机名称
     * @param $routingKey
     * @return $this
     */
    public function setConfig($exchange, $routingKey)
    {
        $this->job = new MqJob($exchange, $routingKey);
        return $this;
    }

    /**
     * 监听
     * @param callable $call
     * @param float $breakTime
     * @param float $waitTime
     * @param int $maxCurrency
     */
    function listen(callable $call, float $breakTime = 0.01, float $waitTime = 0.1, int $maxCurrency = 128)
    {
        if (empty($this->job->getExchange()) && empty($this->job->getRoutingKey())) {
            throw new Exception('exchange and routingKey parameters cannot be null or empty');
        }
        $job = $this->driver->consumerPop($call,$this->job);  //这边本身自己会挂起
    }

    function stopListen(): Consumer
    {
        return $this;
    }
}
