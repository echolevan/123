<?php
/**
 * Created by PhpStorm.
 * User: tears
 * Date: 2018/11/20
 * Time: 3:28 PM
 */

namespace app\command;


use app\util\Tools;
use eth\driver\Api;
use think\console\Command;
use think\console\Input;
use think\console\Output;
use think\Db;
use think\db\exception\DataNotFoundException;
use think\db\exception\ModelNotFoundException;
use think\Env;
use think\Exception;
use think\exception\DbException;
use think\exception\PDOException;
use think\Log;

class CoinOut extends Command
{
    protected $RunStatus = 1;

    protected function handler_quit($sig)
    {
        $this->RunStatus = 0;
        $this->writeln("收到信号:" . $sig);
    }

    protected function configure()
    {
        $this->setName('CoinOutService')->setDescription('打币服务');
    }

    public function execute(Input $input, Output $output)
    {
        $this->writeln("开启用打币服务");
        pcntl_signal(SIGINT, function ($sig) {
            $this->handler_quit($sig);
        });
        pcntl_signal(SIGTERM, function ($sig) {
            $this->handler_quit($sig);
        });
        pcntl_signal(SIGQUIT, function ($sig) {
            $this->handler_quit($sig);
        });
        $config['api_url'] = Env::get('dabi.api_url', 'http://127.0.0.1:8080');  // 接口URL
        $config['main_address'] = Env::get('dabi.main_address', '');  // 主钱包地址
        $config['token_address'] = Env::get('dabi.token_address', ''); // 操作的token地址

        for ($i = 0; $i < 8640 && $this->RunStatus == 1; $i++) {
            pcntl_signal_dispatch();
            $db = db('transactions');
            try {
                $log = $db->where('(tx_hash is null  or tx_hash=\'\') and tx_receipt_status="wait" and to_address is not null and from_address is not null and dabi_status=0 and amount>0 and sh_status=1')->find();
            } catch (Exception $e) {
                $this->writeln("Exception".$e->getMessage());
                break;
            }
            if (!$log) {
                sleep(1);
                continue;
            }
            $config['main_address'] = $log['from_address'];
            $account = Api::getAccount($config);
            $balance_eth = $account->balance($log['from_address']);
            if ($log['token_address'] == 'eth') {
                $balance = $balance_eth;
                $balance_eth -= $log['amount'];
            } else {
                $balance = $account->balance_token($log['from_address']);
            }
            if ($balance < $log['amount']) {
                Log::write('打币:' . $log['id'] . ":账号余额不足:" . $balance, 'error');
                $data['dabi_status'] = 2;
                $data['dabi_message'] = '账号余额不足';
            }
            $transaction = Api::getTransaction($config);
            $fee = $transaction->suggest_gas($log['to_address']);
            if ($fee > $balance_eth) {
                Log::write('打币:' . $log['id'] . ":账号手续费余额不足:" . $balance, 'error');
                $data['dabi_status'] = 2;
                $data['dabi_message'] = '账号手续费余额不足';
            }
            if ($log['token_address'] == 'eth') {
                $r = $transaction->transfer($log['to_address'], $log['amount']);
            } else {
                $r = $transaction->transfer_token($log['to_address'], $log['amount']);

            }
            if (empty($r) || $r == false) {
                Log::write('打币:' . $log['id'] . ":失败:" . json_encode($r), 'error');
                $data['dabi_status'] = 2;
                $data['dabi_message'] = 'fail';
            }
            if (empty($data)) {
                $data['tx_hash'] = $r;
                $data['tx_receipt_status'] = 'pending';
                $data['dabi_status'] = 1;
                $data['dabi_message'] = 'success';
            }
            $data['dabi_time'] = $db->raw('now()');
            try {
                $db->where('(tx_hash is null  or tx_hash=\'\')and tx_receipt_status="wait" and to_address is not null and from_address is not null and dabi_status=0 and amount>0')->where('id=' . $log['id'])->update($data);
            } catch (Exception $e) {
                $this->writeln("Exception".$e->getMessage());
                break;
            }
            Log::write('打币:' . $log['id'] . "结果:" . json_encode($r), 'info');
            unset($r);
            unset($data);
            unset($fee);
            unset($db);
            unset($log);
            unset($balance_eth);
            unset($transaction);
            unset($account);
        }
        $this->writeln("结束打币服务");
    }


    public function writeln($messages)
    {
        $this->output->writeln(date('Y-m-d H:i:s') . "\t " . $messages);
    }

}