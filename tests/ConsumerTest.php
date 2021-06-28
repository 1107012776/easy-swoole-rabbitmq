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
 * @method assertEquals($a, $b)
 */
class ConsumerTest extends TestCase
{

    private $driver;

    protected function setUp(): void
    {
        $this->driver = new \EasySwoole\RabbitMq\RabbitMqQueueDriver('127.0.0.1', 5672, 'test', 'test', "/");
    }

    /**
     * php vendor/bin/phpunit tests/ConsumerTest.php --filter testDirectListen
     * @throws
     */
    public function testDirectListen()
    {
        go(function () {
            MqQueue::getInstance($this->driver);
            MqQueue::getInstance()->consumer()->setConfig('kd_sms_send_ex', 'hello', 'direct')->listen(function (MqJob $job) {
                var_dump($job->getJobData());
            });
        });
    }

    /**
     * php vendor/bin/phpunit tests/ConsumerTest.php --filter testTopicListen
     * @throws
     */
    public function testTopicListen()
    {
        go(function () {
            MqQueue::getInstance($this->driver);
            MqQueue::getInstance()->consumer()->setConfig('test_topic_ex', 'com.#', 'topic', 'topic_hello')->listen(function (MqJob $job) {
                var_dump($job->getJobData());
            });
        });
    }


    /**
     * php vendor/bin/phpunit tests/ConsumerTest.php --filter testFanoutListen
     * @throws
     */
    public function testFanoutListen()
    {
        go(function () {
            MqQueue::getInstance($this->driver);
            MqQueue::getInstance()->consumer()->setConfig('test_fanout_ex', 'fanout_hello', 'fanout', 'fanout_hello')->listen(function (MqJob $job) {
                var_dump($job->getJobData());
            });
        });
    }

    /**
     * php vendor/bin/phpunit tests/ConsumerTest.php --filter testFanout1Listen
     * @throws
     */
    public function testFanout1Listen()
    {
        go(function () {
            MqQueue::getInstance($this->driver);
            MqQueue::getInstance()->consumer()->setConfig('test_fanout_ex', 'fanout_hello', 'fanout', 'fanout_hello1')->listen(function (MqJob $job) {
                var_dump($job->getJobData());
            });
        });
    }

}
