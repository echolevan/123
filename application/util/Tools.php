<?php
/**
 *
 * @since   2017-11-01
 * @author  zhaoxiang <zhaoxiang051405@gmail.com>
 */

namespace app\util;


use Aliyun\Sms\Api;
use app\api\controller\Base;
use GuzzleHttp\Client;
use think\Db;
use think\Env;

class Tools
{
    public static function dbDebug()
    {
        Db::listen(function ($sql, $time, $explain) {
            dump($sql);
        });
    }

    static function sendSmsCode($mobile, $code, $option)
    {
//        dump($option);
        /** 短信推送配置信息 **/
//        $config = [
//            'accessKeyId' => $option['accessKeyId'],
//            'accessKeySecret' =>  $option['accessKeySecret'],
//            'signName' =>  $option['signName'],
//            'defaultTemplate' =>  $option['defaultTemplate'],
//        ];
        $smsApi = new Api($option);
        $param = ['code' => $code];
        $result = $smsApi->setTemplate($param)->send($mobile);
        $r = false;
//        dump($result);
        if (false !== $result) {
            if (isset($result->Code) && strtolower($result->Code) == 'ok') {
                $r = true;
            }
        }
        return $r;
    }

    static function sendSmsCodeForData($mobile, $param, $option)
    {
        $smsApi = new Api($option);
        $result = $smsApi->setTemplate($param)->send($mobile);
//        dump($result);
        $r = false;
        if (false !== $result) {
            if (isset($result->Code) && strtolower($result->Code) == 'ok') {
                $r = true;
            }
        }
        return $r;
    }

    static function sendSmsCodeOld($mobile, $code)
    {
        $curl = new Client();
        $post_data = [
            'auth'            => trim(Env::get('sms.sms_auth')),
            'SMSTemplateCode' => trim(Env::get('sms.SMSTemplateCode')),
            'mobile'          => $mobile,
            'code'            => $code,
            'ext'             => 'json',
        ];
        try {
            $response = (string)$curl->post(Env::get('sms.sms_url'), ['form_params' => $post_data])->getBody();
            $r = json_decode($response, true);
        } catch (\Exception $e) {
            return false;
        }
        return isset($r['code']) && $r['code'] == 2;
    }

    public static function getDate($timestamp)
    {
        $now = time();
        $diff = $now - $timestamp;
        if ($diff <= 60) {
            return $diff . '秒前';
        } elseif ($diff <= 3600) {
            return floor($diff / 60) . '分钟前';
        } elseif ($diff <= 86400) {
            return floor($diff / 3600) . '小时前';
        } elseif ($diff <= 2592000) {
            return floor($diff / 86400) . '天前';
        } else {
            return '一个月前';
        }
    }

    /**
     * 二次封装的密码加密
     * @param $str
     * @param string $auth_key
     * @return string
     * @author zhaoxiang <zhaoxiang051405@gmail.com>
     */
    public static function userMd5($str, $auth_key = '')
    {
        if (!$auth_key) {
            $auth_key = config('apiAdmin.AUTH_KEY');
        }

        return '' === $str ? '' : md5(sha1($str) . $auth_key);
    }

    /**
     * 判断当前用户是否是超级管理员
     * @param string $uid
     * @return bool
     * @author zhaoxiang <zhaoxiang051405@gmail.com>
     */
    public static function isAdministrator($uid = '')
    {
        if (!empty($uid)) {
            $adminConf = config('apiAdmin.USER_ADMINISTRATOR');
            if (is_array($adminConf)) {
                if (is_array($uid)) {
                    $m = array_intersect($adminConf, $uid);
                    if (count($m)) {
                        return true;
                    }
                } else {
                    if (in_array($uid, $adminConf)) {
                        return true;
                    }
                }
            } else {
                if (is_array($uid)) {
                    if (in_array($adminConf, $uid)) {
                        return true;
                    }
                } else {
                    if ($uid == $adminConf) {
                        return true;
                    }
                }
            }
        }

        return false;
    }

