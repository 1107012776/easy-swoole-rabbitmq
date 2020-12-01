<?php
namespace EasySwoole\RabbitMq;

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

/**
 * Class RabbitMqQueueDriver
 */
class RabbitMqQueueDriver{
    /**
     * @var AMQPStreamConnection
     */
    protected $connection;
    /**
     * @var MqJob $job
     */
    protected $job;
    protected $config = [];
    public function __construct($host,$port,$user,$password,$vhost = '/',$insist = false,
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
            $host, $port,$user, $password,$vhost,
            $insist,$login_method,$login_response,$locale,$connection_timeout,$read_write_timeout,
            $context,$keepalive,$heartbeat
        ];
        $this->connection();
    }

    /**
     * 链接
     */
    protected function connection(){
        list($host, $port,$user, $password,$vhost,
            $insist,$login_method,$login_response,$locale,$connection_timeout,$read_write_timeout,
            $context,$keepalive,$heartbeat) = $this->config;
        $this->connection = new AMQPStreamConnection($host, $port,$user, $password,$vhost,
            $insist,$login_method,$login_response,$locale,$connection_timeout,$read_write_timeout,
            $context,$keepalive,$heartbeat
        );
    }

    /**
     * 刷新链接
     */
    public function refreshConnect(){
        list($host, $port,$user, $password,$vhost,
            $insist,$login_method,$login_response,$locale,$connection_timeout,$read_write_timeout,
            $context,$keepalive,$heartbeat) = $this->config;
        return new self($host, $port,$user, $password,$vhost,
            $insist,$login_method,$login_response,$locale,$connection_timeout,$read_write_timeout,
            $context,$keepalive,$heartbeat);
    }

    /**
     * 生产发布信息
     * @param  MqJob $job
     * @return bool
     */
    public function push($job): bool
    {
        try{
            $channel = $this->connection->channel();
        }catch (\Exception $e){
            try{
                $this->connection->close();
            }catch (\Exception $e){

            }
            $this->connection();
            $channel = $this->connection->channel();
        }
        $exchange = $job->getExchange(); //交换机名
        $queueName = $routingKey = $job->getRoutingKey(); //路由关键字(也可以省略)
        $channel->exchange_declare($exchange, 'direct', false, true, false); //声明初始化交换机
        $channel->queue_declare($queueName, false, true, false, false);
        $channel->queue_bind($queueName,$exchange,$routingKey);
        $body = $job->getJobData();
        is_array($body) && $body = json_encode($body, JSON_UNESCAPED_UNICODE);
        $msg = new AMQPMessage($body,[
            'delivery_mode' => 2 // make message persistent 持久化消息
        ]);
        $channel->tx_select();  //事务声明
        try{
            $channel->basic_publish($msg, $exchange, $routingKey);
            $channel->tx_commit();
            $isOk = true;
        }catch (\Exception $e){
            $channel->tx_rollback();
            $isOk = false;
        } finally {
            $channel->close();
        }
        return $isOk;
    }

    /**
     * @param MqJob $job
     * @return MqJob
     */
    public function bind($job){
        return $this->job = $job;
    }



    /**
     * @param $callback
     * @return MqJob
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
        $channel->basic_consume($queueName, '', false, false, false, false, function ($msg) use($job,$callback){
            $job->setJobData($msg->body);
            $res = $callback($job);
            if($res === false){  //明确消息是失败直接reject
                $msg->delivery_info['channel']->basic_reject($msg->delivery_info['delivery_tag'], true); //回滚
                return;
            }
            $msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']);  //ack回应消息收到了
        });
        while(count($channel->callbacks)) {
            try{
                $channel->wait(null,false,10);
            }catch (\Exception $e){

            }
        }
        return $job;
    }

    public function size(): ?int
    {
        return 0;
    }

}