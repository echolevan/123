<?php
/**
 * Created by PhpStorm.
 * User: tears
 * Date: 2018/12/15
 * Time: 4:54 PM
 */

namespace app\ms_admin\controller;


use app\ms_admin\model\QuancunModel;

class Quancun extends Base
{
    public $status = [1 => '已存入',  0 => '未存入'];

    public function index()
    {
        if (request()->isAjax()) {

            $param = input('param.');

            $limit = $param['pageSize'];
            $offset = ($param['pageNumber'] - 1) * $limit;

            $where = [];
            if (!empty($param['searchText'])) {
                $where['memberid'] = ['like', '%' . $param['searchText'] . '%'];
            }
            if (!empty($param['txid'])) {
                $where['txid'] = ['like', '%' . $param['txid'] . '%'];
            }

            $model = new QuancunModel();
            $selectResult = $model->getByWhere($where, $offset, $limit);

            foreach ($selectResult as $key => $vo) {
//                $selectResult[$key]['operate'] = showOperate($this->makeButton($vo['id']));
//                $selectResult[$key]['type'] = $this->type[$vo['type']];
                $selectResult[$key]['status'] = $this->status[$vo['status']];
            }

            $return['total'] = $model->getAllCount($where);  // 总数据
            $return['rows'] = $selectResult;

            return json($return);
        }

        return $this->fetch();
    }

}