    /**
     * 将查询的二维对象转换成二维数组
     * @param array $res
     * @param string $key 允许指定索引值
     * @return array
     * @author zhaoxiang <zhaoxiang051405@gmail.com>
     */
    public static function buildArrFromObj($res, $key = '')
    {
        $arr = [];
        foreach ($res as $value) {
            $value = $value->toArray();
            if ($key) {
                $arr[$value[$key]] = $value;
            } else {
                $arr[] = $value;
            }
        }

        return $arr;
    }

    /**
     * 将二维数组变成指定key
     * @param $array
     * @param $keyName
     * @author zhaoxiang <zhaoxiang051405@gmail.com>
     * @return array
     */
    public static function buildArrByNewKey($array, $keyName = 'id')
    {
        $list = [];
        foreach ($array as $item) {
            $list[$item[$keyName]] = $item;
        }
        return $list;
    }

    public static function diffDate($date1, $date2)
    {
        $datetime1 = new \DateTime($date1);
        $datetime2 = new \DateTime($date2);
        $interval = $datetime1->diff($datetime2);
        $time['y'] = $interval->format('%Y');
        $time['m'] = $interval->format('%m');
        $time['d'] = $interval->format('%d');
        $time['h'] = $interval->format('%H');
        $time['i'] = $interval->format('%i');
        $time['s'] = $interval->format('%s');
        $time['a'] = $interval->format('%a');    // 两个时间相差总天数
        return $time;
    }

