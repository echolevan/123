<?php

namespace app\ms_admin\model;

use think\Model;

class BusinessOrderModel extends Model
{
    protected $name = 'business_order';

    /**
     * 查询商家订单
     * @param $where
     * @param $offset
     * @param $limit
     * @return false|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getBusinessOrderByWhere($where, $offset, $limit)
    {
        return $this->where($where)->limit($offset, $limit)->order('id desc')->select();
    }

    /**
     * 根据搜索条件获取所有的商家订单数量
     * @param $where
     * @return int|string
     * @throws \think\Exception
     */
    public function getAllBusinessOrder($where)
    {
        return $this->where($where)->count();
    }
}
