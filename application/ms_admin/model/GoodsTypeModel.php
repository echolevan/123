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

class GoodsTypeModel extends Model
{
    // 确定链接表名
    protected $table = 't_shop_goods_type';

    /**
     * 查询商品分类
     * @param $where
     * @param $offset
     * @param $limit
     * @return false|\PDOStatement|string|\think\Collection
     */
    public function getGoodsTypeByWhere($where, $offset, $limit)
    {
        return $this->where($where)->limit($offset, $limit)->order('id desc')->select();
    }

    /**
     * 根据搜索条件获取所有的商品分类数量
     * @param $where
     * @return int|string
     */
    public function getAllGoodsType($where)
    {
        return $this->where($where)->count();
    }

    /**
     * 新增商品分类
     * @param $param
     * @return array|\think\response\Json
     */
    public function addGoodsType($param)
    {
        try {
            $result = $this->allowField(['typeid', 'typename', 'sortid', 'status', 'lrdate'])->validate('MallValidate.goodstypeAdd')->save($param);
            if (false === $result) {
                return msg(-1, '', $this->getError());
            } else {
                return msg(1, url('mall/goodstype'), '添加商品分类成功');
            }
        } catch (\Exception $e) {
            return msg(-1, '', $e->getMessage());
        }
    }

    /**
     * 编辑商品分类
     * @param $param
     * @return array|\think\response\Json
     */
    public function editGoodsType($param)
    {
        try {
            $result = $this->allowField(['typeid', 'typename', 'sortid', 'status'])->validate('MallValidate.goodstypeEdit')->save($param, ['id' => $param['goods_type_id']]);
            if (false === $result) {
                return msg(-1, '', $this->getError());
            } else {
                return msg(1, url('mall/goodstype'), '编辑商品分类成功');
            }
        } catch (\Exception $e) {
            return msg(-1, '', $e->getMessage());
        }
    }

    /**
     * 商品分类单数据
     * @param $id
     * @param $fields
     * @return array|false|\PDOStatement|string|Model
     */
    public function getOneGoodsType($id, $fields)
    {
        return $this->field($fields)->where('id', 'eq', $id)->find();
    }

    /**
     * 商品分类删除
     * @param $id
     * @return \think\response\Json
     */
    public function delGoodsType($id)
    {
        try {
            $rec = $this->where('id', 'eq', $id)->delete();
            if (!$rec) {
                return msg(-1, '', '商品分类删除失败');
            }
            return msg(1, '', '商品分类删除成功');
        } catch (\Exception $e) {
            return msg(-1, '', $e->getMessage());
        }
    }
}
