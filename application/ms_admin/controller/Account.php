<?php
/**
 * Created by PhpStorm.
 * User: tears
 * Date: 2018/12/13
 * Time: 2:48 PM
 */

namespace app\ms_admin\controller;


use app\ms_admin\model\AccountLogModel;
use app\ms_admin\model\AccountModel;
use app\ms_admin\model\YejiLogModel;
use app\util\Tools;
use think\Db;

class Account extends Base
{
    public $status = ['1' => '启用', '0' => '禁用'];
    public $fengkong_status = ['1' => '是', '0' => '否'];

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

            switch ($param['zhuce_status']){
                case '未注册':
                    $where['address']=Db::raw('address is null');
                    break;
                case '已注册':
                    $where['address']=Db::raw('address is not null');
                    break;
            }

            $model = new AccountModel();
            $selectResult = $model->getByWhere($where, $offset, $limit);

            foreach ($selectResult as $key => $vo) {
                $selectResult[$key]['operate'] = showOperate($this->makeButton($vo['memberid'], $vo['status']));
                $selectResult[$key]['status'] = $this->status[$vo['status']];
                $selectResult[$key]['fengkong_status'] = $this->fengkong_status[$vo['fengkong_status']];
            }

            $return['total'] = $model->getAllCount($where);  // 总数据
            $return['rows'] = $selectResult;

            return json($return);
        }

        return $this->fetch();
    }

    public function mx()
    {
        $param = input('param.');
        if (request()->isAjax()) {


            $limit = $param['pageSize'];
            $offset = ($param['pageNumber'] - 1) * $limit;

            $where = [];
            if (array_key_exists('memberid', $param) && !empty($param['memberid'])) {
                $where['memberid'] = ['eq', $param['memberid']];
            }
            if (array_key_exists('searchText', $param) && !empty($param['searchText'])) {
                $where['memberid'] = ['eq', $param['searchText']];
            }
            $type = 'eth';
            if (array_key_exists('type', $param) && !empty($param['type'])) {
                $type = $param['type'];
            }
            if (array_key_exists('account_type', $param) && !empty($param['account_type'])) {
                $type = $param['account_type'];
            }
            $table = 'log_eth_account';
            if ($type != 'eth') {
                $table = 'log_wuc_account';
            }
            $model = new AccountLogModel();
            $selectResult = $model->getByWhere($where, $offset, $limit, $table);
            $return['total'] = $model->getAllCount($where);  // 总数据
            $return['rows'] = $selectResult;

            return json($return);
        }

        $data = [
            'memberid' => array_key_exists('memberid', $param) ? $param['memberid'] : '',
            'type' => array_key_exists('type', $param) ? $param['type'] : ''
        ];

        return $this->assign($data)->fetch();
    }

    public function yeji()
    {
        $param = input('param.');
        if (request()->isAjax()) {


            $limit = $param['pageSize'];
            $offset = ($param['pageNumber'] - 1) * $limit;

            $where = [];
            if (array_key_exists('memberid', $param) && !empty($param['memberid'])) {
                $where['memberid'] = ['eq', $param['memberid']];
            }
            if (array_key_exists('searchText', $param) && !empty($param['searchText'])) {
                $where['memberid'] = ['eq', $param['searchText']];
            }
            $type = 'eth';
            if (array_key_exists('type', $param) && !empty($param['type'])) {
                $type = $param['type'];
            }
            if (array_key_exists('account_type', $param) && !empty($param['account_type'])) {
                $type = $param['account_type'];
            }
            $table = 'log_eth_yeji';
            if ($type != 'eth') {
                $table = 'log_wuc_yeji';
            }
            $model = new YejiLogModel();
            $selectResult = $model->getByWhere($where, $offset, $limit, $table);
            $return['total'] = $model->getAllCount($where);  // 总数据
            $return['rows'] = $selectResult;

            return json($return);
        }

        $data = [
            'memberid' => array_key_exists('memberid', $param) ? $param['memberid'] : '',
            'type' => array_key_exists('type', $param) ? $param['type'] : ''
        ];

        return $this->assign($data)->fetch();
    }

    public function bo()
    {
        $param = input('param.');
        $model = new AccountModel();

        if (request()->isPost()) {
            $param = input('post.');
            if (!array_key_exists('memberid', $param)) {
                return json(msg(-1, [], '参数错误'));
            }
            $user = $model->where('memberid', 'eq', $param['memberid'])->find();
            if (empty($user)) {
                return json(msg(-1, [], '会员账户存在'));
            }
            $param['user'] = $user;
            $flag = $model->op_Balance($param, 'AccountValidate', url('Account/index'));

            return json(msg($flag['code'], $flag['data'], $flag['msg']));
        }
        if (!array_key_exists('memberid', $param)) {
            $this->error('参数错误!');
        }
        $user = $model->where('memberid', 'eq', $param['memberid'])->find();
        if (empty($user)) {
            $this->error('会员账户存在!');
        }
        return $this->assign(['user' => $user])->fetch();
    }

    public function stop()
    {
        $id = input('param.id');

        $model = new AccountModel();
        $flag = $model->stop($id);
        return json(msg($flag['code'], $flag['data'], $flag['msg']));
    }

    public function start()
    {
        $id = input('param.id');

        $model = new AccountModel();
        $flag = $model->start($id);
        return json(msg($flag['code'], $flag['data'], $flag['msg']));
    }

    private function makeButton($id, $status)
    {
        $data = [
            '通用账户明细' => [
                'auth' => 'Account/mx',
                'href' => url('Account/mx', ['memberid' => $id, 'type' => 'eth']),
                'btnStyle' => 'primary',
                'icon' => 'fa fa-list-ul'
            ],
            '业绩明细' => [
                'auth' => 'Account/yeji',
                'href' => url('Account/yeji', ['memberid' => $id, 'type' => 'wuc']),
                'btnStyle' => 'primary',
                'icon' => 'fa fa-list-ul'
            ],
//            '区块链明细' => [
//                'auth' => 'Project/info',
//                'href' => url('Project/info', ['memberid' => $id]),
//                'btnStyle' => 'primary',
//                'icon' => 'fa fa-list-ul'
//            ],
            '拨通用余额' => [
                'auth' => 'Account/bo',
                'href' => url('Account/bo', ['memberid' => $id]),
                'btnStyle' => 'primary',
                'icon' => 'fa fa-list-ul'
            ],
        ];
        if ($status == 1) {
            $data['禁用'] = [
                'auth' => 'Account/stop',
                'href' => "javascript:stop(" . $id . ")",
                'btnStyle' => 'danger',
                'icon' => 'fa fa-minus-square'
            ];
        } else {
            $data['启用'] = [
                'auth' => 'Account/start',
                'href' => "javascript:start(" . $id . ")",
                'btnStyle' => 'success',
                'icon' => 'fa fa-play'
            ];
        }
        return $data;
    }
}