<?php
/**
 * Created by PhpStorm.
 * User: tears
 * Date: 2019/2/19
 * Time: 2:09 PM
 */

namespace app\api\controller;

function msg($code, $data, $msg)
{
    return compact('code', 'data', 'msg');
}

use think\Cache;
use think\Db;
use think\db\exception\DataNotFoundException;
use think\db\exception\ModelNotFoundException;
use think\exception\DbException;

class Biz extends Common
{

    private $member;

    protected function _initialize()
    {
        parent::_initialize();
        $this->member = $this->app_member;
    }

    /**
     * 通证存储初始化
     *
     * @return \think\response\Json
     */
    public function storageInit()
    {
        // 累计存储总量 touzimoney_sum
        // 锁仓数量 paidan_shouyi_dongjie
        // 释放数量 paidan_shouyi_keyong
        // 预期收益 suanli_num_keyong
        // 挖矿系数  f_lian_wakuangxishu
        // 收益数量 suanli_shouyi_keyong
        // 结算预期收益 (IFNULL(suanli_num_keyong,0)-IFNULL(paidan_shouyi_xiaofei,0)) as suanli_num_jiesuan
        $res = Db::query('select f_lian_wakuangxishu(?) as r', [$this->app_member['memberid']]);
        $data = [
            'leijicunchu' => $this->app_member['touzimoney_sum'],
            'suocangshuliang' => $this->app_member['paidan_shouyi_dongjie'],
            'shifangshuliang' => $this->app_member['paidan_shouyi_keyong'],
            'yuqishouyi' => $this->app_member['suanli_num_keyong'],
            'shouyishuliang' => $this->app_member['suanli_shouyi_keyong'],
            'jianglixishu' => $res[0]['r'],
            'MSTHtiqu' => $this->app_member['lian_num_keyong'],
            'MSTHyucun' => $this->app_member['into_lian_keyong'],
            'jiesuanyuqishouyi' => $this->app_member['suanli_num_keyong']-$this->app_member['paidan_shouyi_xiaofei'],
            'zuixiaojine' => $this->sys['1001'],
            'zuixiaobeishu' => $this->sys['1002'],
        ];

        return json(arr_msg(1, $data, 'success'));
    }


    /**
     * 通证存储验证
     * @return \think\response\Json
     */
    public function storageCheck()
    {
        $param = input("param.");
        $flag = $this->validate($param, "IndexValidate.storageCheck");
        if (true !== $flag) {
            return json(arr_msg(-1, '', $flag));
        }

        if ($this->app_member['into_lian_keyong'] < $param['storage_num'] && $param['storage_type'] == 'MSTH预存') {
            return json(arr_msg(-2, '', 'MSTH预存余额不足'));
        }
        if ($this->app_member['lian_num_keyong'] < $param['storage_num'] && $param['storage_type'] == 'MSTH提取') {
            return json(arr_msg(-3, '', 'MSTH提取余额不足'));
        }

        $data = [
            'storage_type' => $param['storage_type'],
            'memberid' => $this->app_member['memberid'],
            'storage_num' => $param['storage_num'],
            'check' => '是',
        ];
        $procedure_name = "p_web_touzi_add";
        $flag = db_query($procedure_name, $data);
        if (1 != $flag['code'] && ($flag['msg'] != '验证成功' && $flag['msg'] != 'Successful verification')) {
            return json($flag);
        }

        $rand = time() . rand(1111, 9999);
        cache($this->app_member['memberid'] . '_submit_check', $rand, 600);
        return json(arr_msg(1, ['rand' => $rand], 'success'));
    }


    /**
     * 通证存储
     *
     * @return \think\response\Json
     */
    public function storage()
    {
        $param = input("param.");
        $flag = $this->validate($param, "IndexValidate.storage");
        if (true !== $flag) {
            return json(msg(-1, '', $flag));
        }

        if ($param['rand'] != Cache::pull($this->member['memberid'] . '_submit_check')) {
            return json(msg(-2, '', '校验信息不相符，请重新操作'));
        }
        if ($this->app_member['into_lian_keyong'] < $param['storage_num'] && $param['storage_type'] == 'MSTH预存') {
            return json(arr_msg(-2, '', 'MSTH预存余额不足'));
        }
        if ($this->app_member['lian_num_keyong'] < $param['storage_num'] && $param['storage_type'] == 'MSTH提取') {
            return json(arr_msg(-3, '', 'MSTH提取余额不足'));
        }

        $data = [
            'storage_type' => $param['storage_type'],
            'memberid' => $this->member['memberid'],
            'storage_num' => $param['storage_num'],
            'check' => '李粪蛋',
        ];
        $procedure_name = "p_web_touzi_add";
        $flag = db_query($procedure_name, $data);
        if (1 !== $flag['code']) {
            return json(msg(-4, '', $flag['msg']));
        }

        return json(msg(1, '', '存储成功'));
    }


