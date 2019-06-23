<?php

namespace app\api\controller;

use app\common\controller\YunXin;
use app\util\Tools;
use Curl\Curl;
use eth\driver\Api;
use function GuzzleHttp\Psr7\str;
use think\Controller;
use think\captcha\Captcha;
use think\Db;
use think\db\exception\DataNotFoundException;
use think\db\exception\ModelNotFoundException;
use think\Exception;
use think\exception\DbException;
use think\Log;
use think\Request;

/**
 * Class Login
 * @package app\api\controller
 */
class Login extends Guest
{

    /**
     * 图片验证码
     * @return \think\Response
     */
    public function captcha()
    {
        $config = [
            'codeSet'  => '02345689',
            'length'   => 4,
            'fontttf'  => '2.ttf',
            'useCurve' => false,
        ];
        $captcha = new Captcha($config);

        return $captcha->entry();
    }

    /**
     * 登录
     * @return \think\response\Json
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     * @throws \ErrorException
     */
    public function login()
    {
        $param = input("param.");
        $flag = $this->validate($param, "LoginValidate.login");
        if (true !== $flag) {
            return json(arr_msg(-1, '', $flag));
        }
        $web_member = Db::table('t_user')->where('memberid', 'eq', $param['memberid'])->find();
        if (empty($web_member)) {
            return json(arr_msg(-1, '', '您还没有注册，请先注册后再次登录！'));
        }
        if ($web_member['login_error_num'] >= 5) {
            return json(arr_msg(-1, '', '今日密码已锁定，请明日再操作！'));
        }
        $password = strtoupper(substr(md5($param['password']), 8, 16));
        $ip = \request()->ip();
        //登录密码不正确
        if (strtoupper($password) != $web_member['paypassword']) {
            if ($web_member['login_error_num'] == 4) {
                Db::table('t_log_errors')->insert([
                    'memberid' => $web_member['memberid'],
                    'ip'       => $ip,
                    'type'     => 'app_login'
                ]);
            }
            Db::table('t_log_login')->insert(['memberid' => $web_member['memberid'], 'ip' => $ip, 'created_time' => date('Y-m-d H:i:s'), 'status' => 'fail', 'times' => $web_member['login_error_num'] + 1, 'type' => '登录']);
            Db::table('t_user')->where('memberid', 'eq', $param['memberid'])->setInc('login_error_num');
            $yu_num = 5 - ($web_member['login_error_num'] + 1);
            return json(arr_msg(-3, '', '账号或密码不正确，还有' . $yu_num . '次登录的机会'));
        }

        //勾稽验证
        $gou_ji_ver = gou_ji($web_member['memberid']);
        if (!$gou_ji_ver) {
            return json(arr_msg(-1, '', '异常账户,您的账号已冻结'));
        }

        //生成云信
        $status_config = config('status_config');
        $yun_xin_token = $web_member['yx_token'];
        $yunxin = new YunXin();
        if (empty($web_member['yx_token']) && 0 == $status_config['yx_status']) {
            $yunxin_rec = $yunxin->regist($web_member['memberid'], $web_member['mobile']);
            if (isset($yunxin_rec->code) && $yunxin_rec->code != 200) {
                return json(arr_msg(-1, '', '生成云信token失败！'));
            }
            $yx_token = $yunxin_rec->info->token;
            if (empty($yx_token)) {
                return json(arr_msg(-1, '', '未获取到云信token！'));
            }
            $yx_token_rec = Db::table('t_user')->where('memberid', 'eq', $param['memberid'])->update(['yx_token' => $yx_token]);
            if (!$yx_token_rec) {
                return json(arr_msg(-1, '', '存入云信token失败'));
            }
            $yun_xin_token = $yx_token;
        }
        $ke_fu_member = config('ke_fu_member');
        if (0 == $status_config['yx_status'] && !empty($ke_fu_member)) {
            //强制加好友
            $yunxin->addFriend($web_member['memberid'], $ke_fu_member);
        }

        $token = md5(time() . rand(1111, 9999));
        $login_time = config('login_time');
        if (isset($login_time) && !empty($login_time)) {
            cache($web_member['memberid'], $token, $login_time);
            cache($token, $web_member['memberid'], $login_time);
        } else {
            cache($web_member['memberid'], $token);
            cache($token, $web_member['memberid']);
        }
        $log_data = [
            'memberid'     => $web_member['memberid'],
            'ip'           => $ip,
            'created_time' => date('Y-m-d H:i:s'),
            'status'       => 'success',
            'times'        => 0,
            'type'         => '登录'
        ];
        $log_rec = Db::table('t_log_login')->insert($log_data);
        if (!$log_rec) {
            return json(arr_msg(-1, '', '存入登录日志失败！'));
        }
        
        //清除登录次数限制
        Db::table('t_user')->where('memberid', 'eq', $web_member['memberid'])->update(['login_error_num' => 0]);

        return json(arr_msg(1, ['token' => $token, 'yun_xin_token' => $yun_xin_token], 'success'));
    }

