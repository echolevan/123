<?php

namespace app\ms_admin\controller;

use app\ms_admin\model\BusinessKeyongModel;
use app\ms_admin\model\BusinessModel;
use app\ms_admin\model\BusinessOrderModel;
use app\ms_admin\model\BusinessTypeModel;
use think\Controller;
use think\Db;
use think\Request;

class Business extends Base
{
    /**
     * 显示资源列表
     * @return \think\Response
     */
    public function index()
    {
        if (request()->isAjax()) {
            $param = input('param.');
            $flag = $this->validate($param, 'BusinessValidate.index');
            if (true !== $flag) {
                return json(msg(-1, '', $flag));
            }

            $limit = $param['pageSize'];
            $offset = ($param['pageNumber'] - 1) * $limit;

            $where = [];
            if (!empty($param['searchText'])) {
                $where['business_name'] = ['like', '%' . str_fun($param['searchText']) . '%'];
            }
            if (!empty($param['member_id'])) {
                $where['memberid'] = str_fun($param['member_id']);
            }

            $business = new BusinessModel();
            try {
                $selectResult = $business->getBusinessByWhere($where, $offset, $limit);
            } catch (\Exception $e) {
                return json(msg(-1, '', $e->getMessage()));
            }

            foreach ($selectResult as $key => $vo) {
                $buttons = $this->makeBusinessButton($vo['id']);
                if ($vo['status'] != '已审') {
                    unset($buttons['冻结']);
                } else {
                    unset($buttons['审核']);
                }
                $selectResult[$key]['operate'] = showOperate($buttons);
            }

            try {
                $return['total'] = $business->getAllBusiness($where); // 总数据
            } catch (\Exception $e) {
                return json(msg(-1, '', $e->getMessage()));
            }
            $return['rows'] = $selectResult;

            return json($return);
        }

        return $this->fetch();
    }

    /**
     * 商家拼装操作按钮
     * @param $id
     * @return array
     */
    private function makeBusinessButton($id)
    {
        return [
            '审核' => [
                'auth'     => 'business/toexamine',
                'href'     => "javascript:toexamine(" . $id . ")",
                'btnStyle' => 'primary',
                'icon'     => 'fa fa-arrow-up',
            ],
            '冻结' => [
                'auth'     => 'business/frozen',
                'href'     => url('business/frozen', ['dj_business_id' => $id]),
                'btnStyle' => 'danger',
                'icon'     => 'fa fa-arrow-down',
            ]
        ];
    }

    /**
     * 商家审核
     */
    public function toExamine()
    {
        $param = input('param.');
        $flag = $this->validate($param, 'BusinessValidate.toExamine');
        if (true !== $flag) {
            return json(msg(-1, '', $flag));
        }
        $data = [
            'business_id' => $param['sh_business_id'],
            'op_id'       => session('username')
        ];
        $procedure_name = 'p_business_sh';
        $rec = db_query($procedure_name, $data);
        if (1 != $rec['code']) {
            return json($rec);
        }

        return json(msg(1, url('business/index'), '审核成功'));
    }

    /**
     * 商家冻结
     */
    public function frozen()
    {
        if (request()->isPost()) {
            $param = input('param.');
            $flag = $this->validate($param, 'BusinessValidate.frozen');
            if (true !== $flag) {
                return json(msg(-1, '', $flag));
            }
            Db::startTrans();
            try {
                $business = Db::name('business')->field('id,memberid')->where('id', 'eq', $param['dj_business_id'])->where('status', 'eq', '已审')->find();
                $business_rec = Db::name('business')->where('id', 'eq', $param['dj_business_id'])->where('status', 'eq', '已审')->update(['dj_date' => date('Y-m-d H:i:s'), 'dj_op_id' => session('username'), 'dj_memo' => $param['dj_memo'], 'status' => '冻结']);
                $member_rec = Db::name('user')->where('memberid', 'eq', $business['memberid'])->where('status', 'eq', '已审')->where('is_business', 'eq', '是')->update(['is_business' => '冻结']);
                if (!$business_rec || !$member_rec) {
                    Db::rollback();
                    return json(msg(-1, '', '商家冻结失败'));
                }
            } catch (\Exception $e) {
                Db::rollback();
                return json(msg(-1, '', $e->getMessage()));
            }
            Db::commit();

            return json(msg(1, url('business/index'), '商家冻结成功'));
        }
        $dj_business_id = input('param.dj_business_id/d');
        $this->assign('dj_business_id', $dj_business_id);

        return $this->fetch();
    }

