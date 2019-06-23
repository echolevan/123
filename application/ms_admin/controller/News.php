<?php
/**
 * Created by PhpStorm.
 * User: tears
 * Date: 2018/12/11
 * Time: 4:15 PM
 */

namespace app\ms_admin\controller;


use app\ms_admin\model\NewsModel;
use app\ms_admin\model\NewsReadModel;
use app\util\Tools;
use think\Controller;
use think\Env;

class News extends Base
{
// 文章列表
    public function index()
    {
        if (request()->isAjax()) {

            $param = input('param.');

            $limit = $param['pageSize'];
            $offset = ($param['pageNumber'] - 1) * $limit;

            $where = [];
            if (!empty($param['searchText'])) {
                $where['title'] = ['like', '%' . $param['searchText'] . '%'];
            }
            $where['status'] = ['egt', 0];
            $article = new NewsModel();
            $selectResult = $article->getArticlesByWhere($where, $offset, $limit);
            foreach ($selectResult as $key => $vo) {
                $selectResult[$key]['operate'] = showOperate($this->makeButton($vo['id'], $vo['status']));
            }
            $return['total'] = $article->getAllArticles($where);  // 总数据
            $return['rows'] = $selectResult;

            return json($return);
        }

        return $this->fetch();
    }

    // 添加文章
    public function articleAdd()
    {
        if (request()->isPost()) {
            $param = input('post.');

//            unset($param['file']);
//            $param['add_time'] = date('Y-m-d H:i:s');
            $param['surplus_amount'] = $param['total_amount'];
            $article = new NewsModel();
            $flag = $article->addArticle($param);

            return json(msg($flag['code'], $flag['data'], $flag['msg']));
        }

        return $this->fetch();
    }

    public function mx()
    {
        $id = input('id');

        if (request()->isAjax()) {

            $param = input('param.');
            if (empty($id)) {
                return json(msg(-1, [], '参数错误'));
            }
            $limit = $param['pageSize'];
            $offset = ($param['pageNumber'] - 1) * $limit;

            $where = [];
            $where['news_id'] = ['eq', $id];
            $model = new NewsReadModel();
            $selectResult = $model->getByWhere($where, $offset, $limit);

//            foreach ($selectResult as $key => $vo) {
//                $selectResult[$key]['status'] = $this->lq_status[$vo['status']];
//            }

            $return['total'] = $model->getAllCount($where);  // 总数据
            $return['rows'] = $selectResult;

            return json($return);
        }
        if (empty($id)) {
            $this->error('参数错误');
        }
        return $this->assign('id', $id)->fetch();
    }

    public function articleEdit()
    {
        $article = new NewsModel();
        if (request()->isPost()) {

            $param = input('post.');
            unset($param['file']);
            $flag = $article->editArticle($param);

            return json(msg($flag['code'], $flag['data'], $flag['msg']));
        }

        $id = input('param.id');
        $this->assign([
            'article' => $article->getOneArticle($id)
        ]);
        return $this->fetch();
    }

    public function articleDel()
    {
        $id = input('param.id');

        $article = new NewsModel();
        $flag = $article->delArticle($id);
        return json(msg($flag['code'], $flag['data'], $flag['msg']));
    }

    public function articleStop()
    {
        $id = input('param.id');

        $article = new NewsModel();
        $flag = $article->stopArticle($id);
        return json(msg($flag['code'], $flag['data'], $flag['msg']));
    }

    public function articleStart()
    {
        $id = input('param.id');

        $article = new NewsModel();
        $flag = $article->startArticle($id);
        return json(msg($flag['code'], $flag['data'], $flag['msg']));
    }


    /**
     * 拼装操作按钮
     * @param $id
     * @param $status
     * @return array
     */
    private function makeButton($id, $status)
    {
        $data = [
            '编辑' => [
                'auth' => 'news/articleedit',
                'href' => url('news/articleedit', ['id' => $id]),
                'btnStyle' => 'primary',
                'icon' => 'fa fa-paste'
            ],
            '阅读记录' => [
                'auth' => 'news/mx',
                'href' => url('news/mx', ['id' => $id]),
                'btnStyle' => 'primary',
                'icon' => 'fa fa-list-ul'
            ],
            '删除' => [
                'auth' => 'news/articledel',
                'href' => "javascript:articleDel(" . $id . ")",
                'btnStyle' => 'danger',
                'icon' => 'fa fa-trash-o'
            ],
            '停用' => [
                'auth' => 'news/articlestop',
                'href' => "javascript:articleStop(" . $id . ")",
                'btnStyle' => 'danger',
                'icon' => 'fa fa-stop'
            ]
        ];
        if ($status == 0) {
            unset($data['停用']);
            $data['启用'] = [
                'auth' => 'news/articlestart',
                'href' => "javascript:articleStart(" . $id . ")",
                'btnStyle' => 'success',
                'icon' => 'fa fa-play'
            ];
        }
        return $data;
    }
}