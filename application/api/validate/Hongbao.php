<?php
/**
 * Created by PhpStorm.
 * User: tears
 * Date: 2018/12/6
 * Time: 5:34 PM
 */

namespace app\api\validate;


use think\Db;
use think\Validate;

class Hongbao extends Validate
{

    protected $rule = [
        'to|红包接收对象' => 'require|alphaNum|checkTo:',
        'type|红包类型' => 'require|in:1,2,3',
        'title|红包标题' => 'require|length:1,20',
        'number|红包数量' => 'require|number|min:1|max:500|checkNum:',
        'money|红包钱数' => 'require|number|min:0.1|max:500|checkMoney:',
        'trade_password|交易密码' => 'require|number|length:6|checkTradePassword:',
    ];

    protected $message = [
        'to.checkTo' => '接收对象不正确',
        'trade_password.checkradePassword' => '交易密码不正确',
        'number.checkNum' => '红包数量不正确',
        'money.checkMoney' => '红包钱数不得超过500不得小于0.1',
    ];

    protected $scene = [

    ];

    protected function checkTradePassword($value, $rule, $data)
    {
        $memberid = $data['accid'];
        $member = Db::table("t_user")->where('memberid', 'eq', $memberid)->find();
        if ($member['login_error_num_pay'] >= 5) {
            return '今日交易操作已锁定';
        }
        $password = strtoupper(substr(md5($value), 8, 16));
        if ($password != $member['paypassword_zhifu']) {
            if ($member['login_error_num_pay'] == 4) {
                Db::table('t_log_errors')->insert([
                    'memberid' => $member['memberid'],
                    'ip' => \request()->ip(),
                    'type' => 'app_pay'
                ]);
            }
            Db::table('app_login_log')->insert([
                'memberid' => $memberid,
                'login_time' => date('Y-m-d H:i:s'),
                'login_ip' => \request()->ip(),
                'status' => 'fail',
                'times' => $member['login_error_num_pay'] + 1,
                'type' => 'app_pay'
            ]);
            Db::table('t_user')->where('memberid', 'eq', $memberid)->setInc('login_error_num_pay');
            $yu_num = 5 - ($member['login_error_num_pay'] + 1);
            return '交易密码不正确，您当天还有' . $yu_num . '次';
        }
        Db::table('t_user')->where('memberid', 'eq', $memberid)->update(['login_error_num_pay' => 0]);
        return true;
    }

    protected function checkto($id, $rule, $data)
    {
        $result = true;
        $type = $data['type'];
        switch ($type) {
            case 1:
                $r = Db::name('user')->where('memberid=:id')->bind(['id' => $id])->find();
                if (!$r) {
                    $result = false;
                } elseif ($data['userid'] == $r['id']) {
                    $result = false;
                }
                break;

        }

        return $result;
    }

    protected function checkNum($num, $rule, $data)
    {
        $result = true;
        $type = $data['type'];
        switch ($type) {
            case 1:
                if ($num != 1) {
                    $result = false;
                }
                break;

        }

        return $result;
    }

    protected function checkMoney($money, $rule, $data)
    {
        $result = true;
        //        $type=$data['type'];
        //        if($type==3){
        //            $money=$money*$data['number'];
        //        }
        if ($money / $data['number'] < 0.01) {
            $result = false;
        } elseif ($money / $data['number'] > 500) {
            $result = false;
        }

        return $result;
    }
}