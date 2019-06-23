<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 流年 <liu21st@gmail.com>
// +----------------------------------------------------------------------

// 应用公共文件
\think\Lang::setAllowLangList(['zh-cn', 'en-us']);

/**
 * 调用存储过程
 *
 * @param $procedure_name
 * @param $data
 *
 * @return array
 */
function db_query($procedure_name, $data)
{
    $str_data = '';
    $v_data = [];
    $ver_status = 0;
    $ver_msg = '';
    foreach ($data as $key => $item) {
        $str_data .= ':' . $key . ',';
        if (!verStrFun($item)) {
            $ver_status = 1;
            $ver_msg = $key . '存在特殊字符无法进行操作';
            \think\Log::write(dump($item, false), 'error');
            break;
        }
        if (is_numeric($item)) {
            $v_data[$key] = $item;
        } else {
            $v_data[$key] = str_fun($item);
        }
    }
    if ($ver_status != 0) {
        return msg(-2, '', $ver_msg);
    }
    $sql = "call " . $procedure_name . "(" . trim($str_data, ',') . ")";
    $flag = \think\Db::query($sql, $v_data);
    if ('成功' == $flag[0][0]['r']) {
        $data = [];
        if (!empty($flag[0][0]['v'])) {
            $data['v'] = $flag[0][0]['v'];
        }
        return arr_msg(1, $data, '操作成功');
    }

    return arr_msg(-2, '', $flag[0][0]['r']);
}

/**
 * 统一返回信息
 * @param int $code 状态码
 * @param $data
 * @param string $msg 提示信息
 * @return array
 */
function arr_msg($code, $data, $msg)
{
    $lang = \think\Lang::range();
    if ($lang != 'zh-cn') {
        $msg = translate($msg);
        if ($msg['status'] !== 200) {
            $code = 400;
        }
        $msg = $msg['msg'];
    }

    return compact('code', 'data', 'msg');
}

/**
 * 翻译
 *
 * @param $string
 *
 * @return string
 */
function translate($string)
{
    $host = "https://trans.xiaohuaerai.com";
    $path = "/trans";
    $method = "POST";
    $appcode = "";
    $headers = [];
    array_push($headers, "Authorization:APPCODE " . $appcode);
    //根据API的要求，定义相对应的Content-Type
    array_push($headers, "Content-Type" . ":" .
        "application/x-www-form-urlencoded; charset=UTF-8");
    $querys = "";
    $bodys = "d=" . urlencode('en') . "&q=" . urlencode($string) . "&s=" .
        urlencode('zh-CN');
    $url = $host . $path;

    $curl = curl_init();
    curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($curl, CURLOPT_FAILONERROR, false);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_HEADER, true);
    if (1 == strpos("$" . $host, "https://")) {
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
    }
    curl_setopt($curl, CURLOPT_POSTFIELDS, $bodys);
    $res = curl_exec($curl);
    list($header, $body) = explode("\r\n\r\n", $res, 2);
    $flag = json_decode($body, true);

    return $flag;
}

/**
 * 控制器返回
 *
 * @param $data
 */
function show_ajax($data)
{
    header("content-type:application/json;charset=uft-8");
    echo json_encode($data);
    exit;
}

/**
 * 发送手机验证码
 * @param $mobile
 * @return array
 */
