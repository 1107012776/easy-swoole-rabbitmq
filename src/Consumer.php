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

    /**
     * @var callable  $moniterWaitErrorCallable
     */
    private $moniterWaitErrorCallable = null;   //错误监控函数针对 $channel->wait(null, false, $waitTime);

    function __construct(RabbitMqQueueDriver $driver)
    {
        $this->driver = $driver;
    }

    /**
     * 初始化监听队列名
     * @param $exchange //交换器名称
     * @param $routingKey //路由和绑定队列名称
     * @param $mqType //交换器类型
     * @return $this
     */
    public function setConfig($exchange, $routingKey, $mqType = 'direct', $queueName = '')
    {
        $this->job = new MqJob($exchange, $routingKey, $mqType, $queueName);
        return $this;
    }

    /**
     * 设置错误监控函数针对 $channel->wait(null, false, $waitTime);
     * @param callable $moniterWaitErrorCallable
     * @return $this
     */
    function setMoniterWaitError(callable $moniterWaitErrorCallable){
        $this->moniterWaitErrorCallable = $moniterWaitErrorCallable;
        return $this;
    }

    /**
     * 监听
     * @param callable $call
     * @param  $breakTime
     * @param  $waitTime
     * @param  $maxCurrency
     * @throws
     */
    function listen(callable $call, $breakTime = 0.001, $waitTime = 5, int $maxCurrency = 128)
    {
        if (empty($this->job->getExchange()) && empty($this->job->getRoutingKey())) {
            throw new Exception('exchange and routingKey parameters cannot be null or empty');
        }
        $job = $this->driver->consumerPop($call, $this->job, $breakTime, $waitTime, $this->moniterWaitErrorCallable);  //这边本身自己会挂起
    }

    function stopListen(): Consumer
    {
        return $this;
    }
}
