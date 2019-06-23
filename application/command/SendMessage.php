<?php
/**
 * Created by PhpStorm.
 * User: tears
 * Date: 2018/12/7
 * Time: 9:39 AM
 */

namespace app\command;

use app\common\controller\YunXin;
use think\Cache;
use think\console\Command;
use think\console\Input;
use think\console\Output;
use think\Db;
use think\Exception;
use think\Log;

class SendMessage extends Command
{
    protected $RunStatus = 1;

    protected function handler_quit($sig)
    {
        $this->RunStatus = 0;
        $this->writeln("收到信号:" . $sig);
    }

    protected function configure()
    {
        $this->setName('SendMessageService')->setDescription('消息群发服务');
    }

    public function execute(Input $input, Output $output)
    {
        $this->writeln("开启消息群发服务");
        pcntl_signal(SIGINT, function ($sig) {
            $this->handler_quit($sig);
        });
        pcntl_signal(SIGTERM, function ($sig) {
            $this->handler_quit($sig);
        });
        pcntl_signal(SIGQUIT, function ($sig) {
            $this->handler_quit($sig);
        });
        $cached = Cache::init();
        $cache_handler = $cached->handler();

        for ($i = 0; $i < 8640 && $this->RunStatus == 1; $i++) {
            pcntl_signal_dispatch();
            try {
                $yunxin = new YunXin();
            } catch (\ErrorException $e) {
                $this->writeln("fail:开启云信通信失败");
                break;
            }
            $key = $cache_handler->lpop('app:qunfa');
            if (empty($keys)) {
                sleep(1);
                continue;
            }
            $queue = $cached->get($key);
            //  如果空 goto end;
            if (!$queue) {
                Log::write("群发消息:没有取得发送数据:" . $key);
                continue;
            }
            $message = $queue['message'];
            if (empty($message)) {
                Log::write("群发消息:发送的消息为空:" . $key);
                continue;
            }
            $i = 0;
            while (true) {
                $list = Db::name('account')->where('status', 'eq',
                    '1')->where('yx_token is not null')->order('c_time asc')->limit($i * 500, 500)->column('memberid');
                if (empty($list)) {
                    Log::write("群发消息:发送完成:" . $key);
                    $queue['status'] = 1;
                    $queue['msg'] = '发送完成!';
                    $cached->set($key, $queue);
                    break;
                }
                if ($i > 100) {
                    $i = 0;
                    sleep(60);
                }
                $msg_res = $yunxin->sendBatchAttachMsg($list, $message);
                if (isset($msg_res) && (isset($msg_res->code) && $msg_res->code == 200)) {
                    Log::write("群发消息:" . $key . ":" . $i . '发送成功!');
                    $i++;
                } else {
                    Log::write("群发消息:" . $key . ":" . $i . '发送失败!');
                    $i--;
                }
            }
        }
        $this->writeln("结束消息群发服务");
    }

    public function writeln($messages)
    {
        $this->output->writeln(date('Y-m-d H:i:s') . "\t " . $messages);
    }

}