function send_mobile_code($mobile)
{
    $code = rand(100000, 999999);
    $num = cache($mobile . '-' . date('Y-m-d'));
    $num = empty($num) ? 1 : $num + 1;
    $inum = cache($mobile . '-s');
    $inum2 = cache($mobile . '-s2');
    $inum2 = empty($inum2) ? 1 : $inum2 + 1;
    if (!empty($inum)) {
        return arr_msg(-4, '', '一分钟只能接收一条短信!');
    }
    if ($inum2 > 5) {
        return arr_msg(-4, '', '一个小时只能接收5条短信!');
    }
    if ($num > 10) {
        return arr_msg(-4, '', '今天已经达到接收短信上线,请明天再试!');
    }
    $msg = '您的验证码是：' . $code . '，如非本人操作，可不用理会！';
    $url = 'http://smssh1.253.com/msg/send/json';
    $postFields = [
        'account'  => 'N2853784',
        'password' => 'eOtx7k6oB',
        'msg'      => $msg,
        'phone'    => $mobile,
        'report'   => false,
    ];
    $postFields = json_encode($postFields);
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json; charset=utf-8']);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);
    curl_setopt($ch, CURLOPT_TIMEOUT, 1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    $ret = curl_exec($ch);
    if (false == $ret) {
        $result = arr_msg(-1, '', curl_error($ch));
    } else {
        $rsp = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if (200 != $rsp) {
            $result = arr_msg(-1, '', '请求错误，请重新操作！');
        } else {
            $res = json_decode($ret, true);
            $error = $res['errorMsg'];
            $result = 0 == $res['code'] ? arr_msg(1, '', '发送成功') : arr_msg(-1, '', $error);
        }
    }
    curl_close($ch);
    if (1 == $result['code']) {
        cache($mobile . '-' . date('Y-m-d'), $num, 3600 * 24);
        cache($mobile . '-s', 1, 60);
        cache($mobile . '-s2', $inum2, 3660);
        cache($mobile . 'code', $code, 3600);

        return arr_msg(1, '', '发送成功');
    }

    return arr_msg(-11, '', $result['msg']);
}

/**
 * 发送邮箱验证码
 *
 * @param $email
 *
 * @return array
 */
function send_email_code($email)
{
    $code = rand(100000, 999999);
    \think\Log::write("邮箱验证码:" . dump($code, false));
    $num = cache($email . '-' . date('Y-m-d'));
    $num = empty($num) ? 1 : $num + 1;
    $inum = cache($email . '-s');
    $inum2 = cache($email . '-s2');
    $inum2 = empty($inum2) ? 1 : $inum2 + 1;
    if (!empty($inum)) {
        return arr_msg(-4, '', '一分钟只能接收一条邮箱验证码!');
    }
    if ($inum2 > 5) {
        return arr_msg(-4, '', '一个小时只能接收5条邮箱验证码!');
    }
    if ($num > 10) {
        return arr_msg(-4, '', '今天已经达到接收邮箱验证码上线,请明天再试!');
    }
    $local_email = '';
    //发送邮件
    $mail = new \Nette\Mail\Message();
    $message = '您的邮箱验证码为：' . $code . '。';
    $lang = \think\Lang::range();
    if ($lang != 'zh-cn') {
        $message = 'Your mailbox verification code is:' . $code . '.';
    }
    $mail->setFrom($local_email)
        ->addTo($email)
        ->setSubject('MSTH')
        ->setBody($message);
    $mailer = new \Nette\Mail\SmtpMailer([
        'host'     => "smtp.exmail.qq.com",
        'username' => "",
        'password' => "",
        "port"     => 465,
        'secure'   => 'ssl',
    ]);
    $mailer->send($mail);

    cache($email . 'code', $code, 3600);
    cache($email . '-' . date('Y-m-d'), $num, 3600 * 24);
    cache($email . '-s', 1, 60);
    cache($email . '-s2', $inum2, 3660);

    return arr_msg(1, '', '发送成功');
}

/**
 * POST 提交
 */
function Post($curlPost, $url)
{
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_HEADER, false);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_NOBODY, true);
    curl_setopt($curl, CURLOPT_POST, true);
    curl_setopt($curl, CURLOPT_POSTFIELDS, $curlPost);
    $return_str = curl_exec($curl);
    curl_close($curl);

    return $return_str;
}

/**
 * xml转换数据
 */
function xml_to_array($xml)
{
    $reg = "/<(\w+)[^>]*>([\\x00-\\xFF]*)<\\/\\1>/";
    if (preg_match_all($reg, $xml, $matches)) {
        $count = count($matches[0]);
        for ($i = 0; $i < $count; $i++) {
            $subxml = $matches[2][$i];
            $key = $matches[1][$i];
            if (preg_match($reg, $subxml)) {
                $arr[$key] = xml_to_array($subxml);
            } else {
                $arr[$key] = $subxml;
            }
        }
    }

    return $arr;
}

/**
 * 导出报表
 *
 * @param      $header 表头
 * @param      $data   数据
 * @param      $total  汇总
 * @param bool $footer 汇总名称
 */
function get_excel($header, $data, $total = false, $footer = false)
{
    header("Content-Type: application/vnd.ms-execl");
    header("Content-type: application/vnd.ms-excel;charset=UTF-8");
    header("Content-Disposition:filename=" . date('YmdHis') . ".xls");
    header("Pragma: no-cache");
    header("Expires: 0");
    foreach ($header as $v) {
        echo ($v) . "\t";
    }
    echo "\r\n";
    foreach ($data as $key => $value) {
        foreach ($header as $k => $v) {
            if ($k == 'bank_id') {
                $zhuangtai = chunk_split($value['bank_id'], 4, " ");
                echo $zhuangtai . "\t";
                continue;
            }
            echo ($value[$k]) . "\t";
        }
        echo "\r\n";
    }
    if ($total) {
        $num1 = count($header);
        $num = 2 * count($total);
        $j = $num1 - $num;
        if ($j < 0) {
            die("汇总数据过多!");
        }
        for ($i = 1; $i <= $j; $i++) {
            echo ("") . "\t";
        }
        foreach ($footer as $k => $v) {
            echo ($v) . "\t" . (round($total[$k]['total'], 2)) . "\t";
        }
        echo "\r\n";
    }
    die();
}

