<?php
/**
 * Created by PhpStorm.
 * User: tears
 * Date: 2018/12/12
 * Time: 9:08 AM
 */

namespace app\ms_admin\controller;


use app\ms_admin\model\FenhongModel;
use app\ms_admin\model\ProjectModel;
use app\ms_admin\model\ProjectUserModel;
use app\util\Tools;

class Project extends Base
{
    public $status = ['1' => '启用', '-1' => '停止释放', '-2' => '删除', '0' => '禁用'];
//    public $sys_status = ['1' => '定时', '2' => '区块', '3' => '人工', '0' => '无'];
    public $tz_status = ['1' => '正常', '0' => '关闭', '-1' => '取出', '-2' => '系统关闭'];
    public $tiqianzhiqu_status = ['1' => '允许', '0' => '不允许'];
    public $jisuanyeji_status = ['1' => '已计算', '0' => '未计算'];

//    public $zhongjiang_status = ['1' => '中奖', '0' => '未中奖'];

    public function index()
    {
        if (request()->isAjax()) {

            $param = input('param.');

            $limit = $param['pageSize'];
            $offset = ($param['pageNumber'] - 1) * $limit;

            $where['status'] = ['neq', -2];
            $model = new ProjectModel();
            $selectResult = $model->getByWhere($where, $offset, $limit);
            foreach ($selectResult as $key => $vo) {
                $selectResult[$key]['operate'] = showOperate($this->makeButton($vo['id'], $vo['status']));
                $selectResult[$key]['status'] = $this->status[$vo['status']];
                $selectResult[$key]['tiqianzhiqu'] = $this->tiqianzhiqu_status[$vo['tiqianzhiqu']];
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
            $where = [];
            if (!empty($id)) {
                $where['pro_id'] = ['eq', $id];
            }
            $param = input('param.');

            $limit = $param['pageSize'];
            $offset = ($param['pageNumber'] - 1) * $limit;
            if (array_key_exists('searchText', $param) && !empty($param['searchText'])) {
                $where['memberid'] = ['eq', $param['searchText']];
            }

            $model = new ProjectUserModel();
//            Tools::dbDebug();
            $selectResult = $model->getByWhere($where, $offset, $limit);
            foreach ($selectResult as $key => $vo) {
                $selectResult[$key]['operate'] = showOperate($this->makeButton1($vo['id']));
                $selectResult[$key]['tz_status'] = $this->tz_status[$vo['tz_status']];
                $selectResult[$key]['jisuanyeji_status'] = $this->jisuanyeji_status[$vo['jisuanyeji_status']];
//                $selectResult[$key]['zhongjiang_status'] = $this->send_status[$vo['zhongjiang_status']];
            }
            $return['total'] = $model->getAllcount($where);  // 总数据
            $return['rows'] = $selectResult;

            return json($return);
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
            $model = new ProjectModel();
            $flag = $model->addInfo($param, 'ProjectValidate', url('Project/index'), '添加成功');

            return json(msg($flag['code'], $flag['data'], $flag['msg']));
        }

        return $this->fetch();
    }

    public function del()
    {
        $id = input('param.id');

        $model = new ProjectModel();
        $flag = $model->del($id);
        return json(msg($flag['code'], $flag['data'], $flag['msg']));
    }

    public function stop()
    {
        $id = input('param.id');

        $model = new ProjectModel();
        $flag = $model->stop($id);
        return json(msg($flag['code'], $flag['data'], $flag['msg']));
    }

    public function start()
    {
        $id = input('param.id');

        $model = new ProjectModel();
        $flag = $model->start($id);
        return json(msg($flag['code'], $flag['data'], $flag['msg']));
    }

    public function close()
    {
        $id = input('param.id');

        $model = new ProjectModel();
        $flag = $model->close($id);
        return json(msg($flag['code'], $flag['data'], $flag['msg']));
    }

    public function edit()
    {
        $model = new ProjectModel();
        if (request()->isPost()) {

            $param = input('post.');
            unset($param['account_type']);
            $flag = $model->edit($param, 'ProjectValidate.edit', url('Project/index'),
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
                'auth' => 'Project/edit',
                'href' => url('Project/edit', ['id' => $id]),
                'btnStyle' => 'primary',
                'icon' => 'fa fa-paste'
            ],
            '投资明细' => [
                'auth' => 'Project/info',
                'href' => url('Project/info', ['id' => $id]),
                'btnStyle' => 'primary',
                'icon' => 'fa fa-list-ul'
            ],
            '删除' => [
                'auth' => 'Project/del',
                'href' => "javascript:del(" . $id . ")",
                'btnStyle' => 'danger',
                'icon' => 'fa fa-trash-o'
            ],
            '停止释放' => [
                'auth' => 'Project/stop',
                'href' => "javascript:stop(" . $id . ")",
                'btnStyle' => 'danger',
                'icon' => 'fa fa-minus-square'
            ]
        ];
        if ($status == -1) {
            unset($data['停止释放']);
        }
        if ($status == 1) {
            $data['禁用'] = [
                'auth' => 'Project/close',
                'href' => "javascript:close(" . $id . ")",
                'btnStyle' => 'danger',
                'icon' => 'fa fa-stop'
            ];
        }
        if ($status < 1) {
            $data['启用'] = [
                'auth' => 'Project/start',
                'href' => "javascript:start(" . $id . ")",
                'btnStyle' => 'success',
                'icon' => 'fa fa-play'
            ];
        }
        return $data;
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
                $where['user_pro_id'] = ['eq', $id];
            }
            if (array_key_exists('searchText', $param) && !empty($param['searchText'])) {
                $where['memberid'] = ['eq', $param['searchText']];
            }
            $model = new FenhongModel();
//            Tools::dbDebug();
            $selectResult = $model->getByWhere($where, $offset, $limit);
            foreach ($selectResult as $key => $vo) {
                $selectResult[$key]['tz_status'] = $this->tz_status[$vo['tz_status']];
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

    private function makeButton1($id)
    {
        $data = [
            '分红详情' => [
                'auth' => 'Project/fenhong',
                'href' => url('Project/fenhong', ['id' => $id]),
                'btnStyle' => 'primary',
                'icon' => 'fa fa-paste'
            ],

        ];
        return $data;
    }
}