    public static function OcrImgAliYun($base64)
    {
        $b = new Base();
        if (empty($base64)) {
            return $b->buildResponse([], '1', '图片不能为空');
        }
        $url = "https://dm-53.data.aliyun.com/rest/160601/ocr/ocr_vehicle.json";
        $appcode = "9a32c253320c435197cdaca7f8f84011";
//        $file = "你的文件路径";
        //如果输入带有inputs, 设置为True，否则设为False
        $is_old_format = false;
        //如果没有configure字段，config设为空
        $config = [
            "side" => "face"
        ];
        //$config = array()


//        if($fp = fopen($file, "rb", 0)) {
//            $binary = fread($fp, filesize($file)); // 文件读取
//            fclose($fp);
//            $base64 = base64_encode($binary); // 转码
//        }
        $headers = [];
        array_push($headers, "Authorization:APPCODE " . $appcode);
        //根据API的要求，定义相对应的Content-Type
        array_push($headers, "Content-Type" . ":" . "application/json; charset=UTF-8");
        $querys = "";
        if ($is_old_format == true) {
            $request = [];
            $request["image"] = [
                "dataType"  => 50,
                "dataValue" => "$base64"
            ];

            if (count($config) > 0) {
                $request["configure"] = [
                    "dataType"  => 50,
                    "dataValue" => json_encode($config)
                ];
            }
            $body = json_encode(["inputs" => [$request]]);
        } else {
            $request = [
                "image" => "$base64"
            ];
            if (count($config) > 0) {
                $request["configure"] = json_encode($config);
            }
            $body = json_encode($request);
        }
        $method = "POST";
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl, CURLOPT_FAILONERROR, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HEADER, true);
        if (1 == strpos("$" . $url, "https://")) {
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        }
        curl_setopt($curl, CURLOPT_POSTFIELDS, $body);
        $result = curl_exec($curl);
        $header_size = curl_getinfo($curl, CURLINFO_HEADER_SIZE);
        $rheader = substr($result, 0, $header_size);
        $rbody = substr($result, $header_size);

        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        if ($httpCode == 200) {
            if ($is_old_format) {
                $output = json_decode($rbody, true);
                $result_str = $output["outputs"][0]["outputValue"]["dataValue"];
            } else {
                $result_str = $rbody;
            }
            return $b->buildResponse(json_decode($result_str, true), '0', '识别成功');
//            printf("result is :\n %s\n", $result_str);
        } else {
//            printf("Http error code: %d\n", $httpCode);
//            printf("Error msg in body: %s\n", $rbody);
//            printf("header: %s\n", $rheader);
            return $b->buildResponse([], '1', 'Http error:' . $rbody);

        }
    }

    public static function OcrImg($ImgBase64)
    {
        if (empty($ImgBase64)) {
            $b = new Base();
            return $b->buildResponse([], '1', '图片不能为空');
        }

        $USER_AGENTS = [
            "Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1; AcooBrowser; .NET CLR 1.1.4322; .NET CLR 2.0.50727)",
            "Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 6.0; Acoo Browser; SLCC1; .NET CLR 2.0.50727; Media Center PC 5.0; .NET CLR 3.0.04506)",
            "Mozilla/4.0 (compatible; MSIE 7.0; AOL 9.5; AOLBuild 4337.35; Windows NT 5.1; .NET CLR 1.1.4322; .NET CLR 2.0.50727)",
            "Mozilla/5.0 (Windows; U; MSIE 9.0; Windows NT 9.0; en-US)",
            "Mozilla/5.0 (compatible; MSIE 9.0; Windows NT 6.1; Win64; x64; Trident/5.0; .NET CLR 3.5.30729; .NET CLR 3.0.30729; .NET CLR 2.0.50727; Media Center PC 6.0)",
            "Mozilla/5.0 (compatible; MSIE 8.0; Windows NT 6.0; Trident/4.0; WOW64; Trident/4.0; SLCC2; .NET CLR 2.0.50727; .NET CLR 3.5.30729; .NET CLR 3.0.30729; .NET CLR 1.0.3705; .NET CLR 1.1.4322)",
            "Mozilla/4.0 (compatible; MSIE 7.0b; Windows NT 5.2; .NET CLR 1.1.4322; .NET CLR 2.0.50727; InfoPath.2; .NET CLR 3.0.04506.30)",
            "Mozilla/5.0 (Windows; U; Windows NT 5.1; zh-CN) AppleWebKit/523.15 (KHTML, like Gecko, Safari/419.3) Arora/0.3 (Change: 287 c9dfb30)",
            "Mozilla/5.0 (X11; U; Linux; en-US) AppleWebKit/527+ (KHTML, like Gecko, Safari/419.3) Arora/0.6",
            "Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.2pre) Gecko/20070215 K-Ninja/2.1.1",
            "Mozilla/5.0 (Windows; U; Windows NT 5.1; zh-CN; rv:1.9) Gecko/20080705 Firefox/3.0 Kapiko/3.0",
            "Mozilla/5.0 (X11; Linux i686; U;) Gecko/20070322 Kazehakase/0.4.5",
            "Mozilla/5.0 (X11; U; Linux i686; en-US; rv:1.9.0.8) Gecko Fedora/1.9.0.8-1.fc10 Kazehakase/0.5.6",
            "Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/535.11 (KHTML, like Gecko) Chrome/17.0.963.56 Safari/535.11",
            "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_7_3) AppleWebKit/535.20 (KHTML, like Gecko) Chrome/19.0.1036.7 Safari/535.20",
            "Opera/9.80 (Macintosh; Intel Mac OS X 10.6.8; U; fr) Presto/2.9.168 Version/11.52",
            "Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/536.11 (KHTML, like Gecko) Chrome/20.0.1132.11 TaoBrowser/2.0 Safari/536.11",
            "Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.1 (KHTML, like Gecko) Chrome/21.0.1180.71 Safari/537.1 LBBROWSER",
            "Mozilla/5.0 (compatible; MSIE 9.0; Windows NT 6.1; WOW64; Trident/5.0; SLCC2; .NET CLR 2.0.50727; .NET CLR 3.5.30729; .NET CLR 3.0.30729; Media Center PC 6.0; .NET4.0C; .NET4.0E; LBBROWSER)",
            "Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1; QQDownload 732; .NET4.0C; .NET4.0E; LBBROWSER)",
            "Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/535.11 (KHTML, like Gecko) Chrome/17.0.963.84 Safari/535.11 LBBROWSER",
            "Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 6.1; WOW64; Trident/5.0; SLCC2; .NET CLR 2.0.50727; .NET CLR 3.5.30729; .NET CLR 3.0.30729; Media Center PC 6.0; .NET4.0C; .NET4.0E)",
            "Mozilla/5.0 (compatible; MSIE 9.0; Windows NT 6.1; WOW64; Trident/5.0; SLCC2; .NET CLR 2.0.50727; .NET CLR 3.5.30729; .NET CLR 3.0.30729; Media Center PC 6.0; .NET4.0C; .NET4.0E; QQBrowser/7.0.3698.400)",
            "Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1; QQDownload 732; .NET4.0C; .NET4.0E)",
            "Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 5.1; Trident/4.0; SV1; QQDownload 732; .NET4.0C; .NET4.0E; 360SE)",
            "Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1; QQDownload 732; .NET4.0C; .NET4.0E)",
            "Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 6.1; WOW64; Trident/5.0; SLCC2; .NET CLR 2.0.50727; .NET CLR 3.5.30729; .NET CLR 3.0.30729; Media Center PC 6.0; .NET4.0C; .NET4.0E)",
            "Mozilla/5.0 (Windows NT 5.1) AppleWebKit/537.1 (KHTML, like Gecko) Chrome/21.0.1180.89 Safari/537.1",
            "Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.1 (KHTML, like Gecko) Chrome/21.0.1180.89 Safari/537.1",
            "Mozilla/5.0 (iPad; U; CPU OS 4_2_1 like Mac OS X; zh-cn) AppleWebKit/533.17.9 (KHTML, like Gecko) Version/5.0.2 Mobile/8C148 Safari/6533.18.5",
            "Mozilla/5.0 (Windows NT 6.1; Win64; x64; rv:2.0b13pre) Gecko/20110307 Firefox/4.0b13pre",
            "Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:16.0) Gecko/20100101 Firefox/16.0",
            "Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.11 (KHTML, like Gecko) Chrome/23.0.1271.64 Safari/537.11",
            "Mozilla/5.0 (X11; U; Linux x86_64; zh-CN; rv:1.9.2.10) Gecko/20100922 Ubuntu/10.10 (maverick) Firefox/3.6.10"
        ];

        $rand_ua = $USER_AGENTS[array_rand($USER_AGENTS, 1)];
        $url = "http://ai.baidu.com/aidemo";
        define("PROXY_SERVER", "tcp://n5.t.16yun.cn:6441");
        define("PROXY_USER", "16VBVBVV");
        define("PROXY_PASS", "619490");
        $proxyAuth = base64_encode(PROXY_USER . ":" . PROXY_PASS);
        $tunnel = rand(1, 10000);
        $headers = implode("\r\n", [
            "Proxy-Authorization: Basic {$proxyAuth}",
            "Proxy-Tunnel: ${tunnel}",
            "User-Agent: {$rand_ua}",
        ]);
        $options = [
            "http" => [
                "proxy"           => PROXY_SERVER,
                "header"          => $headers,
                "method"          => "GET",
                'request_fulluri' => true,
            ],
        ];
        $context = stream_context_create($options);
        $result = file_get_contents($url, false, $context);
        $responseInfo = $http_response_header;
        $cookie = str_replace("Set-Cookie: ", "", $responseInfo[8]);
        $post_data = [
            'image'     => $ImgBase64,//base64EncodeImage(__DIR__.'\WechatIMG250.jpeg')
            'type'      => 'vehicle',
            'image_url' => '',
        ];

        $headers = implode("\r\n", [
            "Proxy-Authorization: Basic {$proxyAuth}",
            "Proxy-Tunnel: ${tunnel}",
            "User-Agent: {$rand_ua}",
            "Cookie: {$cookie}",
            "Content-type:application/x-www-form-urlencoded",
        ]);
        $options = [
            "http" => [
                "proxy"           => PROXY_SERVER,
                "header"          => $headers,
                "method"          => "POST",
                'content'         => http_build_query($post_data),
                'request_fulluri' => true,
            ],
        ];
        $context = stream_context_create($options);
        $result = file_get_contents($url, false, $context);
        return json_decode($result, true);
    }

    public static function base64EncodeImageAliYun($image_file)
    {
        if (empty($image_file)) {
            return false;
        }
        $image_file = is_object($image_file) ? $image_file->getInfo('tmp_name') : null;
        if (empty($image_file)) {
            return false;
        }
        $image_info = getimagesize($image_file);
        $image_data = fread(fopen($image_file, 'r'), filesize($image_file));
//        $base64_image = 'data:' . $image_info['mime'] . ';base64,' . chunk_split(base64_encode($image_data));
        $base64_image = chunk_split(base64_encode($image_data));
        return $base64_image;
    }

    public static function base64EncodeImage($image_file)
    {
        if (empty($image_file)) {
            return false;
        }
        $image_file = is_object($image_file) ? $image_file->getInfo('tmp_name') : null;
        if (empty($image_file)) {
            return false;
        }
        $image_info = getimagesize($image_file);
        $image_data = fread(fopen($image_file, 'r'), filesize($image_file));
        $base64_image = 'data:' . $image_info['mime'] . ';base64,' . chunk_split(base64_encode($image_data));
//        $base64_image = chunk_split(base64_encode($image_data));
        return $base64_image;
    }

    /**
     * 红包生成算法
     *
     * @param $money 总金额
     * @param $number 红包数量
     * @param float $ratio 浮动系数
     * @return array|bool
     */
    public static function build_hongbao($money, $number, $ratio = 0.5)
    {
        $avg = bcdiv($money, $number, 8);
        $hongbao_min = 0.01;
        if ($avg < $hongbao_min) {
            return false;
        }
        $total = $money;
        $res = []; //结果数组
        $min = bcmul($avg, (bcsub(1, $ratio, 8)), 8);   //最小值
        if ($min < $hongbao_min) {
            $min = $hongbao_min;
            $ratio = bcadd(bcdiv($min, $avg, 8), 1, 8);
        }
        $max = bcmul($avg, (bcadd(1, $ratio, 8)), 8);   //最大值
        /*--- 第一步：分配低保 ---*/
        for ($i = 0; $i < $number; $i++) {
            $res[$i] = $min;
            $money = bcsub($money, $min, 8);
        }
        if ($money < 0) {
            return false;
        }
        /*--- 第二步：随机分配 ---*/
        $randRatio = 100;
        $randMax = bcmul(bcsub($max, $min, 8), $randRatio, 8);
        for ($i = 0; $i < $number; $i++) {
            //随机分钱
            $randRes = mt_rand(0, $randMax);
            $randRes = bcdiv($randRes, $randRatio, 8);
            if ($money >= $randRes) { //余额充足
                $res[$i] = bcadd($res[$i], $randRes, 8);
                $money = bcsub($money, $randRes, 8);
            } elseif ($money > 0) {     //余额不足
                $res[$i] = bcadd($res[$i], $money, 8);
                $money = bcsub($money, $money, 8);
            } else {                   //没有余额
                break;
            }
        }
        /*--- 第三步：平均分配上一步剩余 ---*/
        if ($money > 0) {
            $avg = bcdiv($money, $number, 8);
            for ($i = 0; $i < $number; $i++) {
                $res[$i] = bcadd($res[$i], $avg, 8);
            }
            $money = bcsub($money, $avg, 8);
        }
        if ($money < 0) {
            return false;
        }
        /*--- 第五步：格式化金额(可选) ---*/
        $r = 0;
        $t = 0;
        foreach ($res as $k => $v) {
            //两位小数，不四舍五入
            preg_match('/^\d+(\.\d{1,2})?/', $v, $match);
            $match[0] = number_format($match[0], 2);
            $r = bcadd($r, bcsub($v, $match[0], 8), 8);
            $res[$k] = $match[0];
            $t = bcadd($t, $match[0], 8);
        }
        $key = array_search(max($res), $res);
        $res[$key] = bcadd($r, $res[$key], 2);
        //    $res[$key]=bcadd($res[$key],bcsub($total,$res[$key]),2);
        //    dump($res);
        //    dump($total);
        //    dump(bcadd($t,$r,2));
        if ($total != bcadd($t, $r, 2)) {
            return false;
        }
        /*--- 第四步：打乱顺序 ---*/
        shuffle($res);

        return $res;
    }

    public static function fileCache($key, $v = '', $tiems = 0)
    {
        return cache($key, $v, ['type' => 'file', 'expire' => $tiems]);
    }
}
