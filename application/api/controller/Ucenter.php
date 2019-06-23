<?php

namespace app\api\controller;

use app\common\controller\YunXin;
use OSS\Core\OssException;
use OSS\OssClient;
use think\Controller;
use think\Db;
use think\Env;
use think\Exception;
use think\Request;
use app\util\Tools;

class Ucenter extends Common
{

    /**
     * 个人中心
     * @throws \ErrorException
     */
    public function index()
    {
        $accids[0] = $this->app_member['memberid'];
        $yun_xin = new YunXin();
        $yun_xin_result = $yun_xin->getUinfoss($accids);
        if (isset($yun_xin_result->code) && $yun_xin_result->code != 200) {
            return json(arr_msg(-1, '', '获取用户名片信息失败'));
        }
        $icon = request()->domain() . '/static/index/images/head_logo.png';
        if (isset($yun_xin_result->uinfos[0]->icon)) {
            $icon = $yun_xin_result->uinfos[0]->icon;
        }
        $sign = '';
        if (isset($yun_xin_result->uinfos[0]->sign)) {
            $sign = $yun_xin_result->uinfos[0]->sign;
        }
        $nick_name = '';
        if (isset($yun_xin_result->uinfos[0]->name)) {
            $nick_name = $yun_xin_result->uinfos[0]->name;
        }

        $data = [
            'head_img'    => $icon,
            'autograph'   => $sign,
            'membername'  => $this->app_member['membername'],
            'memberid'    => $this->app_member['memberid'],
            'yx_token'    => $this->app_member['yx_token'],
            'mobile'      => $this->app_member['mobile'],
            'mobile_hide' => init_string($this->app_member['mobile'], 3, 4),
//            'email'       => email_string($this->app_member['email'], 4),
            'nick_name'   => $nick_name
        ];

        return json(arr_msg(1, $data, 'success'));
    }

    /**
     * 个人设置
     * @throws \ErrorException
     */
    public function personSet()
    {
        $autograph = input('param.autograph');
        $nick_name = input('param.nick_name');
        $data = [
            'autograph' => $autograph,
            'nick_name' => $nick_name
        ];
        $flag = $this->validate($data, "IndexValidate.personSet");
        if (true !== $flag) {
            return json(arr_msg(-1, '', $flag));
        }
        $yun_xin = new YunXin();
        $yun_xin_result = $yun_xin->updateUinfo($this->app_member['memberid'], $data['nick_name'], '', $data['autograph']);
        if (isset($yun_xin_result->code) && $yun_xin_result->code != 200) {
            return json(arr_msg(-1, '', '更新用户名片失败'));
        }

        return json(arr_msg(1, '', '更新用户成功'));
    }

    /**
     * 修改资料
     * @return \think\response\Json
     */
    public function editProfile()
    {
        $param = input("param.");
        $flag = $this->validate($param, "IndexValidate.editProfile");
        if (true !== $flag) {
            return json(arr_msg(-1, '', $flag));
        }

        $token = md5(time() . rand(11, 99));
        cache("profile_" . $this->app_member['memberid'], $token, 3600);

        return json(arr_msg(1, ['profile_token' => $token], 'success'));
    }

    /**
     * 发送验证码
     * @return \think\response\Json
     */
    public function sendSms()
    {
        $param = input("param.");
        $flag = $this->validate($param, "IndexValidate.sendSms");
        if (true !== $flag) {
            return json(arr_msg(-1, '', $flag));
        }

//        $flag = $param['check_type'] == 'mobile' ? send_mobile_code($this->app_member['mobile']) : send_email_code($this->app_member['email']);
        $flag = send_mobile_code($this->app_member['mobile']);

        return json($flag);
    }

    /**
     * 修改手机号
     * @return \think\response\Json
     * @throws \Exception
     */
    public function editMobile()
    {
        $param = input("param.");
        $flag = $this->validate($param, "IndexValidate.editMobile");
        if (true !== $flag) {
            return json(arr_msg(-1, '', $flag));
        }

        $count = Db::table("t_user")->where('mobile', 'eq', $param['mobile'])->count(1);
        if ($count >= $this->sys[1002]) {
            return json(arr_msg(-3, '', '每个手机号最多注册' . $this->sys[1002] . '次'));
        }

        $flag = Db::table("t_user")->where('memberid', 'eq', $this->app_member['memberid'])->update(['mobile' => $param['mobile']]);
        if (!$flag) {
            return json(arr_msg(-2, '', '修改手机号失败'));
        }
        cache("profile_" . $this->app_member['memberid'], null);

        return json(arr_msg(1, '', '修改手机号成功'));
    }

    /**
     * 修改邮箱
     * @return \think\response\Json
     * @throws \Exception
     */
    public function editEmail()
    {
        $param = input("param.");
        $flag = $this->validate($param, "IndexValidate.editEmail");
        if (true !== $flag) {
            return json(arr_msg(-1, '', $flag));
        }
        $count = Db::table("t_user")->where('email', 'eq', $param['email'])->count(1);
        if ($count >= $this->sys[1002]) {
            return json(arr_msg(-3, '', '每个邮箱最多注册' . $this->sys[1002] . '次'));
        }
        $flag = Db::table("t_user")->where('memberid', 'eq', $this->app_member['memberid'])->update(['email' => $param['email']]);
        if (!$flag) {
            return json(arr_msg(-2, '', '修改邮箱失败'));
        }
        cache("profile_" . $this->app_member['memberid'], null);

        return json(arr_msg(1, '', '修改邮箱成功'));
    }

