<?php
/**
 * Created by PhpStorm.
 * User: tears
 * Date: 2018/12/14
 * Time: 3:12 PM
 */

namespace app\ms_admin\controller;


use app\ms_admin\model\TeamModel;
use app\common\controller\YunXin;
use think\Db;

class Team extends Base
{
    public function index()
    {
        if (request()->isAjax()) {

            $param = input('param.');

            $limit = $param['pageSize'];
            $offset = ($param['pageNumber'] - 1) * $limit;
            $where = [];
            if (array_key_exists('searchText', $param) && !empty($param['searchText'])) {
                $where['name'] = ['eq', $param['searchText']];
            }
            $model = new TeamModel();
            $selectResult = $model->getByWhere($where, $offset, $limit,'create_time desc');
            foreach ($selectResult as $key => $vo) {
                $selectResult[$key]['operate'] = showOperate($this->makeButton($vo['tid']));
            }
            $return['total'] = $model->getAllcount($where);  // 总数据
            $return['rows'] = $selectResult;

            return json($return);
        }

        return $this->fetch();
    }

    public function info()
    {
        $tid = input('tid');
        if (request()->isAjax()) {
            if (empty($tid)) {
                return json(msg(-1, [], "参数错误"));
            }
            try {
                $yunxin = new YunXin();
            } catch (\ErrorException $e) {
                return json(msg(-1, [], "开启云信通信失败" . $e->getMessage()));
            }
            $members = $yunxin->getQunMembers($tid);
            if (isset($members) && (isset($members->code) && $members->code != 200) || !isset($members->code) || !isset($members->tinfo)) {
                return json(msg(-1, [], "群信息不正确"));
            }
            $list = [];
            // 记录群成员
            $yunxinInfo = $members->tinfo;
            if (isset($yunxinInfo->admins)) {
                $list = array_merge($list, $yunxinInfo->admins);
            }
            if (isset($yunxinInfo->members)) {
                $list = array_merge($list, $yunxinInfo->members);
            }
            if (isset($yunxinInfo->owner)) {
                Db::name('team')->where('tid', 'eq', $tid)->update(['owner' => $yunxinInfo->owner->accid]);
                $list[] = $yunxinInfo->owner;
            }
            foreach ($list as $key => $vo) {
                $list[$key]->createtime = date(DATE_TIME_FORMAT, substr($list[$key]->createtime, 0, -3));
                $list[$key]->updatetime = date(DATE_TIME_FORMAT, substr($list[$key]->updatetime, 0, -3));
                $list[$key] = json_decode(json_encode($vo), true);
                if ($vo->accid != $yunxinInfo->owner->accid) {
                    $list[$key]['operate'] = showOperate($this->makeButton1($tid, $vo->accid));
                }
            }
            $return['total'] = count($list);  // 总数据
            $return['rows'] = $list;

            return json($return);
        }
        if (empty($tid)) {
            $this->error('参数错误!');
        }
        $this->assign('id', $tid);
        return view();
    }


    public function del()
    {
        $tid = input('tid');
        try {
            $yunxin = new YunXin();
        } catch (\ErrorException $e) {
            return json(msg(-1, [], "开启云信通信失败" . $e->getMessage()));
        }
        $members = $yunxin->getQunMembers($tid);
        if (isset($members) && (isset($members->code) && $members->code != 200) || !isset($members->code) || !isset($members->tinfo)) {
            return json(msg(-1, [], "群信息不正确"));
        }
        $yunxinInfo = $members->tinfo;
        $url = "https://api.netease.im/nimserver/team/remove.action";
        $data['tid'] = $tid;
        $data['owner'] = $yunxinInfo->owner->accid;
        $r = $yunxin->send($url, $data);
        if (isset($r) && (isset($r->code) && $r->code != 200) || !isset($r->code)) {
            return json(msg(-1, [], "操作失败"));
        }
        if ($r->code == 200) {
            Db::name('team')->where('tid', 'eq', $tid)->delete();
            return json(msg(1, [], "操作成功"));
        } else {
            return json(msg(-1, [], "操作失败"));
        }
    }

    private function makeButton($id)
    {
        $data = [
            '查看成员' => [
                'auth' => 'Team/info',
                'href' => url('Team/info', ['tid' => $id]),
                'btnStyle' => 'primary',
                'icon' => 'fa fa-list-ul'
            ],
            '解散' => [
                'auth' => 'Team/del',
                'href' => "javascript:del(" . $id . ")",
                'btnStyle' => 'danger',
                'icon' => 'fa fa-trash-o'
            ]
        ];
        return $data;
    }

    public function yichu()
    {
        $tid = input('tid');
        $accid = input('accid');
        if (empty($tid) || empty($accid)) {
            return json(msg(-1, [], "参数错误"));
        }
        try {
            $yunxin = new YunXin();
        } catch (\ErrorException $e) {
            return json(msg(-1, [], "开启云信通信失败" . $e->getMessage()));
        }
        $members = $yunxin->getQunMembers($tid);
        if (isset($members) && (isset($members->code) && $members->code != 200) || !isset($members->code) || !isset($members->tinfo)) {
            return json(msg(-1, [], "群信息不正确"));
        }
        $yunxinInfo = $members->tinfo;
        $url = "https://api.netease.im/nimserver/team/kick.action";
        $data['tid'] = $tid;
        $data['member'] = $accid;
        $data['owner'] = $yunxinInfo->owner->accid;
        $r = $yunxin->send($url, $data);
        if (isset($r) && (isset($r->code) && $r->code != 200) || !isset($r->code)) {
            return json(msg(-1, [], "操作失败"));
        }
        if ($r->code == 200) {
            return json(msg(1, [], "操作成功"));
        } else {
            return json(msg(-1, [], "操作失败"));
        }
    }

    private function makeButton1($id, $accid)
    {
        $data = [
            '移除' => [
                'auth' => 'Team/yichu',
                'href' => "javascript:del(" . $id . ",'" . $accid . "')",
                'btnStyle' => 'danger',
                'icon' => 'fa fa-trash-o'
            ]
        ];
        return $data;
    }

}