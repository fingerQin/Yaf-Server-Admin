<?php
/**
 * 默认 CLI 控制器。
 * @author fingerQin
 * @date 2018-08-16
 */

use finger\RedisMutexLock;
use finger\Database\Db;
use Utils\YCache;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
use Services\Event\Consumer;
use Services\Event\Producer;
use Models\Event;
use Services\Game\Riddle;


class IndexController extends \Common\controllers\Cli
{
    public function nameAction()
    {
        $result = Riddle::randomMake(1);
        print_r($result);
        exit;
    }

    public function testAction()
    {
        $money     = 100;
        $people    = 200;
        $scatter   = 100;
        $reward    = new RandMoney();
        $rewardArr = $reward->splitReward($money, $people, $scatter);
        print_r($rewardArr);
        exit;
    }

    /**
     * 给多进程(线程)持续放入数据。
     * 
     * -- 定时启动。
     */
    public function threadPushAction()
    {
        $datetime  = date('Y-m-d H:i:s', time());
        for ($i = 0; $i < 100; $i++) {
            Producer::push([
                'code'        => Event::CODE_LOGIN,
                'userid'      => 1,
                'mobile'      => '18575202691',
                'platform'    => 1,
                'app_v'       => '0.0.1',
                'v'           => '1.0.0',
                'login_time'  => $datetime
            ]);
        }
        echo "ok:{$datetime}\n";
    }

    public function indexAction()
    {
        // [1] 创建 RabbitMQ 连接
        $connection = new AMQPStreamConnection('localhost', 5672, 'guest', 'guest');
        // [2] 打开一个通道。
        $channel    = $connection->channel();
        // [3] 定义一个队列。
        $channel->queue_declare('hello', false, false, false, false);
        $channel->queue_declare('hello2', false, false, false, false);
        // 循环插入数据。
        for ($i = 0; $i < 2; $i++) {
            // [4] 创建一条字符串消息对象。
            // $msg = new AMQPMessage("Hello World![{$i}]");

            $msg = new AMQPMessage("Hello World![{$i}]", ['delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT]);

            // [5] 向队列当中推送一条消息。
            $channel->basic_publish($msg, '', 'hello');
            $channel->basic_publish($msg, '', 'hello2');
            echo " [{$i}] Sent 'Hello World!'\n";
        }

        // [6] 关闭通道。
        $channel->close();
        // [7] 关闭连接。
        $connection->close();
    }

    public function getAction()
    {
        // [1] 创建 RabbitMQ 连接。
        $connection = new AMQPStreamConnection('localhost', 5672, 'guest', 'guest');
        // [2] 打开一个通道。
        $channel = $connection->channel();
        // [3] 定义一个队列。
        $channel->queue_declare('hello', false, false, false, false);

        echo ' [*] Waiting for messages. To exit press CTRL+C', "\n";

        // [4] 定义一个匿名方法处理接收到的消息。
        $callback = function($msg) {
            echo " [x] Received ", $msg->body, "\n";
            $msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']);
            //$msg->delivery_info['channel']->basic_nack($msg->delivery_info['delivery_tag']);
        };
        $channel->basic_qos(null, 1, null);
        // [5] 设置接收到的消息由谁来消息。
        $channel->basic_consume('hello', '', false, false, false, false, $callback);
        // [6] 循环等待接收队列的消息。
        while(count($channel->callbacks)) {
            $channel->wait();
        }
    }

    public function getTwoAction()
    {
        // [1] 创建 RabbitMQ 连接。
        $connection = new AMQPStreamConnection('localhost', 5672, 'guest', 'guest');
        // [2] 打开一个通道。
        $channel = $connection->channel();
        // [3] 定义一个队列。
        $channel->queue_declare('hello2', false, false, false, false);

        echo ' [*] Waiting for messages. To exit press CTRL+C', "\n";

        // [4] 定义一个匿名方法处理接收到的消息。
        $callback = function($msg) {
            echo " [x] Received ", $msg->body, "\n";
        };
        // [5] 设置接收到的消息由谁来消息。
        $channel->basic_consume('hello2', '', false, true, false, false, $callback);
        // [6] 循环等待接收队列的消息。
        while(count($channel->callbacks)) {
            $channel->wait();
        }
    }
}