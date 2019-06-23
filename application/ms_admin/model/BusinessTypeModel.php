<?php

namespace app\ms_admin\model;

use think\Model;

class BusinessTypeModel extends Model
{
    protected $name = 'business_type';

    /**
     * 查询商家类型
     * @param $where
     * @param $offset
     * @param $limit
     * @return false|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getBusinessTypeByWhere($where, $offset, $limit)
    {
        return $this->where($where)->limit($offset, $limit)->order('sort asc')->select();
    }

    /**
     * 根据搜索条件获取所有的商家类型数量
     * @param $where
     * @return int|string
     * @throws \think\Exception
     */
    public function getAllBusinessType($where)
    {
        return $this->where($where)->count();
    }

    /**
     * 新增商家类型
     * @param $param
     * @return array
     */
    public function addBusinessType($param)
    {
        try {
            $result = $this->validate('BusinessValidate.typeAdd')->save($param);
            if (false === $result) {
                return msg(-1, '', $this->getError());
            } else {
                return msg(1, url('business/typeindex'), '添加商家类型成功');
            }
        } catch (\Exception $e) {
            return msg(-1, '', $e->getMessage());
        }
    }

    /**
     * 编辑商家类型
     * @param $param
     * @return array
     */
    public function editBusinessType($param)
    {
        try {
            $result = $this->allowField(['name', 'status', 'sort'])->save($param, ['id' => $param['id']]);
            if (false === $result) {
                return msg(-1, '', $this->getError());
            } else {
                return msg(1, url('business/typeindex'), '操作成功');
            }
        } catch (\Exception $e) {
            return msg(-1, '', $e->getMessage());
        }
    }
}