    /**
     * 修改密码
     * @return \think\response\Json
     * @throws \Exception
     */
    public function editPassword()
    {
        $param = input("param.");
        $flag = $this->validate($param, "IndexValidate.editPassword");
        if (true !== $flag) {
            return json(arr_msg(-1, '', $flag));
        }

        $password = strtoupper(substr(md5($param['password']), 8, 16));
        if ($password == $this->app_member['paypassword']) {
            return json(arr_msg(-2, '', '新旧密码不可一致'));
        }
        $flag = Db::table("t_user")->where('memberid', 'eq', $this->app_member['memberid'])->update(['paypassword' => $password]);
        if (!$flag) {
            return json(arr_msg(-3, '', '修改密码失败'));
        }
        cache("profile_" . $this->app_member['memberid'], null);

        return json(arr_msg(1, '', '修改密码成功'));
    }

    /**
     * 修改交易密码
     * @throws \Exception
     */
    public function editPayPassword()
    {
        $param = input("param.");
        $flag = $this->validate($param, "IndexValidate.editPayPassword");
        if (true !== $flag) {
            return json(arr_msg(-1, '', $flag));
        }

        $password = strtoupper(substr(md5($param['cipher']), 8, 16));
        if ($password == $this->app_member['paypassword_zhifu']) {
            return json(arr_msg(-2, '', '新旧密码不可一致'));
        }
        $flag = Db::table("t_user")->where('memberid', 'eq', $this->app_member['memberid'])->update(['paypassword_zhifu' => $password]);
        if (!$flag) {
            return json(arr_msg(-3, '', '修改交易密码失败'));
        }
        cache("profile_" . $this->app_member['memberid'], null);

        return json(arr_msg(1, '', '修改交易密码成功'));
    }

    /**
     * 收货地址列表
     * @throws \Exception
     */
    public function mallAddress()
    {
        if ('开启' != $this->sys[1007004]) {
            return json(arr_msg(-1, '', '此功能暂时关闭，请稍后再试'));
        }
        $list = Db::table('t_shop_user_address')->where('memberid', 'eq', $this->app_member['memberid'])->order('default desc,id desc')->select();

        return json(arr_msg(1, ['list' => $list], 'success'));
    }

    /**
     * 收货地址新增
     * @throws \Exception
     */
    public function mallAddressAdd()
    {
        $param = input('param.');
        $flag = $this->validate($param, 'IndexValidate.mallAddressAdd');
        if (true !== $flag) {
            return json(arr_msg(-1, '', $flag));
        }
        if ('开启' != $this->sys[1007004]) {
            return json(arr_msg(-1, '', '此功能暂时关闭，请稍后再试'));
        }
        Db::startTrans();
        $address = Db::table('t_shop_user_address')->where('memberid', 'eq', $this->app_member['memberid'])->where('default', 'eq', 1)->find();
        $rec = true;
        if (!empty($address) && 1 == $param['address_default']) {
            $rec = Db::table('t_shop_user_address')->where('memberid', 'eq', $this->app_member['memberid'])->update(['default' => 0]);
        }
        $data = [
            'memberid'     => $this->app_member['memberid'],
            'address'      => trim(str_fun($param['address'])),
            'address_info' => trim(str_fun($param['address_info'])),
            'name'         => trim(str_fun($param['address_name'])),
            'mobile'       => trim(str_fun($param['address_mobile'])),
            'default'      => trim($param['address_default'])
        ];
        $result = Db::table('t_shop_user_address')->insert($data);
        if (!$result || !$rec) {
            Db::rollback();
            return json(arr_msg(-1, '', '新增收货地址失败'));
        }

        Db::commit();
        return json(arr_msg(1, '', '新增收货地址成功'));
    }

    /**
     * 收货地址详情
     * @throws \Exception
     */
    public function mallAddressRead()
    {
        $param = input('param.');
        $flag = $this->validate($param, 'IndexValidate.mallAddressRead');
        if (true !== $flag) {
            return json(arr_msg(-1, '', $flag));
        }
        if ('开启' != $this->sys[1007004]) {
            return json(arr_msg(-1, '', '此功能暂时关闭，请稍后再试'));
        }
        $address = Db::table('t_shop_user_address')->where('id', 'eq', $param['address_id'])->find();

        return json(arr_msg(1, ['info' => $address], 'success'));
    }

    /**
     * 收货地址编辑
     * @throws \Exception
     */
    public function mallAddressEdit()
    {
        $param = input('param.');
        $flag = $this->validate($param, 'IndexValidate.mallAddressEdit');
        if (true !== $flag) {
            return json(arr_msg(-1, '', $flag));
        }
        if ('开启' != $this->sys[1007004]) {
            return json(arr_msg(-1, '', '此功能暂时关闭，请稍后再试'));
        }
        Db::startTrans();
        $address = Db::table('t_shop_user_address')->where('memberid', 'eq', $this->app_member['memberid'])->where('default', 'eq', 1)->find();
        $rec = true;
        if (!empty($address) && 1 == $param['address_default']) {
            $rec = Db::table('t_shop_user_address')->where('memberid', 'eq', $this->app_member['memberid'])->update(['default' => 0]);
        }
        $data = [
            'address'      => trim(str_fun($param['address'])),
            'address_info' => trim(str_fun($param['address_info'])),
            'name'         => trim(str_fun($param['address_name'])),
            'mobile'       => trim(str_fun($param['address_mobile'])),
            'default'      => trim($param['address_default'])
        ];
        $result = Db::table('t_shop_user_address')->where('id', 'eq', $param['address_id'])->where('memberid', 'eq', $this->app_member['memberid'])->update($data);
        if (!$result || !$rec) {
            Db::rollback();
            return json(arr_msg(-1, '', '收货地址修改失败'));
        }

        Db::commit();
        return json(arr_msg(1, '', '收货地址修改成功'));
    }

