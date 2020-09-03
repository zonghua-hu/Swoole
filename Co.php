<?php
/**
 * 协程插入数据到数据库
 */
use Swoole\Coroutine as co;
use Swoole\Coroutine\MySQL;

go(
    function () {

        function getPhone()
        {
            return "13636807795";
        }
        $channel = new Co\Channel();
        $mysql = new Swoole\Coroutine\MySQL();
        $db = $mysql->connect([
                'host'  => '10.1.3.2',
                'user'    => 'preview',
                'password'    => 'FMNxXvjaM0sINcQ!',
                'port'        => '3306',
                'database'    => 'business',
            ]);
        $url = __DIR__."/10.txt";
        $file = fopen($url, 'r');
        $sql = "insert into temp_user_data(source,name,real_name,phone) VALUES ";

        go(
            function () use ($channel, $url, $sql, $file) {
                while (!feof($file)) {
                    $row = fgets($file);
                    $row = explode("\t", $row);
                    $values = "(";
                    foreach ($row as $key => $value) {
                        switch ($key) {
                            case 0:
                                $values.= uniqid();
                                break;
                            case 1:
                                $values.= $value == "微信" ? "1":"2";
                                break;
                            case 2:
                                $values.= "数据导入";
                                break;
                            case 3:
                                $values.= getPhone();
                                break;
                        }
                    }
                    $values = trim($values).")";
                    if (count($row) < 11) {
                        file_put_contents(__DIR__."/text.txt");
                        continue;
                    }
                    $channel->push($values);
                }
            }
        );

        go(function () use ($channel, $mysql, $sql) {
            $i = 0;
            $temp = $sql;

            while ($value=$channel->pop(10)) {
                if ($i === 0) {
                    $temp.= $value;
                } else {
                    $temp.= ",".$value;
                }

                if ($i >=50) {
                    $a = $mysql->query($temp);
                    if ($a === false) {
                        file_put_contents(__DIR__."/11.txt", $temp, FILE_APPEND);
                    }
                    $temp = $sql;
                    $i = 0;
                } else {
                    $i++;
                }
            }

            $a = $mysql->query($temp);
            if ($a == false) {
                file_put_contents(__DIR__."12.txt", $temp, FILE_APPEND);
            }
        });
    }
);
