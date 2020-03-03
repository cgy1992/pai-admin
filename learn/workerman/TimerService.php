<?php


namespace learn\workerman;

use Workerman\Lib\Timer;
use think\worker\Server;
use Workerman\Worker;

class TimerService extends Server
{
    /**
     * 协议
     * @var string
     */
    protected $protocol = "websocket";

    /**
     * 监听地址
     * @var string
     */
    protected $host = '0.0.0.0';

    /**
     * 端口
     * @var string
     */
    protected $port = 1999;

    /**
     * @var int
     */
    protected $timer;

    /**
     * @var null
     */
    protected $worker = null;

    /**
     * @var int|float
     */
    protected $interval = 1;

    /**
     * 基础配置
     * @var array
     */
    protected $option = [
        'count'		=> 1,
        'name'		=> 'timer'
    ];

    /**
     * @param Worker $worker
     */
    protected function init(Worker $worker)
    {
        parent::init(); // TODO: Change the autogenerated stub
        $this->worker = $worker;
    }

    /**
     * 定时器开启
     * @throws \Exception
     */
    public function onWorkerStart()
    {
        $last = time();
        $task = [1 => $last, 6 => $last, 10 => $last, 30 => $last, 60 => $last, 180 => $last, 300 => $last];
        $this->timer = Timer::add($this->interval, function () use (&$task) {
            try {
                $now = time();
                foreach ($task as $sec => $time) {
                    if ($now - $time >= $sec) {
                        event('task_' . $sec);
                        $task[$sec] = $now;
                    }
                }
            } catch (\Exception $e) {
                var_dump($e);
            }
        });
    }
}

