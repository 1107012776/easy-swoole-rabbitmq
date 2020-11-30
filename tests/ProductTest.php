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
    /**
     * php vendor/bin/phpunit tests/ProductTest.php --filter testPush
     */
    public function testPush()
    {

        $driver = new \EasySwoole\RabbitMq\RabbitMqQueueDriver('127.0.0.1', 5672, 'test', 'test', "/");
        MqQueue::getInstance($driver);
        $job = new MqJob();
        $job->setJobData('hello word');
        $res = MqQueue::getInstance()->producer()->setConfig('kd_sms_send_ex', 'hello')->push($job);
        $this->assertEquals(true, !empty($res));

    }


}