    /**
     * 发送验证码
     * @return \think\response\Json
     */
    public function sendSms()
    {
        $param = input("param.");
        $flag = $this->validate($param, "LoginValidate.r_sendSms");
        if (true !== $flag) {
            return json(arr_msg(-1, '', $flag));
        }

//        $flag = $param['check_type'] == 'mobile' ? send_mobile_code($param['r_mobile']) : send_email_code($param['r_email']);
        $flag = send_mobile_code($param['r_mobile']);

        return json($flag);
    }

    /**
     * 用户协议接口
     */
    public function userProtocol()
    {
        try {
            $list = Db::table('t_articles')->field('title,content')->where('keywords', 'eq', '用户协议')->find();
        } catch (\Exception $e) {
            return json(arr_msg(-1, '', $e->getMessage()));
        }
        $list['content'] = htmlspecialchars_decode(htmlspecialchars_decode($list['content']));

        return json(arr_msg(1, ['list' => $list], 'success'));
    }

    /**
     * 注册
     * @return \think\response\Json
     */
    public function register()
    {
        $param = input("param.");
        $flag = $this->validate($param, "LoginValidate.register");
        if (true !== $flag) {
            return json(arr_msg(-1, '', $flag));
        }
        $password = strtoupper(substr(md5($param['password']), 8, 16));
        $data = [
            'referee'     => str_fun($param['referee_memberid']),
            'memberid'    => str_fun($param['r_memberid']),
            'membername'  => '',
            'mobile'      => '',
            'password'    => $password,
            'email'       => '',
            'mobile_code' => ''
        ];
        $procedure_name = "p_web_member_reg";
        $flag = db_query($procedure_name, $data);
        if (1 != $flag['code']) {
            return json($flag);
        }

        return json(arr_msg(1, '', '注册成功'));
    }

