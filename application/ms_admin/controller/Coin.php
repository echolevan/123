<?php
/**
 * Created by PhpStorm.
 * User: tears
 * Date: 2018/12/15
 * Time: 5:21 PM
 */

namespace app\ms_admin\controller;


use app\ms_admin\model\CoinModel;

class Coin extends Base
{
    public $dabi_status = ['1' => '成功', '2' => '失败', '3' => '手工到账', '0' => '未打币', -1 => '收币', -2 => '已退回'];
    public $sh_status = ['1' => '已审核', '0' => '未审核', '2' => '已退回'];

    public function index()
    {
        if (request()->isAjax()) {

            $param = input('param.');

            $limit = $param['pageSize'];
            $offset = ($param['pageNumber'] - 1) * $limit;

            $where = [];
            $address = '';
            if (!empty($param['account_type'])) {
                if ($param['account_type'] == 'eth') {
                    $where['token_address'] = ['eq', 'eth'];
                } else {
                    $where['token_address'] = ['neq', 'eth'];
                }
            }
            if (!empty($param['searchText'])) {
                $address = $param['searchText'];
            }
            if (!empty($param['tx_hash'])) {
                $where['tx_hash'] = ['like', '%' . $param['tx_hash'] . '%'];
            }

            $model = new CoinModel();
            $selectResult = $model->getTransaction($where, $address, $offset, $limit);
            foreach ($selectResult as $key => $vo) {
                $selectResult[$key]['operate'] = showOperate($this->makeButton($vo['id'], $vo['sh_status'],
                    $vo['dabi_status']));
                $selectResult[$key]['dabi_status'] = $this->dabi_status[$vo['dabi_status']];
                $selectResult[$key]['sh_status'] = $this->sh_status[$vo['sh_status']];
            }

            $return['total'] = $model->getTransactionCount($where, $address);  // 总数据
            $return['rows'] = $selectResult;

            return json($return);
        }

        return $this->fetch();
    }

    public function start()
    {
        $id = input('param.id');

        $model = new CoinModel();
        $flag = $model->start($id);
        return json(msg($flag['code'], $flag['data'], $flag['msg']));
    }


    public function back()
    {
        $id = input('param.id');

        $model = new CoinModel();
        $flag = $model->back($id);
        return json(msg($flag['code'], $flag['data'], $flag['msg']));
    }

    public function restart()
    {
        $id = input('param.id');

        $model = new CoinModel();
        $flag = $model->restart($id);
        return json(msg($flag['code'], $flag['data'], $flag['msg']));
    }

    public function manual()
    {
        $id = input('id');
        $txid = input('txid');
        if (empty($id) || empty($txid)) {
            return json(msg(-1, '', '参数错误'));
        }
        $model = new CoinModel();
        $info = $model->where('dabi_status', 'eq', 2)->where('id', 'eq', $id)->find();
        if (empty($info)) {
            return json(msg(-1, '', '信息不正确'));
        }
        if ($model->where('dabi_status', 'eq', 2)->where('id', 'eq', $id)->update([
            'dabi_status' => 3,
            'tx_hash' => $txid,
            'dabi_time' => $model->raw('now()'),
            'tx_receipt_status'=>'success',
            'tx_receipt_time'=>$model->raw('now()'),
        ])) {
            return json(msg(1, '', '操作成功'));
        } else {
            return json(msg(-1, '', '操作失败'));
        }
    }

    private function makeButton($id, $sh_status, $dabi_status)
    {
        $data = [];

        if ($sh_status == 0) {
            $data['审核'] = [
                'auth' => 'Coin/start',
                'href' => "javascript:start(" . $id . ")",
                'btnStyle' => 'info',
                'icon' => 'fa fa-chevron-right'
            ];
            $data['退回'] = [
                'auth' => 'Coin/back',
                'href' => "javascript:back(" . $id . ")",
                'btnStyle' => 'danger',
                'icon' => 'fa fa-backward'
            ];
        }

        if ($dabi_status == 2) {
            $data['重新打币'] = [
                'auth' => 'Coin/restart',
                'href' => "javascript:restart(" . $id . ")",
                'btnStyle' => 'success',
                'icon' => 'fa fa-repeat'
            ];
            $data['手动到账'] = [
                'auth' => 'Coin/manual',
                'href' => "javascript:manual(" . $id . ")",
                'btnStyle' => 'success',
                'icon' => 'fa fa-repeat'
            ];
        }
        return $data;
    }
}