    /**
     * 收货地址删除
     * @throws \Exception
     */
    public function mallAddressDel()
    {
        $param = input('param.');
        $flag = $this->validate($param, 'IndexValidate.mallAddressDel');
        if (true !== $flag) {
            return json(arr_msg(-1, '', $flag));
        }
        if ('开启' != $this->sys[1007004]) {
            return json(arr_msg(-1, '', '此功能暂时关闭，请稍后再试'));
        }
        $result = Db::table('t_shop_user_address')->where('id', 'eq', $param['address_id'])->delete();
        if (!$result) {
            return json(arr_msg(-1, '', '收货地址删除失败'));
        }

        return json(arr_msg(1, '', '收货地址删除成功'));
    }

    /**
     * 收货地址默认
     * @throws \Exception
     */
    public function mallAddressDefault()
    {
        $param = input('param.');
        $flag = $this->validate($param, 'IndexValidate.mallAddressDefault');
        if (true !== $flag) {
            return json(arr_msg(-1, '', $flag));
        }
        if ('开启' != $this->sys[1007004]) {
            return json(arr_msg(-1, '', '此功能暂时关闭，请稍后再试'));
        }
        Db::startTrans();
        $address = Db::table('t_shop_user_address')->where('memberid', 'eq', $this->app_member['memberid'])->where('default', 'eq', 1)->find();
        $rec = true;
        if (!empty($address)) {
            $rec = Db::table('t_shop_user_address')->where('memberid', 'eq', $this->app_member['memberid'])->update(['default' => 0]);
        }
        $result = Db::table('t_shop_user_address')->where('id', 'eq', $param['address_id'])->where('memberid', 'eq', $this->app_member['memberid'])->update(['default' => 1]);
        if (!$rec || !$result) {
            Db::rollback();
            return json(arr_msg(-1, '', '设置默认收货地址失败'));
        }

        Db::commit();
        return json(arr_msg(1, '', '设置默认收货地址成功'));
    }

    /**
     * 我的商城订单
     */
    public function mallMyOrder()
    {
        $param = input('param.');
        $flag = $this->validate($param, 'IndexValidate.mallMyOrder');
        if (true !== $flag) {
            return json(arr_msg(-1, '', $flag));
        }
        try {
            $order = [];
            $count = 0;
            $limit = $param['page_size'];
            $offset = ($param['page'] - 1) * $limit;
            switch ($param['order_status']) {
                case 1:
                    $count = Db::table('t_shop_order')->where('memberid', 'eq', $this->app_member['memberid'])->where('status', 'in', '1,2,3,4,5,6')->count('id');
                    $order = Db::table('t_shop_order')->field('id,products_img,goods_name,products_sell_price,order_num,payable_amount,real_amount,payable_freight,real_freight,order_num,status,distribution_status,goods_market_price,products_market_price,order_amount,spec_array,order_no,create_time')->where('memberid', 'eq', $this->app_member['memberid'])->where('status', 'in', '1,2,3,4,5,6')->limit($offset, $limit)->order('id desc')->select();
                    break;
                case 2:
                    $count = Db::table('t_shop_order')->where('memberid', 'eq', $this->app_member['memberid'])->where('status', 'eq', 2)->where('distribution_status', 'eq', 0)->count('id');
                    $order = Db::table('t_shop_order')->field('id,products_img,goods_name,products_sell_price,order_num,payable_amount,real_amount,payable_freight,real_freight,order_num,status,distribution_status,goods_market_price,products_market_price,order_amount,spec_array,order_no,create_time')->where('memberid', 'eq', $this->app_member['memberid'])->where('status', 'eq', 2)->where('distribution_status', 'eq', 0)->limit($offset, $limit)->order('id desc')->select();
                    break;
                case 3:
                    $count = Db::table('t_shop_order')->where('memberid', 'eq', $this->app_member['memberid'])->where('status', 'eq', 2)->where('distribution_status', 'eq', 1)->count('id');
                    $order = Db::table('t_shop_order')->field('id,products_img,goods_name,products_sell_price,order_num,payable_amount,real_amount,payable_freight,real_freight,order_num,status,distribution_status,goods_market_price,products_market_price,order_amount,spec_array,order_no,create_time')->where('memberid', 'eq', $this->app_member['memberid'])->where('status', 'eq', 2)->where('distribution_status', 'eq', 1)->limit($offset, $limit)->order('id desc')->select();
                    break;
                case 4:
                    $count = Db::table('t_shop_order')->where('memberid', 'eq', $this->app_member['memberid'])->where('status', 'eq', 5)->count('id');
                    $order = Db::table('t_shop_order')->field('id,products_img,goods_name,products_sell_price,order_num,payable_amount,real_amount,payable_freight,real_freight,order_num,status,distribution_status,goods_market_price,products_market_price,order_amount,spec_array,order_no,create_time')->where('memberid', 'eq', $this->app_member['memberid'])->where('status', 'eq', 5)->limit($offset, $limit)->order('id desc')->select();
                    break;
                case 5:
                    $count = Db::table('t_shop_order')->where('memberid', 'eq', $this->app_member['memberid'])->where('status', 'eq', 1)->where('pay_status', 'eq', 0)->count('id');
                    $order = Db::table('t_shop_order')->field('id,products_img,goods_name,products_sell_price,order_num,payable_amount,real_amount,payable_freight,real_freight,order_num,status,distribution_status,goods_market_price,products_market_price,order_amount,spec_array,order_no,create_time')->where('memberid', 'eq', $this->app_member['memberid'])->where('status', 'eq', 1)->where('pay_status', 'eq', 0)->limit($offset, $limit)->order('id desc')->select();
                    break;
            }
            $order_list = $order->toArray();
            foreach ($order_list as $key => $value) {
                $status_str = '';
                if (1 == $value['status']) {
                    $status_str = '待付款';
                } elseif (2 == $value['status'] && 0 == $value['distribution_status']) {
                    $status_str = '待发货';
                } elseif (2 == $value['status'] && 1 == $value['distribution_status']) {
                    $status_str = '待收货';
                } elseif (3 == $value['status']) {
                    $status_str = '取消订单';
                } elseif (4 == $value['status']) {
                    $status_str = '作废订单';
                } elseif (5 == $value['status']) {
                    $status_str = '已完成';
                }
                $order_list[$key]['status_str'] = $status_str;
                $spec_array = json_decode($value['spec_array'], true);
                $spec_array_str = '';
                foreach ($spec_array as $k => $v) {
                    $spec_array_str .= $v['name'] . ':' . $v['tip'] . '；';
                }
                $order_list[$key]['spec'] = $spec_array_str;
                unset($order_list[$key]['status']);
                unset($order_list[$key]['distribution_status']);
                unset($order_list[$key]['spec_array']);
            }
            $total_quan_num = Db::table('t_shop_order')->where('memberid', 'eq', $this->app_member['memberid'])->where('status', 'in', '1,2,3,4,5,6')->count('id');
            $total_fa_num = Db::table('t_shop_order')->where('memberid', 'eq', $this->app_member['memberid'])->where('status', 'eq', 2)->where('distribution_status', 'eq', 0)->count('id');
            $total_shou_num = Db::table('t_shop_order')->where('memberid', 'eq', $this->app_member['memberid'])->where('status', 'eq', 2)->where('distribution_status', 'eq', 1)->count('id');
            $total_wan_num = Db::table('t_shop_order')->where('memberid', 'eq', $this->app_member['memberid'])->where('status', 'eq', 5)->count('id');
            $total_no_pay_num = Db::table('t_shop_order')->where('memberid', 'eq', $this->app_member['memberid'])->where('status', 'eq', 1)->where('pay_status', 'eq', 0)->count('id');

            return json(arr_msg(1, ['list' => $order_list, 'quan_bu' => $total_quan_num, 'dai_fa' => $total_fa_num, 'dai_shou' => $total_shou_num, 'wan_cheng' => $total_wan_num, 'total_no_pay_num' => $total_no_pay_num, 'pages' => ceil($count / $limit)], 'success'));
        } catch (\Exception $e) {
            return json(arr_msg(-1, '', $e->getMessage()));
        }
    }

