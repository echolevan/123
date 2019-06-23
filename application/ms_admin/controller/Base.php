<?php
// +----------------------------------------------------------------------
// | snake
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2022 http://baiyf.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: NickBai <1902822973@qq.com>
// +----------------------------------------------------------------------
namespace app\ms_admin\controller;


use think\Controller;
use app\ms_admin\model\RoleModel;
use think\Db;

class Base extends Controller
{
    public function _initialize()
    {
        if (empty(session('username')) || empty(session('id'))) {

            $loginUrl = url('login/index');
            if (request()->isAjax()) {
                return msg(111, $loginUrl, '登录超时');
            }

            $this->redirect($loginUrl);
        }

        // 检查缓存
        $this->cacheCheck();

        // 检测权限
        $control = lcfirst(request()->controller());
        $action = lcfirst(request()->action());

        if (empty(authCheck($control . '/' . $action))) {
            if (request()->isAjax()) {
                return msg(403, '', '您没有权限');
            }

            $this->error('403 您没有权限');
        }

        //存入操作日志
        $data = [
            'oper_id'    => session('username'),
            'oper_fun'   => $control . '/' . $action,
            'oper_value' => json_encode(input('param.')),
            'ip'         => request()->ip(),
            'oper_tiems' => date('Y-m-d H:i:s')
        ];
        $oper_log_rec = Db::table('t_admin_log_oper')->insert($data);
        if (!$oper_log_rec) {
            $this->error('存入操作日志失败');
        }

        $this->assign([
            'head'     => session('head'),
            'username' => session('username'),
            'rolename' => session('role')
        ]);

    }

    private function cacheCheck()
    {
        $action = cache(session('role_id'));

        if (is_null($action) || empty($action)) {

            // 获取该管理员的角色信息
            $roleModel = new RoleModel();
            $info = $roleModel->getRoleInfo(session('role_id'));
            cache(session('role_id'), $info['action']);
        }
    }

    protected function removRoleCache()
    {
        $roleModel = new RoleModel();
        $roleList = $roleModel->getRole();

        foreach ($roleList as $value) {
            cache($value['id'], null);
        }
    }
}
