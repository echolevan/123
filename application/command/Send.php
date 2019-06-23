<?php
/**
 * Created by PhpStorm.
 * User: wandell
 * Date: 2018/9/19
 * Time: 17:31
 */

namespace app\command;

use eth\driver\Api;
use think\console\Command;
use think\console\Input;
use think\console\Output;
use think\Db;

class Send extends Command
{

    protected function configure()
    {
        $this->setName('send')->setDescription('send coin');
    }

    protected function execute(Input $input, Output $output)
    {
        $config = [
            'api_url'       => config("coin_config.api_url"),
            'main_address'  => config("coin_config.main_address"),
            'token_address' => config("coin_config.msth_token_address"),
        ];
        echo date("Y-m-d H:i:s") . ':start' . PHP_EOL;
        $transaction = Api::getTransaction($config);
        $list = Db::name("yw_qkl_dabi")->where(['status' => '已审', 'chuli' => 0])->order("id desc")->limit(0, 10)->select();
        foreach ($list as $item) {
            $txid = $transaction->transfer_token($item['address'], $item['money_dz']);
            if ($txid) {
                $data = [
                    'txid'   => $txid,
                    'chuli'  => 3,
                    'status' => '已打款'
                ];
            } else {
                $data = [
                    'chuli'       => 2,
                    'dabimessage' => json_encode($txid),
                ];
            }
            Db::name("yw_qkl_dabi")->where(['chuli' => 0, 'id' => $item['id']])->update($data);
        }
        sleep(5);
        echo date("Y-m-d H:i:s") . ':end' . PHP_EOL;
    }
}