<?php
/**
 * Created by PhpStorm.
 * User: tears
 * Date: 2018/12/7
 * Time: 9:00 AM
 */

namespace app\command;


use app\common\controller\YunXin;
use app\util\Tools;
use think\Cache;
use think\console\Command;
use think\console\Input;
use think\console\Output;
use think\Db;
use think\Exception;
use think\Log;

class HongbaoSend extends Command
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
        $this->setName('HongbaoSendService')->setDescription('红包发放服务');
    }

    public function execute(Input $input, Output $output)
    {
        $this->writeln("开启红包发放服务");
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
            try {
                $yunxin = new YunXin();
            } catch (\ErrorException $e) {
                $this->writeln("fail:开启云信通信失败");
                break;
            }
            $key = $cache_handler->lpop('app:hongbao');
            if (empty($key)) {
                sleep(1);
                continue;
            }
            $info = cache($key);
            if (empty($info)) {
                sleep(1);
                Log::write('红包发放:' . $key . "发放数据不存在!", 'error');
                continue;
            }
            if ($info['status'] == 99) {
                // 1.验证对象
                if ($info['type'] == 1) {
                    // 接收用户验证
                    try {
                        $user = Db::name('user')->where('memberid', $info['to'])->find();
                    } catch (Exception $e) {
                        Log::write('红包发放:' . $key . "异常!:" . $e->getMessage(), 'error');
                        break;
                    }
                    if (!$user) {
                        $info['status'] = 2;
                        $info['info'] = '接收用户不存在';
                        cache($key, $info);
                        Log::write('红包发放:' . $key . "接收用户不存在!:" . $info['to'], 'error');
                        continue;
                    }
                } else {
                    // 请求云信接口
                    $members = $yunxin->getQunMembers($info['to']);
//                    dump($members);
//                    dump(!isset($members->tinfo));
//                    dump(!isset($members->code));
//                    dump(isset($members) && (isset($members->code) && $members->code != 200));
//
//                    die;
                    if (isset($members) && (isset($members->code) && $members->code != 200) || !isset($members->code) || !isset($members->tinfo)) {
                        $info['status'] = 3;
                        $info['info'] = '接收群组不存在';
                        cache($key, $info);
                        Log::write('红包发放:' . $key . "接收群组不存在!:" . $info['to'] . ":" . json_encode($members), 'error');
                        continue;
                    }
                    // 记录群成员
                    $yunxinInfo = $members->tinfo;
                    if (isset($yunxinInfo->admins)) {
                        foreach ($yunxinInfo->admins as $v) {
                            $cache_handler->sAdd("app:hongbao:qun:member:list:{$key}", $v->accid);
                        }
                    }
                    if (isset($yunxinInfo->members)) {
                        foreach ($yunxinInfo->members as $v) {
                            $cache_handler->sAdd("app:hongbao:qun:member:list:{$key}", $v->accid);
                        }
                    }
                    if (isset($yunxinInfo->owner)) {
                        $cache_handler->sAdd("app:hongbao:qun:member:list:{$key}", $yunxinInfo->owner->accid);
                    }
                }

                try {
                    $sendUser = Db::name('user')->where('id', 'eq',
                        $info['userid'])->find();
                } catch (Exception $e) {
                    $sendUser = [];
                }
                if (empty($sendUser)) {
                    $info['status'] = 2;
                    $info['info'] = '发放用户不存在!';
                    cache($key, $info);
                    Log::write('红包发放:' . $key . "发放用户不存在!:" . $info['userid'], 'error');
                    continue;
                }
                $balance = $sendUser["lian_num_keyong"];
                if ($balance < $info['money']) {
                    $info['status'] = 2;
                    $info['info'] = '账户余额不足';
                    cache($key, $info);
                    Log::write('红包发放:' . $key . "发放用户账户余额不足!:" . $info['userid'], 'error');
                    continue;
                }

                $sys_count = $this->sys[10011004];
                $sys_money_num = $this->sys[10011002];

                // 金额限制
                if ($sys_money_num != -1) {
                    $hbDataDb = Db::name('hongbao');
                    $money = $hbDataDb->where('userid', 'eq',
                        $sendUser['id'])->where('DATEDIFF(now(),op_time)=0')->sum('money');

                    if ($money + $info['money'] > $sys_money_num) {
                        $info['status'] = 2;
                        $info['info'] = '今日最多发放' . $sys_money_num . '数量的红包';
                        cache($key, $info);
                        Log::write('红包发放:' . $key . "达到发放数量上线!:" . $info['userid'], 'info');
                        continue;
                    }
                }
                // 次数限制

                if ($sys_count != -1) {
                    $hbDataDb = Db::name('hongbao');
                    $count = $hbDataDb->where('userid', 'eq',
                        $sendUser['id'])->where('DATEDIFF(now(),op_time)=0')->count();

                    if ($count + 1 > $sys_count) {
                        $info['status'] = 2;
                        $info['info'] = '今日最多发放' . $sys_count . '次红包';
                        cache($key, $info);
                        Log::write('红包发放:' . $key . "达到发放次数上线!:" . $info['userid'], 'info');
                        continue;
                    }
                }
                //                dbDebug();
                $res0 = Db::query('select uuid() as uuid');
                $uuid = $res0[0]['uuid'];
                Db::startTrans();
                // 记录红包
                $r0 = Db::name('hongbao')->insert([
                    'id' => $uuid,
                    'redis_key' => $key,
                    'status' => 0,
                    'type' => $info['type'],
                    'num' => $info['number'],
                    'money' => $info['money'],
                    'userid' => $info['userid'],
                    'to' => $info['to'],
                ]);
                // 2 扣除资金
                $sql = "INSERT INTO `t_log_lian`  (`memberid`, `membername`,op_time, `type`, `yue`, `num`, `from_type`, `remark`, `op_id`,orderid,account_type) 
					 select memberid,membername,now(),'支出',IFNULL(lian_num_keyong,0),{$info['money']}, '红包', '发放红包', '','{$uuid}','可用账户' from t_user  where memberid='{$sendUser['memberid']}'; ";
                $r1 = Db::execute($sql);
                $sql2 = "update t_user set lian_num_keyong=IFNULL(lian_num_keyong,0)-{$info['money']}, 
								lian_num_xiaofei=IFNULL(lian_num_xiaofei,0)+ {$info['money']}   
			where memberid='{$sendUser['memberid']}';";
                $r2 = Db::execute($sql2);


                // 3 记录红包
                $hbData = [];
                $op = 0;
                if ($info['type'] == 2) {
                    $op = 0.6;
                }
                //                生成红包
                $times = 0;
                do {
                    $times++;
                    $hongbaolist = Tools::build_hongbao($info['money'], $info['number'], $op);
                } while (!$hongbaolist && $times < 5);
                //                dump($times);
                if (!$hongbaolist) {
                    Db::rollback();
                    $info['status'] = 2;
                    $info['info'] = '数据处理失败';
                    cache($key, $info);
                    Log::write('红包发放:' . $key . "数据处理失败!", 'error');
                    continue;
                }

                foreach ($hongbaolist as $k => $v) {
                    $hbData[$k]['id'] = 'uuid()';
                    //                    $hbData[$key]['id'] = ['exp'=>'uuid()'];
                    $hbData[$k]['money'] = $v;
                    $hbData[$k]['biz_id'] = $uuid;
                    $hbData[$k]['userid'] = $info['userid'];
                    $hbData[$k]['status'] = 0;
                    //                    $hbData[$key]['to'] = '';
                }
                $sql = Db::name('hongbao_data')->fetchSql(true)->insertAll($hbData);
                $sql = str_replace("'uuid()'", 'uuid()', $sql);
                // 4.修改状态
                $r3 = Db::execute($sql);

                $info['biz_uuid'] = $uuid;
                $info['status'] = 1;
                $r4 = cache($key, $info);
                if ($r0 && $r1 && $r2 && $r3 && $r4) {
//                    $url = "https://api.netease.im/nimserver/msg/sendMsg.action";
                    $data['from'] = $info['accid'];
                    $data['ope'] = $info['type'] == 1 ? 0 : 1;// 0 点对点 1 群
                    $data['to'] = $info['to'];//接收者
                    $data['type'] = 100;
                    $body['redType'] = intval($info['type']); // 红包的类型
                    $body['type'] = 1;
                    $body['title'] = $info['title'];
                    $body['number'] = intval($info['number']);
                    $body['biz_id'] = explode(':', $key)[2];
                    $data['body'] = json_encode($body);
//                    $r5 = $yunxin->send($url, $data);
                    $r5 = false;
                    try {
                        Db::commit();
                        Log::write('红包发放:' . $key . "成功!云信通知:" . json_encode($r5), 'info');
                    } catch (\Exception $e) {
                        Db::rollback();
                        $info['status'] = 2;
                        $info['info'] = '数据处理失败';
                        cache($key, $info);
                        Log::write('红包发放:' . $key . "处理失败!:" . $e->getMessage(), 'error');
                        continue;
                    }
                } else {
                    Db::rollback();
                    $info['status'] = 2;
                    $info['info'] = '数据处理失败';
                    cache($key, $info);
                    Log::write('红包发放:' . $key . "数据处理失败!:" . json_encode([$r0, $r1, $r3, $r4]), 'error');
                    continue;
                }

            }
            unset($user);
            unset($info);
            unset($key);
            unset($sql);
            unset($body);
            unset($members);
            unset($hongbaolist);
            unset($data);
            unset($hbData);
            unset($sendUser);
            unset($yunxinInfo);
            unset($balance);
        }
        $this->writeln("结束红包发放服务");
    }


    public function writeln($messages)
    {
        $this->output->writeln(date('Y-m-d H:i:s') . "\t " . $messages);
    }

}