    /**
     * 存储明细
     *
     * @return \think\response\Json
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function storageList()
    {
        $param = input("param.");
        $flag = $this->validate($param, "IndexValidate.storageList");
        if (true !== $flag) {
            return json(msg(-1, '', $flag));
        }

        $limit = $param['page_size'];
        $offset = ($param['page'] - 1) * $limit;
        $count = Db::name("yw_touzi")->where(['memberid' => $this->member['memberid'], 'money' => ['>', 0]])
            ->count('1');
        $list = Db::name("yw_touzi")->where(['memberid' => $this->member['memberid'], 'money' => ['>', 0]])
            ->field("id,laiyuan,money,lrdate")->limit($offset, $limit)->order("id desc")->select()
            ->each(function ($model) {
                $model['lrdate'] = date("Y-m-d H:i", strtotime($model['lrdate']));
//                $model['laiyuan'] = $model['laiyuan'] == '预存通证' ? get_eng_or_ch('预存存储') : get_eng_or_ch('补贴存储');

                return $model;
            });

        return json(msg(1, ['list' => $list, 'pages' => ceil($count / $limit)], 'success'));
    }


    /**
     * 通证归集初始化
     *
     * @return \think\response\Json
     */
    public function notionalPoolingInit()
    {
        $data = [
            'jianglizhanghu' => $this->member['cunchu_jiangli_account'],
            'keyongyeji' => $this->member['business_yuqi_shouyi_keyong'],
            'fanzhengzhanghu' => $this->member['shop_yuqi_shouyi_keyong'],
            'shouyizhanghu' => $this->member['suanli_shouyi_keyong'],
            'shifangzhanghu' => $this->member['paidan_shouyi_keyong'],
            'jianglizhanghu_shouxufeibili' => $this->sys['1029'],
            'keyongyeji_shouxufeibili' => $this->sys['1030'],
            'fanzhengzhanghu_shouxufeibili' => $this->sys['1031'],
            'shifangzhanghu_shouxufeibili' => $this->sys['1026'],
            'shouyizhanghu_shouxufeibili' => $this->sys['1028'],
            'zuixiaojine' => $this->sys['1024'],
            'zuixiaobeishu' => $this->sys['1025'],
        ];

        return json(msg(1, $data, 'success'));
    }


    /**
     * 归集
     *
     * @return \think\response\Json
     */
    public function notionalPooling()
    {
        $param = input("param.");
        $flag = $this->validate($param, "IndexValidate.notionalPooling");
        if (true !== $flag) {
            return json(msg(-1, '', $flag));
        }

        $data = [
            'type' => $param['notional_pooling_type'],
            'memberid' => $this->member['memberid'],
            'num' => $param['num'],
        ];
        $procedure_name = "p_web_shouyi_guiji";
        $flag = db_query($procedure_name, $data);
        if (1 != $flag['code']) {
            return json($flag);
        }

        return json(msg(1, '', '节点确认成功'));
    }

