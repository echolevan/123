<?php
/**
 * Created by PhpStorm.
 * User: tears
 * Date: 2018/12/12
 * Time: 9:08 AM
 */

namespace app\ms_admin\controller;


use app\ms_admin\model\LuckDrawModel;
use app\ms_admin\model\LuckDrawUserFenhongModel;
use app\ms_admin\model\LuckDrawUserModel;

class Luckdraw extends Base
{
    public $status = ['1' => '开启投注', '2' => '停止投注', '3' => '已开奖', '4' => '已发奖', '0' => '未开启投注'];
    public $sys_status = ['1' => '定时', '2' => '区块', '3' => '人工', '0' => '无'];
    public $user_touzhu_status = ['1' => '已计算', '2' => '计算失败', '0' => '未计算'];
    public $send_status = ['1' => '已发奖', '0' => '未发奖'];
    public $zhongjiang_status = ['1' => '中奖', '0' => '未中奖'];

    public function index()
    {
        if (request()->isAjax()) {

            $param = input('param.');

            $limit = $param['pageSize'];
            $offset = ($param['pageNumber'] - 1) * $limit;

            $where = [];
            if (!empty($param['searchText'])) {
                $where['title'] = ['like', '%' . $param['searchText'] . '%'];
            }
            $where['status'] = ['neq', -1];
            $model = new LuckDrawModel();
            $selectResult = $model->getByWhere($where, $offset, $limit);
            foreach ($selectResult as $key => $vo) {
                $selectResult[$key]['operate'] = showOperate($this->makeButton($vo['id'], $vo['status']));
                $selectResult[$key]['status'] = $this->status[$vo['status']];
                $selectResult[$key]['start_type'] = $this->sys_status[$vo['start_type']];
                $selectResult[$key]['end_type'] = $this->sys_status[$vo['end_type']];
            }
            $return['total'] = $model->getAllcount($where);  // 总数据
            $return['rows'] = $selectResult;

            return json($return);
        }

        return $this->fetch();
    }

    public function info()
    {
        $id = input('param.id');

        if (request()->isAjax()) {
            $param = input('param.');

            $limit = $param['pageSize'];
            $offset = ($param['pageNumber'] - 1) * $limit;

            $where = [];
            if (!empty($id)) {
                $where['luck_draw_id'] = ['eq', $id];
            }
            if (array_key_exists('searchText', $param) && !empty($param['searchText'])) {
                $where['memberid'] = ['eq', $param['searchText']];
            }
            $model = new LuckDrawUserModel();
//            Tools::dbDebug();
            $selectResult = $model->getByWhere($where, $offset, $limit);
            foreach ($selectResult as $key => $vo) {
                $selectResult[$key]['operate'] = showOperate($this->makeButton1($vo['id']));
                $selectResult[$key]['jisuan_status'] = $this->user_touzhu_status[$vo['jisuan_status']];
                $selectResult[$key]['send_status'] = $this->send_status[$vo['send_status']];
                $selectResult[$key]['zhongjiang_status'] = $this->send_status[$vo['zhongjiang_status']];
            }
            $return['total'] = $model->getAllcount($where);  // 总数据
            $return['rows'] = $selectResult;

            return json($return);
        }
        if (empty($id)) {
            $$id = '';
        }
        $this->assign(['id' => $id]);
        return $this->fetch();
    }

