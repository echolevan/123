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

class NewsModel extends Model
{
    // 确定链接表名
    protected $name = 'news';

    /**
     * 查询文章
     * @param $where
     * @param $offset
     * @param $limit
     */
    public function getArticlesByWhere($where, $offset, $limit)
    {
        return $this->where($where)->limit($offset, $limit)->order('id desc')->select();
    }

    /**
     * 根据搜索条件获取所有的文章数量
     * @param $where
     */
    public function getAllArticles($where)
    {
        return $this->where($where)->count();
    }

    /**
     * 添加文章
     * @param $param
     */
    public function addArticle($param)
    {
        try {
            $result = $this->validate('NewsValidate')->save($param);
            if (false === $result) {
                // 验证失败 输出错误信息
                return msg(-1, '', $this->getError());
            } else {

                return msg(1, url('news/index'), '添加文章成功');
            }
        } catch (\Exception $e) {
            return msg(-2, '', $e->getMessage());
        }
    }

    /**
     * 编辑文章信息
     * @param $param
     */
    public function editArticle($param)
    {
        try {

            $result = $this->validate('NewsValidate')->save($param, ['id' => $param['id']]);

            if (false === $result) {
                // 验证失败 输出错误信息
                return msg(-1, '', $this->getError());
            } else {

                return msg(1, url('news/index'), '编辑文章成功');
            }
        } catch (\Exception $e) {
            return msg(-2, '', $e->getMessage());
        }
    }

    /**
     * 根据文章的id 获取文章的信息
     * @param $id
     */
    public function getOneArticle($id)
    {
        return $this->where('id', $id)->find();
    }

    /**
     * 删除文章
     * @param $id
     */
    public function delArticle($id)
    {
        try {

            $this->where('id', $id)->update(['status' => -1]);
            return msg(1, '', '删除文章成功');

        } catch (\Exception $e) {
            return msg(-1, '', $e->getMessage());
        }
    }

    public function stopArticle($id)
    {
        try {

            $this->where('id', $id)->update(['status' => 0]);
            return msg(1, '', '停用文章成功');

        } catch (\Exception $e) {
            return msg(-1, '', $e->getMessage());
        }
    }

    public function startArticle($id)
    {
        try {

            $this->where('id', $id)->update(['status' => 1]);
            return msg(1, '', '启用文章成功');

        } catch (\Exception $e) {
            return msg(-1, '', $e->getMessage());
        }
    }
}
