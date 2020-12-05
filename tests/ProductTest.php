<?php

use PHPUnit\Framework\TestCase;

$file_load_path = '../../../autoload.php';
if (file_exists($file_load_path)) {
    include $file_load_path;
} else {
    include '../vendor/autoload.php';
}

use EasySwoole\RabbitMq\MqJob;
use  EasySwoole\RabbitMq\MqQueue;

/**
 * Class ProductTest
 * @method assertEquals($a, $b)
 */
class ProductTest extends TestCase
{
    private $driver;
    protected function setUp(): void
    {
        $this->driver = new \EasySwoole\RabbitMq\RabbitMqQueueDriver('127.0.0.1', 5672, 'test', 'test', "/");
    }
    /**
     * php vendor/bin/phpunit tests/ProductTest.php --filter testDirectPush
     */
    public function testDirectPush()
    {

        MqQueue::getInstance($this->driver);
        $job = new MqJob();
        $job->setJobData('hello word');
        $res = MqQueue::getInstance()->producer()->setConfig('kd_sms_send_ex', 'hello','direct')->push($job);
        $this->assertEquals(true, !empty($res));

    }

    /**
     * php vendor/bin/phpunit tests/ProductTest.php --filter testTopicPush
     */
    public function testTopicPush()
    {

        MqQueue::getInstance($this->driver);
        $job = new MqJob();
        $job->setJobData('hello word');
        $res = MqQueue::getInstance()->producer()->setConfig('test_topic_ex', 'com.topic_hello','topic','topic_hello')->push($job);
        $this->assertEquals(true, !empty($res));

    }


}
