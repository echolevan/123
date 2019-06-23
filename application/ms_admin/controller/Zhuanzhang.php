<?php
/**
 * Created by PhpStorm.
 * User: tears
 * Date: 2018/12/15
 * Time: 5:21 PM
 */

namespace app\ms_admin\controller;


use app\ms_admin\model\ZhuanzhangModel;

class Zhuanzhang extends Base
{
    public function index()
    {
        if (request()->isAjax()) {

            $param = input('param.');

            $limit = $param['pageSize'];
            $offset = ($param['pageNumber'] - 1) * $limit;

            $where = [];
            if (!empty($param['searchText'])) {
                $where['from_memberid'] = ['like', '%' . $param['searchText'] . '%'];
            }
            if (!empty($param['to_memberid'])) {
                $where['to_memberid'] = ['like', '%' . $param['to_memberid'] . '%'];
            }

            $model = new ZhuanzhangModel();
            $selectResult = $model->getByWhere($where, $offset, $limit);

            foreach ($selectResult as $key => $vo) {
//                $selectResult[$key]['operate'] = showOperate($this->makeButton($vo['memberid'], $vo['status']));
//                $selectResult[$key]['status'] = $this->status[$vo['status']];
            }

            $return['total'] = $model->getAllCount($where);  // 总数据
            $return['rows'] = $selectResult;

            return json($return);
        }

        return $this->fetch();
    }
}