    /**
     * 忘记密码
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function forgotPassword()
    {
        $param = input("param.");
        $flag = $this->validate($param, "LoginValidate.forgotPassword");
        if (true !== $flag) {
            return json(arr_msg(-1, '', $flag));
        }

        $member = Db::table('t_user')->where('memberid', 'eq', str_fun($param['memberid']))->find();
        $token = sha1(time() . rand(11, 99));
        cache($token, $member['memberid'], 1800);

        return json(arr_msg(1, ['forgot_token' => $token], 'success'));
    }

    /**
     * 忘记密码发送验证码
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function fSendSms()
    {
        $param = input("param.");
        $flag = $this->validate($param, "LoginValidate.fSendSms");
        if (true !== $flag) {
            return json(arr_msg(-1, '', $flag));
        }

        $member = Db::table("t_user")->where('memberid', 'eq', str_fun($param['memberid']))->find();
        if (empty($member['mobile'])) {
            return json(arr_msg(-1, '', '您还没有绑定手机号，请先绑定手机号后再次操作！'));
        }
//        $flag = "mobile" == $param['check_type'] ? send_mobile_code($member['mobile']) : send_email_code($member['email']);
        $flag = send_mobile_code($member['mobile']);

        return json($flag);
    }


    /**
     * 找回密码
     * @return \think\response\Json
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function forgotPasswordSubmit()
    {
        $param = input("param.");
        $flag = $this->validate($param, "LoginValidate.forgotPasswordSubmit");
        if (true !== $flag) {
            return json(arr_msg(-1, '', $flag));
        }
        $forgot_token = str_fun($param['forgot_token']);

        $memberid = cache($forgot_token);
        $member = Db::table("t_user")->where('memberid', 'eq', $memberid)->find();
        if (!$member) {
            return json(arr_msg(-2, '', '校验失败请返回上一页重新提交'));
        }

        $password = strtoupper(substr(md5($param['password']), 8, 16));
        if ($password == $member['paypassword']) {
            return json(arr_msg(-3, '', '新旧密码不可一致'));
        }
        $flag = Db::table("t_user")->where('memberid', 'eq', $memberid)->update(['paypassword' => $password]);
        if ($flag) {
            cache($forgot_token, null);

            return json(arr_msg(1, '', '修改成功'));
        } else {
            return json(arr_msg(-1, '', '修改失败'));
        }
    }

    /**
     * 商城首页
     */
    public function mallIndex()
    {
        $param = input('param.');
        $flag = $this->validate($param, 'LoginValidate.mallIndex');
        if (true !== $flag) {
            return json(arr_msg(-1, '', $flag));
        }
        if ('开启' != $this->sys[1007004]) {
            return json(arr_msg(-1, '', '此功能暂时关闭，请稍后再试'));
        }
        try {
            $banner = Db::table('t_shop_banner')->field('id,url,img')->where('type', 'eq', 'banner')->order('sort asc')->select();
            $discount = Db::table('t_shop_banner')->field('id,url,img')->where('type', 'eq', '优惠区')->order('sort asc')->select();
            $goods_type = Db::table('t_shop_goods_type')->field('id,typename')->where('status', 'eq', 1)->order('sortid asc')->select();
            $county_type = Db::table('t_shop_country_type')->field('id,name,img')->where('status', 'eq', 1)->order('sortid asc')->select();
            $moren_type = $goods_type[0]['id'];
            $limit = $param['page_size'];
            $offset = ($param['page'] - 1) * $limit;
            $county_type_id = intval($param['county_type_id']);
            $goods_name = str_fun($param['goods_name']);
            $goods_type_id = intval($param['goods_type_id']);
            $goods_where = [];
            if (!empty($county_type_id)) {
                $goods_where['county_type_id'] = $county_type_id;
            }
            if (!empty($goods_name)) {
                $goods_where['name'] = ['like', '%' . $goods_name . '%'];
            }
            if (!empty($goods_type_id)) {
                $goods_where['goods_type_id'] = $goods_type_id;
            }
            $count = Db::table('t_shop_goods')->where('is_del', 'eq', 2)->where($goods_where)->count('id');
            $goods = Db::table('t_shop_goods')->field('id,goods_type_id,goods_type_name,name,title,sell_price,market_price,store_nums,sale,img')->where('is_del', 'eq', 2)->where($goods_where)->limit($offset, $limit)->order('id desc')->select();

            return json(arr_msg(1, ['banner' => $banner, 'discount' => $discount, 'goods_type' => $goods_type, 'county_type' => $county_type, 'moren_type' => $moren_type, 'goods' => $goods, 'pages' => ceil($count / $limit)], 'success'));
        } catch (\Exception $e) {
            return json(arr_msg(-1, '', $e->getMessage()));
        }
    }

    /**
     * 商品分类筛选商品
     */
    public function mallTypeGoods()
    {
        $param = input('param.');
        $flag = $this->validate($param, 'LoginValidate.mallTypeGoods');
        if (true !== $flag) {
            return json(arr_msg(-1, '', $flag));
        }
        if ('开启' != $this->sys[1007004]) {
            return json(arr_msg(-1, '', '此功能暂时关闭，请稍后再试'));
        }
        $limit = $param['page_size'];
        $offset = ($param['page'] - 1) * $limit;
        try {
            $count = Db::table('t_shop_goods')->where('is_del', 'eq', 2)->where('goods_type_id', 'eq', $param['type_id'])->count('id');
            $goods = Db::table('t_shop_goods')->field('id,goods_type_id,goods_type_name,name,title,sell_price,market_price,store_nums,sale,img')->where('is_del', 'eq', 2)->where('goods_type_id', 'eq', $param['type_id'])->limit($offset, $limit)->order('id desc')->select();

            return json(arr_msg(1, ['goods' => $goods, 'pages' => ceil($count / $limit)], 'success'));
        } catch (\Exception $e) {
            return json(arr_msg(-1, '', $e->getMessage()));
        }
    }

