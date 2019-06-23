<?php
        function writelog($message,$logFileDir){
            $logFile = 'git_info.log';
            $log = date('Y-m-d H:i:s') . ' - ' . $message . "\n";
            $fp = fopen($logFileDir.$logFile, 'a+');
            fwrite($fp, $log);
            fclose($fp);
        }
    $target = dirname(__FILE__);
    $json = json_decode(file_get_contents('php://input'), true);
    $password='qibei';
    //    writelog(json_encode($json));
    if (empty($json['password']) || $json['password'] !== $password) {
        exit('error request');
    }
    $cmd=" cd {$target} ;sudo -Hu www git pull 2<&1";
    $r= shell_exec($cmd);
    print_r($r);
    echo date('Y-m-d H:i:s');