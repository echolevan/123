<?php
/**
 * Created by PhpStorm.
 * User: tears
 * Date: 2018/12/13
 * Time: 2:49 PM
 */

namespace app\ms_admin\model;


class AccountModel extends BaseModel
{
    protected $name = 'account';

    public function start($id)
    {
        try {

            $this->where('id', $id)->update(['status' => 1]);
            return msg(1, '', '启用成功');

        } catch (\Exception $e) {
            return msg(-1, '', $e->getMessage());
        }
    }

    public function stop($id)
    {
        try {

            $this->where('id', $id)->update(['status' => 0]);
            return msg(1, '', '禁用成功');

        } catch (\Exception $e) {
            return msg(-1, '', $e->getMessage());
        }
    }

    public function op_Balance($param, $validate, $url)
    {
        $vr = $this->validate($validate)->validateData($param);
        if (!$vr) {
            return msg(-1, '', $this->getError());
        }
        if (strtolower($param['account_type']) == 'eth') {
            $sql = "select app_f_op_eth_account(:memberid, :membername,:user_eth , :amount , 0 , '后台拨入', '收入',:remark) as r;";
            $data = [
                'memberid' => $param['user']['memberid'],
                'membername' => $param['user']['membername'],
                'user_eth' => $param['user']['eth_account'],
                'amount' => $param['number'],
                'remark' => "后台转入,操作员:" . session('id'),
            ];
        } else {
            $sql = "select app_f_op_wuc_account(:memberid, :membername,:user_wuc , :amount , 0 , '后台拨入', '收入',:remark) as r;";
            $data = [
                'memberid' => $param['user']['memberid'],
                'membername' => $param['user']['membername'],
                'user_wuc' => $param['user']['wuc_account'],
                'amount' => $param['number'],
                'remark' => "后台转入,操作员:" . session('id'),
            ];
        }
        try {
            $r = $this->query($sql, $data);
            if ($r[0]['r'] == 1) {
                return msg(1, $url, '拨通用账户成功');
            }
            return msg(-1, '', '拨通用账户失败');
        } catch (\Exception $e) {
            return msg(-1, '', $e->getMessage());
        }

    }
}