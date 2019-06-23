<?php

namespace app\api\controller;

use think\Controller;
use think\Db;
use think\Env;
use think\Lang;
use think\Log;
use think\Request;

class Guest extends Controller
{

    protected $sys; //会员系统参数

    protected function _initialize()
    {
        //跨域
        $this->kuayu();
        $action = strtolower($this->request->action());
        //系统冻结
        $h = date("H");
        $i = date("i");
        if ($h == '0' && $i < '15' && $action != 'captcha') {
            show_ajax(arr_msg(-500, '', '系统冻结中，请稍后操作'));
        }

        $accout_password = input('accout_password');
        $maintenance = Db::name("sys")->find(1);
        $actions = [
            'captcha'
        ];
        if (!in_array($action, $actions)) {
            if (0 != $maintenance['s_weihu']) {
                show_ajax(arr_msg(-500, '', '系统升级中'));
            }
        }
        if ($accout_password <> $maintenance['api_password']) {
            show_ajax(arr_msg(501, '', '通讯密匙不正确'));
        }

        $system = Db::name("sys_parameter")->select();
        $temp = [];
        foreach ($system as $item) {
            $temp[$item['s_key']] = $item['s_value'];
        }
        //app热更新
        $version = input('version');
        if (!in_array($action, ['captcha'])) {
            $debugs = Env::get('app_debug');
            if (\request()->domain() != 'https://msadmin.msth2019.com' && !$debugs) {
                show_ajax(arr_msg(503, '', '发现新版本'));
            }
            if ($version != $temp[7003]) {
                show_ajax(arr_msg(503, '', '发现新版本'));
            }
        }
        $this->sys = $temp;
    }

    /**
     * 跨域方法
     */
    private function kuayu()
    {
        header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");
        header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
        header('Access-Control-Allow-Credentials: true');
        !isset($_SERVER['HTTP_ORIGIN']) && $_SERVER['HTTP_ORIGIN'] = '';
        header('Access-Control-Allow-Origin:' . $_SERVER['HTTP_ORIGIN']);
    }
}
