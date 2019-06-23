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

class BannerModel extends Model
{
    // 确定链接表名
    protected $table = 't_shop_banner';

    /**
     * 查询文章
     * @param $where
     * @param $offset
     * @param $limit
     * @return false|\PDOStatement|string|\think\Collection
     */
    public function getBannerByWhere($where, $offset, $limit)
    {
        return $this->where($where)->limit($offset, $limit)->order('id desc')->select();
    }

    /**
     * 根据搜索条件获取所有的轮播图数量
     * @param $where
     * @return int|string
     */
    public function getAllBanner($where)
    {
        return $this->where($where)->count();
    }

    /**
     * 新增轮播图
     * @param $param
     * @return array|\think\response\Json
     */
    public function addBanner($param)
    {
        try {
            $result = $this->validate('MallValidate.bannerAdd')->save($param);
            if (false === $result) {
                return msg(-1, '', $this->getError());
            } else {
                return msg(1, url('mall/banner'), '添加轮播图成功');
            }
        } catch (\Exception $e) {
            return msg(-1, '', $e->getMessage());
        }
    }

    /**
     * 编辑轮播图
     * @param $param
     * @return array|\think\response\Json
     */
    public function editBanner($param)
    {
        try {
            $result = $this->allowField(['title', 'type', 'img', 'sort', 'url'])->validate('MallValidate.bannerEdit')->save($param, ['id' => $param['banner_id']]);
            if (false === $result) {
                return msg(-1, '', $this->getError());
            } else {
                return msg(1, url('mall/banner'), '编辑轮播图成功');
            }
        } catch (\Exception $e) {
            return msg(-1, '', $e->getMessage());
        }
    }

    /**
     * 轮播图单数据
     * @param $id
     * @param $fields
     * @return array|false|\PDOStatement|string|Model
     */
    public function getOneBanner($id, $fields)
    {
        return $this->field($fields)->where('id', 'eq', $id)->find();
    }

    /**
     * 轮播图删除
     * @param $id
     * @return \think\response\Json
     */
    public function delBanner($id)
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
}
