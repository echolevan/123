<?php
/**
 * Created by PhpStorm.
 * User: tears
 * Date: 2018/12/7
 * Time: 10:23 AM
 */

namespace app\command;

use think\console\Command;
use think\console\Input;
use think\console\Output;
use think\Db;
use think\Exception;
use think\Log;

class HongbaoReturn extends Command
{
    protected $RunStatus = 1;

    protected function handler_quit($sig)
    {
        $this->RunStatus = 0;
        $this->writeln("收到信号:" . $sig);
    }

    protected function configure()
    {
        $this->setName('HongbaoReturnService')->setDescription('红包退回服务');
    }

    public function writeln($messages)
    {
        $this->output->writeln(date('Y-m-d H:i:s') . "\t " . $messages);
    }

    public function execute(Input $input, Output $output)
    {
        $this->writeln("开启红包退回服务");
        pcntl_signal(SIGINT, function ($sig) {
            $this->handler_quit($sig);
        });
        pcntl_signal(SIGTERM, function ($sig) {
            $this->handler_quit($sig);
        });
        pcntl_signal(SIGQUIT, function ($sig) {
            $this->handler_quit($sig);
        });
//        $cached = Cache::init();
//        $cache_handler = $cached->handler();

        for ($i = 0; $i < 8640 && $this->RunStatus == 1; $i++) {
            pcntl_signal_dispatch();
//            try {
//                $yunxin = new YunXin();
//            } catch (\ErrorException $e) {
//                $this->writeln("fail:开启云信通信失败");
//                break;
//            }
            try {
                $hb = Db::name('hongbao')->field('*,money-deal_money as amount')
                    ->where('money', 'gt', 'deal_money')
                    ->where('status', 'eq', 0)->where('num', 'gt', 'deal_num')
                    ->where('hour(TIMEDIFF(now(),op_time))>=24')->order('op_time asc')->find();
            } catch (Exception $e) {
                $this->writeln("Exception" . $e->getMessage());
                break;
            }
            if (empty($hb)) {
                sleep(1);
                continue;
            }

            try {
                $sendUser = Db::name('user')->where('id', 'eq',
                    $hb['userid'])->find();
            } catch (Exception $e) {
                $sendUser = [];
            }
            if (empty($sendUser)) {
                Log::write('红包退回:' . $hb['id'] . "发放用户不存在!:" . $hb['userid'], 'error');
                continue;
            }
            Db::startTrans();
            $sql = "INSERT INTO `t_log_lian`  (`memberid`, `membername`,op_time, `type`, `yue`, `num`, `from_type`, `remark`, `op_id`,orderid,account_type) 
					 select memberid,membername,now(),'收入',IFNULL(lian_num_keyong,0),{$hb['amount']}, '红包', '红包退回', '','{$hb['id']}','可用账户' from t_user  where memberid='{$sendUser['memberid']}'; ";
            $r3 = Db::execute($sql);
            $sql2 = "update t_user set lian_num_keyong=IFNULL(lian_num_keyong,0)+{$hb['amount']}, 
								lian_num_total=IFNULL(lian_num_total,0)+ {$hb['amount']} where memberid='{$sendUser['memberid']}';";
            $r2 = Db::execute($sql2);
//            $balance = $sendUser[$hb['account_type'] . "_account"];
//            $res_op = Db::query("select app_f_op_{$hb['account_type']}_account('{$sendUser['memberid']}','{$sendUser['membername']}',{$balance},{$hb['amount']},'{$hb['id']}','红包','收入','红包退回') as res");
//            $r2 = $res_op[0]['res'];

            try {
                $r1 = Db::name('hongbao')->where('id', 'eq', $hb['id'])->update(['status' => 2]);
            } catch (Exception $e) {
                $r1 = false;
            }
            if ($r1 && $r2 && $r3) {
                $info = cache($hb['redis_key']);
                if ($info) {
                    $info['status'] = 102;
                    $rr = cache($hb['redis_key'], $info);
                    if ($rr) {
                        Db::commit();
                        //  发送红包通知
                        $msg_res = false;
//                        $msg_res = $yunxin->sendAttachMsg($info['accid'], '您有红包超过24小时为被领取退回', 0);
                        Log::write('红包退回:' . $hb['id'] . "成功!云信通知:" . json_encode($msg_res), 'info');
                    }else{
                        Db::rollback();
                        Log::write('红包退回:' . $hb['id'] . "redis error: " . $hb['redis_key'] . 'save fail', 'error');
                    }
                } else {
                    Db::rollback();
                    Log::write('红包退回:' . $hb['id'] . "redis error:" . $hb['redis_key'] . 'not exist', 'error');
                }
            } else {
                Db::rollback();
                Log::write('红包退回:' . $hb['id'] . "数据处理失败!:" . json_encode([$r1, $r2]), 'error');
            }
            unset($info);
            unset($balance);
            unset($sendUser);
            unset($hb);

        }
        $this->writeln("结束红包退回服务");

    }

}