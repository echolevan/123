<?php

namespace app\ms_admin\model;

use think\Model;

class BusinessModel extends Model
{
    protected $name = 'business';

    /**
     * 查询商家
     * @param $where
     * @param $offset
     * @param $limit
     * @return false|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getBusinessByWhere($where, $offset, $limit)
    {
        return $this->where($where)->limit($offset, $limit)->order('id desc')->select();
    }

    /**
     * 根据搜索条件获取所有的商家数量
     * @param $where
     * @return int|string
     * @throws \think\Exception
     */
    public function getAllBusiness($where)
    {
        return $this->where($where)->count();
    }

    /**
     * 编辑商家
     * @param $param
     * @return array
     */
    public function editBusiness($param)
    {
        try {
            $result = $this->allowField(['status', 'dj_date', 'dj_op_id'])->save($param, ['id' => $param['business_id']]);
            if (false === $result) {
                return msg(-1, '', $this->getError());
            } else {
                return msg(1, url('business/index'), '操作成功');
            }
        } catch (\Exception $e) {
            return msg(-1, '', $e->getMessage());
        }
    }
}