    /**
     * 商家类型列表
     */
    public function typeIndex()
    {
        if (request()->isAjax()) {
            $param = input('param.');
            $flag = $this->validate($param, 'BusinessValidate.index');
            if (true !== $flag) {
                return json(msg(-1, '', $flag));
            }

            $limit = $param['pageSize'];
            $offset = ($param['pageNumber'] - 1) * $limit;

            $where = [];
            if (!empty($param['searchText'])) {
                $where['name'] = ['like', '%' . str_fun($param['searchText']) . '%'];
            }

            $type = new BusinessTypeModel();
            try {
                $selectResult = $type->getBusinessTypeByWhere($where, $offset, $limit);
            } catch (\Exception $e) {
                return json(msg(-1, '', $e->getMessage()));
            }

            foreach ($selectResult as $key => $vo) {
                $buttons = $this->makeBusinessTypeButton($vo['id']);
                if ($vo['status'] == '启用') {
                    unset($buttons['启用']);
                } else {
                    unset($buttons['禁用']);
                }
                $selectResult[$key]['operate'] = showOperate($buttons);
            }

            try {
                $return['total'] = $type->getAllBusinessType($where); // 总数据
            } catch (\Exception $e) {
                return json(msg(-1, '', $e->getMessage()));
            }
            $return['rows'] = $selectResult;

            return json($return);
        }

        return $this->fetch();
    }

    /**
     * 商家拼装操作按钮
     * @param $id
     * @return array
     */
    private function makeBusinessTypeButton($id)
    {
        return [
            '编辑' => [
                'auth'     => 'business/typeedit',
                'href'     => url('business/typeedit', ['id' => $id]),
                'btnStyle' => 'primary',
                'icon'     => 'fa fa-paste'
            ],
            '启用' => [
                'auth'     => 'business/typestart',
                'href'     => "javascript:typestart(" . $id . ")",
                'btnStyle' => 'primary',
                'icon'     => 'fa fa-arrow-up',
            ],
            '禁用' => [
                'auth'     => 'business/typestop',
                'href'     => "javascript:typestop(" . $id . ")",
                'btnStyle' => 'danger',
                'icon'     => 'fa fa-arrow-down',
            ]
        ];
    }

    /**
     * 商家类型新增
     */
    public function typeAdd()
    {
        if (\request()->isPost()) {
            $param = input('param.');
            $flag = $this->validate($param, 'BusinessValidate.typeAdd');
            if (true !== $flag) {
                return json(msg(-1, '', $flag));
            }

            $data = [
                'name'   => $param['name'],
                'sort'   => $param['sort'],
                'lrdate' => date('Y-m-d H:i:s')
            ];
            $type = new BusinessTypeModel();
            $result = $type->addBusinessType($data);

            return json($result);
        }

        return $this->fetch();
    }

    /**
     * 商家类型编辑
     */
    public function typeEdit()
    {
        if (\request()->isPost()) {
            $param = input('param.');
            $flag = $this->validate($param, 'BusinessValidate.typeEdit');
            if (true !== $flag) {
                return json(msg(-1, '', $flag));
            }

            $data = [
                'id'   => $param['type_id'],
                'name' => $param['type_name'],
                'sort' => $param['sort']
            ];
            $type = new BusinessTypeModel();
            $result = $type->editBusinessType($data);

            return json($result);
        }
        $id = input('param.id/d');
        if (empty($id)) {
            $this->error('未获取到商家类型ID');
        }
        try {
            $type = Db::name('business_type')->where('id', 'eq', $id)->find();
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }
        if (empty($type)) {
            $this->error('未获取到商家类型信息');
        }
        $this->assign(['type_id' => $id, 'type' => $type]);

        return $this->fetch();
    }

    /**
     * 商家类型启用
     */
    public function typeStart()
    {
        $param = input('param.');
        $flag = $this->validate($param, 'BusinessValidate.typeStart');
        if (true !== $flag) {
            return json(msg(-1, '', $flag));
        }

        $data = [
            'id'     => $param['type_id'],
            'status' => '启用'
        ];
        $type = new BusinessTypeModel();
        $result = $type->editBusinessType($data);

        return json($result);
    }