/**
 * 截图字符串
 *
 * @param     $string
 * @param int $start
 * @param int $end
 *
 * @return string
 */
function init_string($string, $start = 6, $end = 6)
{
    $new_string = '';
    $new_string .= substr($string, 0, $start);
    $new_string .= "****";
    $new_string .= substr($string, -1 * $end);

    return $new_string;
}

/**
 * get 提交
 */
function Get($url)
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    $return_str = curl_exec($ch);
    curl_close($ch);

    return $return_str;
}

/**
 * 生成随机字符串
 * @param int $length
 * @return string
 */
function generate_str($length = 16)
{
    // 密码字符集，可任意添加你需要的字符
    $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    $str = "";
    for ($i = 0; $i < $length; $i++) {
        $str .= $chars[mt_rand(0, strlen($chars) - 1)];
    }

    return $str;
}

/**
 * 转换小数点位数
 * @param string $num 格式化数字
 * @return string $result 返回格式化后的数字
 */
function format_num($num)
{
    $wei_num = config('wei_shu');
    $result = bcadd($num, '0', $wei_num);
    return $result;
}

/**
 * 系统核算状态
 */
function sys_status()
{
    $jie_suan_status_list = \think\Db::query('select app_lian_sys_jiesuan_open() as jie_suan_status');
    $sys_status_list = \think\Db::query('select app_lian_sys_status() as sys_status');
    if (!empty($jie_suan_status_list[0][0]['jie_suan_status']) || !empty($sys_status_list[0][0]['sys_status'])) {
        return false;
    }

    return true;
}

/**
 * 过滤特殊sql字符
 */
function safe_replace($str)
{
    $str = str_ireplace('%20', '', $str);
    $str = str_ireplace('%27', '', $str);
    $str = str_ireplace('%2527', '', $str);
    $str = str_ireplace('*', '', $str);
    $str = str_ireplace('"', '&quot;', $str);
    $str = str_ireplace("'", '', $str);
    $str = str_ireplace('"', '', $str);
    $str = str_ireplace(';', '', $str);
    $str = str_ireplace('<', '&lt;', $str);
    $str = str_ireplace('>', '&gt;', $str);
    $str = str_ireplace("{", '', $str);
    $str = str_ireplace('}', '', $str);
    $str = str_ireplace("and", "", $str);
    $str = str_ireplace("execute", "", $str);
    $str = str_ireplace("update", "", $str);
    $str = str_ireplace("count", "", $str);
    $str = str_ireplace("chr", "", $str);
    $str = str_ireplace("mid", "", $str);
    $str = str_ireplace("master", "", $str);
    $str = str_ireplace("truncate", "", $str);
    $str = str_ireplace("char", "", $str);
    $str = str_ireplace("declare", "", $str);
    $str = str_ireplace("select", "", $str);
    $str = str_ireplace("create", "", $str);
    $str = str_ireplace("delete", "", $str);
    $str = str_ireplace("insert", "", $str);
    $str = str_ireplace("'", "", $str);
    $str = str_ireplace(" ", "", $str);
    $str = str_ireplace("or", "", $str);
    $str = str_ireplace("=", "", $str);

    return $str;
}

/**
 * 字符串过滤
 */
function fn_safe($str_string)
{
    //直接剔除
    $_arr_dangerChars = ["|", ";", "$", "@", "+", "\t", "\r", "\n", ",", "(", ")", PHP_EOL];
    //正则剔除
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
    $_str_return = $str_string;
    foreach ($_arr_dangerChars as $_key => $_value) {
        $_str_return = str_ireplace($_value, "", $_str_return);
    }
    foreach ($_arr_dangerRegs as $_key => $_value) {
        $_str_return = preg_replace($_value, "", $str_string);
    }
    $_str_return = htmlentities($_str_return, ENT_QUOTES, "UTF-8", true);

    return $_str_return;
}

/**
 * 过滤sql与php文件操作的关键字
 */
function filter_keyword($string)
{
    $keyword = 'select|insert|update|delete|\'|\/\*|\*|\.\.\/|\.\/|union|and|union|order|or|into|load_file|outfile';
    $arr = explode('|', $keyword);
    $result = str_ireplace($arr, '', $string);

    return $result;
}

/**
 * 字符串过滤
 */
function str_fun($str)
{
    $member_ver = new \think\Validate(['member_str' => 'require|length:6,10|alphaNum']);
    $member_rec = checkStr($str);
    if ($member_ver->check(['member_str' => $str]) && $member_rec) {
        return $str;
    }
    $password_ver = new \think\Validate(['pwd_str' => 'require|length:8,10|alphaNum']);
    $password_rec = checkStr($str);
    if ($password_ver->check(['pwd_str' => $str]) && $password_rec) {
        return $str;
    }
    $str_str = safe_replace($str);
    $str_str = fn_safe($str_str);
    $str_str = filter_keyword($str_str);

    return $str_str;
}

