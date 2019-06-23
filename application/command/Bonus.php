<?php
/**
 * Created by PhpStorm.
 * User: tears
 * Date: 2018/11/20
 * Time: 3:28 PM
 */

namespace app\command;


use think\console\Command;
use think\console\Input;
use think\console\Output;
use think\Exception;
use think\Log;

class Bonus extends Command
{
    protected $RunStatus = 1;

    protected function handler_quit($sig)
    {
        $this->RunStatus = 0;
        $this->writeln("收到信号:" . $sig);
    }

    protected function configure()
    {
        $this->setName('BonusService')->setDescription('计算投资奖金服务');
    }

    public function execute(Input $input, Output $output)
    {
        $this->writeln("开启用计算投资奖金服务");
        pcntl_signal(SIGINT, function ($sig) {
            $this->handler_quit($sig);
        });
        pcntl_signal(SIGTERM, function ($sig) {
            $this->handler_quit($sig);
        });
        pcntl_signal(SIGQUIT, function ($sig) {
            $this->handler_quit($sig);
        });
        for ($i = 0; $i < 8640 && $this->RunStatus == 1; $i++) {
            pcntl_signal_dispatch();
            $db = db('user_project');
            try {
                $log = $db->where('jisuanyeji_status=0 and tz_status=1')->find();
            } catch (Exception $e) {
                $this->writeln("Exception" . $e->getMessage());
                break;
            }
            if (!$log) {
                sleep(1);
                continue;
            }
            try {
                $r = $db->query("call app_run_bonus({$log['id']})");
            } catch (Exception $e) {
                $this->writeln("Exception" . $e->getMessage());
                break;
            }
            Log::write('计算奖金:' . $log['id'] . "结果:" . json_encode($r), 'info');
            unset($log);
            unset($r);
            unset($db);

        }
        $this->writeln("结束计算投资奖金服务");
    }


    public function writeln($messages)
    {
        $this->output->writeln(date('Y-m-d H:i:s') . "\t " . $messages);
    }

}