    /**
     * 我的订单查看物流
     * @throws \Exception
     */
    public function mallExpress()
    {
        $param = input('param.');
        $flag = $this->validate($param, 'IndexValidate.mallExpress');
        if (true !== $flag) {
            return json(arr_msg(-1, '', $flag));
        }
        $order = Db::table('t_shop_order')->field('express_name,express_code')->where('id', 'eq', $param['order_id'])->where('memberid', 'eq', $this->app_member['memberid'])->where('status', 'eq', 2)->where('distribution_status', 'eq', 1)->where('pay_status', 'eq', 1)->find();
        if (empty($order)) {
            return json(arr_msg(-1, '', '此订单还未进行发货，无法查看快递信息！'));
        }

        return json(arr_msg(1, ['express_name' => $order['express_name'], 'express_code' => $order['express_code']], 'success'));
    }

    /**
     * 我的订单确认收货
     * @throws \Exception
     */
    public function mallTakeGoods()
    {
        $param = input('param.');
        $flag = $this->validate($param, 'IndexValidate.mallTakeGoods');
        if (true !== $flag) {
            return json(arr_msg(-1, '', $flag));
        }
        $order = Db::table('t_shop_order')->field('express_name,express_code')->where('id', 'eq', $param['order_id'])->where('memberid', 'eq', $this->app_member['memberid'])->where('status', 'eq', 2)->where('distribution_status', 'eq', 1)->where('pay_status', 'eq', 1)->find();
        if (empty($order)) {
            return json(arr_msg(-1, '', '此订单还未进行发货，无法确认收货！'));
        }
        try {
            $order_rec = Db::table('t_shop_order')->where('id', 'eq', $param['order_id'])->update(['status' => 5, 'accept_time' => date('Y-m-d H:i:s'), 'completion_time' => date('Y-m-d H:i:s')]);
            if (!$order_rec) {
                return json(arr_msg(-1, '', '确认收货失败！'));
            }

            return json(arr_msg(1, '', '确认收货成功！'));
        } catch (\Exception $e) {
            return json(arr_msg(-1, '', $e->getMessage()));
        }
    }

