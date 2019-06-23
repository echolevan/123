<?php
/**
 * Created by PhpStorm.
 * User: tears
 * Date: 2018/12/6
 * Time: 2:37 PM
 */

namespace app\api\controller;


use think\Cache;
use think\Db;
use think\Exception;

class Hongbao extends Common
{

    public function send()
    {
        if ($this->sys[10011001] != '开启') {
            return $this->quickResponse([], -1, '该功能已经关闭');
        }
        // 2.验证
        $data['userid'] = $this->app_member['id'];
        $data['accid'] = $this->app_member['memberid'];
        $data['to'] = input('to');
        $data['type'] = input('type');
        $data['money'] = input('money');
        $data['number'] = input('number');
        $data['nickname'] = input('nickname',
            empty($this->app_member['membername']) ? $this->app_member['memberid'] : $this->app_member['membername']);
        $data['title'] = input('title', '恭喜发财,大吉大利');
        $data['trade_password'] = input('trade_password');
        $result = $this->validate($data, 'Hongbao');
        if (true !== $result) {
            return json(arr_msg(-1, '', $result));
        }
        if ($data['type'] == 3) {
            $data['money'] = $data['money'] * $data['number'];
        }
        $coin_num = $this->app_member["lian_num_keyong"];

        if ($coin_num < $data['money']) {

            return $this->quickResponse([], -1, '账户余额不足');
        }

        // 金额限制 次数限制

        $sys_count = $this->sys[10011004];
        $sys_money_num = $this->sys[10011002];

        // 金额限制
        if ($sys_money_num != -1) {
            $hbDataDb = Db::name('hongbao');
            $money = $hbDataDb->where('userid', 'eq',
                $this->app_member['id'])->where('DATEDIFF(now(),op_time)=0')->sum('money');

            if ($money + $data['money'] > $sys_money_num) {
                return $this->quickResponse([], -1, '今日最多发放' . $sys_money_num . '数量的红包');
            }
        }
        // 次数限制

        if ($sys_count != -1) {
            $hbDataDb = Db::name('hongbao');
            $count = $hbDataDb->where('userid', 'eq',
                $this->app_member['id'])->where('DATEDIFF(now(),op_time)=0')->count();

            if ($count + 1 > $sys_count) {
                return $this->quickResponse([], -1, '今日最多发放' . $sys_count . '次红包');
            }
        }

        $fenkong = Db::query('select f_lian_goujiinfo(:type,:account,:memberid) as fengkong_status',
            ['memberid' => $this->app_member['memberid'], "type" => "勾稽状态", "account" => "MSTH提取"]);
        if ($fenkong[0]['fengkong_status'] != 0) {
            return $this->quickResponse([], -1, '风控用户暂时无法使用该功能');
        }


        //1 生成业务编号
        $biz_id = md5(openssl_random_pseudo_bytes(32));

        //3. 生成红包缓存
        $data['status'] = 99;
        $data['info'] = '';
        unset($data['trade_password']);
        //        $data['lingqu'] =0;
        $data['add_time'] = time();
        try {
            $key = 'app:hongbao:' . $biz_id;
            $cached = Cache::init();
            if ($cached->has($key)) {
                throw new \Exception('缓存已存在');
            }
            $res = cache($key, $data);

            $res2 = $cached->handler()->rPush('app:hongbao', $key);
            if ($res && $res2) {
                return $this->quickResponse(['biz_id' => $biz_id]);
            } else {
                throw new \Exception('缓存失败');
            }
        } catch (\Exception $e) {
            return $this->quickResponse([], -1, '发送失败:' . $e->getMessage());
        }


    }

    public function get_send_status()
    {
        $biz_id = input('biz_id');
        if (empty($biz_id)) {
            return $this->quickResponse([], -1, 'biz_id 不正确');
        }
        //1 根据业务编号查询状态
        $data = cache('app:hongbao:' . $biz_id);
        if ($data) {
            if ($data['userid'] != $this->app_member['id']) {
                return $this->quickResponse([], -1, 'biz_id 不正确');
            }
            switch ($data['status']) {
                case 99:
                    return $this->quickResponse([], 99, '请等待..');
                    break;
                case 1:
                    return $this->quickResponse([]);
                    break;
                default:
                    return $this->quickResponse([], -2, '红包未能发送:' . $data['info']);
                    break;
            }
        } else {
            return $this->quickResponse([], -1, 'biz_id 不正确');
        }

    }

