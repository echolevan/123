<?php
/**
 * Created by PhpStorm.
 * User: tears
 * Date: 2018/12/14
 * Time: 4:33 PM
 */

namespace app\ms_admin\controller;


use app\common\controller\YunXin;
use think\Cache;

class Message extends Base
{
    public function index()
    {
        if (request()->isPost()) {
            $this->send();
        }
        return $this->assign('to', input('to'))->fetch();
    }

    public function send()
    {
        $to = input('to');
        $message = input('message');
        if (empty($message)) {
            $this->error('参数错误!');
        }
        $type = 1;
        if ($to == '[全网用户]') {
            $type = 3;
        }
        if (is_numeric($to)) {
            $type = 2;
        }
        switch ($type) {
            case 1:
                if (empty($to)) {
                    return json(msg(-1, [], "参数错误"));
                }
                try {
                    $yunxin = new YunXin();
                } catch (\ErrorException $e) {
                    return json(msg(-1, [], "开启云信通信失败" . $e->getMessage()));
                }
                $msg_res = $yunxin->sendAttachMsg($to, $message, 0);
                if (isset($msg_res) && (isset($msg_res->code) && $msg_res->code == 200)) {
                    return json(msg(1, [], "发送成功1"));
                } else {
                    return json(msg(-1, [], "发送失败"));
                }
                break;
            case 2:
                if (empty($to)) {
                    return json(msg(-1, [], "参数错误"));
                }
//                $message = input('title') . "群:" . $message;
                try {
                    $yunxin = new YunXin();
                } catch (\ErrorException $e) {
                    return json(msg(-1, [], "开启云信通信失败" . $e->getMessage()));
                }
                $msg_res = $yunxin->sendAttachMsg($to, $message, 1);
                if (isset($msg_res) && (isset($msg_res->code) && $msg_res->code == 200)) {
                    return json(msg(1, [], "发送成功2"));
                } else {
                    return json(msg(-1, [], "发送失败"));
                }
                break;
            case 3:
                $biz_id = md5(openssl_random_pseudo_bytes(32));
                $data['status'] = 99;
                $data['message'] = $message;
                $data['msg'] = '';
                $data['add_time'] = time();
                try {
                    $key = 'app:qunfa:' . $biz_id;
                    $cached = Cache::init();
                    if ($cached->has($key)) {
                        throw new \Exception('缓存已存在');
                    }
                    $res = $cached->set($key, $data);
                    $res2 = $cached->handler()->rpush('app:qunfa', $key);
                    if (!$res || !$res2) {
                        throw new \Exception('缓存失败');
                    }
                } catch (\Exception $e) {
                    return json(msg(-1, [], "操作失败" . $e->getMessage()));
                }
                return json(msg(1, ['id' => $key], "操作成功"));
                break;
            default :
                return json(msg(-1, [], "参数错误"));
        }
    }

}