    /**
     * 我的订单取消订单
     * @throws \Exception
     */
    public function mallDelOrder()
    {
        $param = input('param.');
        $flag = $this->validate($param, 'IndexValidate.mallDelOrder');
        if (true !== $flag) {
            return json(arr_msg(-1, '', $flag));
        }
        $order = Db::table('t_shop_order')->field('express_name,express_code')->where('id', 'eq', $param['order_id'])->where('memberid', 'eq', $this->app_member['memberid'])->where('status', 'eq', 1)->where('pay_status', 'eq', 0)->find();
        if (empty($order)) {
            return json(arr_msg(-1, '', '订单状态非生成订单状态，无法进行取消订单！'));
        }
        try {
            $order_rec = Db::table('t_shop_order')->where('id', 'eq', $param['order_id'])->update(['status' => 3, 'qu_xiao_time' => date('Y-m-d H:i:s')]);
            if (!$order_rec) {
                return json(arr_msg(-1, '', '取消订单失败！'));
            }

            return json(arr_msg(1, '', '取消订单成功！'));
        } catch (\Exception $e) {
            return json(arr_msg(-1, '', $e->getMessage()));
        }
    }

    /**
     * 我的团队接口
     */
    public function myTeam()
    {
        $param = input('param.');
        $flag = $this->validate($param, 'IndexValidate.myTeam');
        if (true !== $flag) {
            return json(arr_msg(-1, '', $flag));
        }
        try {
            $limit = $param['page_size'];
            $offset = ($param['page'] - 1) * $limit;
            $share_num = Db::table('t_user')->where('tuijianren_id', 'eq', $this->app_member['memberid'])->count('id');
            $share = Db::table('t_user')->field('id,memberid,reg_time')->where('tuijianren_id', 'eq', $this->app_member['memberid'])->limit($offset, $limit)->order('id desc')->select();

            return json(arr_msg(1, ['touzimoney_yeji_sum' => $this->app_member['touzimoney_yeji_sum'], 'share_num' => $share_num, 'share' => $share, 'pages' => ceil($share_num / $limit)], 'success'));
        } catch (\Exception $e) {
            return json(arr_msg(-1, '', $e->getMessage()));
        }
    }

    /**
     * 钱包地址列表
     */
    public function addressList()
    {
        try {
            $list = Db::name("b_qianbaoaddress")->where('memberid', 'eq', $this->app_member['memberid'])->where('status', 'eq', '已验证')->field("id,qbname,qklqbdz,status")->order('id desc')->select();

            return json(arr_msg(1, ['list' => $list], 'success'));
        } catch (\Exception $e) {
            return json(arr_msg(-1, '', $e->getMessage()));
        }
    }

    /**
     * 钱包地址新增
     */
    public function addressAdd()
    {
        $param = input("param.");
        $flag = $this->validate($param, "IndexValidate.addressAdd");
        if (true !== $flag) {
            return json(arr_msg(-1, '', $flag));
        }
        try {
            $data = [
                'memberid' => $this->app_member['memberid'],
                'qbname'   => str_fun($param['qbname']),
                'qklqbdz'  => $param['qklqbdz'],
                'status'   => '已验证',
            ];
            $result = Db::name("b_qianbaoaddress")->insert($data);
            if (!$result) {
                return json(arr_msg(-1, '', '添加钱包地址失败'));
            }

            return json(arr_msg(1, '', '添加钱包地址成功'));
        } catch (\Exception $e) {
            return json(arr_msg(-1, '', $e->getMessage()));
        }
    }

    /**
     * 钱包地址删除
     */
    public function addressDel()
    {
        $param = input("param.");
        $flag = $this->validate($param, "IndexValidate.addressDel");
        if (true !== $flag) {
            return json(arr_msg(-1, '', $flag));
        }
        try {
            $result = Db::name('b_qianbaoaddress')->where('memberid', 'eq', $this->app_member['memberid'])->where('id', 'eq', intval($param['lian_address_id']))->update(['status' => '已删除']);
            if (!$result) {
                return json(arr_msg(-1, '', '钱包地址删除失败'));
            }

            return json(arr_msg(1, '', '钱包地址删除成功'));
        } catch (\Exception $e) {
            return json(arr_msg(-1, '', $e));
        }
    }

    /**
     * 充证明细
     * @return \think\response\Json
     */
    public function chargeCardList()
    {
        $param = input('param.');
        $flag = $this->validate($param, 'IndexValidate.chargeCardList');
        if (true !== $flag) {
            return json(arr_msg(-1, '', $flag));
        }
        $limit = $param['page_size'];
        $offset = ($param['page'] - 1) * $limit;
        try {
            $count = Db::name('yw_qkl_shoubi')->where('memberid', 'eq', $this->app_member['memberid'])->count('id');
            $list = Db::name('yw_qkl_shoubi')->field('id,i_time,amount')->where('memberid', 'eq', $this->app_member['memberid'])->limit($offset, $limit)->order('id desc')->select()->each(function ($model) {
                $model['i_time'] = date('Y-m-d H:i', strtotime($model['i_time']));
                $model['amount'] = format_num($model['amount']);

                return $model;
            });

            return json(arr_msg(1, ['list' => $list, 'pages' => ceil($count / $limit)], 'success'));
        } catch (\Exception $e) {
            return json(arr_msg(-1, '', $e->getMessage()));
        }
    }