    public function get()
    {
        if ($this->sys[10011001] != '开启') {
            return $this->quickResponse([], -1, '该功能已经关闭');
        }
        $biz_id = input('biz_id');
        if (empty($biz_id)) {
            return $this->quickResponse([], -1, 'biz_id 不正确');
        }
        $key = 'app:hongbao:' . $biz_id;
        $nickname = input('nickname', $this->app_member['memberid']);
        //1 根据业务编号查询状态
        $data = cache($key);
        if ($data) {
            switch ($data['status']) {
                case 1:
                    //  超过24小时不可以领取
                    if (time() - $data['add_time'] >= 24 * 3600) {
                        return $this->quickResponse([], -5, '该红包超过24小时已经退回!');
                    }
                    $cached = Cache::init();
                    $cache_handler = $cached->handler();
                    // 1. 验证红包对象
                    if ($data['type'] == 1) {
                        if ($data['to'] != $this->app_member['memberid']) {
                            return $this->quickResponse([], -1, '领取对象不正确!');
                        }
                    } else {
                        // 是否是群成员
                        $res = $cache_handler->sIsMember("app:hongbao:qun:member:list:{$key}",
                            $this->app_member['memberid']);
                        if (!$res) {
                            return $this->quickResponse([], -1, '领取对象不正确!');
                        }
                    }
                    // 2. 验证是否还有剩余  sCard testset
                    $r = $cache_handler->sCard("app:hongbao:lingqu:list:{$key}");
                    if ($r >= $data['number']) {
                        $data['status'] = 100;
                        cache($key, $data);

                        return $this->quickResponse([], 100, '已经领取完毕!');
                    }
                    // 3. 验证是否领取 && 4. 标记领取
                    $r = $cache_handler->sAdd("app:hongbao:lingqu:list:{$key}", $this->app_member['memberid']);
                    if (!$r) {
                        return $this->quickResponse([], -2, '已经领取过了!');
                    }

                    // 次数限制
                    $sys_count = $this->sys[10011006];
                    if ($sys_count != -1) {
                        $hbDataDb = Db::name('hongbao_data');
                        $count = $hbDataDb->where('to', 'eq',
                            $this->app_member['id'])->where('DATEDIFF(now(),lq_date)=0')->count();
                        if ($count + 1 > $sys_count) {
                            return $this->quickResponse([], -2, '今日最多领取' . $sys_count . '个红包');
                        }
                    }


                    $fenkong = Db::query('select f_lian_goujiinfo(:type,:account,:memberid) as fengkong_status',
                        ['memberid' => $this->app_member['memberid'], "type" => "勾稽状态", "account" => "MSTH提取"]);
                    if ($fenkong[0]['fengkong_status'] != 0) {
                        return $this->quickResponse([], -1, '风控用户暂时无法使用该功能');
                    }

                    // 5. 执行数据操作
                    // 生成业务编号
                    $biz_id = md5(openssl_random_pseudo_bytes(32));
                    $res = $cache_handler->rPush('app:hongbao:lingqu',
                        "{$key}:{$biz_id}:{$this->app_member['memberid']}");
                    $lingqudata = [
                        'memberid' => $this->app_member['memberid'],
                        'status' => 99,
                        'money' => 0,
                        'create_time' => time(),
                        'nickname' => $nickname,
                        'msg' => ''
                    ];
                    $res2 = cache('app:hongbao:lingqu:' . $biz_id, $lingqudata);
                    if ($res && $res2) {
                        return $this->quickResponse(['biz_id' => $biz_id]);
                    } else {
                        // 6. 回滚 sRem
                        $cache_handler->sRem("app:hongbao:lingqu:list:{$key}", $this->app_member['memberid']);

                        return $this->quickResponse([], -1, '领取失败!');
                    }
                    break;
                case 102:
                    return $this->quickResponse([], -5, '该红包超过24小时已经退回!');
                case 100:
                    return $this->quickResponse([], 100, '已经领取完毕!');
                    break;
                default:
                    return $this->quickResponse([], -1, 'biz_id 不正确');
                    break;
            }
        } else {
            return $this->quickResponse([], -1, 'biz_id 不正确');
        }
    }

    public function get_get_status()
    {
        $biz_id = input('biz_id');
        if (empty($biz_id)) {
            return $this->quickResponse([], -1, 'biz_id 不正确');
        }
        //1 根据业务编号查询状态
        $data = cache('app:hongbao:lingqu:' . $biz_id);
        if ($data) {
            if ($data['memberid'] != $this->app_member['memberid']) {
                return $this->quickResponse([], -1, 'biz_id 不正确');
            }
            switch ($data['status']) {
                case 99:
                    return $this->quickResponse([], 99, '请等待..');
                    break;
                case 1:
                    //                    dump($data);
                    return $this->quickResponse(['money' => $data['money']]);
                    break;
                default:
                    return $this->quickResponse([], -2, '红包领取失败:' . $data['msg']);
                    break;
            }
        } else {
            return $this->quickResponse([], -1, 'biz_id 不正确');
        }

    }

