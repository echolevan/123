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

class CountryTypeModel extends Model
{
    // 确定链接表名
    protected $table = 't_shop_country_type';

    /**
     * 查询文章
     * @param $where
     * @param $offset
     * @param $limit
     * @return false|\PDOStatement|string|\think\Collection
     */
    public function getCountryByWhere($where, $offset, $limit)
    {
        return $this->where($where)->limit($offset, $limit)->order('id desc')->select();
    }

    /**
     * 根据搜索条件获取所有的国家分类数量
     * @param $where
     * @return int|string
     */
    public function getAllCountry($where)
    {
        return $this->where($where)->count();
    }

    /**
     * 新增国家分类
     * @param $param
     * @return array|\think\response\Json
     */
    public function addCountry($param)
    {
        try {
            $result = $this->allowField(['name', 'sortid', 'status', 'lrdate', 'img'])->validate('MallValidate.countryAdd')->save($param);
            if (false === $result) {
                return msg(-1, '', $this->getError());
            } else {
                return msg(1, url('mall/country'), '添加国家分类成功');
            }
        } catch (\Exception $e) {
            return msg(-1, '', $e->getMessage());
        }
    }

    /**
     * 编辑国家分类
     * @param $param
     * @return array|\think\response\Json
     */
    public function editCountry($param)
    {
        try {
            $result = $this->allowField(['name', 'sortid', 'status', 'lrdate', 'img'])->validate('MallValidate.countryEdit')->save($param, ['id' => $param['country_id']]);
            if (false === $result) {
                return msg(-1, '', $this->getError());
            } else {
                return msg(1, url('mall/country'), '编辑商品国家分类成功');
            }
        } catch (\Exception $e) {
            return msg(-1, '', $e->getMessage());
        }
    }

    /**
     * 国家分类单数据
     * @param $id
     * @param $fields
     * @return array|false|\PDOStatement|string|Model
     */
    public function getOneCountry($id, $fields)
    {
        return $this->field($fields)->where('id', 'eq', $id)->find();
    }

    /**
     * 国家分类删除
     * @param $id
     * @return \think\response\Json
     */
    public function delCountry($id)
    {
        try {
            $rec = $this->where('id', 'eq', $id)->delete();
            if (!$rec) {
                return msg(-1, '', '国家分类删除失败');
            }
            return msg(1, '', '国家分类删除成功');
        } catch (\Exception $e) {
            return msg(-1, '', $e->getMessage());
        }
    }
}