    /**
     * 商品信息
     */
    public function mallGoods()
    {
        $param = input('param.');
        $flag = $this->validate($param, 'LoginValidate.mallGoods');
        if (true !== $flag) {
            return json(arr_msg(-1, '', $flag));
        }
        if ('开启' != $this->sys[1007004]) {
            return json(arr_msg(-1, '', '此功能暂时关闭，请稍后再试'));
        }
        try {
            $goods = Db::table('t_shop_goods')->field('id,name,sell_price,market_price,store_nums,sale,content')->where('id', 'eq', $param['goods_id'])->where('is_del', 'eq', 2)->find();
            $goods_img = Db::table('t_shop_goods_photo')->where('goods_id', 'eq', $goods['id'])->select();
            $goods['goods_img'] = $goods_img;
            $articles = Db::table('t_articles')->field('content')->where('keywords', 'eq', '返利规则')->find();
            $goods['articles'] = htmlspecialchars_decode(htmlspecialchars_decode($articles['content']));
            $goods['content'] = rep_str(htmlspecialchars_decode(htmlspecialchars_decode($goods['content'])));

            return json(arr_msg(1, ['list' => $goods], 'success'));
        } catch (\Exception $e) {
            return json(arr_msg(-1, '', $e->getMessage()));
        }
    }

    /**
     * 商品规格初始化
     */
    public function mallSpecInit()
    {
        $param = input('param.');
        $flag = $this->validate($param, 'LoginValidate.mallSpecInit');
        if (true !== $flag) {
            return json(arr_msg(-1, '', $flag));
        }
        if ('开启' != $this->sys[1007004]) {
            return json(arr_msg(-1, '', '此功能暂时关闭，请稍后再试'));
        }
        try {
            $goods = Db::table('t_shop_goods')->field('id,name,sell_price,market_price,store_nums,sale,content,img,spec_array')->where('id', 'eq', $param['goods_id'])->where('is_del', 'eq', 2)->find();
            $goods['spec_array'] = json_decode($goods['spec_array'], true);
            foreach ($goods['spec_array'] as $key => $value) {
                $val_list = [];
                $i = 0;
                foreach ($value['value'] as $k => $v) {
                    $val_list[$i]['tip'] = $k;
                    $val_list[$i]['val'] = $v;
                    $i++;
                }
                $goods['spec_array'][$key]['value'] = $val_list;
            }

            return json(arr_msg(1, ['list' => $goods], 'success'));
        } catch (\Exception $e) {
            return json(arr_msg(-1, '', $e->getMessage()));
        }
    }

    /**
     * 商品规格筛选货品信息
     * @throws \Exception
     */
    public function mallSpecProducts()
    {
        $param = input('param.');
        $flag = $this->validate($param, 'LoginValidate.mallSpecProducts');
        if (true !== $flag) {
            return json(arr_msg(-1, '', $flag));
        }
        if ('开启' != $this->sys[1007004]) {
            return json(arr_msg(-1, '', '此功能暂时关闭，请稍后再试'));
        }
        $spec_arr = explode('|', str_fun($param['spec_str']));
        $spec_data = [];
        foreach ($spec_arr as $key => $value) {
            $spec_list = explode(',', $value);
            $spec = Db::table('t_shop_spec')->field('id,name,type')->where('id', 'eq', $spec_list[0])->where('is_del', 'eq', 0)->find();
            $spec_data[$key]['id'] = strval($spec['id']);
            $spec_data[$key]['type'] = strval($spec['type']);
            $spec_data[$key]['value'] = $spec_list[2];
            $spec_data[$key]['name'] = $spec['name'];
            $spec_data[$key]['tip'] = $spec_list[1];
        }
        $products = Db::table('t_shop_products')->field('id,goods_id,products_no,products_img,market_price,sell_price,store_nums')->where('goods_id', 'eq', $param['goods_id'])->where('spec_array', 'eq', json_encode($spec_data, JSON_UNESCAPED_UNICODE))->find();
        $products_status = 1;
        if (empty($products)) {
            $products_status = 0;
        }

        return json(arr_msg(1, ['list' => $products, 'products_status' => $products_status], 'success'));
    }

}
