<?php
/**
 * Created by PhpStorm.
 * User: tears
 * Date: 2018/12/8
 * Time: 11:03 AM
 */

namespace app\api\controller;


use app\api\model\BlockNumber;
use eth\driver\Api;
use think\Db;
use think\Env;
use think\Log;

class Coinin
{

    public function receive()
    {
        $input = file_get_contents('php://input', 'r');
        Log::write("收币原始数据:" . $input, 'info');
        if (!$input) {
            abort(404);
        }
        $param = json_decode($input, true)['data'];
//        Log::write("收币:" . json_encode($param), 'info');

        $data = [
            'tx_hash' => $param['tx_hash'],
            'from_address' => $param['from_address'],
            'to_address' => $param['to_address'],
            'amount' => $param['amount'],
            'block_number' => $param['blockNumber'],
            'block_hash' => $param['blockHash'],
            'token_address' => 'eth',
            'biz_type' => '收币',
            'dabi_status' => '-1',
        ];
        // 执行更新区块

        BlockNumber::app_update_block_num($param['blockNumber'], $param['blockHash']);


        if (array_key_exists('token_address', $param)) {
            if (strtolower($param['token_address']) != strtolower(Env::get('dabi.token_address', ''))) {
                return 'fail';
            }
            $data['token_address'] = $param['token_address'];
        }
        // 查询交易是否存在
        $log = Db::name("transactions")->where('tx_hash', 'eq', $param['tx_hash'])->find();
        $replace = false;
        if ($log) {
            $replace = true;
            $data = $log;
            $data['block_number'] = $param['blockNumber'];
            $data['block_hash'] = $param['blockHash'];
        }
        // 验证交易状态
        $config['api_url'] = Env::get('dabi.api_url', 'http://127.0.0.1:8080');  // 接口URL
        $config['main_address'] = Env::get('dabi.main_address', '');  // 主钱包地址
        $config['token_address'] = Env::get('dabi.token_address', ''); // 操作的token地址
        if (!array_key_exists('tx_receipt_status', $data) || $data['tx_receipt_status'] != 'success') {
//            $search = Api::getSearch($config);
//            $s = $search->transactionReceipt($param['tx_hash']);
//            if (empty($s) || $s == false) {
//                $data['tx_receipt_status'] = 'pending';
//            } elseif (array_key_exists('success', $s)) {
//                $data['tx_receipt_status'] = $s['success'] ? 'success' : 'fail';
                $data['tx_receipt_status'] = 'success';
                $data['tx_receipt_time'] = Db::raw('now()');
//            } else {
//                $data['tx_receipt_status'] = 'pending';
//            }
            Db::startTrans();
            $flag = Db::name("transactions")->insert($data, $replace);
            if (!$flag) {
                Log::write("收币存储数据失败:" . json_encode($flag), 'error');
                Db::rollback();
                abort(400);
            }
            // 验证是否是圈存
            if (array_key_exists('biz_type', $data) && $data['biz_type'] == "圈存" && $log['id'] > 0) {
                $r = Db::query("call app_quancun_daozhang({$log['id']},'{$data['tx_hash']}')");
                if ($r[0][0]['r'] != '成功') {
                    Log::write("收币圈存失败:" . json_encode($r), 'error');
                    Db::rollback();
                    abort(400);
                }
            }
            Db::commit();
        }
        return 'SUCCESS';
    }

    public function BlockNotify()
    {
        $input = file_get_contents('php://input', 'r');
        Log::write("区块通知原始数据:" . $input, 'info');
        if (!$input) {
            abort(404);
        }
        $param = json_decode($input, true)['data'];
        // 执行更新区块
        BlockNumber::app_update_block_num($param['blockNumber'], $param['blockHash']);
        return 'SUCCESS';
    }
}