<?php
/**
 * Created by PhpStorm.
 * User: tears
 * Date: 2018/12/15
 * Time: 3:08 PM
 */

namespace app\ms_admin\controller;


use app\ms_admin\model\HongbaoDataModel;
use app\ms_admin\model\HongbaoModel;

class Hongbao extends Base
{
    public $type = [1 => '个人红包', 2 => '群拼手气红包', 3 => '群普通红包'];
    public $status = [1 => '领取完毕', 2 => '未领取退回', 0 => '领取中'];
    public $lq_status = [1 => '已领取', 0 => '未领取'];

    public function index()
    {
        if (request()->isAjax()) {

            $param = input('param.');

            $limit = $param['pageSize'];
            $offset = ($param['pageNumber'] - 1) * $limit;

            $where = [];
            if (!empty($param['searchText'])) {
                $where['b.memberid'] = ['like', '%' . $param['searchText'] . '%'];
            }

            $model = new HongbaoModel();
            $selectResult = $model->getByWhere($where, $offset, $limit);

            foreach ($selectResult as $key => $vo) {
                $selectResult[$key]['operate'] = showOperate($this->makeButton($vo['id']));
                $selectResult[$key]['type'] = $this->type[$vo['type']];
                $selectResult[$key]['status'] = $this->status[$vo['status']];
            }

            $return['total'] = $model->getAllCount($where);  // 总数据
            $return['rows'] = $selectResult;

            return json($return);
        }

        return $this->fetch();
    }

    public function mx()
    {
        $id = input('id');

        if (request()->isAjax()) {

            $param = input('param.');
            if (empty($id)) {
                return json(msg(-1, [], '参数错误'));
            }
            $limit = $param['pageSize'];
            $offset = ($param['pageNumber'] - 1) * $limit;

            $where = [];
            $where['a.biz_id'] = $id;

            $model = new HongbaoDataModel();
            $selectResult = $model->getByWhere($where, $offset, $limit);

            foreach ($selectResult as $key => $vo) {
                $selectResult[$key]['status'] = $this->lq_status[$vo['status']];
            }

            $return['total'] = $model->getAllCount($where);  // 总数据
            $return['rows'] = $selectResult;

            return json($return);
        }
        if (empty($id)) {
            $this->error('参数错误');
        }
        return $this->assign('id',$id)->fetch();
    }

    private function makeButton($id)
    {
        $data = [
            '查看详情' => [
                'auth' => 'Hongbao/mx',
                'href' => url('Hongbao/mx', ['id' => $id]),
                'btnStyle' => 'primary',
                'icon' => 'fa fa-list-ul'
            ],
        ];
        return $data;
    }

}