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
    /*    public function testRun(){
            var_dump(213123);
            $stack = [];
            $this->assertEquals(0, count($stack));
            var_dump(new MqJob());
        }*/


    /**
     * php vendor/bin/phpunit tests/ProductTest.php --filter testPush
     */
    public function testPush()
    {

        $driver = new \EasySwoole\RabbitMq\RabbitMqQueueDriver('182.254.241.195', 5672, 'admin', 'admin', "/");
        MqQueue::getInstance($driver);
        $job = new MqJob();
        $job->setJobData('hello word');
        $res = MqQueue::getInstance()->producer()->setConfig('kd_sms_send_ex', 'hello')->push($job);
        $this->assertEquals(true, !empty($res));

    }


}