    /**
     * 归集明细
     *
     * @return \think\response\Json
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function notionalPoolingList()
    {
        $param = input("param.");
        $flag = $this->validate($param, "IndexValidate.notionalPoolingList");
        if (true !== $flag) {
            return json(msg(-1, '', $flag));
        }

        $limit = $param['page_size'];
        $offset = ($param['page'] - 1) * $limit;
        $count = Db::name("yw_zichanguiji")->where(['memberid' => $this->member['memberid'], 'num' => ['>', 0]])
            ->count('1');
        $list = Db::name("yw_zichanguiji")->where(['memberid' => $this->member['memberid'], 'num' => ['>', 0]])
            ->field("id,lrdate,account_zc,num")->order("id desc")->limit($offset, $limit)->select()
            ->each(function ($model) {
                $model['lrdate'] = date("Y-m-d H:i", strtotime($model['lrdate']));
                $model['account_zc'] = get_eng_or_ch($model['account_zc']);

                return $model;
            });

        return json(msg(1, ['list' => $list, 'pages' => ceil($count / $limit)], 'success'));
    }


    /**
     * 节点互转初始化
     *
     * @return \think\response\Json
     */
    public function crossRotationInit()
    {
        $data = [
            'MSTHtiqu' => $this->member['lian_num_keyong'],
            'MSTHyucun' => $this->member['into_lian_keyong'],
            'zuixiaojine' => $this->sys[1023],
            'MSTHtiquzhuanMSTHyucun_shouxufeibili' => $this->sys[1019],
            'MSTHyucun_shouxufeibili' => $this->sys[1021],
            'MSTHtiqu_shouxufeibili' => $this->sys[1020],

        ];

        return json(msg(1, $data, 'success'));
    }

    /**
     * 校验账户
     *
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function checkMemberid()
    {
        $memberid = input("check_memberid");
        if (!$memberid) {
            return json(msg(-1, '', '请输入要验证的账号'));
        }
        $flag = Db::query("select f_lian_member_jiazu('{$this->member['memberid']}','{$memberid}','上下级') as r");
        if ('存在' != $flag[0]['r']) {
            return json(msg(-2, '', '该会员不是您的上下级'));
        }
        $member = Db::name("user")->where(['memberid' => $memberid])->find();

        return json(msg(1, ['membername' => $member['membername']], 'success'));
    }

    /**
     * 节点互转验证
     * @return \think\response\Json
     */
    public function crossRotationCheck()
    {
        $param = input("param.");
        $flag  = $this->validate($param, "IndexValidate.crossRotationCheck");
        if (true !== $flag) {
            return json(msg(-1, '', $flag));
        }

        $data           = [
            'type'        => $param['cross_rotation_type'],
            'memberid'    => $this->member['memberid'],
            'to_memberid' => $param['to_memberid'],
            'num'         => $param['num'],
            'check'       => '是',
        ];
        $procedure_name = "p_web_tuandui_huzhuan";
        $flag           = db_query($procedure_name, $data);
        if (1 != $flag['code']&&$flag['msg']!='验证成功'&&$flag['msg']!='Successful verification') {
            return json($flag);
        }

        $rand = time().rand(1111,9999);
        cache($this->member['memberid'].'_submit_check',$rand,600);
        return json(msg(1, ['rand'=>$rand], 'success'));
    }
    /**
     * 节点互转
     *
     * @return \think\response\Json
     */
    public function crossRotation()
    {
        $param = input("param.");
        $flag  = $this->validate($param, "IndexValidate.crossRotation");
        if (true !== $flag) {
            return json(msg(-1, '', $flag));
        }

        if ($param['rand']!=Cache::pull($this->member['memberid'].'_submit_check'))
        {
            return json(msg(-2,'','校验信息不相符，请重新操作'));
        }
        $data           = [
            'type'        => $param['cross_rotation_type'],
            'memberid'    => $this->member['memberid'],
            'to_memberid' => $param['to_memberid'],
            'num'         => $param['num'],
            'check'       => '李粪蛋',
        ];
        $procedure_name = "p_web_tuandui_huzhuan";
        $flag           = db_query($procedure_name, $data);
        if (1 != $flag['code']) {
            return json($flag);
        }

        return json(msg(1, '', '操作成功'));
    }