    /**
     * 充证明细详情
     * @return \think\response\Json
     */
    public function chargeCardDet()
    {
        $param = input('param.');
        $flag = $this->validate($param, 'IndexValidate.chargeCardDet');
        if (true !== $flag) {
            return json(arr_msg(-1, '', $flag));
        }
        try {
            $list = Db::name('yw_qkl_shoubi')->field('id,from_address,to_address,txid,i_time,amount,status,laiyuan')->where('memberid', 'eq', $this->app_member['memberid'])->where('id', 'eq', intval($param['charge_id']))->find();
            if (empty($list)) {
                return json(arr_msg(-1, '', '不存在此明细详情'));
            }
            $list['i_time'] = date('Y-m-d H:i', strtotime($list['i_time']));
            $list['amount'] = format_num($list['amount']);

            return json(arr_msg(1, ['list' => $list], 'success'));
        } catch (\Exception $e) {
            return json(arr_msg(-1, '', $e->getMessage()));
        }
    }

    /**
     * 提证明细
     * @return \think\response\Json
     */
    public function carryCardList()
    {
        $param = input('param.');
        $flag = $this->validate($param, 'IndexValidate.carryCardList');
        if (true !== $flag) {
            return json(arr_msg(-1, '', $flag));
        }
        $limit = $param['page_size'];
        $offset = ($param['page'] - 1) * $limit;
        try {
            $count = Db::name('yw_qkl_dabi')->where('memberid', 'eq', $this->app_member['memberid'])->count('id');
            $list = Db::name('yw_qkl_dabi')->field('id,op_time,money,money_dz')->where('memberid', 'eq', $this->app_member['memberid'])->limit($offset, $limit)->order('id desc')->select()->each(function ($model) {
                $model['op_time'] = date('Y-m-d H:i', strtotime($model['op_time']));
                $model['money_dz'] = format_num($model['money_dz']);
                $model['money'] = format_num($model['money']);

                return $model;
            });

            return json(arr_msg(1, ['list' => $list, 'pages' => ceil($count / $limit)], 'success'));
        } catch (\Exception $e) {
            return json(arr_msg(-1, '', $e->getMessage()));
        }
    }

    /**
     * 提证明细详情
     * @return \think\response\Json
     */
    public function carryCardDet()
    {
        $param = input('param.');
        $flag = $this->validate($param, 'IndexValidate.carryCardDet');
        if (true !== $flag) {
            return json(arr_msg(-1, '', $flag));
        }
        try {
            $list = Db::name('yw_qkl_dabi')->field('id,address as to_address,txid,op_time,money,money_sxf,money_dz,status,laiyuan')->where('memberid', 'eq', $this->app_member['memberid'])->where('id', 'eq', intval($param['carry_id']))->find();
            if (empty($list)) {
                return json(arr_msg(-1, '', '不存在此明细详情'));
            }
            $list['op_time'] = date('Y-m-d H:i', strtotime($list['op_time']));
            $list['money'] = format_num($list['money']);
            $list['money_sxf'] = format_num($list['money_sxf']);
            $list['money_dz'] = format_num($list['money_dz']);
            $list['from_address'] = Env::get('dabi.main_address');

            return json(arr_msg(1, ['list' => $list], 'success'));
        } catch (\Exception $e) {
            return json(arr_msg(-1, '', $e->getMessage()));
        }
    }

    /**
     * 互转明细
     * @return \think\response\Json
     */
    public function interturnList()
    {
        $param = input('param.');
        $flag = $this->validate($param, 'IndexValidate.interturnList');
        if (true !== $flag) {
            return json(arr_msg(-1, '', $flag));
        }
        $limit = $param['page_size'];
        $offset = ($param['page'] - 1) * $limit;
        try {
            $count = Db::name('yw_tuandui_huzhuan')->where('memberid|to_memberid', 'eq', $this->app_member['memberid'])->count('id');
            $list = Db::name('yw_tuandui_huzhuan')->field('id,memberid,to_memberid,lrdate,num_dz,num')->where('memberid|to_memberid', 'eq', $this->app_member['memberid'])->limit($offset, $limit)->order('id desc')->select()->each(function ($model) {
                $model['lrdate'] = date('Y-m-d H:i', strtotime($model['lrdate']));
                $fu_hao = '';
                if ($model['memberid'] == $this->app_member['memberid']) {
                    $fu_hao = '-';
                    $model['num_dz'] = $model['num'] >= 0 ? $fu_hao . format_num($model['num']) : format_num($model['num']);
                } elseif ($model['to_memberid'] == $this->app_member['memberid']) {
                    $fu_hao = '+';
                    $model['num_dz'] = $model['num_dz'] >= 0 ? $fu_hao . format_num($model['num_dz']) : format_num($model['num_dz']);
                }

                return $model;
            });

            return json(arr_msg(1, ['list' => $list, 'pages' => ceil($count / $limit)], 'success'));
        } catch (\Exception $e) {
            return json(arr_msg(-1, '', $e->getMessage()));
        }
    }

    /**
     * 互转明细详情
     * @return \think\response\Json
     */
    public function interturnDet()
    {
        $param = input('param.');
        $flag = $this->validate($param, 'IndexValidate.interturnDet');
        if (true !== $flag) {
            return json(arr_msg(-1, '', $flag));
        }
        try {
            $list = Db::name('yw_tuandui_huzhuan')->field('id,account,num,num_sxf,num_dz,memberid,to_memberid,lrdate')->where('memberid|to_memberid', 'eq', $this->app_member['memberid'])->where('id', 'eq', intval($param['interturn_id']))->find();
            if (empty($list)) {
                return json(arr_msg(-1, '', '不存在此明细详情'));
            }
            $list['lrdate'] = date('Y-m-d H:i', strtotime($list['lrdate']));
            $list['num'] = format_num($list['num']);
            $list['num_sxf'] = format_num($list['num_sxf']);
            $list['num_dz'] = format_num($list['num_dz']);

            return json(arr_msg(1, ['list' => $list], 'success'));
        } catch (\Exception $e) {
            return json(arr_msg(-1, '', $e->getMessage()));
        }
    }