    /**
     * 商家类型禁用
     */
    public function typeStop()
    {
        $param = input('param.');
        $flag = $this->validate($param, 'BusinessValidate.typeStop');
        if (true !== $flag) {
            return json(msg(-1, '', $flag));
        }

        $data = [
            'id'     => $param['type_id'],
            'status' => '禁用'
        ];
        $type = new BusinessTypeModel();
        $result = $type->editBusinessType($data);

        return json($result);
    }

    /**
     * 商家订单列表
     */
    public function orderIndex()
    {
        if (request()->isAjax()) {
            $param = input('param.');
            $flag = $this->validate($param, 'BusinessValidate.index');
            if (true !== $flag) {
                return json(msg(-1, '', $flag));
            }

            $limit = $param['pageSize'];
            $offset = ($param['pageNumber'] - 1) * $limit;

            $where = [];
            if (!empty($param['order_no'])) {
                $where['order_no'] = $param['order_no'];
            }
            if (!empty($param['pay_member_id'])) {
                $where['memberid'] = $param['pay_member_id'];
            }
            if (!empty($param['business_member_id'])) {
                $where['business_memberid'] = $param['business_member_id'];
            }
            if (!empty($param['order_status'])) {
                $where['status'] = $param['order_status'];
            }
            if ($param['pay_status'] >= 0) {
                $where['pay_status'] = $param['pay_status'];
            }
            $start = @$param['start'] ? str_fun(date('Y-m-d',strtotime($param['start']))) : 0;
            $end = @$param['end'] ? str_fun(date('Y-m-d',strtotime($param['end']))) . ' 23:59:59' : date('Y-m-d') . ' 23:59:59';
            $where['lrdate'] = ['between time', [$start, $end]];

            $order = new BusinessOrderModel();
            try {
                $selectResult = $order->getBusinessOrderByWhere($where, $offset, $limit);
            } catch (\Exception $e) {
                return json(msg(-1, '', $e->getMessage()));
            }

            try {
                $return['total'] = $order->getAllBusinessOrder($where); // 总数据
                //平台业绩可用数量
                $ping_tai_num = Db::name('business_pingtai_yeji')->where('id','eq',1)->value('keyong_yeji_num');
                $ping_tai_tong_ji = Db::name('business_order')->where('pay_status','eq',1)->where('status','eq','已支付')->sum('ping_tai_yeji_num');
                $return['ping_tai_num'] = $ping_tai_tong_ji;
            } catch (\Exception $e) {
                return json(msg(-1, '', $e->getMessage()));
            }
            $return['rows'] = $selectResult;

            return json($return);
        }

        return $this->fetch();
    }

    /**
     * 商家订单导出
     */
    public function orderExport()
    {
        $param = input('param.');
        $flag = $this->validate($param, 'BusinessValidate.orderExport');
        if (true !== $flag) {
            return json(msg(-1, '', $flag));
        }

        $where = [];
        if (!empty($param['order_no'])) {
            $where['order_no'] = $param['order_no'];
        }
        if (!empty($param['pay_member_id'])) {
            $where['memberid'] = $param['pay_member_id'];
        }
        if (!empty($param['business_member_id'])) {
            $where['business_memberid'] = $param['business_member_id'];
        }
        if (!empty($param['order_status'])) {
            $where['status'] = $param['order_status'];
        }
        if ($param['pay_status'] >= 0) {
            $where['pay_status'] = $param['pay_status'];
        }
        $start = @$param['start'] ? str_fun(date('Y-m-d',strtotime($param['start']))) : 0;
        $end = @$param['end'] ? str_fun(date('Y-m-d',strtotime($param['end']))) . ' 23:59:59' : date('Y-m-d') . ' 23:59:59';
        $where['lrdate'] = ['between time', [$start, $end]];

        $order = new BusinessOrderModel();
        try {
            $selectResult = $order->where($where)->order('id desc')->select();
        } catch (\Exception $e) {
            return json(msg(-1, '', $e->getMessage()));
        }

        $header = [
            "id"                    => "商家ID",
            "order_no"              => "订单号",
            "memberid"              => "付款会员编号",
            "business_memberid"     => "商家会员编号",
            "business_id"           => "商家ID",
            "business_name"         => "商家名称",
            "num"                   => "付款数量",
            "shiji_yeji_bili"       => "商家业绩比例",
            "pingtai_yeji_bili"     => "平台业绩比例",
            "sfdate_begin"          => "返证账户释放时间",
            "shouyi_yuqi_num"       => "返证释放总量",
            "shouyi_yuqi_keyong"    => "返证释放可用",
            "shouyi_yuqi_shifang"   => "返证释放数量",
            "shouyi_yuqi_daynum"    => "返证多少天后释放",
            "business_sfdate_begin" => "可用业绩释放时间",
            "business_yuqi_num"     => "可用业绩释放总量",
            "business_yuqi_keyong"  => "可用业绩释放可用",
            "business_yuqi_shifang" => "可用业绩释放数量",
            "business_yuqi_daynum"  => "可用业绩多少天后释放",
            "ping_tai_yeji_num"     => "平添业绩数量",
            "status"                => "订单状态",
            "pay_status"            => "支付状态",
            "from_type"             => "账户类型",
            "pay_time"              => "支付时间",
            "memo"                  => "订单备注",
            "lrdate"                => "录入时间",
        ];
        get_excel($header, $selectResult);
    }

