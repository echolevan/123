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

class ProductsModel extends Model
{
    // 确定链接表名
    protected $table = 't_shop_products';

    /**
     * 查询货品规格
     * @param $where
     * @param $offset
     * @param $limit
     * @return false|\PDOStatement|string|\think\Collection
     */
    public function getProductsByWhere($where, $offset, $limit)
    {
        return $this->where($where)->limit($offset, $limit)->order('id desc')->select();
    }

    /**
     * 根据搜索条件获取所有的货品数量
     * @param $where
     * @return int|string
     */
    public function getAllProducts($where)
    {
        return $this->where($where)->count();
    }

    /**
     * 编辑货品
     * @param $param
     * @return array|\think\response\Json
     */
    public function editProducts($param)
    {
        try {
            $result = $this->allowField(['name', 'value', 'type'])->save($param, ['id' => $param['id']]);
            if (false === $result) {
                return msg(-1, '', $this->getError());
            } else {
                return msg(1, url('mall/spec'), '编辑商品规格成功');
            }
        } catch (\Exception $e) {
            return msg(-1, '', $e->getMessage());
        }
    }

    /**
     * 货品单数据
     * @param $id
     * @param $fields
     * @return array|false|\PDOStatement|string|Model
     */
    public function getOneProducts($id, $fields)
    {
        return $this->field($fields)->where('id', 'eq', $id)->find();
    }
}
