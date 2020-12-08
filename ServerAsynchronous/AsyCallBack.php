<?php

class AsyCallBack extends ServerTcpAsy
{
    public function onPacket($server, $data, array $clients)
    {
        foreach ($clients as $fd) {
            $server->send($fd, 'Swoole: '.$data);
            $server->close($fd);
        }
    }

    public function onReceive($server, $fd, $reactor_id, $data)
    {
        $server->send($fd, 'Swoole: '.$data);
        $server->close($fd);
    }

    public function onConnect($server, $fd)
    {
        $client = $server->getClientInfo($fd);
        $server->task("this is asy swoole connect data", -1);
        echo "from ".$client['remote_ip']." Client: Connect succeed.\n";
    }

    public function onTask($data, $fromId, $taskId, $server)
    {
        if (strpos($data, '2')) {
            $data = "来自自定义进程的消息处理完毕，即将返回worker进程";
        } else {
            $data = '收到来自：'.$fromId."投递的任务：".$taskId."参数：". $data;
        }
        sleep(2);
        $server->finish($taskId, $data);
    }

    public function onFinish($taskId, $data)
    {
        echo "【{$taskId}】"."finish task data is: ".$data;
    }


}