    /**
     * 消费返证明细
     * @return \think\response\Json
     */
    public function reversionList()
    {
        $param = input('param.');
        $flag = $this->validate($param, 'IndexValidate.reversionList');
        if (true !== $flag) {
            return json(arr_msg(-1, '', $flag));
        }
        $limit = $param['page_size'];
        $offset = ($param['page'] - 1) * $limit;
        try {
            $count = Db::name('log_shop_fanzheng')->where('memberid', 'eq', $this->app_member['memberid'])->count('id');
            $list = Db::name('log_shop_fanzheng')->field('id,op_time,num,type')->where('memberid', 'eq', $this->app_member['memberid'])->limit($offset, $limit)->order('id desc')->select()->each(function ($model) {
                $model['op_time'] = date('Y-m-d', strtotime($model['op_time']));
                if ($model['type'] == '收入') {
                    $fu_hao = '+';
                } else {
                    $fu_hao = '-';
                }
                $model['num'] = $model['num'] >= 0 ? $fu_hao . format_num($model['num']) : format_num($model['num']);

                return $model;
            });
            $order = Db::name('shop_order')->where('memberid', 'eq', $this->app_member['memberid'])->where('pay_status', 'eq', 1)->sum('real_amount');
            $business_order = Db::name('business_order')->where('memberid', 'eq', $this->app_member['memberid'])->where('status', 'eq', '已支付')->where('pay_status', 'eq', 1)->sum('num');
            $total_order = $order + $business_order;

            return json(arr_msg(1, ['list' => $list, 'pages' => ceil($count / $limit), 'total_order_num' => format_num($total_order), 'dong_jie' => format_num($this->app_member['shop_yuqi_shouyi_dongjie']), 'keyong' => format_num($this->app_member['shop_yuqi_shouyi_keyong'])], 'success'));
        } catch (\Exception $e) {
            return json(arr_msg(-1, '', $e->getMessage()));
        }
    }

    /**
     * 消费返证明细详情
     * @return \think\response\Json
     */
    public function reversionDet()
    {
        $param = input('param.');
        $flag = $this->validate($param, 'IndexValidate.reversionDet');
        if (true !== $flag) {
            return json(arr_msg(-1, '', $flag));
        }
        try {
            $list = Db::name('log_shop_fanzheng')->field('id,memberid,type,yue,num,from_type,op_time,account_type,remark')->where('memberid', 'eq', $this->app_member['memberid'])->where('id', 'eq', intval($param['reversion_id']))->find();
            if (empty($list)) {
                return json(arr_msg(-1, '', '不存在此明细详情'));
            }
            $list['op_time'] = date('Y-m-d H:i', strtotime($list['op_time']));
            $list['yue'] = format_num($list['yue']);
            $list['num'] = format_num($list['num']);
            if ('释放锁仓' == $list['from_type']) {
                $remark = explode('，', $list['remark']);
                $bi_li = explode('：', $remark[1]);
                $list['shi_fang_bi_li'] = $bi_li[1] * 100;
            }
            $account_type = $list['account_type'];
            if ($account_type == '冻结账户') {
                $account_type = '待返账户';
            } elseif ($account_type == '可用账户') {
                $account_type = '返证账户';
            }
            $list['account_type'] = $account_type;

            return json(arr_msg(1, ['list' => $list], 'success'));
        } catch (\Exception $e) {
            return json(arr_msg(-1, '', $e->getMessage()));
        }
    }

    /**
     * 上传头像
     * @throws \ErrorException
     * @throws \Exception
     */
    public function uploadHead()
    {
        $image = input("image");
        if (!$image) {
            return json(arr_msg(-1, '', '请选择上传的图片'));
        }
        if (!preg_match('/^(data:\s*image\/(\w+);base64,)/', $image)) {
            return json(arr_msg(-1, '', '请上传正确的图片'));
        }
        $img_arr = explode(',', $image);
        $tmp = $img_arr[0];
        $type = explode(';', explode(':', $tmp)[1])[0];
        if (!in_array($type, ['image/jpeg', 'image/png'])) {
            return json(arr_msg(-2, '', '请上传正确的图片类型'));
        }
        $image = $img_arr[1];
        $size_image = str_replace('=', '', $image);
        $img_len = strlen($size_image);
        $file_size = number_format((($img_len - ($img_len / 8) * 2) / 1024) / 1024, 2);
        if ($file_size > 10) {
            return json(arr_msg(-3, '', '上传图片需小于10MB'));
        }
        //存图片
        $pic_body = base64_decode($image);
        $root_path = ROOT_PATH . 'public';
        $path = '/upload/heads';
        if (!file_exists(($root_path . $path))) {
            mkdir(($root_path . $path));
        }
        $name = md5(time() . rand(11, 99));
        file_put_contents(($root_path . $path) . '/' . $name . '.png', $pic_body);
        $url = $root_path . $path . '/' . $name . '.png';
        $ossFileName = implode('/', ['upload/image', date('Ymd'), $name . '.png']);
        try {
            $oss_config = config('aliyun_oss');
            $ossClient = new OssClient($oss_config['KeyId'], $oss_config['KeySecret'], $oss_config['Endpoint']);
            $result = $ossClient->uploadFile($oss_config['Bucket'], $ossFileName, $url);
            if (!isset($result['oss-request-url']) || empty($result['oss-request-url'])) {
                return json(arr_msg(-1, '', '上传失败'));
            }
            $img_rec = Db::table('t_user')->where('memberid', 'eq', $this->app_member['memberid'])->update(['img_touxiang' => $result['oss-request-url']]);
            if (!$img_rec) {
                return json(arr_msg(-1, '', '存入头像失败'));
            }
            $yun_xin = new YunXin();
            $yun_xin_result = $yun_xin->updateUinfo($this->app_member['memberid'], '', $result['oss-request-url']);
            if (isset($yun_xin_result->code) && $yun_xin_result->code != 200) {
                return json(arr_msg(-1, '', '更新头像失败'));
            }

            return json(arr_msg(1, ['url' => $result['oss-request-url']], 'success'));
        } catch (OssException $e) {
            return json(arr_msg(-1, '', $e->getMessage()));
        } finally {
            unlink($url);
        }
    }

