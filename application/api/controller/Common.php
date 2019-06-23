<?php

namespace app\api\controller;

use app\util\Tools;
use think\Controller;
use think\Db;
use think\Env;
use think\Request;

class Common extends Guest
{
    protected $app_member;

    protected function _initialize()
    {
        parent::_initialize();

        $token = input('token');
        if (empty($token)) {
            show_ajax(arr_msg(300, '', 'token不存在'));
        }

        $memberid = cache($token);
        if (empty($memberid)) {
            show_ajax(arr_msg(301, '', '系统安全退出,请重新登录'));
        }

        $maintenance = Db::name("sys")->find(1);
        $action = strtolower($this->request->action());
        if (0 != $maintenance['s_weihu']) {
            show_ajax(arr_msg(-500, '', '系统升级中'));
        }
//        $member_ver_list = ['mstz001', 'mstz002', 'mstz003', 'mstz004', 'mstz005', 'mstz006', 'mstz007', 'mstz009', 'mstz010', 'Mstz011', 'Mstz012', 'Mstz013', 'Mstz014', 'Mstz015'];
//        if (!in_array($memberid, $member_ver_list)) {
//            show_ajax(arr_msg(-500, '', '系统升级中'));
//        }

        $debug = Env::get("app_debug");
        if (!$debug) {
            if (cache($memberid) <> $token) {
                cache($token, null);
                show_ajax(arr_msg(304, '', '不允许多地登录!'));
            }
        }

        $member = Db::name("user")->where('memberid', 'eq', $memberid)->find();
        if (!$member) {
            show_ajax(arr_msg(302, '', '系统安全退出,请重新登录'));
        }
        if ('冻结' == $member['status']) {
            show_ajax(arr_msg(303, '', '您的账号已冻结，请联系系统管理员！'));
        }
        //异常操作退出
        $actions = [
            'chainstoresub', 'chainstoredraw', 'mallplaceorder', 'mallpay', 'transferimsub'
        ];
        if (in_array($action, $actions)) {
            //勾稽验证
            $gou_ji_vers = gou_ji($member['memberid']);
            if (!$gou_ji_vers) {
                cache($token, null);
                show_ajax(arr_msg(303, '', '异常账户,您的账号已冻结'));
            }
        }
        $this->app_member = $member;
        //更新存活时间
        $login_time = config('login_time');
        if (isset($login_time) && !empty($login_time)) {
            cache($member['memberid'], $token, $login_time);
            cache($token, $member['memberid'], $login_time);
        }
    }

}
