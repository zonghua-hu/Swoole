<?php

use server\SwooleTcp;

class ServerTcpAsy
{
    private $ip;
    private $port;
    private $config;

    protected static $install = null;

    public function __construct($params)
    {
        $this->ip = $params['ip'];
        $this->port = $params['port'];
        $this->config = $params['config'];
        $this->init();
    }

    private function init()
    {
        $server = new \Swoole\Server($this->ip, $this->port, SWOOLE_PROCESS, SWOOLE_SOCK_TCP);
        $server->addlistener('127.0.0.1', 9503, SWOOLE_TCP);
        $server->addlistener('0.0.0.0', 9504, SWOOLE_UDP);
//        $server->addlistener('127.0.0.1', 9502, SWOOLE_TCP | SWOOLE_SSL);
        $port = $server->addListener("0.0.0.0", 0, SWOOLE_SOCK_TCP);
//        var_dump($port);
//        echo $port;
        $server->set($this->config);
       //$server->addProcess($this->processFunc($server));
//        $server->on('packet', [AsyCallBack::class, 'onPacket']);

        $server->on('packet', function ($server, $clients, $data) {
            foreach ($clients as $fd) {
                $server->send($fd, 'Swoole: '.$data);
                $server->close($fd);
            }
        });
//        $server->on('receive', [AsyCallBack::class, 'onReceive']);
        $server->on('receive', function ($server, $fd, $data) {
            $server->send($fd, 'Swoole: '.$data);
            $server->close($fd);
        });
        //$server->on('Connect', [SwooleTcp::class, 'onConnect']);

        $server->on('Connect', [AsyCallBack::class, 'onConnect']);
        $server->on('task', [SwooleTcp::class, 'onTask']);
        $server->on('finish', [SwooleTcp::class, 'onFinish']);
        $server->start();
    }

    private function onPacket($server, $data, array $clients)
    {
        foreach ($clients as $fd) {
            $server->send($fd, 'Swoole ASY: '.$data);
            $server->close($fd);
        }
//        $socket = $process->exportSocket();
//        $socket->send($data);
    }

    /**
     * @Notes:自定义进程
     * @param $server
     * @return \Swoole\Process
     * @User: Hans
     * @Date: 2020/10/21
     * @Time: 2:15 下午
     */
    public function processFunc($server)
    {
        return new \Swoole\Process(function ($process) use ($server) {
            $socket = $process->exportScoket();
            while (true) {
                $msg = $socket->recv();
                foreach ($server->connections as $coon) {
                    $server->send($coon, $msg);
                }
            }
        }, false, 2, 1);
    }

    /**
     * @Notes:启动
     * @param array $params
     * @return ServerTcpAsy
     * @User: Hans
     * @Date: 2020/10/21
     * @Time: 2:26 下午
     */
    public static function run($params = [])
    {
        if (! self::$install instanceof self) {
            self::$install = new  self($params);
        }
        return self::$install;
    }

}
