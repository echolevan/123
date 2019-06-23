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

class ProductsImgModel extends Model
{
    // 确定链接表名
    protected $table = 't_shop_products_img';

    protected $auto = ['create_time', 'status'];

    /**
     * 查询货品图片集
     * @param $where
     * @param $offset
     * @param $limit
     * @return false|\PDOStatement|string|\think\Collection
     */
    public function getProductsImgByWhere($where, $offset, $limit)
    {
        return $this->where($where)->limit($offset, $limit)->order('id desc')->select();
    }

    /**
     * 根据搜索条件获取所有的货品图片集
     * @param $where
     * @return int|string
     */
    public function getAllProductsImg($where)
    {
        return $this->where($where)->count();
    }

    /**
     * 新增货品图片分类
     * @param $param
     * @return array|\think\response\Json
     */
    public function addProductsImg($param)
    {
        try {
            $result = $this->allowField(['goods_id', 'img_name', 'img', 'status', 'create_time'])->validate('MallValidate.productsimgAdd')->save($param);
            if (false === $result) {
                return msg(-1, '', $this->getError());
            } else {
                return msg(1, url('mall/productsimg', ['goods_id' => $param['goods_id']]), '添加货品图片成功');
            }
        } catch (\Exception $e) {
            return msg(-1, '', $e->getMessage());
        }
    }

    /**
     * 货品图片集删除
     * @param $id
     * @return \think\response\Json
     */
    public function delProductsImg($id)
    {
        try {
            $rec = $this->save(['status' => 3], ['id' => $id]);
            if (!$rec) {
                return msg(-1, '', '货品图片删除失败');
            }
            return msg(1, '', '货品图片删除成功');
        } catch (\Exception $e) {
            return msg(-1, '', $e->getMessage());
        }
    }

    /**
     * 货品图片集启用禁用
     * @param $id
     * @return \think\response\Json
     */
    public function statusProductImg($id, $status)
    {
        try {
            $rec = $this->save(['status' => $status], ['id' => $id]);
            if (!$rec) {
                return msg(-1, '', '货品图片状态更新失败');
            }
            return msg(1, '', '货品图片状态更新成功');
        } catch (\Exception $e) {
            return msg(-1, '', $e->getMessage());
        }
    }


    /**
     * 货品图片集单数据
     * @param $id
     * @param $fields
     * @return array|false|\PDOStatement|string|Model
     */
    public function getOneProductsImg($id, $fields)
    {
        return $this->field($fields)->where('id', 'eq', $id)->find();
    }

    /**
     * 返回原有数据  不自动进行时间转换
     * @param $time
     * @return mixed
     */
    public function getCreateTimeAttr($time)
    {
        return $time;
    }

    /**
     * 图片状态进行整合
     */
    public function getStatusAttr($status)
    {
        $data = [1 => '启用', 2 => '禁用', 3 => '删除'];
        return $data[$status];
    }
}
