<?php
/**
 * Created by PhpStorm.
 * User: tears
 * Date: 2018/12/15
 * Time: 5:32 PM
 */

namespace app\ms_admin\model;


use think\Db;

class CoinModel extends BaseModel
{
    protected $name = 'transactions';

    public function getTransaction($where, $address, $offset, $limit)
    {
        $db = Db::name('transactions');
        if (!empty($address)) {
            $db->where('from_address|to_address', $address);
        }
        return $db->where($where)->limit($offset, $limit)->order('id desc')->select()->toArray();
    }

    public function getTransactionCount($where, $address)
    {
        try {
            $db = Db::name('transactions');
            if (!empty($address)) {
                $db->whereor('from_address', $address)->whereor('to_address', $address);
            }
            return $db->where($where)->count();
        } catch (Exception $e) {
            return 0;
        }
    }

    public function start($id)
    {
        try {
            $coin = $this->where('id', $id)->find();
            if (!$coin) {
                return msg(-1, '', '信息不存在');
            }
            $user = Db::name('account')->where('address', 'eq', $coin['to_address'])->find();
            if (!$user) {
                return msg(-1, '', '用户信息不存在');
            }
            $fenkong = Db::query('select app_f_fenkong(:memberid) as fengkong_status',
                ['memberid' => $user['memberid']]);
            if ($fenkong[0]['fengkong_status'] == 0) {
                return msg(-1, '', '风控用户暂时无法使用该功能');
            }
            $this->where('id', $id)->update(['sh_status' => 1, 'sh_time' => $this->raw('now()')]);
            return msg(1, '', '审核成功');

        } catch (\Exception $e) {
            return msg(-1, '', $e->getMessage());
        }
    }

    public function restart($id)
    {
        try {
            $this->where('id', $id)->update(['tx_hash' => '', 'tx_receipt_status' => 'wait', 'dabi_status' => 0]);
            return msg(1, '', '重新打币成功');

        } catch (\Exception $e) {
            return msg(-1, '', $e->getMessage());
        }
    }

    public function back($id)
    {
        try {
            $info = $this->where('id', $id)->where('dabi_status=0  and sh_status=0')->find();
            $user = Db::name('account')->where('address', 'eq', $info['to_address'])->find();
            if ($info && $user) {
                $this->startTrans();
//                Tools::dbDebug();
                $r = $this->where('id', $id)->where('dabi_status=0 and sh_status=0')->update([
                    'sh_status' => 2,
                    'tx_receipt_status' => 'back',
                    'dabi_status' => -2
                ]);
                $account_type = strtolower($info['token_address']) == 'eth' ? "eth" : "wuc";
                $balance = strtolower($info['token_address']) == 'eth' ? $user['eth_account'] : $user['wuc_account'];
                $sql = "select app_f_op_{$account_type}_account(:str_memberid, :str_membername, :balance , :in_amount , :str_id , '提现', '收入',concat(\"提现退回\")) as res ; ";
                $data['str_memberid'] = $user['memberid'];
                $data['str_membername'] = $user['membername'];
                $data['balance'] = $balance;
                $data['in_amount'] = $info['sys_fee'] + $info['amount'];
                $data['str_id'] = $info['id'];
                $res_op = Db::query($sql, $data);
                $r1 = $res_op[0]['res'];
                if ($r && $r1) {
                    $this->commit();
                    return msg(1, '', '退回打币成功');
                } else {
                    $this->rollback();
                    return msg(-1, '', '退回打币失败');
                }
            }

        } catch (\Exception $e) {
            return msg(-1, '', $e->getMessage());
        }
    }
}