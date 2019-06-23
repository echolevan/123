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

class OrderModel extends Model
{
    // 确定链接表名
    protected $table = 't_shop_order';


    /**
     * 查询订单
     * @param $where
     * @param $offset
     * @param $limit
     * @return false|\PDOStatement|string|\think\Collection
     */
    public function getOrderByWhere($where, $offset, $limit)
    {
        return $this->where($where)->limit($offset, $limit)->order('id desc')->select();
    }

    /**
     * 根据搜索条件获取所有的订单数量
     * @param $where
     * @return int|string
     */
    public function getAllOrder($where)
    {
        return $this->where($where)->count();
    }

    /**
     * 编辑订单
     * @param $param
     * @return array|\think\response\Json
     */
    public function editOrder($param)
    {
        try {
            $result = $this->allowField(['express_name', 'express_code', 'distribution_status', 'send_time'])->validate('MallValidate.orderEdit')->save($param, ['id' => $param['order_id']]);
            if (false === $result) {
                return msg(-1, '', $this->getError());
            } else {
                return msg(1, url('mall/orderindex'), '发货成功');
            }
        } catch (\Exception $e) {
            return msg(-1, '', $e->getMessage());
        }
    }

    /**
     * 订单单数据
     * @param $id
     * @param $fields
     * @return array|false|\PDOStatement|string|Model
     */
    public function getOneOrder($id, $fields)
    {
        return $this->field($fields)->where('id', 'eq', $id)->find();
    }

    /**
     * 订单作废
     * @param $id
     * @return \think\response\Json
     */
    public function delOrder($id)
    {
        try {
            $rec = $this->where('id', 'eq', $id)->delete();
            if (!$rec) {
                return msg(-1, '', '轮播图删除失败');
            }
            return msg(1, '', '轮播图删除成功');
        } catch (\Exception $e) {
            return msg(-1, '', $e->getMessage());
        }
    }

    /**
     * 获取器 支付时间
     */
    public function getPayTimeAttr($time)
    {
        return $time;
    }

    /**
     * 获取器 发货时间
     */
    public function getSendTimeAttr($time)
    {
        return $time;
    }

    /**
     * 获取器 下单时间
     */
    public function getCreateTimeAttr($time)
    {
        return $time;
    }

    /**
     * 获取器 订单完成时间
     */
    public function getCompletionTimeAttr($time)
    {
        return $time;
    }

    /**
     * 获取器 用户收货时间
     */
    public function getAcceptTimeAttr($time)
    {
        return $time;
    }

    /**
     * 获取器 锁仓释放时间
     */
    public function getSfdateBeginAttr($time)
    {
        return $time;
    }

    /**
     * 获取器 取消订单时间
     */
    public function getQuXiaoTimeAttr($time)
    {
        return $time;
    }

    /**
     * 获取器 订单状态
     */
    public function getStatusAttr($status)
    {
        $data = [1 => '待付款', 2 => '已支付', 3 => '已取消', 5 => '已完成'];
        return $data[$status];
    }

    /**
     * 获取器 支付状态
     */
    public function getPayStatusAttr($status)
    {
        $data = [0 => '未支付', 1 => '已支付'];
        return $data[$status];
    }

    /**
     * 获取器 发货状态
     */
    public function getDistributionStatusAttr($status)
    {
        $data = [0 => '未发货', 1 => '已发货'];
        return $data[$status];
    }
}