    /**
     * 消息列表
     */
    public function newsList()
    {
        $param = input('param.');
        $flag = $this->validate($param, 'IndexValidate.newsList');
        if (true !== $flag) {
            return json(arr_msg(-1, '', $flag));
        }
        $limit = $param['page_size'];
        $offset = ($param['page'] - 1) * $limit;
        try {
            $count = Db::name('articles')->field('id,title,content,add_time')->where('keywords', 'eq', '消息')->count();
            $list = Db::name('articles')->field('id,title,content,add_time')->where('keywords', 'eq', '消息')->order('id desc')->limit($offset, $limit)->select()->each(function ($model) {
                $model['content'] = htmlspecialchars_decode(htmlspecialchars_decode($model['content']));

                return $model;
            });
        } catch (\Exception $e) {
            return json(arr_msg(-1, '', $e->getMessage()));
        }

        return json(arr_msg(1, ['list' => $list, 'pages' => ceil($count / $limit)], 'success'));
    }

    /**
     * 消息详情
     */
    public function newsDet()
    {
        $param = input('param.');
        $flag = $this->validate($param, 'IndexValidate.newsDet');
        if (true !== $flag) {
            return json(arr_msg(-1, '', $flag));
        }
        try {
            $list = Db::name('articles')->field('id,title,content,add_time')->where('keywords', 'eq', '消息')->where('id', 'eq', $param['news_id'])->find();
            $list['content_s'] = rep_str(htmlspecialchars_decode(htmlspecialchars_decode($list['content'])));
        } catch (\Exception $e) {
            return json(arr_msg(-1, '', $e->getMessage()));
        }

        return json(arr_msg(1, ['list' => $list, 'img_url' => req_img(htmlspecialchars_decode(htmlspecialchars_decode($list['content'])))], 'success'));
    }

    /**
     * 奖励账户明细
     */
    public function rewardList()
    {
        $param = input('param.');
        $flag = $this->validate($param, 'IndexValidate.rewardList');
        if (true !== $flag) {
            return json(arr_msg(-1, '', $flag));
        }
        $limit = $param['page_size'];
        $offset = ($param['page'] - 1) * $limit;
        try {
            $count = Db::name('log_cunchu_jiangli_account')->where('memberid', 'eq', $this->app_member['memberid'])->count('id');
            $list = Db::name('log_cunchu_jiangli_account')->field('id,memberid,type,yue,num,from_type,op_time,account_type,remark')->where('memberid', 'eq', $this->app_member['memberid'])->limit($offset, $limit)->order('id desc')->select()->each(function ($model) {
                $model['yue'] = format_num($model['yue']);
                $model['num'] = format_num($model['num']);

                return $model;
            });
        } catch (\Exception $e) {
            return json(arr_msg(-1, '', $e->getMessage()));
        }

        return json(arr_msg(1, ['list' => $list, 'pages' => ceil($count / $limit)], 'success'));
    }

    /**
     * 奖励明细详情
     */
    public function rewardDet()
    {
        $param = input('param.');
        $flag = $this->validate($param, 'IndexValidate.rewardDet');
        if (true !== $flag) {
            return json(arr_msg(-1, '', $flag));
        }
        try {
            $list = Db::name('log_cunchu_jiangli_account')->field('id,memberid,type,yue,num,from_type,op_time,account_type,remark')->where('memberid', 'eq', $this->app_member['memberid'])->where('id', 'eq', $param['reward_id'])->find();
            if (empty($list)) {
                return json(arr_msg(-1, '', '不存在奖励明细详情'));
            }
        } catch (\Exception $e) {
            return json(arr_msg(-1, '', $flag));
        }

        return json(arr_msg(1, ['list' => $list], 'success'));
    }

    /**
     * 退出
     */
    public function logout()
    {
        $token = input("token");
        cache($token, null);
        cache($this->app_member['memberid'], null);
        cache('notice_status' . $this->app_member['memberid'], null);

        return json(arr_msg(1, '', '退出成功'));
    }

    /**
     * 商家商城关闭开启状态
     */
    public function getShopBusinessStatus()
    {
        $bai_ming_dan = $this->sys[1008007];
        $business_status = $this->sys[1008003];
        if (!empty($bai_ming_dan)) {
            $bai_ming_dan_list = explode('|', $bai_ming_dan);
            if (!empty($bai_ming_dan_list) && !in_array($this->app_member['memberid'],$bai_ming_dan_list)) {
                $business_status = '关闭';
            }
        }
        return json(arr_msg('1', ['shop_status' => $this->sys[1007004], 'business_status' => $business_status], 'success'));
    }
}