    /**
     * 互转明细
     *
     * @return \think\response\Json
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function crossRotationList()
    {
        $param = input("param.");
        $flag  = $this->validate($param, "IndexValidate.storageList");
        if (true !== $flag) {
            return json(msg(-1, '', $flag));
        }

        $limit  = $param['page_size'];
        $offset = ($param['page'] - 1) * $limit;
        $count  = Db::name("yw_tuandui_huzhuan")->where([
            'memberid|to_memberid' => $this->member['memberid'], 'num' => ['>', 0],
        ])
            ->count('1');
        $list   = Db::name("yw_tuandui_huzhuan")->where([
            'memberid|to_memberid' => $this->member['memberid'], 'num' => ['>', 0],
        ])
            ->order("id desc")->limit($offset, $limit)->select()->each(function ($model) {
                $model = [
                    'id'     => $model['id'],
                    'lrdate' => date("Y-m-d H:i", strtotime($model['lrdate'])),
                    'num'    => $model['memberid'] == $this->member['memberid'] ? '-'.$model['num'] : '+'.$model['num'],
                    'type'   => $model['memberid'] == $this->member['memberid'] ? get_eng_or_ch("转出") : get_eng_or_ch("转入"),
                ];

                return $model;
            });

        return json(msg(1, ['list' => $list, 'pages' => ceil($count / $limit)], 'success'));
    }

    /**
     * 互转详情
     *
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function crossRotationDetail()
    {
        $param = input("param.");
        $flag  = $this->validate($param, "IndexValidate.storageDetail");
        if (true !== $flag) {
            return json(msg(-1, '', $flag));
        }

        $info = Db::name("yw_tuandui_huzhuan")->where([
            'memberid|to_memberid' => $this->member['memberid'], 'id' => $param['record_id'],
        ])->find();
        $data = [
            'num'           => $info['num'],
            'ratio'         => number_format($info['sxfbili'] * 100, 2).'%',
            'bili'          => $info['sxfbili'],
            'sxfmoney'      => $info['num_sxf'],
            'dzmoney'       => $info['num_dz'],
            'from_memberid' => $info['memberid'],
            'to_memberid'   => $info['to_memberid'],
            'lrdate'        => date("m/d/Y H:i:s", strtotime($info['lrdate'])),
            'type'          => $info['memberid'] == $this->member['memberid'] ? "转出" : "转入",
        ];

        return json(msg(1, $data, 'success'));
    }

    /**
     * 提取通证初始化
     *
     * @return \think\response\Json
     */
    public function liftChainInit()
    {
        $data = [
            'MSTHtiqu' => $this->member['lian_num_keyong'],
            'min' => $this->sys[2001],
            'max' => $this->sys[2002],
            'day_max' => $this->sys[2003],
            'ratio' => $this->sys[2004],
            'start' => $this->sys[2005],
            'end' => $this->sys[2006]
        ];

        return json(msg(1, $data, 'success'));
    }

    /**
     * 提取通证验证
     */
    public function liftChainCheck()
    {
        $param = input("param.");
        $flag  = $this->validate($param, "IndexValidate.liftChainCheck");
        if (true !== $flag) {
            return json(msg(-1, '', $flag));
        }

        try {
            $address = Db::name("b_qianbaoaddress")->where([
                'id' => ['eq', $param['address_id']],
                'memberid' => ['eq', $this->app_member['memberid']],
                'status' => ['eq', '已验证'],
            ])->find();
        } catch (DataNotFoundException $e) {
            $address=[];
        } catch (ModelNotFoundException $e) {
            $address=[];
        } catch (DbException $e) {
            $address=[];
        }
        if ( ! $address) {
            return json(msg(-2, '', '钱包地址不合法'));
        }
        $data           = [
            'account_zc' => "MSTH提取",
            'memberid'   => $this->member['memberid'],
            'num'        => $param['num'],
            'address_id' => $param['address_id'],
            'laiyuan'    => 'MSTH提取',
            'check'      => '是',
        ];
        $procedure_name = "p_web_zichan_out";
        $flag           = db_query($procedure_name, $data);
        if (1 != $flag['code'] && $flag['msg'] != '验证成功' && $flag['msg'] != 'Successful verification') {
            return json($flag);
        }

        $rand = time().rand(1111, 9999);
        cache($this->member['memberid'].'_submit_check', $rand, 600);

        return json(msg(1, ['rand' => $rand], 'success'));
    }

    /**
     * 提取通证
     *
     * @return \think\response\Json
     */
    public function liftChain()
    {
        $param = input("param.");
        $flag  = $this->validate($param, "IndexValidate.liftChain");
        if (true !== $flag) {
            return json(msg(-1, '', $flag));
        }

        if ($param['rand'] != Cache::pull($this->member['memberid'].'_submit_check')) {
            return json(msg(-2, '', '校验信息不相符，请重新操作'));
        }
        $address = Db::name("b_qianbaoaddress")->where([
            'id'       => $param['address_id'],
            'memberid' => $this->member['memberid'], 'stauts' => '已验证',
        ]);
        if ( ! $address) {
            return json(msg(-2, '', '钱包地址不合法'));
        }
        $data           = [
            'account_zc' => "MSTH提取",
            'memberid'   => $this->member['memberid'],
            'num'        => $param['num'],
            'address_id' => $param['address_id'],
            'laiyuan'    => 'MSTH提取',
            'check'      => '李粪蛋',
        ];
        $procedure_name = "p_web_zichan_out";
        $flag           = db_query($procedure_name, $data);
        if (1 != $flag['code']) {
            return json($flag);
        }

        return json(msg(1, '', '提取通证成功'));
    }


