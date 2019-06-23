<?php
/**
 * Created by PhpStorm.
 * User: tears
 * Date: 2018/12/7
 * Time: 9:39 AM
 */

namespace app\command;

use think\Cache;
use think\console\Command;
use think\console\Input;
use think\console\Output;
use think\Db;
use think\Exception;
use think\Log;

class HongbaoGet extends Command
{
    protected $RunStatus = 1;

    protected $sys = [];

    protected function handler_quit($sig)
    {
        $this->RunStatus = 0;
        $this->writeln("收到信号:" . $sig);
    }

    protected function configure()
    {
        $this->setName('HongbaoGetService')->setDescription('红包领取服务');
    }

    public function execute(Input $input, Output $output)
    {
        $this->writeln("开启红包领取服务");
        pcntl_signal(SIGINT, function ($sig) {
            $this->handler_quit($sig);
        });
        pcntl_signal(SIGTERM, function ($sig) {
            $this->handler_quit($sig);
        });
        pcntl_signal(SIGQUIT, function ($sig) {
            $this->handler_quit($sig);
        });


        $cached = Cache::init();
        $cache_handler = $cached->handler();

        for ($i = 0; $i < 8640 && $this->RunStatus == 1; $i++) {
            pcntl_signal_dispatch();
            $system = Db::table("t_sys_parameter")->select();
            $temp = [];
            foreach ($system as $item) {
                $temp[$item['s_key']] = $item['s_value'];
            }
            $this->sys = $temp;
//            try {
//                $yunxin = new YunXin();
//            } catch (\ErrorException $e) {
//                $this->writeln("fail:开启云信通信失败");
//                break;
//            }
            $keys = $cache_handler->lpop('app:hongbao:lingqu');
            if (empty($keys)) {
                sleep(1);
                continue;
            }
            $key = explode(":", $keys);
            $info = cache("app:hongbao:lingqu:" . $key[3]);
            $hongbao = cache($key[0] . ":" . $key[1] . ":" . $key[2]);
            if (isset($hongbao) && isset($info) && !empty($hongbao) && !empty($info)) {
                // 数据库操作!
                //查询红包
                try {
                    $hongbaoInfo = Db::name('hongbao')->where('id', 'eq', $hongbao['biz_uuid'])
                        ->where('status', 'eq', 0)->find();
                } catch (Exception $e) {
                    $this->writeln("Exception" . $e->getMessage());
                    break;
                }

                if (empty($hongbaoInfo)) {
                    $info['status'] = 2;
                    $info['msg'] = '没有相应红包数据';
                    cache("app:hongbao:lingqu:" . $key[3], $info);
                    Log::write('红包领取:' . $key[3] . "没有相应红包数据!:" . $hongbao['biz_uuid'], 'error');
                    continue;
                }
                if ($hongbaoInfo['num'] <= $hongbaoInfo['deal_num']) {

                    $info['status'] = 2;
                    $info['msg'] = '红包发放完成';
                    cache("app:hongbao:lingqu:" . $key[3], $info);
                    Log::write('红包领取:' . $key[3] . "红包发放完成!:" . $hongbao['biz_uuid'], 'error');
                    continue;
                }
                try {
                    $hbData = Db::name('hongbao_data')->where('biz_id', 'eq', $hongbao['biz_uuid'])
                        ->where('status', 'eq', 0)->find();
                } catch (Exception $e) {
                    $hbData = [];
                }

                if (empty($hbData)) {
                    $info['status'] = 2;
                    $info['msg'] = '没有相应红包领取数据';
                    cache("app:hongbao:lingqu:" . $key[3], $info);
                    Log::write('红包领取:' . $key[3] . "没有相应红包领取数据!:" . $hongbao['biz_uuid'], 'error');
                    continue;
                }
                // 查询用户
                try {
                    $user = Db::name('user')->alias('b')->where('b.memberid', 'eq',
                        $info['memberid'])->find();
                } catch (Exception $e) {
                    $user = [];
                }
                if (empty($user)) {
                    $info['status'] = 2;
                    $info['msg'] = '领取用户不存在';
                    cache("app:hongbao:lingqu:" . $key[3], $info);
                    Log::write('红包领取:' . $key[3] . "领取用户不存在!:" . $info['memberid'], 'error');
                    continue;
                }

                // 领取次数限制
                $sys_count = $this->sys[10011006];

                if ($sys_count != -1) {
                    $hbDataDb = Db::name('hongbao_data');
                    $count = $hbDataDb->where('to', 'eq',
                        $user['id'])->where('DATEDIFF(now(),lq_date)=0')->count();

                    if ($count + 1 > $sys_count) {
                        $info['status'] = 2;
                        $info['msg'] = '今日最多领取' . $sys_count . '个红包';
                        cache("app:hongbao:lingqu:" . $key[3], $info);
                        Log::write('红包领取:' . $key[3] . "达到领取上线!:" . $info['memberid'], 'info');
                        continue;
                    }
                }


                Db::startTrans();
                // 1.锁定数据
                try {
                    $r0 = Db::name('hongbao_data')->where('id', 'eq', $hbData['id'])->update([
                        'status' => 1,
                        'to' => $user['id'],
                        'redis_key' => $key[3],
                    ]);
                } catch (Exception $e) {
                    $r0 = false;
                }

                // 2 更新资金
                $sql = "INSERT INTO `t_log_lian`  (`memberid`, `membername`,op_time, `type`, `yue`, `num`, `from_type`, `remark`, `op_id`,orderid,account_type) 
					 select memberid,membername,now(),'收入',IFNULL(lian_num_keyong,0),{$hbData['money']}, '红包', '领取红包', '','{$hbData['id']}','可用账户' from t_user  where memberid='{$user['memberid']}'; ";
                $r1 = Db::execute($sql);
                $sql2 = "update t_user set lian_num_keyong=IFNULL(lian_num_keyong,0)+{$hbData['money']}, 
								lian_num_total=IFNULL(lian_num_total,0)+ {$hbData['money']} where memberid='{$user['memberid']}';";
                $r2 = Db::execute($sql2);
                // 3 记录红包
                $db = Db::name('hongbao');
                $r4 = true;
                $upDate ['deal_num'] = $db->raw("deal_num+1");
                $upDate ['deal_money'] = $db->raw("deal_money+" . $hbData['money']);
                if ($hongbaoInfo['deal_num'] + 1 == $hongbaoInfo['num']) {
                    $upDate['status'] = 1;
                }
                try {
                    $r5 = $db->where('id', 'eq', $hongbaoInfo['id'])->update($upDate);
                } catch (Exception $e) {
                    $r5 = false;
                }
                if ($r0 && $r1 && $r2 && $r4 && $r5) {
//                    $url = "https://api.netease.im/nimserver/msg/sendMsg.action";
                    $data['from'] = $info['memberid'];
                    $data['ope'] = $hongbao['type'] == 1 ? 0 : 1;// 0 点对点 1 群
                    $data['to'] = $hongbao['type'] == 1 ? $hongbao['accid'] : $hongbao['to'];//接收者
                    $body['title'] = $info['nickname'] . ' 领取了' . $hongbao['nickname'] . '的红包!';
                    if ($hongbao['type'] == 1) {
                        $data['to'] = $hongbao['accid'];
                        $data['from'] = $info['memberid'];//接收者
                        $body['title'] = $info['nickname'] . ' 领取了的红包!';
                    }
                    $data['type'] = 100;
                    $body['type'] = 4;
                    $data['body'] = json_encode($body);

//                    $r6 = $yunxin->send($url, $data);
                    $r6 = false;
                    try {
                        $info['money'] = $hbData['money'];
                        $info['status'] = 1;
                        cache("app:hongbao:lingqu:" . $key[3], $info);
                        Db::commit();
                        Log::write('红包领取:' . $key[3] . "成功!云信通知:" . json_encode($r6), 'info');
                    } catch (\Exception $e) {
                        Db::rollback();
                        $info['status'] = 2;
                        $info['msg'] = '数据处理失败';
                        cache("app:hongbao:lingqu:" . $key[3], $info);
                        Log::write('红包领取:' . $key[3] . "数据处理失败!:" . $e->getMessage(), 'error');
                        continue;
                    }
                } else {
                    Db::rollback();
                    $info['status'] = 2;
                    $info['msg'] = '数据处理失败';
                    cache("app:hongbao:lingqu:" . $key[3], $info);
                    Log::write('红包领取:' . $key[3] . "数据处理失败!:" . json_encode([$r0, $r1, $r4, $r5]), 'error');
                    continue;
                }
            } else {
                Log::write('红包领取:' . $key[3] . "没有相应红包数据!", 'error');
                continue;
            }
            unset($user);
            unset($keys);
            unset($info);
            unset($key);
            unset($body);
            unset($data);
            unset($hbData);
            unset($upDate);
            unset($hongbao);
            unset($res_op);
            unset($hongbaoInfo);
        }
        $this->writeln("结束红包领取服务");
    }

    public function writeln($messages)
    {
        $this->output->writeln(date('Y-m-d H:i:s') . "\t " . $messages);
    }

}