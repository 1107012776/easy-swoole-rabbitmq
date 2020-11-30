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


    /**
     * php vendor/bin/phpunit tests/ConsumerTest.php --filter testListen
     * @throws
     */
    public function testListen()
    {

        $driver = new \EasySwoole\RabbitMq\RabbitMqQueueDriver('182.254.241.195', 5672, 'admin', 'admin', "/");
        MqQueue::getInstance($driver);

        MqQueue::getInstance()->consumer()->setConfig('kd_sms_send_ex', 'hello')->listen(function (MqJob $job) {
            var_dump($job->getJobData());
        });

    }


}