    /**
     * 商家业绩日志
     */
    public function logIndex()
    {
        if (request()->isAjax()) {
            $param = input('param.');
            $flag = $this->validate($param, 'BusinessValidate.index');
            if (true !== $flag) {
                return json(msg(-1, '', $flag));
            }

            $limit = $param['pageSize'];
            $offset = ($param['pageNumber'] - 1) * $limit;

            $where = [];
            if (!empty($param['member_id'])) {
                $where['memberid'] = $param['member_id'];
            }
            if (!empty($param['account_type'])) {
                $where['account_type'] = $param['account_type'];
            }
            $start = @$param['start'] ? str_fun(date('Y-m-d',strtotime($param['start']))) : 0;
            $end = @$param['end'] ? str_fun(date('Y-m-d',strtotime($param['end']))) . ' 23:59:59' : date('Y-m-d') . ' 23:59:59';
            $where['op_time'] = ['between time', [$start, $end]];

            $order = new BusinessKeyongModel();
            try {
                $selectResult = $order->getBusinessKeyongByWhere($where, $offset, $limit);
            } catch (\Exception $e) {
                return json(msg(-1, '', $e->getMessage()));
            }

            try {
                $return['total'] = $order->getAllBusinessKeyong($where); // 总数据
            } catch (\Exception $e) {
                return json(msg(-1, '', $e->getMessage()));
            }
            $return['rows'] = $selectResult;

            return json($return);
        }

        return $this->fetch();
    }

    /**
     * 商家业绩导出
     */
    public function logExport()
    {
        $param = input('param.');
        $flag = $this->validate($param, 'BusinessValidate.logExport');
        if (true !== $flag) {
            return json(msg(-1, '', $flag));
        }

        $where = [];
        if (!empty($param['member_id'])) {
            $where['memberid'] = $param['member_id'];
        }
        if (!empty($param['account_type'])) {
            $where['account_type'] = $param['account_type'];
        }
        $start = @$param['start'] ? str_fun(date('Y-m-d',strtotime($param['start']))) : 0;
        $end = @$param['end'] ? str_fun(date('Y-m-d',strtotime($param['end']))) . ' 23:59:59' : date('Y-m-d') . ' 23:59:59';
        $where['op_time'] = ['between time', [$start, $end]];

        $order = new BusinessKeyongModel();
        try {
            $selectResult = $order->where($where)->order('id desc')->select();
        } catch (\Exception $e) {
            return json(msg(-1, '', $e->getMessage()));
        }

        $header = [
            "id"           => "明细ID",
            "memberid"     => "会员账号",
            "membername"   => "会员名称",
            "type"         => "类型",
            "yue"          => "余额(发生之前)",
            "num"          => "数量",
            "from_type"    => "来源类型",
            "orderid"      => "关联订单ID",
            "remark"       => "备注",
            "account_type" => "账户类型",
            "op_time"      => "操作时间"
        ];
        get_excel($header, $selectResult);
    }

}
