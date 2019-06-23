<?php
/**
 * Created by PhpStorm.
 * User: wandell
 * Date: 2018/9/10
 * Time: 15:47
 */

namespace app\common\validate;

use think\Db;
use think\Env;
use think\Validate;

class LoginValidate extends Validate
{

    protected $message = [
        'memberid.require'          => '请输入正确的节点账号：6-10英文加数字',
        'memberid.checkPassword'    => '请输入正确的节点账号：6-10英文加数字',
        'memberid.length'           => '请输入正确的节点账号：6-10英文加数字',
        'password.checkPassword'    => '请输入正确的登录密码：8-10数字加英文',
        'password.require'          => '请输入正确的登录密码：8-10数字加英文',
        'password.length'           => '请输入正确的登录密码：8-10数字加英文',
        'r_memberid.checkPassword'  => '请输入正确的通证节点账号：6-10英文加数字',
        'r_memberid.length'         => '请输入正确的通证节点账号：6-10英文加数字',
        'r_mobile.regex'            => '请输入正确的手机号',
        'confirm_password.confirm'  => '两次输入的密码不一致',
        'forgot_token.require'      => '连接超时请返回上一页重新提交',
        'referee_memberid.length'   => '请输入正确的分享通证节点账号：6-10位数字加英文',
        'referee_memberid.inMember' => '请输入正确的分享通证节点账号',
    ];

    protected $rule = [
        'memberid|节点账户'           => 'require|length:6,10|alphaNum|checkPassword:|verStrFun:',
        'password|密码'             => 'require|length:8,10|alphaNum|checkPassword:|verStrFun:',
        'captcha|图片验证码'           => 'require|captcha',
        'check_type|验证码类型'        => 'require|in:mobile|verStrFun:',
        'r_mobile|手机号'            => ['regex' => '^[1][3-9]\d{9}$', 'requireIf:check_type,mobile', 'verStrFun:'],
        'r_email|邮箱'              => 'requireIf:check_type,email|email',
        'referee_memberid|分享节点账号' => 'require|length:6,10|alphaNum|checkPassword:|verStrFun:|inMember:',
        'r_memberid|节点账户'         => 'require|length:6,10|alphaNum|checkPassword:|verStrFun:|onlyMemberId:',
        'membername|节点姓名'         => 'require|length:2,30',
        'confirm_password|确认密码'   => 'require|confirm:password',
        'check_code|验证码信息'        => 'require|checkCode:',
        'f_check_code|验证码信息'      => 'require|fCheckCode:',
        'forgot_token|忘记密码验证'     => 'require',
        'page|页数'                 => 'require|number|gt:0',
        'page_size|页码'            => 'require|number|gt:0',
        'type_id|商品分类'            => 'require|number|gt:0|checkGoodsType:',
        'goods_id|商品ID'           => 'require|number|gt:0|checkGoods:',
        'spec_str|规格'             => 'require|verStrFun:',
        'news_memberid|会员账号'      => 'length:6,10|alphaNum|checkPassword:',
        'news_id|新闻ID'            => 'require|number|gt:0|checkNews:',
        'county_type_id|国家分类ID'   => 'number|gt:0',
        'goods_type_id|商品分类ID'    => 'number|gt:0',
        'goods_name|商品名称'         => 'verStrFun:',
    ];

    protected $scene = [
        'login'                => ['memberid', 'password', 'captcha'],
        'r_sendSms'            => ['check_type', 'r_mobile', 'captcha'],
        'register'             => ['referee_memberid', 'r_memberid', 'password', 'captcha'],
        'forgotPassword'       => ['memberid', 'f_check_code', 'check_type'],
        'fSendSms'             => ['memberid', 'captcha', 'check_type'],
        'forgotPasswordSubmit' => ['forgot_token', 'password', 'confirm_password'],
        'mallIndex'            => ['page', 'page_size', 'county_type_id', 'goods_name', 'goods_type_id'],
        'mallTypeGoods'        => ['type_id', 'page', 'page_size'],
        'mallGoods'            => ['goods_id'],
        'mallSpecInit'         => ['goods_id'],
        'mallSpecProducts'     => ['goods_id', 'spec_str'],
        'newsInit'             => ['page', 'page_size'],
        'newsDet'              => ['news_memberid', 'news_id']
    ];

    /**
     * 验证英文加数字
     * @param $password
     * @return bool
     */
    protected function checkPassword($password)
    {
        $arr = str_split($password);
        $num = $alpha = false;
        foreach ($arr as $item) {
            if (is_numeric($item)) {
                $num = true;
                break;
            }
        }
        foreach ($arr as $item) {
            if (ctype_alpha($item)) {
                $alpha = true;
                break;
            }
        }
        if ($num && $alpha) {
            return true;
        }

        return false;
    }

    /**
     * 是否是会员
     * @param $referee_memberid
     * @return bool
     * @throws \Exception
     */
    protected function inMember($referee_memberid)
    {
        $member = Db::table("t_user")->where('memberid', 'eq', $referee_memberid)->find();
        if (!$member) {
            return false;
        }

        return true;
    }

    /**
     * 验证会员账号唯一
     * @param $r_memberid
     * @return bool
     * @throws \Exception
     */
    protected function onlyMemberId($r_memberid)
    {
        $member = Db::table("t_user")->where('memberid', 'eq', $r_memberid)->find();
        if (!empty($member)) {
            return '节点账户已存在，请登录！';
        }

        return true;
    }