    public function info()
    {
        $biz_id = input('biz_id');
        if (empty($biz_id)) {
            return $this->quickResponse([], -1, 'biz_id 不正确');
        }
        $key = 'app:hongbao:' . $biz_id;
        $data = cache($key);
        if ($data) {
            switch ($data['status']) {
                case 100:
                case 102:
                case 1:

                    $cached = Cache::init();
                    $cache_handler = $cached->handler();
                    // 1. 验证红包对象
                    if ($data['type'] == 1) {
                        if ($data['to'] != $this->app_member['memberid'] && $data['userid'] != $this->app_member['id']) {
                            return $this->quickResponse(['is_can_open' => 0], -1, '领取对象不正确!');
                        }
                    } else {
                        // 是否是群成员
                        $res = $cache_handler->sIsMember("app:hongbao:qun:member:list:{$key}",
                            $this->app_member['memberid']);
                        if (!$res) {
                            return $this->quickResponse(['is_can_open' => 0], -1, '领取对象不正确!');
                        }
                    }
                    $is_can_open = 1;
                    // 2. 验证是否还有剩余  sCard testset
                    if ($data['status'] != 1) {
                        $is_can_open = 0;
                    } else {
                        $r = $cache_handler->sCard("app:hongbao:lingqu:list:{$key}");
                        if ($r >= $data['number']) {
                            $data['status'] = 100;
                            cache($key, $data);
                            $is_can_open = 0;
                        }
                    }
                    //  超过24小时不可以领取
                    if (time() - $data['add_time'] >= 24 * 3600) {
                        $is_can_open = 0;
                    }
                    try {
                        $hbData = Db::name('hongbao')->where('id', $data['biz_uuid'])->find();
                    } catch (Exception $e) {
                        return $this->quickResponse(['is_can_open' => 0], -1, '数据查询失败!');
                    }
                    $returnData['total_num'] = $hbData['num'];
                    $returnData['title'] = $data['title'];
                    $returnData['deal_num'] = $hbData['deal_num'];
                    $returnData['total_money'] = $hbData['money'];
                    $returnData['deal_money'] = $hbData['deal_money'];
                    $r = $cache_handler->sIsMember("app:hongbao:lingqu:list:{$key}", $this->app_member['memberid']);
                    if ($r) {
                        $is_can_open = 0;
                    }
                    if ($data['type'] == 1) {
                        if ($data['userid'] == $this->app_member['id']) {
                            $is_can_open = 0;
                        }
                    }
                    //                    if ($data['userid'] == $this->app_member['id']) {
                    //                        $r = $cache_handler->sIsMember("app:hongbao:lingqu:list:{$key}", $this->app_member['memberid']);
                    //                        if ($r) {
                    //                            $is_can_open = 0;
                    //                        }
                    //                    } else {
                    //                        // 3. 验证是否领取 && 4. 标记领取
                    //                        $r = $cache_handler->sIsMember("app:hongbao:lingqu:list:{$key}", $this->app_member['memberid']);
                    //                        if (!$r) {
                    //                            if ($is_can_open == 1) {
                    //                                return $this->quickResponse(['is_can_open' => $is_can_open], 1, '尚未领取不允许查看!');
                    //                            }
                    //                        }
                    //                    }
                    try {
                        $list = Db::name('hongbao_data')->alias('a')
                            ->join('user b', 'a.to=b.id')
                            ->field('b.memberid as accid,a.money,a.lq_date')
                            ->where('a.status', 'eq', 1)->where('a.biz_id', $data['biz_uuid'])
                            ->order('a.lq_date desc')->select();
                    } catch (Exception $e) {
                        $list = [];
                    }
                    $returnData['is_can_open'] = $is_can_open;
                    $returnData['list'] = $list;

                    return $this->quickResponse($returnData);
                    break;
                default:
                    return $this->quickResponse(['is_can_open' => 0], -1, 'biz_id 不正确');
                    break;
            }
        } else {
            return $this->quickResponse(['is_can_open' => 0], -1, 'biz_id 不正确');
        }

    }

    private function quickResponse(array $array, int $int = 1, string $string = '操作成功!')
    {
        return json(arr_msg($int, $array, $string));
    }
}