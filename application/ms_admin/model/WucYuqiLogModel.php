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
namespace app\ms_admin\model;

use think\Model;

class WucYuqiLogModel extends Model
{
    // 确定链接表名
    protected $table = 't_log_shop_fanzheng';


    /**
     * 查询释放
     * @param $where
     * @param $offset
     * @param $limit
     * @return false|\PDOStatement|string|\think\Collection
     */
    public function getWucYuqiLogByWhere($where, $offset, $limit)
    {
        return $this->where($where)->limit($offset, $limit)->order('id desc')->select();
    }

    /**
     * 根据搜索条件获取所有的释放数量
     * @param $where
     * @return int|string
     */
    public function getAllWucYuqiLog($where)
    {
        return $this->where($where)->count();
    }

    /**
     * 释放单数据
     * @param $id
     * @param $fields
     * @return array|false|\PDOStatement|string|Model
     */
    public function getOneWucYuqiLog($id, $fields)
    {
        return $this->field($fields)->where('id', 'eq', $id)->find();
    }

    /**
     * 获取器 录入时间
     */
    public function getOpTimeAttr($time)
    {
        return $time;
    }

}