    /**
     * MSTH提取明细
     */
    public function MSTHtiqu()
    {
        $param = input("param.");
        $flag  = $this->validate($param, "IndexValidate.storageList");
        if (true !== $flag) {
            return json(msg(-1, '', $flag));
        }

        $limit  = $param['page_size'];
        $offset = ($param['page'] - 1) * $limit;
        $count  = Db::name("log_lian")->where(['memberid' => $this->member['memberid'], 'num' => ['>', 0]])
            ->count('1');
        $list   = Db::name("log_lian")->where(['memberid' => $this->member['memberid'], 'num' => ['>', 0]])
            ->order("id desc")->limit($offset, $limit)->select()->each(function ($model) {
                $model = [
                    'id'     => $model['id'],
                    'lrdate' => date("Y-m-d H:i", strtotime($model['op_time'])),
                    'num'    => $model['type'] == '支出' ? '-'.$model['num'] : '+'.$model['num'],
                    'type'   => get_eng_or_ch($model['type']),
                ];

                return $model;
            });

        return json(msg(1, ['list' => $list, 'pages' => ceil($count / $limit),'MSTHtiqu'=>$this->app_member['lian_num_keyong']], 'success'));
    }

    /**
     * MSTH提取详情
     */
    public function MSTHtiquDetail()
    {
        $param = input("param.");
        $flag  = $this->validate($param, "IndexValidate.storageDetail");
        if (true !== $flag) {
            return json(msg(-1, '', $flag));
        }

        $info = Db::name("log_lian")->where([
            'memberid' => $this->member['memberid'], 'id' => $param['record_id'],
        ])->find();
        $data = [
            'num'       => $info['num'],
            'type'      => $info['type'],
            'yue'       => $info['yue'],
            'from_type' => $info['from_type'],
            'op_time'   => $info['op_time'],
            'remark'    => $info['remark'],
        ];

        return json(msg(1, $data, 'success'));
    }
    /**
     * MSTH预存明细
     */
    public function MSTHyucun()
    {
        $param = input("param.");
        $flag  = $this->validate($param, "IndexValidate.storageList");
        if (true !== $flag) {
            return json(msg(-1, '', $flag));
        }

        $limit  = $param['page_size'];
        $offset = ($param['page'] - 1) * $limit;
        $count  = Db::name("log_into_lian")->where(['memberid' => $this->member['memberid'], 'num' => ['>', 0]])
            ->count('1');
        $list   = Db::name("log_into_lian")->where(['memberid' => $this->member['memberid'], 'num' => ['>', 0]])
            ->order("id desc")->limit($offset, $limit)->select()->each(function ($model) {
                $model = [
                    'id'     => $model['id'],
                    'lrdate' => date("Y-m-d H:i", strtotime($model['op_time'])),
                    'num'    => $model['type'] == '支出' ? '-'.$model['num'] : '+'.$model['num'],
                    'type'   => get_eng_or_ch($model['type']),
                ];

                return $model;
            });

        return json(msg(1, ['list' => $list, 'pages' => ceil($count / $limit),'MSTHyucun'=>$this->app_member['into_lian_keyong']], 'success'));
    }

    /**
     * MSTH预存详情
     */
    public function MSTHyucunDetail()
    {
        $param = input("param.");
        $flag  = $this->validate($param, "IndexValidate.storageDetail");
        if (true !== $flag) {
            return json(msg(-1, '', $flag));
        }

        $info = Db::name("log_into_lian")->where([
            'memberid' => $this->member['memberid'], 'id' => $param['record_id'],
        ])->find();
        $data = [
            'num'       => $info['num'],
            'type'      => $info['type'],
            'yue'       => $info['yue'],
            'from_type' => $info['from_type'],
            'op_time'   => $info['op_time'],
            'remark'    => $info['remark'],
        ];

        return json(msg(1, $data, 'success'));
    }

}