    /**
     * 校验验证码
     * @param $code
     * @param $rule
     * @param $data
     * @return bool
     */
    protected function checkCode($code, $rule, $data)
    {
        $type = $data['check_type'];
        if ($type == 'mobile') {
            $key = $data['r_mobile'];
            $msg = "手机验证码不正确";
        } else {
            $key = $data['r_email'];
            $msg = "邮箱验证码不正确";
        }
        $debug = Env::get('app_debug');
        if ($debug && $code == '618660') {
            return true;
        }
        if (cache($key . 'code') != $code) {
            return $msg;
        }

        return true;
    }

    /**
     * 忘记密码校验验证码
     * @param $code
     * @param $rule
     * @param $data
     * @return bool
     * @throws \Exception
     */
    protected function fCheckCode($code, $rule, $data)
    {
        $type = $data['check_type'];
        if ($type == 'mobile') {
            $key = 'mobile';
            $msg = "手机验证码不正确";
        } else {
            $key = 'email';
            $msg = "邮箱验证码不正确";
        }
        $memberid = $data['memberid'];
        $member = Db::table("t_user")->where('memberid', 'eq', $memberid)->find();
        if (!$member) {
            return '该账户不存在';
        }
        $key = $member[$key];
        $debug = Env::get('app_debug');
        if ($debug && $code == '618660') {
            return true;
        }
        if (cache($key . 'code') != $code) {
            return $msg;
        }

        return true;
    }

    /**
     * 验证商品分类
     * @param $type_id
     * @return bool
     * @throws \Exception
     */
    protected function checkGoodsType($type_id)
    {
        $goods_type = Db::table('t_shop_goods_type')->where('status', 'eq', 1)->where('id', 'eq', $type_id)->find();
        if (empty($goods_type)) {
            return '商品分类不存在或此商品分类已被禁用！';
        }

        return true;
    }

    /**
     * 验证商品
     * @param $goods_id
     * @return bool
     * @throws \Exception
     */
    protected function checkGoods($goods_id)
    {
        $goods = Db::table('t_shop_goods')->where('id', 'eq', $goods_id)->where('is_del', 'eq', 2)->find();
        if (empty($goods)) {
            return '商品信息不存在';
        }

        return true;
    }

    /**
     * 验证新闻
     * @param $news_id
     * @return bool
     * @throws \Exception
     */
    protected function checkNews($news_id)
    {
        $news = Db::table('app_news')->where('id', 'eq', $news_id)->where('status', 'eq', 1)->find();
        if (empty($news)) {
            return '新闻信息不存在';
        }

        return true;
    }

    /**
     * 验证字符是否存在特殊字符
     * @param $str
     * @return bool
     */
    protected function verStrFun($str)
    {
        $str = strtolower($str);
        //正则匹配
        $ver_regular = true;
        $_arr_dangerRegs = [
            "/<(script|frame|iframe|bgsound|link|object|applet|embed|blink|style|layer|ilayer|base|meta)\s+\S*>/i",
            "/on(afterprint|beforeprint|beforeunload|error|haschange|load|message|offline|online|pagehide|pageshow|popstate|redo|resize|storage|undo|unload|blur|change|contextmenu|focus|formchange|forminput|input|invalid|reset|select|submit|keydown|keypress|keyup|click|dblclick|drag|dragend|dragenter|dragleave|dragover|dragstart|drop|mousedown|mousemove|mouseout|mouseover|mouseup|mousewheel|scroll|abort|canplay|canplaythrough|durationchange|emptied|ended|error|loadeddata|loadedmetadata|loadstart|pause|play|playing|progress|ratechange|readystatechange|seeked|seeking|stalled|suspend|timeupdate|volumechange|waiting)\s*=\s*(\"|')?\S*(\"|')?/i",
            "/\w+\s*=\s*(\"|')?(java|vb)script:\S*(\"|')?/i",
            "/(document|location)\s*\.\s*\S*/i",
            "/(eval|alert|prompt|msgbox)\s*\(.*\)/i",
            "/expression\s*:\s*\S*/i",
            "/show\s+(databases|tables|index|columns)/i",
            "/create\s+(database|table|(unique\s+)?index|view|procedure|proc)/i",
            "/alter\s+(database|table)/i",
            "/drop\s+(database|table|index|view|column)/i",
            "/backup\s+(database|log)/i",
            "/truncate\s+table/i",
            "/replace\s+view/i",
            "/(add|change)\s+column/i",
            "/(select|update|delete)\s+\S*\s+from/i",
            "/insert\s+into/i",
            "/load_file\s*\(.*\)/i",
            "/(outfile|infile)\s+(\"|')?\S*(\"|')/i",
        ];
        foreach ($_arr_dangerRegs as $item) {
            if (preg_match($item, $str)) {
                $ver_regular = false;
                break;
            }
        }
        //字符串验证
        $ver_string = true;
        $str_ver_arr = ["%20", "%27", "%2527", "*", '"', ";", "<", ">", "{", "}", "and", "execute", "update", "count", "chr", "mid", "master", "truncate", "char", "declare", "select", "create", "delete", "insert", " ", "'", "or", "=", "\'", "\/\*", "\.\.\/", "\.\/", "union", "and", "order", "or", "into", "load_file", "outfile"];
        foreach ($str_ver_arr as $value) {
            if (stripos($str, $value) !== false) {
                $ver_string = false;
                break;
            }
        }
        if (!$ver_regular || !$ver_string) {
            \think\Log::write(dump($str, false), 'error');
            return '存在特殊字符，请修改后再次操作！';
        }

        return true;
    }

}