/**
 * 验证英文加数字
 */
function checkStr($str)
{
    $arr = str_split($str);
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
 * 文章内容过滤
 */
function content_fun($content)
{
    $str_str = safe_replace($content);
    $str_str = filter_keyword($str_str);

    return $str_str;
}

/**
 * 根据当前环境返回英文还是中文
 * @param string $key 转换字符串
 * @return mixed
 */
function get_eng_or_ch($key)
{
    $arr = [
        '购买商品'      => 'Purchase goods',
        '释放锁仓'      => 'Release lock',
        '冻结账户'      => 'Frozen accounts',
        '可用账户'      => 'Available accounts',
        'MSTH锁仓归集'   => 'MSTH lock storage collection',
        'MSTH释放锁仓可用' => 'MSTH release locks available',
        'MSTH通用账户'   => 'MSTH general account',
        '太阳0号'      => 'Sun 0',
        '水星1号'      => 'Mercury 1',
        '金星2号'      => 'Venus 2',
        '地球3号'      => 'Earth 3',
        '火星4号'      => 'Mars 4',
        '木星5号'      => 'Jupiter 5',
        '土星6号'      => 'Saturn 6',
        '天王星7号'     => 'Uranus 7',
        '海王星8号'     => 'Neptune 8',
        '冥王星9号'     => 'Pluto 9'
    ];
    $string = \think\Lang::range() == 'zh-cn' ? $key : @$arr[$key];

    return $string;
}

/**
 * 替换img
 * @param $str
 * @return mixed
 */
function rep_str($str)
{
    $domain = request()->domain();
    $pregRule = "/(<[img|IMG].*?src=[\'|\"])(.*?(?:[\.jpg|\.jpeg|\.png|\.gif|\.bmp]))([\'|\"].*?[\/]?>)/";
    $content = preg_replace($pregRule, '${1}' . $domain . '${2}${3}', $str);
    return $content;
}

/**
 * 取出img
 */
function req_img($str)
{
    preg_match('/<img.+src=\"?(.+\.(jpg|gif|bmp|bnp|png))\"?.+>/i', $str, $match);
    $img_url = '';
    if (!empty($match)) {
        $img_url_list = explode(' ', $match[1]);
        $img_url = request()->domain() . trim($img_url_list[0], '"');
    }
    return $img_url;
}

/**
 * 截取邮箱
 */
function email_string($string, $start = 6)
{
    $email_list = explode('@', $string);
    $new_string = '';
    $new_string .= str_replace(substr($email_list[0], -1 * $start), "****", $email_list[0]);
    return $new_string . '@' . $email_list[1];
}

/**
 * 验证字符串
 */
function verStrFun($str)
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
        return false;
    }

    return true;
}

/**
 * 勾稽验证
 * @param $member_id
 * @return bool
 */
function gou_ji($member_id)
{
    $flag = \think\Db::query("select f_lian_goujiinfo(:type,:account,:memberid) as status", ['type' => '勾稽状态', 'account' => 'MSTH提取', 'memberid' => $member_id]);
    $flag1 = \think\Db::query("select f_lian_goujiinfo(:type,:account,:memberid) as status", ['type' => '勾稽状态', 'account' => '释放通证', 'memberid' => $member_id]);
    $flag2 = \think\Db::query("select f_lian_goujiinfo(:type,:account,:memberid) as status", ['type' => '勾稽状态', 'account' => '收益通证', 'memberid' => $member_id]);
    $flag3 = \think\Db::query("select f_lian_goujiinfo(:type,:account,:memberid) as status", ['type' => '勾稽状态', 'account' => 'MSTH预存', 'memberid' => $member_id]);
    $flag4 = \think\Db::query("select f_lian_goujiinfo(:type,:account,:memberid) as status", ['type' => '勾稽状态', 'account' => '奖励账户', 'memberid' => $member_id]);
    $flag5 = \think\Db::query("select f_lian_goujiinfo(:type,:account,:memberid) as status", ['type' => '勾稽状态', 'account' => '返证账户', 'memberid' => $member_id]);
    $flag6 = \think\Db::query("select f_lian_goujiinfo(:type,:account,:memberid) as status", ['type' => '勾稽状态', 'account' => '可用业绩', 'memberid' => $member_id]);
    if ((int)$flag[0]['status'] === 1 || (int)$flag1[0]['status'] === 1 || (int)$flag2[0]['status'] === 1 || (int)$flag3[0]['status'] === 1 || (int)$flag4[0]['status'] === 1 || (int)$flag5[0]['status'] === 1 || (int)$flag6[0]['status'] === 1) {
        return false;
    }

    return true;
}
