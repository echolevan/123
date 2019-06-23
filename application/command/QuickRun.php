<?php
/**
 * Created by PhpStorm.
 * User: tears
 * Date: 2018/12/7
 * Time: 3:38 PM
 */

namespace app\command;

use app\common\controller\YunXin;
use app\util\Tools;
use eth\driver\Api;
use think\Cache;
use think\console\Command;
use think\console\Input;
use think\console\Output;
use think\Db;
use think\Env;
use think\Exception;
use think\Log;

class QuickRun extends Command
{
    protected $RunStatus = 1;

    protected function handler_quit($sig)
    {
        $this->RunStatus = 0;
        $this->writeln("收到信号:" . $sig);
    }

    protected function configure()
    {
        $this->setName('QuickRun')->setDescription('测试执行');
        $this->addArgument('token');
    }

    public function execute(Input $input, Output $output)
    {
        $token = $input->getArgument('token');
        $this->writeln("开启测试执行");
        pcntl_signal(SIGINT, function ($sig) {
            $this->handler_quit($sig);
        });
        pcntl_signal(SIGTERM, function ($sig) {
            $this->handler_quit($sig);
        });
        pcntl_signal(SIGQUIT, function ($sig) {
            $this->handler_quit($sig);
        });
        pcntl_signal_dispatch();
//        $memberid = Tools::fileCache($token);
        $config['api_url'] = Env::get('dabi.api_url', 'http://127.0.0.1:8080');  // 接口URL
        $config['main_address'] = Env::get('dabi.main_address', '');  // 主钱包地址
        $config['token_address'] = Env::get('dabi.token_address', ''); // 操作的token地址
        $search = Api::getSearch($config);
        $s = $search->transactionReceipt($token);
        if (empty($s) || $s == false) {
            $data['tx_receipt_status'] = 'pending';
        } elseif (array_key_exists('success', $s)) {
            $data['tx_receipt_status'] = $s['success'] ? 'success' : 'fail';
            $data['tx_receipt_time'] = Db::raw('now()');
        } else {
            $data['tx_receipt_status'] = 'pending';
        }
        dump($data);
        $this->writeln("{$token}:t结束测试执行:" . json_encode($s));
    }

    public function writeln($messages)
    {
        $this->output->writeln(date('Y-m-d H:i:s') . "\t " . $messages);
    }

}