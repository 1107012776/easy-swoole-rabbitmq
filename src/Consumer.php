<?php


namespace EasySwoole\RabbitMq;


use Swoole\Coroutine;
use Swoole\Exception;

class Consumer
{
    private $driver;
    private $enableListen = false;
    private $exchange = '';
    private $routingKey = '';

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
        $this->exchange = $exchange;
        $this->routingKey = $routingKey;
        $job = new MqJob($exchange, $routingKey);
        $this->driver->bind($job);
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
        if (empty($this->exchange) && empty($this->routingKey)) {
            throw new Exception('exchange and routingKey parameters cannot be null or empty');
        }
        $this->enableListen = true;
        $running = 0;
        while ($this->enableListen) {
            if ($running >= $maxCurrency) {
                Coroutine::sleep($breakTime);
                continue;
            }
            Coroutine::create(function () use (&$running, $call) {
                $running++;
                $job = $this->driver->consumerPop($call);
            });
            Coroutine::sleep($breakTime);
        }
    }

    function stopListen(): Consumer
    {
        $this->enableListen = false;
        return $this;
    }
}
