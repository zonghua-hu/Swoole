<?php

error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING & ~E_DEPRECATED);

date_default_timezone_set('Asia/Shanghai');

require_once 'server/SwooleTcp.php';
require_once 'server/SwooleHttp.php';

require_once 'ServerAsynchronous/ServerTcpAsy.php';

$options = getopt('t:');
$version = 1.0;
$serverType = $options['t'];
$arguments['ip'] = '127.0.0.1';
$arguments['port'] = 9501;
$arguments['config'] = ['worker_num' => 1, 'task_worker_num' => 1];

switch ($serverType) {
    case 'tcp':
        \server\SwooleTcp::run($arguments);
        break;
    case 'tcpAsy':
        \ServerTcpAsy::run($arguments);
        break;
    case 'http':
        \server\SwooleHttp::run($arguments);
        break;
    case 'webSocket':
        \server\SwooleWebSocket::run($arguments);
        break;
}
