<?php

namespace app\index\controller;

use think\Controller;
use think\Request;
use think\Db;
use think\Log;

class Login extends Controller
{
    /**
     * 收币
     */
    public function reviceCoin()
    {
        $input = file_get_contents('php://input', 'r');
        Log::write("come in ! \n" . dump($input, false));
        if (!$input) {
            return "MISS";
        }
        $param = json_decode($input, true)['data'];
        Log::write("222 ! \n" . dump($param, false));
        //收币记录
        $data = [
            'txid'          => $param['tx_hash'],
            'info'          => $input,
            'confirmations' => 1,
            'time'          => date("Y-m-d H:i:s"),
            'from_address'  => $param['from_address'],
            'to_address'    => $param['to_address'],
            'amount'        => $param['token'],
            'laiyuan'       => 'msth',
            'weihu'         => '否',
        ];
        $weihu = false;
        if (Db::name('sys')->where(['id' => 1])->value('s_weihu') === 1) {
            $weihu = true;
            $data['weihu'] = '是';
        }
        $procedure_name = "p_web_qkl_shoubi_wuc";
        if (strtolower($param['token_address']) != strtolower(config("coin_config.msth_token_address"))) {
            $data['laiyuan'] = 'mstm';
            $procedure_name = "p_web_qkl_shoubi_wuc_2";
        }
        $flag = Db::name("yw_qkl_shoubi")->insertGetId($data);
        if (!$flag) {
            Log::write("revice coin failed! " . dump($data, false));
            return 'ERROR';
        }
        if (!$weihu) {
            $id = $flag;
            //更改余额
            $flag = db_query($procedure_name, ['id' => $id]);
            if (1 != $flag['code']) {
                Log::write("update user balance failed! " . dump($flag, false));
            }
        }
        return 'SUCCESS';
    }
}
