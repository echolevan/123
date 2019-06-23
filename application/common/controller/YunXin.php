<?php

namespace app\common\controller;

use Curl\Curl;
use think\Config;

class YunXin
{
    private $curl;

    /**
     * YunXin constructor.
     * @throws \ErrorException
     */
    public function __construct()
    {
        $yunxin = Config::get('yx_config');
        $nonce_str = generate_str();
        $time = time();
        $this->curl = new Curl();
        $checkSum = strtolower(sha1($yunxin['appsecret'] . $nonce_str . $time));
        $headers = [
            'AppKey'       => $yunxin['appkey'],
            'Nonce'        => $nonce_str,
            'CurTime'      => $time,
            'CheckSum'     => $checkSum,
            'Content-Type' => 'application/x-www-form-urlencoded;charset=utf-8'
        ];
        $this->curl->setHeaders($headers);
        $this->curl->setOpt(CURLOPT_SSL_VERIFYHOST, 0);
        $this->curl->setOpt(CURLOPT_SSL_VERIFYPEER, 0);
    }

    public function regist($accid, $mobile)
    {
        $url = "https://api.netease.im/nimserver/user/create.action";

        return $this->curl->post($url, ['accid' => $accid, 'mobile' => $mobile, 'name' => $accid]);
    }

    public function refresh($accid)
    {
        $url = "https://api.netease.im/nimserver/user/refreshToken.action";

        return $this->curl->post($url, ['accid' => $accid]);
    }

    public function getQunMembers($tid)
    {
        $url = "https://api.netease.im/nimserver/team/queryDetail.action";

        return $this->curl->post($url, ['tid' => $tid]);
    }

    public function send($url, $data)
    {
        return $this->curl->post($url, $data);
    }

    public function refreshToken($accid)
    {
        $url = "https://api.netease.im/nimserver/user/refreshToken.action";

        return $this->send($url, ['accid' => $accid]);
    }

    public function sendAttachMsg($to, $message, $msgtype)
    {
        $biz_id = md5(openssl_random_pseudo_bytes(32));
        $body['messageId'] = $biz_id;

        $url = "https://api.netease.im/nimserver/msg/sendAttachMsg.action";
        $data['from'] = "admin";
        $data['msgtype'] = $msgtype;// 0 点对点 1 群
        $data['to'] = $to;//接收者
        $body['message'] = $message;
        $body['system'] = $data['msgtype'] == 0 ? "101" : "102";
        $body['from'] = "admin";
        //        dump($body);
        $data['attach'] = json_encode($body, JSON_UNESCAPED_UNICODE);

        //        dump($data);
        return $this->send($url, $data);
    }

    public function sendBatchAttachMsg($to, $message)
    {
        $biz_id = md5(openssl_random_pseudo_bytes(32));
        $body['messageId'] = $biz_id;

        $url = "https://api.netease.im/nimserver/msg/sendBatchAttachMsg.action";
        $data['fromAccid'] = "admin";
        $data['toAccids'] = json_encode($to);//接收者
        $body['message'] = $message;
        $body['system'] = "101";
        $body['from'] = "admin";
        $data['attach'] = json_encode($body);

        return $this->send($url, $data);
    }

    /**
     * @param $accids 用户帐号（例如：JSONArray对应的accid串，如："zhangsan"，如果解析出错，会报414）（一次查询最多为200）
     * @return mixed
     */
    public function getUinfoss($accids)
    {
        $url = 'https://api.netease.im/nimserver/user/getUinfos.action';
        $data = [
            'accids' => json_encode($accids)
        ];
        return $this->curl->post($url, $data);
    }

    /**
     * @param $accid [云信ID，最大长度32字节，必须保证一个APP内唯一（只允许字母、数字、半角下划线_、@、半角点以及半角-组成，不区分大小写，会统一小写处理）]
     * @param string $name [云信ID昵称，最大长度64字节，用来PUSH推送时显示的昵称]
     * @param string $icon [用户icon，最大长度256字节]
     * @param string $sign [用户签名，最大长度256字节]
     * @param string $email [用户email，最大长度64字节]
     * @param string $birth [用户生日，最大长度16字节]
     * @param string $mobile [用户mobile，最大长度32字节]
     * @param string $gender [用户性别，0表示未知，1表示男，2女表示女，其它会报参数错误]
     * @param string $ex [用户名片扩展字段，最大长度1024字节，用户可自行扩展，建议封装成JSON字符串]
     * @return mixed
     */
    public function updateUinfo($accid, $name = '', $icon = '', $sign = '', $email = '', $birth = '', $mobile = '', $gender = '0', $ex = '')
    {
        $url = 'https://api.netease.im/nimserver/user/updateUinfo.action';
        $data['accid'] = $accid;
        if (!empty($name)) {
            $data['name'] = $name;
        }
        if (!empty($icon)) {
            $data['icon'] = $icon;
        }
        if (!empty($sign)) {
            $data['sign'] = $sign;
        }
        if (!empty($email)) {
            $data['email'] = $email;
        }
        if (!empty($birth)) {
            $data['birth'] = $birth;
        }
        if (!empty($mobile)) {
            $data['mobile'] = $mobile;
        }
        if (!empty($gender)) {
            $data['gender'] = $gender;
        }
        if (!empty($ex)) {
            $data['ex'] = $ex;
        }

        return $this->curl->post($url, $data);
    }

    /**
     * 添加好友
     */
    public function addFriend($accid, $faccid, $type = '1', $msg = '')
    {
        $url = 'https://api.netease.im/nimserver/friend/add.action';
        $data = [
            'accid'  => $accid,
            'faccid' => $faccid,
            'type'   => $type,
            'msg'    => $msg
        ];

        return $this->curl->post($url, $data);
    }

    /**
     * 获取好友关系
     */
    public function getFriend($accid)
    {
        $url = 'https://api.netease.im/nimserver/friend/get.action';
        $data = [
            'accid'      => $accid,
            'updatetime' => time()
        ];

        return $this->curl->post($url, $data);
    }
}
