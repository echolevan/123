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

class GuijiModel extends Model
{
    // 确定链接表名
    protected $table = 'app_shop_suocang_guiji';


    /**
     * 查询归集
     * @param $where
     * @param $offset
     * @param $limit
     * @return false|\PDOStatement|string|\think\Collection
     */
    public function getGuijiByWhere($where, $offset, $limit)
    {
        return $this->where($where)->limit($offset, $limit)->order('id desc')->select();
    }

    /**
     * 根据搜索条件获取所有的归集数量
     * @param $where
     * @return int|string
     */
    public function getAllGuiji($where)
    {
        return $this->where($where)->count();
    }

    /**
     * 归集单数据
     * @param $id
     * @param $fields
     * @return array|false|\PDOStatement|string|Model
     */
    public function getOneGuiji($id, $fields)
    {
        return $this->field($fields)->where('id', 'eq', $id)->find();
    }

    /**
     * 获取器 录入时间
     */
    public function getLrdateAttr($time)
    {
        return $time;
    }

    /**
     * 获取器 账户类型
     */
    public function getAccountTypeAttr($account_type)
    {
        $data = ['msth' => 'MSTH', 'mstm' => 'MSTM'];
        return $data[$account_type];
    }
}
