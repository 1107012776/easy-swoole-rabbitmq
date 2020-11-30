<?php

namespace EasySwoole\RabbitMq;

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

/**
 * Class RabbitMqQueueDriver
 * @package EasySwoole\RabbitMq
 */
class RabbitMqQueueDriver
{
    protected $connection;
    /**
     * @var \EasySwoole\RabbitMq\MqJob $job
     */
    protected $job;
    protected $config = [];

    public function __construct($host, $port, $user, $password, $vhost = '/', $insist = false,
                                $login_method = 'AMQPLAIN',
                                $login_response = null,
                                $locale = 'en_US',
                                $connection_timeout = 3.0,
                                $read_write_timeout = 3.0,
                                $context = null,
                                $keepalive = false,
                                $heartbeat = 60)
    {
        $this->config = [
            $host, $port, $user, $password, $vhost,
            $insist, $login_method, $login_response, $locale, $connection_timeout, $read_write_timeout,
            $context, $keepalive, $heartbeat
        ];
        $this->connection = new AMQPStreamConnection($host, $port, $user, $password, $vhost,
            $insist, $login_method, $login_response, $locale, $connection_timeout, $read_write_timeout,
            $context, $keepalive, $heartbeat
        );
    }

    /**
     * 刷新链接
     */
    public function refreshConnect()
    {
        list($host, $port, $user, $password, $vhost,
            $insist, $login_method, $login_response, $locale, $connection_timeout, $read_write_timeout,
            $context, $keepalive, $heartbeat) = $this->config;
        return new static($host, $port, $user, $password, $vhost,
            $insist, $login_method, $login_response, $locale, $connection_timeout, $read_write_timeout,
            $context, $keepalive, $heartbeat);
    }

    /**
     * 生产发布信息
     * @param \EasySwoole\RabbitMq\MqJob $job
     * @return bool
     */
    public function push($job): bool
    {
        $channel = $this->connection->channel();
        $exchange = $job->getExchange(); //交换机名
        $queueName = $routingKey = $job->getRoutingKey(); //路由关键字
        $channel->exchange_declare($exchange, 'direct', false, true, false); //声明初始化交换机
        $channel->queue_declare($queueName, false, true, false, false);
        $channel->queue_bind($queueName, $exchange, $routingKey);
        $body = $job->getJobData();
        is_array($body) && $body = json_encode($body, JSON_UNESCAPED_UNICODE);
        $msg = new AMQPMessage($body, [
            'delivery_mode' => 2 // make message persistent 持久化消息
        ]);
        $channel->tx_select();  //事务声明
        try {
            $channel->basic_publish($msg, $exchange, $routingKey);
            $channel->tx_commit();
            $isOk = true;
        } catch (\Exception $e) {
            $channel->tx_rollback();
            $isOk = false;
        } finally {
            $channel->close();
        }
        return $isOk;
    }

    /**
     * @param \EasySwoole\RabbitMq\MqJob $job
     * @return \EasySwoole\RabbitMq\MqJob
     */
    public function bind($job)
    {
        return $this->job = $job;
    }


    /**
     * @param $callback
     * @return \EasySwoole\RabbitMq\MqJob
     */
    public function pop($callback)
    {
        $job = $this->job;
        $channel = $this->connection->channel();
        $exchange = $job->getExchange(); //交换机名
        $queueName = $routingKey = $job->getRoutingKey(); //路由关键字(也可以省略)
        $channel->exchange_declare($exchange, 'direct', false, true, false); //声明初始化交换机
        $channel->queue_declare($queueName, false, true, false, false);
        $channel->queue_bind($queueName, $exchange, $routingKey);
        $channel->basic_consume($queueName, '', false, true, false, false, $callback);
        while (count($channel->callbacks)) {
            try {
                $channel->wait(null, false, 10);
                //        $channel->basic_consume('hello', '', false, true, false, false, $callback);
            } catch (\Exception $e) {
                //        print_r($e->getTraceAsString());
            }
        }
        return $job;
    }


    /**
     * @param $callback
     * @return \EasySwoole\RabbitMq\MqJob
     */
    public function consumerPop($callback)
    {
//       $callback = function($msg)
//       {
//            echo " [x] Received ", $msg->body, "\n";
//       };
        $job = $this->job;
        $channel = $this->connection->channel();
        $exchange = $job->getExchange(); //交换机名
        $queueName = $routingKey = $job->getRoutingKey(); //路由关键字(也可以省略)
        $channel->exchange_declare($exchange, 'direct', false, true, false); //声明初始化交换机
        $channel->queue_declare($queueName, false, true, false, false);
        $channel->basic_consume($queueName, '', false, true, false, false, function ($msg) use ($job, $callback) {
            $job->setJobData($msg->body);
            $callback($job);
        });
        while (count($channel->callbacks)) {
            try {
                $channel->wait(null, false, 10);
            } catch (\Exception $e) {

            }
        }
        return $job;
    }

    public function size(): ?int
    {
        return 0;
    }

}