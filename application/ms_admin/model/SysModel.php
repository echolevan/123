<?php
/**
 * Created by PhpStorm.
 * User: tears
 * Date: 2018/12/17
 * Time: 2:25 PM
 */

namespace app\ms_admin\model;


use think\Model;

class SysModel extends Model
{
    // 确定链接表名
    protected $name = 'sys_parameter';

    public function changeValue($code, $value)
    {
        try {
            $result = $this->save(['s_value' => $value], ['s_key' => $code]);
            if (false === $result) {
                // 验证失败 输出错误信息
                return msg(-1, '', $this->getError());
            } else {

                return msg(1, '', '操作成功');
            }
        } catch (\Exception $e) {
            return msg(-2, '', $e->getMessage());
        }
    }

    public function getValue($code)
    {
        $res = $this->where(['s_key' => $code])->select()->toArray();
        return $res[0]['s_value'];
    }

    public function getList($where)
    {
        return $this->where($where)->order('s_key asc')->select();
    }
}