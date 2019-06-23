<?php
/**
 * Created by PhpStorm.
 * User: wandell
 * Date: 2018/9/19
 * Time: 17:31
 */

namespace app\command;

use think\console\Command;
use think\console\Input;
use think\console\Output;
use think\Db;
use think\Log;

class Receive extends Command
{

    protected function configure()
    {
        $this->setName('receive')->setDescription('system down get receive coin');
    }

    protected function execute(Input $input, Output $output)
    {
        echo date("Y-m-d H:i:s") . ':start' . PHP_EOL;
        if (Db::name('sys')->where(['id' => 1])->value('s_weihu') === 1) {
            echo date("Y-m-d H:i:s") . '===> system down ' . PHP_EOL;
            sleep(60);
            exit;
        }
        $list = Db::name('yw_qkl_shoubi')->where(['status' => '未转入', 'weihu' => '是', 'is_cl' => 0])->select();
        foreach ($list as $item) {
            if ($item['laiyuan'] == 'msth') {
                $procedure_name = "p_web_qkl_shoubi_wuc";
            } else {
                $procedure_name = "p_web_qkl_shoubi_wuc_2";
            }
            //更改余额
            $flag = db_query($procedure_name, ['id' => $item['id']]);
            if (1 != $flag['code']) {
                Log::write("update user balance failed! " . dump($flag, false) . ' ======> ' . dump($item, false));
            }
        }
        sleep(5);
        echo date("Y-m-d H:i:s") . ':end' . PHP_EOL;
    }
}