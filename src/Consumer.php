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
     * @param $exchange //交换器名称
     * @param $routingKey  //路由和绑定队列名称
     * @param $mqType  //交换器类型
     * @return $this
     */
    public function setConfig($exchange, $routingKey, $mqType = 'direct')
    {
        $this->job = new MqJob($exchange, $routingKey, $mqType);
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
