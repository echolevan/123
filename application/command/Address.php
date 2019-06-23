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

class Address extends Command
{

    protected function configure()
    {
        $this->setName('address')->setDescription('Get members npdz');
    }

    protected function execute(Input $input, Output $output)
    {
        $config = [
            'api_url'       => config("coin_config.api_url"),
            'main_address'  => config("coin_config.main_address"),
            'token_address' => config("coin_config.msth_token_address"),
        ];
        echo date("Y-m-d H:i:s") . ':start' . PHP_EOL;
        $account = Api::getAccount($config);
        $list = Db::name("user")->where('npqbdz_wuc', null)->where('npqbdz_2', null)->limit(0, 100)->select();
        foreach ($list as $item) {
            $address = $account->newAddress();
            if (!$address) {
                echo $item['memberid'] . '更新钱包地址为 空 ' . PHP_EOL;
                continue;
            }
            $flag = Db::name("user")->where(['memberid' => $item['memberid']])->update(['npqbdz_wuc' => $address, 'npqbdz_2' => $address]);
            if (!$flag) {
                echo $item['memberid'] . '更新钱包地址失败' . PHP_EOL;
                break;
            }
            echo $item['memberid'] . '更新钱包地址成功' . PHP_EOL;
        }
        sleep(5);
        echo date("Y-m-d H:i:s") . ':end' . PHP_EOL;
    }
}