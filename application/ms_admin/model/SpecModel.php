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

class SpecModel extends Model
{
    // 确定链接表名
    protected $table = 't_shop_spec';

    /**
     * 查询商品规格
     * @param $where
     * @param $offset
     * @param $limit
     * @return false|\PDOStatement|string|\think\Collection
     */
    public function getSpecByWhere($where, $offset, $limit)
    {
        return $this->where($where)->limit($offset, $limit)->order('id desc')->select();
    }

    /**
     * 根据搜索条件获取所有的商品规格数量
     * @param $where
     * @return int|string
     */
    public function getAllSpec($where)
    {
        return $this->where($where)->count();
    }

    /**
     * 新增商品规格
     * @param $param
     * @return array|\think\response\Json
     */
    public function addSpec($param)
    {
        try {
            $result = $this->allowField(['name', 'value', 'type'])->save($param);
            if (false === $result) {
                return msg(-1, '', $this->getError());
            } else {
                return msg(1, url('mall/spec'), '添加商品规格成功');
            }
        } catch (\Exception $e) {
            return msg(-1, '', $e->getMessage());
        }
    }

    /**
     * 编辑商品规格
     * @param $param
     * @return array|\think\response\Json
     */
    public function editSpec($param)
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
     * 商品规格单数据
     * @param $id
     * @param $fields
     * @return array|false|\PDOStatement|string|Model
     */
    public function getOneSpec($id, $fields)
    {
        return $this->field($fields)->where('id', 'eq', $id)->find();
    }

    /**
     * 商品规格删除
     * @param $id
     * @return \think\response\Json
     */
    public function delSpec($id)
    {
        try {
            $rec = $this->save(['is_del' => 1], ['id' => $id]);
            if (!$rec) {
                return msg(-1, '', '商品规格删除失败');
            }
            return msg(1, '', '商品规格删除成功');
        } catch (\Exception $e) {
            return msg(-1, '', $e->getMessage());
        }
    }
}
