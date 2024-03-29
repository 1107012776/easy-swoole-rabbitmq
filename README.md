# easy-swoole-rabbitmq
EasySwoole 框架的 RabbitMQ 队列插件，基于 php-amqplib/php-amqplib

### 支持的交换器类型
已支持 direct topic fanout

### 安装
composer require lys/easy-swoole-rabbitmq


### 示例:
我们创建一个MqQueueProcess.php, 消费者进程
```php
<?php

namespace App\Utility;

use EasySwoole\Component\Process\AbstractProcess;
use EasySwoole\EasySwoole\Logger;
use EasySwoole\RabbitMq\MqQueue;
use EasySwoole\RabbitMq\MqJob;

class MqQueueProcess extends AbstractProcess
{
    protected function run($arg)
    {
        go(function () {
            $MqQueue = MqQueue::getInstance()->refreshConnect();
            $MqQueue->consumer()->setConfig($exchange = 'kd_sms_send_ex', $routingKey = 'hello', $mqType = 'direct', $queueName = 'hello')->listen(function(MqJob $obj) {
                echo " [x] Received ", $obj->getJobData(), "\n";
                Logger::getInstance()->log('log level info' . var_export($obj->getJobData(), true), Logger::LOG_LEVEL_INFO, 'DEBUG');//记录info级别日志//例子后面2个参数默认值
                var_dump($obj->getJobData(),'MqQueueProcess');
                //return;   //使用return 终止执行下面的代码
                //return true;   //使用return true终止执行下面的代码
                echo 11111;
                //return false;  //return false消息回滚,所以请注意，不要随意使用return false
            });
        });
    }
}
```
修改EasySwooleEvent.php，在mainServerCreate中添加如下代码
```php
<?php
namespace EasySwoole\EasySwoole;


use EasySwoole\Component\Timer;
use EasySwoole\EasySwoole\Swoole\EventRegister;
use EasySwoole\EasySwoole\AbstractInterface\Event;
use EasySwoole\Http\Request;
use EasySwoole\Http\Response;

use EasySwoole\RabbitMq\MqQueue;
use EasySwoole\RabbitMq\MqJob;
use EasySwoole\RabbitMq\RabbitMqQueueDriver;
use App\Utility\MqQueueProcess;

class EasySwooleEvent implements Event
{

    public static function initialize()
    {
        // TODO: Implement initialize() method.
        date_default_timezone_set('Asia/Shanghai');
    }

    public static function mainServerCreate(EventRegister $register)
    {
             $driver = new RabbitMqQueueDriver('127.0.0.1', 5672, 'test', 'test',"/");
              MqQueue::getInstance($driver);
              $processConfig= new \EasySwoole\Component\Process\Config();
              $processConfig->setProcessGroup('Test');//设置进程组
              $processConfig->setArg(['a'=>123]);//传参
              $processConfig->setRedirectStdinStdout(false);//是否重定向标准io
              $processConfig->setPipeType($processConfig::PIPE_TYPE_SOCK_DGRAM);//设置管道类型
              $processConfig->setEnableCoroutine(true);//是否自动开启协程
              $processConfig->setMaxExitWaitTime(3);//最大退出等待时间
              $processConfig->setProcessName('MqQueueProcessComposer');
              \EasySwoole\EasySwoole\ServerManager::getInstance()->addProcess(new MqQueueProcess($processConfig));
    }

    public static function onRequest(Request $request, Response $response): bool
    {
        // TODO: Implement onRequest() method.
        return true;
    }

    public static function afterRequest(Request $request, Response $response): void
    {
        // TODO: Implement afterAction() method.
    }
}
  ```
  生产者投递消息
 ```php
<?php
 namespace App\Utility;
 
 use EasySwoole\RabbitMq\MqQueue;
 use EasySwoole\RabbitMq\MqJob;

 class MqComposer{

     public static function push(){
         $job = new MqJob();
         $job->setJobData('composer hello word'.date('Y-m-d H:i:s', time()));
         $res = MqQueue::getInstance()->producer()->setConfig($exchange = 'kd_sms_send_ex',$routingKey = 'hello',$mqType = 'direct', $queueName = 'hello')->push($job);
         if($res){
              var_dump('发布成功');
         }else{
              var_dump('发布失败');
         }
         //主动关闭链接
         /* MqQueue::getInstance()->closeConnection(function (\Exception $e){
             //这边是主动关闭异常处理
            });  */  
     }
 }
 
 ```
 
### 更多请关注本人的博客
https://www.developzhe.com/single200.html
 
### Page visitor counter
![visitor counter](https://profile-counter.glitch.me/1107012776_easy-swoole-rabbitmq/count.svg)
