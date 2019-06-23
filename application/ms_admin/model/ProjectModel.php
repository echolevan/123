<?php
/**
 * Created by PhpStorm.
 * User: tears
 * Date: 2018/12/12
 * Time: 5:00 PM
 */

namespace app\ms_admin\model;


class ProjectModel extends BaseModel
{

    protected $name = 'save_project';

    public function del($id)
    {
        try {

            $this->where('id', $id)->update(['status' => -2]);
            return msg(1, '', '删除成功');

        } catch (\Exception $e) {
            return msg(-1, '', $e->getMessage());
        }
    }

    public function start($id)
    {
        try {

            $this->where('id', $id)->update(['status' => 1]);
            return msg(1, '', '启用成功');

        } catch (\Exception $e) {
            return msg(-1, '', $e->getMessage());
        }
    }

    public function close($id)
    {
        try {

            $this->where('id', $id)->update(['status' => 0]);
            return msg(1, '', '禁用成功');

        } catch (\Exception $e) {
            return msg(-1, '', $e->getMessage());
        }
    }

    public function stop($id)
    {
        try {

            $this->where('id', $id)->update(['status' => -1]);
            return msg(1, '', '停止释放成功');

        } catch (\Exception $e) {
            return msg(-1, '', $e->getMessage());
        }
    }

}