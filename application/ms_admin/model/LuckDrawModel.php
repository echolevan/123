<?php
/**
 * Created by PhpStorm.
 * User: tears
 * Date: 2018/12/12
 * Time: 9:12 AM
 */

namespace app\ms_admin\model;


class LuckDrawModel extends BaseModel
{
    // 确定链接表名
    protected $name = 'luck_draw';


    public function del($id)
    {
        try {

            $this->where('status', 'eq', 0)->where('id', $id)->update(['status' => -1]);
            return msg(1, '', '删除成功');

        } catch (\Exception $e) {
            return msg(-1, '', $e->getMessage());
        }
    }

    public function edit($param, $validate, $url, $msg)
    {
        try {

            $result = $this->validate($validate)->where('status', 'eq', 0)->update($param, ['id' => $param['id']]);

            if (false === $result) {
                // 验证失败 输出错误信息
                return msg(-1, '', $this->getError());
            } else {

                return msg(1, $url, $msg);
            }
        } catch (\Exception $e) {
            return msg(-2, '', $e->getMessage());
        }
    }

    public function stop($id)
    {
        try {

            $this->where('status', 'eq', 1)->where('id', $id)->update([
                'status' => 2,
                'end_sys_time' => $this->raw('now()'),
                'end_time' => $this->raw('now()'),
                'end_type' => 3
            ]);
            return msg(1, '', '停止成功');

        } catch (\Exception $e) {
            return msg(-1, '', $e->getMessage());
        }
    }


}