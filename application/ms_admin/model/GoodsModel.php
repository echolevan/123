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

class GoodsModel extends Model
{
    // 确定链接表名
    protected $table = 't_shop_goods';

    /**
     * 查询商品
     * @param $where
     * @param $offset
     * @param $limit
     * @return false|\PDOStatement|string|\think\Collection
     */
    public function getGoodsByWhere($where, $offset, $limit)
    {
        return $this->field('id,img,goods_type_name,county_type_name,name,pay_type,sell_price,market_price,rebate_num,store_nums,sale,is_del,is_delivery_fee,delivery_fee,up_time,down_time,create_time')->where($where)->limit($offset, $limit)->order('id desc')->select();
    }

    /**
     * 根据搜索条件获取所有的商品数量
     * @param $where
     * @return int|string
     */
    public function getAllGoods($where)
    {
        return $this->where($where)->count();
    }

    /**
     * 商品单数据
     * @param $id
     * @param $fields
     * @return array|false|\PDOStatement|string|Model
     */
    public function getOneGoods($id, $fields)
    {
        return $this->field($fields)->where('id', 'eq', $id)->find();
    }

    /**
     * 商品删除
     * @param $id
     * @return \think\response\Json
     */
    public function delGoods($id, $is_del)
    {
        try {
            $param['is_del'] = $is_del;
            if (2 == $is_del) {
                $param['up_time'] = date('Y-m-d H:i:s');
            }
            if (3 == $is_del) {
                $param['down_time'] = date('Y-m-d H:i:s');
            }
            $rec = $this->save($param, ['id' => $id]);
            if (!$rec) {
                return msg(-1, '', '商品状态更新失败');
            }
            return msg(1, '', '商品状态更新成功');
        } catch (\Exception $e) {
            return msg(-1, '', $e->getMessage());
        }
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
     * 返回原有数据  不自动进行时间转换
     * @param $time
     * @return mixed
     */
    public function getUpTimeAttr($time)
    {
        return $time;
    }

    /**
     * 返回原有数据  不自动进行时间转换
     * @param $time
     * @return mixed
     */
    public function getDownTimeAttr($time)
    {
        return $time;
    }

    /**
     * 转换状态
     */
    public function getIsDelAttr($status)
    {
        $data = [0 => '正常', 1 => '已删除', 2 => '上架', 3 => '下架'];
        return $data[$status];
    }

    /**
     * 转化运费状态
     */
    public function getIsDeliveryFeeAttr($status)
    {
        $data = [0 => '收运费', 1 => '免运费'];
        return $data[$status];
    }
}