    public function fenhong()
    {
        $id = input('param.id');

        if (request()->isAjax()) {
            $param = input('param.');

            $limit = $param['pageSize'];
            $offset = ($param['pageNumber'] - 1) * $limit;

            $where = [];
            if (!empty($id)) {
                $where['user_luck_draw_id'] = ['eq', $id];
            }
            if (array_key_exists('searchText', $param) && !empty($param['searchText'])) {
                $where['memberid'] = ['eq', $param['searchText']];
            }
            $model = new LuckDrawUserFenhongModel();
//            Tools::dbDebug();
            $selectResult = $model->getByWhere($where, $offset, $limit);
            foreach ($selectResult as $key => $vo) {
                $selectResult[$key]['jiesuan_status'] = $this->user_touzhu_status[$vo['jiesuan_status']];
                $selectResult[$key]['fenhong_money'] = bcmul($vo['tz_money'], $vo['fenhong'],4);
//                dump(bcmul($vo['tz_money'], $vo['fenhong'],4));
//                $selectResult[$key]['send_status'] = $this->send_status[$vo['send_status']];
//                $selectResult[$key]['zhongjiang_status'] = $this->send_status[$vo['zhongjiang_status']];
            }
            $return['total'] = $model->getAllcount($where);  // 总数据
            $return['rows'] = $selectResult;

            return json($return);
        }
        if (empty($id)) {
            $$id = '';
        }
        $this->assign(['id' => $id]);
        return $this->fetch();
    }

    public function add()
    {
        if (request()->isPost()) {
            $param = input('post.');

//            unset($param['file']);
//            $param['add_time'] = date('Y-m-d H:i:s');
//            $param['surplus_amount'] = $param['total_amount'];
            $model = new LuckDrawModel();
            $flag = $model->addInfo($param, 'LuckDrawValidate', url('Luckdraw/index'), '添加成功');

            return json(msg($flag['code'], $flag['data'], $flag['msg']));
        }

        return $this->fetch();
    }

    public function del()
    {
        $id = input('param.id');

        $model = new LuckDrawModel();
        $flag = $model->del($id);
        return json(msg($flag['code'], $flag['data'], $flag['msg']));
    }

    public function stop()
    {
        $id = input('param.id');

        $model = new LuckDrawModel();
        $flag = $model->stop($id);
        return json(msg($flag['code'], $flag['data'], $flag['msg']));
    }

    public function edit()
    {
        $model = new LuckDrawModel();
        if (request()->isPost()) {

            $param = input('post.');
            unset($param['account_type']);
            $flag = $model->edit($param, 'LuckDrawValidate.edit', url('Luckdraw/index'),
                '编辑成功');

            return json(msg($flag['code'], $flag['data'], $flag['msg']));
        }

        $id = input('param.id');
        $this->assign([
            'v' => $model->find($id)
        ]);
        return $this->fetch();
    }


    private function makeButton($id, $status)
    {
        $data = [
            '编辑' => [
                'auth' => 'Luckdraw/edit',
                'href' => url('Luckdraw/edit', ['id' => $id]),
                'btnStyle' => 'primary',
                'icon' => 'fa fa-paste'
            ],
            '投注详情' => [
                'auth' => 'Luckdraw/info',
                'href' => url('Luckdraw/info', ['id' => $id]),
                'btnStyle' => 'primary',
                'icon' => 'fa fa-paste'
            ],
            '删除' => [
                'auth' => 'Luckdraw/del',
                'href' => "javascript:del(" . $id . ")",
                'btnStyle' => 'danger',
                'icon' => 'fa fa-trash-o'
            ]
        ];
        if ($status > 0) {
            unset($data['编辑']);
            unset($data['删除']);
        }
        if ($status == 1) {
            $data['停止投注'] = [
                'auth' => 'Luckdraw/stop',
                'href' => "javascript:stop(" . $id . ")",
                'btnStyle' => 'danger',
                'icon' => 'fa fa-stop'
            ];
        }
//        if ($status == 0) {
//            $data['开启'] = [
//                'auth' => 'news/start',
//                'href' => "javascript:articleStart(" . $id . ")",
//                'btnStyle' => 'success',
//                'icon' => 'fa fa-play'
//            ];
//        }
        return $data;
    }

    private function makeButton1($id)
    {
        $data = [
            '分红详情' => [
                'auth' => 'Luckdraw/fenhong',
                'href' => url('Luckdraw/fenhong', ['id' => $id]),
                'btnStyle' => 'primary',
                'icon' => 'fa fa-paste'
            ],

        ];
        return $data;
    }
}