<?php
/**
 * Created by PhpStorm.
 * User: tears
 * Date: 2018/12/12
 * Time: 9:12 AM
 */

namespace app\ms_admin\model;


use think\Exception;
use think\Model;

class BaseModel extends Model
{
    public function getByWhere($where, $offset, $limit,$order='id desc')
    {
        return $this->where($where)->limit($offset, $limit)->order($order)->select();
    }

    public function getAllCount($where)
    {
        try {
            return $this->where($where)->count();
        } catch (Exception $e) {
            return 0;
        }
    }

    public function addInfo($param, $validate, $url, $msg)
    {
        try {
            $result = $this->validate($validate)->save($param);
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


    public function del($id)
    {
        try {

            $this->where('id', $id)->update(['status' => -1]);
            return msg(1, '', '删除成功');

        } catch (\Exception $e) {
            return msg(-1, '', $e->getMessage());
        }
    }

    public function edit($param,$validate, $url, $msg)
    {
        try {

            $result = $this->validate($validate)->save($param, ['id' => $param['id']]);

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

}