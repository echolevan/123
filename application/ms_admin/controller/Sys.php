<?php
/**
 * Created by PhpStorm.
 * User: tears
 * Date: 2018/12/17
 * Time: 1:36 PM
 */

namespace app\ms_admin\controller;


use think\Db;

class Sys extends Base
{

    public function jiesuan()
    {
        if (request()->isAjax()) {

            $param = input('param.');

            $limit = $param['pageSize'];
            $offset = ($param['pageNumber'] - 1) * $limit;
            $selectResult = Db::name('log_jiesuan')->limit($offset, $limit)->order('id desc')->select();
            $return['total'] = Db::name('log_jiesuan')->count();  // 总数据
            $return['rows'] = $selectResult;

            return json($return);
        }

        return $this->fetch();
    }

    public function jiangchi()
    {
        if (request()->isAjax()) {

            $param = input('param.');

            $limit = $param['pageSize'];
            $offset = ($param['pageNumber'] - 1) * $limit;
            $selectResult = Db::name('log_luck_draw_sys_bonus')->limit($offset, $limit)->order('id desc')->select();
            $return['total'] = Db::name('log_luck_draw_sys_bonus')->count();  // 总数据
            $return['rows'] = $selectResult;

            return json($return);
        }

        return $this->fetch();

    }

    public function login()
    {
        if (request()->isAjax()) {

            $param = input('param.');
            $where = [];
            if (array_key_exists('searchText', $param) && !empty($param['searchText'])) {
                $where['memberid'] = ['eq', $param['searchText']];
            }
            $limit = $param['pageSize'];
            $offset = ($param['pageNumber'] - 1) * $limit;
            $selectResult = Db::name('login_log')->where($where)->limit($offset, $limit)->order('id desc')->select();
            $return['total'] = Db::name('login_log')->where($where)->count();  // 总数据
            $return['rows'] = $selectResult;

            return json($return);
        }

        return $this->fetch();

    }
}