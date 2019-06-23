<?php

namespace app\ms_admin\controller;

use app\ms_admin\model\BannerModel;
use app\ms_admin\model\CountryTypeModel;
use app\ms_admin\model\GoodsModel;
use app\ms_admin\model\GoodsTypeModel;
use app\ms_admin\model\GuijiModel;
use app\ms_admin\model\OrderModel;
use app\ms_admin\model\ProductsImgModel;
use app\ms_admin\model\ProductsModel;
use app\ms_admin\model\SpecModel;
use app\ms_admin\model\WucYuqiLogModel;
use think\Controller;
use think\Db;
use think\Request;

class Mall extends Base
{
    /**
     * 轮播图管理 列表
     */
    public function banner()
    {
        if (request()->isAjax()) {
            $param = input('param.');
            $flag = $this->validate($param, 'MallValidate.banner');
            if (true !== $flag) {
                return json(msg(-1, '', $flag));
            }

            $limit = $param['pageSize'];
            $offset = ($param['pageNumber'] - 1) * $limit;

            $where = [];
            if (!empty($param['searchText'])) {
                $where['title'] = ['like', '%' . str_fun($param['searchText']) . '%'];
            }

            $banner = new BannerModel();
            $selectResult = $banner->getBannerByWhere($where, $offset, $limit);

            foreach ($selectResult as $key => $vo) {
                $selectResult[$key]['img'] = '<img src="' . $vo['img'] . '" width="40px" height="40px">';
                $selectResult[$key]['operate'] = showOperate($this->makeBannerButton($vo['id']));
            }

            $return['total'] = $banner->getAllBanner($where);  // 总数据
            $return['rows'] = $selectResult;

            return json($return);
        }

        return $this->fetch();
    }

    /**
     * 轮播图管理 新增
     */
    public function bannerAdd()
    {
        if (request()->isPost()) {
            $param = input('param.');
            $flag = $this->validate($param, 'MallValidate.bannerAdd');
            if (true !== $flag) {
                return json(msg(-1, '', $flag));
            }
            $param['title'] = str_fun($param['title']);
            $param['type'] = str_fun($param['type']);
            $param['img'] = request()->domain() . str_fun($param['img']);
            $param['addtime'] = date('Y-m-d H:i:s');
            unset($param['file']);

            $banner = new BannerModel();
            $result = $banner->addBanner($param);

            return json($result);
        }

        return $this->fetch();
    }

    /**
     * 轮播图管理 编辑
     */
    public function bannerEdit()
    {
        $banner = new BannerModel();
        if (request()->isPost()) {
            $param = input('param.');
            $result = $this->validate($param, 'MallValidate.bannerEdit');
            if (true !== $result) {
                return json(msg(-1, '', $result));
            }
            $param['title'] = str_fun($param['title']);
            $param['type'] = str_fun($param['type']);
            $banner_list = $banner->getOneBanner($param['banner_id'], 'id,img');
            if ($param['img'] != $banner_list['img']) {
                $param['img'] = request()->domain() . str_fun($param['img']);
            }
            unset($param['file']);
            $rec = $banner->editBanner($param);

            return json($rec);
        }
        $post_data = input('param.');
        $flag = $this->validate($post_data, 'MallValidate.bannerIndex');
        if (true !== $flag) {
            return json(msg(-1, '', $flag));
        }
        $this->assign('banner', $banner->getOneBanner($post_data['banner_id'], 'id,title,url,type,img,sort'));

        return $this->fetch();
    }

    /**
     * 轮播图删除
     */
    public function bannerDel()
    {
        $param = input('param.');
        $flag = $this->validate($param, 'MallValidate.bannerIndex');
        if (true !== $flag) {
            return json(msg(-1, '', $flag));
        }
        $banner = new BannerModel();
        $result = $banner->delBanner($param['banner_id']);

        return json($result);
    }

    /**
     * 轮播图管理 上传图片
     */
    public function bannerUploadImg()
    {
        if (request()->isAjax()) {
            $file = request()->file('file');
            // 移动到框架应用根目录/public/uploads/ 目录下
            $info = $file->validate(['size' => 156780, 'ext' => 'jpg,png,gif'])->move(ROOT_PATH . 'public' . DS . 'uploads' . DS . 'banner');
            if ($info) {
                $src = '/uploads' . '/banner' . '/' . date('Ymd') . '/' . $info->getFilename();
                return json(msg(0, ['src' => $src], ''));
            } else {
                // 上传失败获取错误信息
                return json(msg(-1, '', $file->getError()));
            }
        }
    }

    /**
     * 轮播图拼装操作按钮
     * @param $id
     * @return array
     */
    private function makeBannerButton($id)
    {
        return [
            '编辑' => [
                'auth'     => 'mall/banneredit',
                'href'     => url('mall/banneredit', ['banner_id' => $id]),
                'btnStyle' => 'primary',
                'icon'     => 'fa fa-paste'
            ],
            '删除' => [
                'auth'     => 'mall/bannerdel',
                'href'     => "javascript:bannerDel(" . $id . ")",
                'btnStyle' => 'danger',
                'icon'     => 'fa fa-trash-o'
            ]
        ];
    }

    /**
     * 商品国家分类管理 列表
     */
    public function country()
    {
        if (request()->isAjax()) {
            $param = input('param.');
            $flag = $this->validate($param, 'MallValidate.country');
            if (true !== $flag) {
                return json(msg(-1, '', $flag));
            }

            $limit = $param['pageSize'];
            $offset = ($param['pageNumber'] - 1) * $limit;

            $where = [];
            if (!empty($param['searchText'])) {
                $where['name'] = ['like', '%' . str_fun($param['searchText']) . '%'];
            }
            if (!empty($param['status'])) {
                $where['status'] = intval($param['status']);
            }

            $country = new CountryTypeModel();
            $selectResult = $country->getCountryByWhere($where, $offset, $limit);

            foreach ($selectResult as $key => $vo) {
                $selectResult[$key]['img'] = '<img src="' . $vo['img'] . '" width="40px" height="40px">';
                $selectResult[$key]['status'] = $vo['status'] == 1 ? '启用' : '禁用';
                $selectResult[$key]['operate'] = showOperate($this->makeCountryButton($vo['id']));
            }

            $return['total'] = $country->getAllCountry($where);  // 总数据
            $return['rows'] = $selectResult;

            return json($return);
        }

        return $this->fetch();
    }

    /**
     * 国家分类管理 新增
     */
    public function countryAdd()
    {
        if (request()->isPost()) {
            $param = input('param.');
            $flag = $this->validate($param, 'MallValidate.countryAdd');
            if (true !== $flag) {
                return json(msg(-1, '', $flag));
            }
            $param['sortid'] = $param['sort'];
            $param['name'] = str_fun($param['name']);
            $param['img'] = request()->domain() . str_fun($param['img']);
            $param['lrdate'] = date('Y-m-d H:i:s');
            unset($param['file']);

            $country = new CountryTypeModel();
            $result = $country->addCountry($param);

            return json($result);
        }

        return $this->fetch();
    }

    /**
     * 国家分类管理 编辑
     */
    public function countryEdit()
    {
        $country = new CountryTypeModel();
        if (request()->isPost()) {
            $param = input('param.');
            $result = $this->validate($param, 'MallValidate.countryEdit');
            if (true !== $result) {
                return json(msg(-1, '', $result));
            }
            $param['sortid'] = $param['sort'];
            $param['name'] = str_fun($param['name']);
            $country_list = $country->getOneCountry($param['country_id'], 'id,img');
            if ($param['img'] != $country_list['img']) {
                $param['img'] = request()->domain() . str_fun($param['img']);
            }
            unset($param['file']);
            $rec = $country->editCountry($param);

            return json($rec);
        }
        $post_data = input('param.');
        $flag = $this->validate($post_data, 'MallValidate.countryIndex');
        if (true !== $flag) {
            return json(msg(-1, '', $flag));
        }
        $this->assign('country', $country->getOneCountry($post_data['country_id'], 'id,name,sortid,status,img,lrdate'));

        return $this->fetch();
    }

    /**
     * 国家分类管理 删除
     */
    public function countryDel()
    {
        $param = input('param.');
        $flag = $this->validate($param, 'MallValidate.countryDel');
        if (true !== $flag) {
            return json(msg(-1, '', $flag));
        }
        $country = new CountryTypeModel();
        $result = $country->delCountry($param['country_del_id']);

        return json($result);
    }

    /**
     * 商品国家分类管理 上传图片
     */
    public function countryUploadImg()
    {
        if (request()->isAjax()) {
            $file = request()->file('file');
            // 移动到框架应用根目录/public/uploads/ 目录下
            $info = $file->validate(['size' => 156780, 'ext' => 'jpg,png,gif'])->move(ROOT_PATH . 'public' . DS . 'uploads' . DS . 'goods_type');
            if ($info) {
                $src = '/uploads' . '/goods_type' . '/' . date('Ymd') . '/' . $info->getFilename();
                return json(msg(0, ['src' => $src], ''));
            } else {
                // 上传失败获取错误信息
                return json(msg(-1, '', $file->getError()));
            }
        }
    }

    /**
     * 国家分类拼装操作按钮
     * @param $id
     * @return array
     */
    private function makeCountryButton($id)
    {
        return [
            '编辑' => [
                'auth'     => 'mall/countryedit',
                'href'     => url('mall/countryedit', ['country_id' => $id]),
                'btnStyle' => 'primary',
                'icon'     => 'fa fa-paste'
            ],
            '删除' => [
                'auth'     => 'mall/countrydel',
                'href'     => "javascript:countryDel(" . $id . ")",
                'btnStyle' => 'danger',
                'icon'     => 'fa fa-trash-o'
            ]
        ];
    }

    /**
     * 商品分类管理 列表
     */
    public function goodstype()
    {
        if (request()->isAjax()) {
            $param = input('param.');
            $flag = $this->validate($param, 'MallValidate.goodstype');
            if (true !== $flag) {
                return json(msg(-1, '', $flag));
            }

            $limit = $param['pageSize'];
            $offset = ($param['pageNumber'] - 1) * $limit;

            $where = [];
            if (!empty($param['searchText'])) {
                $where['typename'] = ['like', '%' . str_fun($param['searchText']) . '%'];
            }
            if (!empty($param['status'])) {
                $where['status'] = intval($param['status']);
            }

            $goods_type = new GoodsTypeModel();
            $selectResult = $goods_type->getGoodsTypeByWhere($where, $offset, $limit);

            foreach ($selectResult as $key => $vo) {
                $selectResult[$key]['status'] = $vo['status'] == 1 ? '启用' : '禁用';
                $selectResult[$key]['operate'] = showOperate($this->makeGoodsTypeButton($vo['id']));
            }

            $return['total'] = $goods_type->getAllGoodsType($where);  // 总数据
            $return['rows'] = $selectResult;

            return json($return);
        }

        return $this->fetch();
    }

    /**
     * 商品分类管理 新增
     */
    public function goodstypeAdd()
    {
        if (request()->isPost()) {
            $param = input('param.');
            $flag = $this->validate($param, 'MallValidate.goodstypeAdd');
            if (true !== $flag) {
                return json(msg(-1, '', $flag));
            }
            $param['sortid'] = $param['sort'];
            $param['typeid'] = str_fun($param['typeid']);
            $param['typename'] = str_fun($param['typename']);
            $param['lrdate'] = date('Y-m-d H:i:s');
            unset($param['file']);

            $goods_type = new GoodsTypeModel();
            $result = $goods_type->addGoodsType($param);

            return json($result);
        }

        return $this->fetch();
    }

    /**
     * 商品分类管理 编辑
     */
    public function goodstypeEdit()
    {
        $goods_type = new GoodsTypeModel();
        if (request()->isPost()) {
            $param = input('param.');
            $result = $this->validate($param, 'MallValidate.goodstypeEdit');
            if (true !== $result) {
                return json(msg(-1, '', $result));
            }
            $param['sortid'] = $param['sort'];
            $param['typeid'] = str_fun($param['type_id']);
            $param['typename'] = str_fun($param['typename']);
            unset($param['file']);
            $rec = $goods_type->editGoodsType($param);

            return json($rec);
        }
        $post_data = input('param.');
        $flag = $this->validate($post_data, 'MallValidate.goodstypeIndex');
        if (true !== $flag) {
            return json(msg(-1, '', $flag));
        }
        $this->assign('goods_type', $goods_type->getOneGoodsType($post_data['goods_type_id'], 'id,typeid,typename,sortid,status'));

        return $this->fetch();
    }

    /**
     * 商品分类管理 删除
     */
    public function goodstypeDel()
    {
        $param = input('param.');
        $flag = $this->validate($param, 'MallValidate.goodstypeDel');
        if (true !== $flag) {
            return json(msg(-1, '', $flag));
        }
        $goods_type = new GoodsTypeModel();
        $result = $goods_type->delGoodsType($param['goods_type_del_id']);

        return json($result);
    }

    /**
     * 商品分类拼装操作按钮
     * @param $id
     * @return array
     */
    private function makeGoodsTypeButton($id)
    {
        return [
//            '编辑' => [
//                'auth'     => 'mall/goodstypeedit',
//                'href'     => url('mall/goodstypeedit', ['goods_type_id' => $id]),
//                'btnStyle' => 'primary',
//                'icon'     => 'fa fa-paste'
//            ],
            '删除' => [
                'auth'     => 'mall/goodstypedel',
                'href'     => "javascript:goodstypeDel(" . $id . ")",
                'btnStyle' => 'danger',
                'icon'     => 'fa fa-trash-o'
            ]
        ];
    }

    /**
     * 商品规格管理 列表
     */
    public function spec()
    {
        if (request()->isAjax()) {
            $param = input('param.');
            $flag = $this->validate($param, 'MallValidate.spec');
            if (true !== $flag) {
                return json(msg(-1, '', $flag));
            }

            $limit = $param['pageSize'];
            $offset = ($param['pageNumber'] - 1) * $limit;

            $where = [];
            if (!empty($param['searchText'])) {
                $where['name'] = ['like', '%' . str_fun($param['searchText']) . '%'];
            }
            $where['is_del'] = 0;

            $spec = new SpecModel();
            $selectResult = $spec->getSpecByWhere($where, $offset, $limit);

            foreach ($selectResult as $key => $vo) {
                $value = json_decode($vo['value'], true);
                $value_str = '';
                foreach ($value as $k => $v) {
                    $value_str .= '描述：' . $k . '；规格值：' . $v . ' | ';
                }
                $selectResult[$key]['value'] = trim($value_str, ' | ');
                $selectResult[$key]['operate'] = showOperate($this->makeSpecButton($vo['id']));
            }

            $return['total'] = $spec->getAllSpec($where);  // 总数据
            $return['rows'] = $selectResult;

            return json($return);
        }

        return $this->fetch();
    }

    /**
     * 商品规格管理 新增
     */
    public function specAdd()
    {
        if (request()->isPost()) {
            $param = input('param.');
            $param['ver_status'] = 1;
            $flag = $this->validate($param, 'MallValidate.specAdd');
            if (true !== $flag) {
                return json(msg(-1, '', $flag));
            }
            $spec_arr = [];
            $spec_memo = $param['spec_memo'];
            $spec_num = $param['spec_num'];
            $count_num = count($param['spec_memo']);
            for ($i = 0; $i < $count_num; $i++) {
                $spec_arr[str_fun($spec_memo[$i])] = str_fun($spec_num[$i]);
            }
            $spec_str = json_encode($spec_arr, JSON_UNESCAPED_UNICODE);
            $data = [
                'name'  => $param['spec_name'],
                'value' => $spec_str,
                'type'  => 1
            ];
            $spec = new SpecModel();
            $result = $spec->addSpec($data);

            return json($result);
        }

        return $this->fetch();
    }

    /**
     * 商品规格管理 编辑
     */
    public function specEdit()
    {
        $spec = new SpecModel();
        if (request()->isPost()) {
            $param = input('param.');
            $param['ver_status'] = 2;
            $result = $this->validate($param, 'MallValidate.specEdit');
            if (true !== $result) {
                return json(msg(-1, '', $result));
            }
            $spec_arr = [];
            $spec_memo = $param['spec_memo'];
            $spec_num = $param['spec_num'];
            $count_num = count($param['spec_memo']);
            for ($i = 0; $i < $count_num; $i++) {
                $spec_arr[str_fun($spec_memo[$i])] = str_fun($spec_num[$i]);
            }
            $spec_str = json_encode($spec_arr, JSON_UNESCAPED_UNICODE);
            $data = [
                'name'  => $param['spec_name'],
                'value' => $spec_str,
                'type'  => 1,
                'id'    => $param['spec_id']
            ];
            $spec = new SpecModel();
            $result = $spec->editSpec($data);

            return json($result);
        }
        $post_data = input('param.');
        $flag = $this->validate($post_data, 'MallValidate.specIndex');
        if (true !== $flag) {
            return json(msg(-1, '', $flag));
        }
        $spec_list = $spec->getOneSpec($post_data['spec_id'], 'id,name,value');
        $spec_list['value'] = json_decode($spec_list['value'], true);
        $this->assign('spec', $spec_list);

        return $this->fetch();
    }

    /**
     * 商品规格管理 删除
     */
    public function specDel()
    {
        $param = input('param.');
        $flag = $this->validate($param, 'MallValidate.specDel');
        if (true !== $flag) {
            return json(msg(-1, '', $flag));
        }
        $spec = new SpecModel();
        $result = $spec->delSpec($param['spec_id']);

        return json($result);

    }

    /**
     * 商品规格拼装操作按钮
     * @param $id
     * @return array
     */
    private function makeSpecButton($id)
    {
        return [
            '编辑' => [
                'auth'     => 'mall/specedit',
                'href'     => url('mall/specedit', ['spec_id' => $id]),
                'btnStyle' => 'primary',
                'icon'     => 'fa fa-paste'
            ],
//            '删除' => [
//                'auth'     => 'mall/specDel',
//                'href'     => "javascript:specDel(" . $id . ")",
//                'btnStyle' => 'danger',
//                'icon'     => 'fa fa-trash-o'
//            ]
        ];
    }

    /**
     * 商品管理 列表
     * @throws \Exception
     */
    public function goods()
    {
        if (request()->isAjax()) {
            $param = input('param.');
            $flag = $this->validate($param, 'MallValidate.goods');
            if (true !== $flag) {
                return json(msg(-1, '', $flag));
            }

            $limit = $param['pageSize'];
            $offset = ($param['pageNumber'] - 1) * $limit;

            $where = [];
            $where['is_del'] = ['neq', 1];
            if (!empty($param['searchText'])) {
                $where['name'] = ['like', '%' . str_fun($param['searchText']) . '%'];
            }
            if (!empty($param['status'])) {
                $where['is_del'] = intval($param['status']) == -1 ? 0 : intval($param['status']);
            }
            if (!empty($param['goods_type'])) {
                $where['goods_type_id'] = intval($param['goods_type']);
            }
            if (!empty($param['country_type'])) {
                $where['county_type_id'] = intval($param['country_type']);
            }
            $goods = new GoodsModel();
            $selectResult = $goods->getGoodsByWhere($where, $offset, $limit);
            foreach ($selectResult as $key => $vo) {
                $buttons = $this->makeGoodsButton($vo['id']);
                if ('已删除' == $vo['is_del']) {
                    unset($buttons['删除']);
                } elseif ('上架' == $vo['is_del']) {
                    unset($buttons['上架']);
                } elseif ('下架' == $vo['is_del']) {
                    unset($buttons['下架']);
                }
                $selectResult[$key]['img'] = '<img src="' . $vo['img'] . '" width="40px" height="40px">';
                $selectResult[$key]['operate'] = showOperate($buttons);
            }

            $return['total'] = $goods->getAllGoods($where);  // 总数据
            $return['rows'] = $selectResult;

            return json($return);
        }
        $country = Db::table('t_shop_country_type')->field('id,name')->where('status', 'eq', 1)->select();
        $goods_type = Db::table('t_shop_goods_type')->field('id,typename')->where('status', 'eq', 1)->select();
        $this->assign(['country' => $country, 'goods_type' => $goods_type]);

        return $this->fetch();
    }

    /**
     * 商品管理 新增
     * @throws \Exception
     */
    public function goodsAdd()
    {
        if (request()->isPost()) {
            $param = input('param.');
            $flag = $this->validate($param, 'MallValidate.goodsAdd');
            if (true !== $flag) {
                return json(msg(-1, '', $flag));
            }
//            $param['img'] = str_fun($param['img']);
//            foreach ($param['pc_src'] as $key => $value) {
//                $param['pc_src'][$key] = str_fun($value);
//            }
            $goods_spec_array = []; //商品规格信息
            $goods_spec_list = [];
            $products_spec_array = []; //货品规格信息
            foreach ($param['spec_val'] as $key => $value) {
                $spec_list = explode(',', $value);
                $products_spec_list = [];
                foreach ($spec_list as $k => $v) {
                    $first_spec_list = explode('|', $v);
                    $products_spec_list[$k] = [
                        'id'    => str_fun($first_spec_list[0]),
                        'type'  => "1",
                        'value' => str_fun($first_spec_list[3]),
                        'name'  => str_fun($first_spec_list[1]),
                        'tip'   => str_fun($first_spec_list[2])
                    ];
                }
                $products_spec_array[$key] = json_encode($products_spec_list, JSON_UNESCAPED_UNICODE);
                $goods_spec_list[$key] = $spec_list;
            }
            $goods_make_data = [];
            $i = 0;
            foreach ($goods_spec_list as $key => $val) {
                foreach ($val as $k => $v) {
                    $goods_make_data[$i] = $v;
                    $i++;
                }
            }
            $goods_make_data = array_unique($goods_make_data);
            $goods_make_list = [];
            foreach ($goods_make_data as $j => $s) {
                $spec_arr = explode('|', $s);
                $goods_make_list[$spec_arr[0]]['id'] = str_fun($spec_arr[0]);
                $goods_make_list[$spec_arr[0]]['name'] = str_fun($spec_arr[1]);
                $goods_make_list[$spec_arr[0]]['type'] = '1';
                $goods_make_list[$spec_arr[0]]['value'][str_fun($spec_arr[2])] = str_fun($spec_arr[3]);
            }
            $c = 1;
            foreach ($goods_make_list as $ss => $vs) {
                $goods_spec_array[$c] = $vs;
                $c++;
            }
            //开启事务
            Db::startTrans();
            try {
                //存入商品
                $goods_type = Db::table('t_shop_goods_type')->field('typename')->where('id', 'eq', intval($param['goods_type_id']))->find();
                $county_type = Db::table('t_shop_country_type')->field('name')->where('id', 'eq', intval($param['county_type_id']))->find();
                $goods_data = [
                    'goods_type_id'    => intval($param['goods_type_id']),
                    'goods_type_name'  => str_fun($goods_type['typename']),
                    'name'             => str_fun($param['goods_name']),
                    'title'            => str_fun($param['goods_name']),
                    'pay_type'         => str_fun($param['pay_type']),
                    'sell_price'       => floatval($param['sell_price']),
                    'market_price'     => floatval($param['market_price']),
                    'rebate_num'       => floatval($param['rebate_num']),
                    'store_nums'       => intval($param['store_nums']),
                    'img'              => $param['img'],
                    'is_del'           => 0,
                    'content'          => filter_keyword($param['content']),
                    'is_delivery_fee'  => intval($param['is_delivery_fee']),
                    'delivery_fee'     => floatval($param['delivery_fee']),
                    'create_time'      => date('Y-m-d H:i:s'),
                    'spec_array'       => json_encode($goods_spec_array, JSON_UNESCAPED_UNICODE),
                    'county_type_id'   => intval($param['county_type_id']),
                    'county_type_name' => str_fun($county_type['name'])
                ];
                $goods_rec = Db::table('t_shop_goods')->insert($goods_data);
                $goods_id = Db::table('t_shop_goods')->getLastInsID();
                //存入货品
                $products_data = [];
                foreach ($products_spec_array as $key => $value) {
                    $products_data[$key] = [
                        'goods_id'     => $goods_id,
                        'spec_array'   => $value,
                        'store_nums'   => $param['products_store_nums'][$key],
                        'market_price' => $param['products_market_price'][$key],
                        'sell_price'   => $param['products_sell_price'][$key]
                    ];
                }
                $products_rec = Db::table('t_shop_products')->insertAll($products_data);
                //存入商品相册
                $goods_photo_data = [];
                foreach ($param['pc_src'] as $key => $value) {
                    $goods_photo_data[$key] = [
                        'goods_id' => $goods_id,
                        'img'      => $value
                    ];
                }
                $goods_photo_rec = Db::table('t_shop_goods_photo')->insertAll($goods_photo_data);
                if (!$goods_rec || !$products_rec || !$goods_photo_rec) {
                    Db::rollback();
                    return json(msg(-1, '', '添加商品失败'));
                }

                Db::commit();
                return json(msg(1, url('mall/goods'), '添加商品成功'));
            } catch (\Exception $e) {
                Db::rollback();
                return json(msg(-1, '', $e->getMessage()));
            }
        }
        $spec = Db::table('t_shop_spec')->field('id,name,value')->where('is_del', 'eq', 0)->select()->toArray();
        foreach ($spec as $key => $value) {
            $spec[$key]['value'] = json_decode($value['value'], true);
        }
        $country = Db::table('t_shop_country_type')->field('id,name')->where('status', 'eq', 1)->select();
        $goods_type = Db::table('t_shop_goods_type')->field('id,typename')->where('status', 'eq', 1)->select();
        $this->assign('spec', $spec);
        $this->assign('country', $country);
        $this->assign('goods_type', $goods_type);

        return $this->fetch();
    }

    /**
     * 商品管理 编辑
     * @throws \Exception
     */
    public function goodsEdit()
    {
        if (request()->isPost()) {
            $param = input('param.');
            $flag = $this->validate($param, 'MallValidate.goodsEdit');
            if (true !== $flag) {
                return json(msg(-1, '', $flag));
            }
            $goods_list = Db::table('t_shop_goods')->where('id', 'eq', intval($param['goods_id']))->find();
            if ($goods_list['img'] != $goods_list['img']) {
                $param['img'] = str_fun($param['img']);
            }
//            foreach ($param['pc_src'] as $key => $value) {
//                $param['pc_src'][$key] = str_fun($value);
//            }
            $goods_spec_array = []; //商品规格信息
            $goods_spec_list = [];
            $products_spec_array = []; //货品规格信息
            foreach ($param['spec_val'] as $key => $value) {
                $spec_list = explode(',', $value);
                $products_spec_list = [];
                foreach ($spec_list as $k => $v) {
                    $first_spec_list = explode('|', $v);
                    $products_spec_list[$k] = [
                        'id'    => str_fun($first_spec_list[0]),
                        'type'  => "1",
                        'value' => str_fun($first_spec_list[3]),
                        'name'  => str_fun($first_spec_list[1]),
                        'tip'   => str_fun($first_spec_list[2])
                    ];
                }
                $products_spec_array[$key] = json_encode($products_spec_list, JSON_UNESCAPED_UNICODE);
                $goods_spec_list[$key] = $spec_list;
            }
            $goods_make_data = [];
            $i = 0;
            foreach ($goods_spec_list as $key => $val) {
                foreach ($val as $k => $v) {
                    $goods_make_data[$i] = $v;
                    $i++;
                }
            }
            $goods_make_data = array_unique($goods_make_data);
            $goods_make_list = [];
            foreach ($goods_make_data as $j => $s) {
                $spec_arr = explode('|', $s);
                $goods_make_list[$spec_arr[0]]['id'] = str_fun($spec_arr[0]);
                $goods_make_list[$spec_arr[0]]['name'] = str_fun($spec_arr[1]);
                $goods_make_list[$spec_arr[0]]['type'] = '1';
                $goods_make_list[$spec_arr[0]]['value'][str_fun($spec_arr[2])] = str_fun($spec_arr[3]);
            }
            $c = 1;
            foreach ($goods_make_list as $ss => $vs) {
                $goods_spec_array[$c] = $vs;
                $c++;
            }
            $products_img = $param['products_img'];
            $products_img_list = [];
            foreach ($products_img as $key => $value) {
                $img_list = Db::table('t_shop_products_img')->field('id,img')->where('id', 'eq', $value)->find();
                $products_img_list[$key]['id'] = $img_list['id'];
                $products_img_list[$key]['img'] = $img_list['img'];
            }
            //开启事务
            Db::startTrans();
            try {
                //修改商品
                $goods_type = Db::table('t_shop_goods_type')->field('typename')->where('id', 'eq', intval($param['goods_type_id']))->find();
                $county_type = Db::table('t_shop_country_type')->field('name')->where('id', 'eq', intval($param['county_type_id']))->find();
                $goods_data = [
                    'goods_type_id'    => intval($param['goods_type_id']),
                    'goods_type_name'  => str_fun($goods_type['typename']),
                    'name'             => str_fun($param['goods_name']),
                    'title'            => str_fun($param['goods_name']),
                    'pay_type'         => str_fun($param['pay_type']),
                    'sell_price'       => floatval($param['sell_price']),
                    'market_price'     => floatval($param['market_price']),
                    'rebate_num'       => floatval($param['rebate_num']),
                    'store_nums'       => intval($param['store_nums']),
                    'img'              => $param['img'],
                    'content'          => filter_keyword($param['content']),
                    'is_delivery_fee'  => intval($param['is_delivery_fee']),
                    'delivery_fee'     => floatval($param['delivery_fee']),
                    'spec_array'       => json_encode($goods_spec_array, JSON_UNESCAPED_UNICODE),
                    'county_type_id'   => intval($param['county_type_id']),
                    'county_type_name' => str_fun($county_type['name'])
                ];
                $goods_rec = Db::table('t_shop_goods')->where('id', 'eq', intval($param['goods_id']))->update($goods_data);
                //删除货品
//                $products_del_rec = Db::table('t_shop_products')->where('goods_id', 'eq', intval($param['goods_id']))->delete();
                //查询未修改前货品ID
                $products_ids_list = Db::table('t_shop_products')->field('id')->where('goods_id', 'eq', intval($param['goods_id']))->select();
                $total_products_id = [];
                foreach ($products_ids_list as $key => $value) {
                    $total_products_id[$key] = $value['id'];
                }

                //根据货品ID查询货品信息
                if (!isset($param['products_ids'])) {
                    $param['products_ids'] = [];
                }
                $up_products_ids = [];
                $no_products_ids = [];
                $del_products_ids = array_diff($total_products_id, $param['products_ids']);
                foreach ($param['products_ids'] as $key => $value) {
                    $products_sel_list = Db::table('t_shop_products')->where('id', 'eq', $value)->find();
                    if ($param['products_market_price'][$key] != $products_sel_list['market_price'] || $param['products_store_nums'][$key] != $products_sel_list['store_nums'] || $param['products_sell_price'][$key] != $products_sel_list['sell_price'] || $param['products_img'][$key] != $products_sel_list['products_img_id']) {
                        $up_products_ids[$key] = $products_sel_list['id'];
                    } else {
                        $no_products_ids[$key] = $products_sel_list['id'];
                    }
                }
                //删除取消货品
                if (!empty($del_products_ids)) {
                    $del_products_id_str = implode(',', $del_products_ids);
                    $products_del_rec = Db::table('t_shop_products')->where('id', 'in', $del_products_id_str)->delete();
                } else {
                    $products_del_rec = true;
                }

                //编辑存入货品
                $products_data = [];
                $ver_products_up_status = 0;
                $sss = 0;
                foreach ($products_spec_array as $key => $value) {
                    if (isset($up_products_ids[$key])) {
                        $products_up_data = [
                            'goods_id'        => intval($param['goods_id']),
                            'spec_array'      => $value,
                            'store_nums'      => $param['products_store_nums'][$key],
                            'market_price'    => $param['products_market_price'][$key],
                            'sell_price'      => $param['products_sell_price'][$key],
                            'products_img_id' => $products_img_list[$key]['id'],
                            'products_img'    => $products_img_list[$key]['img']
                        ];
                        $products_up_rec = Db::table('t_shop_products')->where('id', 'eq', $up_products_ids[$key])->update($products_up_data);
                        if (!$products_up_rec) {
                            $ver_products_up_status = 1;
                            break;
                        }
                    } elseif (!isset($no_products_ids[$key])) {
                        $products_data[$sss] = [
                            'goods_id'        => intval($param['goods_id']),
                            'spec_array'      => $value,
                            'store_nums'      => $param['products_store_nums'][$key],
                            'market_price'    => $param['products_market_price'][$key],
                            'sell_price'      => $param['products_sell_price'][$key],
                            'products_img_id' => $products_img_list[$key]['id'],
                            'products_img'    => $products_img_list[$key]['img']
                        ];
                        $sss++;
                    }
                }
                if (0 != $ver_products_up_status) {
                    Db::rollback();
                    return json(msg(-1, '', '编辑货品信息失败'));
                }
                if (!empty($products_data)) {
                    $products_rec = Db::table('t_shop_products')->insertAll($products_data);
                } else {
                    $products_rec = true;
                }
                //删除商品相册
                $goods_photo_del_rec = Db::table('t_shop_goods_photo')->where('goods_id', 'eq', intval($param['goods_id']))->delete();
                //存入商品相册
                $goods_photo_data = [];
                foreach ($param['pc_src'] as $key => $value) {
                    $goods_photo_data[$key] = [
                        'goods_id' => intval($param['goods_id']),
                        'img'      => $value
                    ];
                }
                $goods_photo_rec = Db::table('t_shop_goods_photo')->insertAll($goods_photo_data);
                if ((!$goods_rec && 0 <> $goods_rec) || !$products_rec || !$goods_photo_rec || !$goods_photo_del_rec || !$products_del_rec) {
                    Db::rollback();
                    return json(msg(-1, '', '编辑商品失败'));
                }

                Db::commit();
                return json(msg(1, url('mall/goods'), '编辑商品成功'));
            } catch (\Exception $e) {
                Db::rollback();
                return json(msg(-1, '', $e->getMessage()));
            }
        }
        $post_data = input('param.');
        $flag = $this->validate($post_data, 'MallValidate.goodsIndex');
        if (true !== $flag) {
            return json(msg(-1, '', $flag));
        }
        $goods = Db::table('t_shop_goods')->where('id', 'eq', intval($post_data['goods_id']))->find();
        $products = Db::table('t_shop_products')->where('goods_id', 'eq', intval($post_data['goods_id']))->select()->toArray();
        $photos = Db::table('t_shop_goods_photo')->where('goods_id', 'eq', intval($post_data['goods_id']))->select();
        $country = Db::table('t_shop_country_type')->field('id,name')->where('status', 'eq', 1)->select();
        $goods_type = Db::table('t_shop_goods_type')->field('id,typename')->where('status', 'eq', 1)->select();
        $spec = Db::table('t_shop_spec')->field('id,name,value')->where('is_del', 'eq', 0)->select()->toArray();
        $products_img = Db::table('t_shop_products_img')->field('id,img_name,img')->where('status', 'eq', 1)->where('goods_id', 'eq', intval($post_data['goods_id']))->select();
        $ver_status_data = [0, 3];
        if (!in_array($goods['is_del'], $ver_status_data)) {
            $this->error('请让商品下架后再次编辑商品');
        }
        foreach ($spec as $key => $value) {
            $spec[$key]['value'] = json_decode($value['value'], true);
        }
        foreach ($products as $key => $value) {
            $spec_list = json_decode($value['spec_array'], true);
            $spec_arr = '';
            $spec_str = '';
            foreach ($spec_list as $k => $v) {
                $spec_arr .= $v['name'] . ':' . $v['tip'] . ';';
                $spec_str .= $v['id'] . '|' . $v['name'] . '|' . $v['tip'] . '|' . $v['value'] . ',';
            }
            $spec_str = trim($spec_str, ',');
            $products[$key]['spec'] = $spec_arr;
            $products[$key]['spec_str'] = $spec_str;
        }
        $this->assign(['goods' => $goods, 'products' => $products, 'photos' => $photos, 'goods_type' => $goods_type, 'country' => $country, 'spec' => $spec, 'products_img' => $products_img]);

        return $this->fetch();
    }

    /**
     * 商品管理 删除
     */
    public function goodsDel()
    {
        $param = input('param.');
        $flag = $this->validate($param, 'MallValidate.goodsIndex');
        if (true !== $flag) {
            return json(msg(-1, '', $flag));
        }
        $goods = new GoodsModel();
        $result = $goods->delGoods(intval($param['goods_id']), 1);

        return json($result);
    }

    /**
     * 商品管理 上架
     */
    public function goodsUp()
    {
        $param = input('param.');
        $flag = $this->validate($param, 'MallValidate.goodsIndex');
        if (true !== $flag) {
            return json(msg(-1, '', $flag));
        }
        $goods = new GoodsModel();
        $result = $goods->delGoods(intval($param['goods_id']), 2);

        return json($result);
    }

    /**
     * 商品管理 下架
     */
    public function goodsDown()
    {
        $param = input('param.');
        $flag = $this->validate($param, 'MallValidate.goodsIndex');
        if (true !== $flag) {
            return json(msg(-1, '', $flag));
        }
        $goods = new GoodsModel();
        $result = $goods->delGoods(intval($param['goods_id']), 3);

        return json($result);
    }

    /**
     * 商品管理 规格
     */
    public function goodsSpec()
    {
        $param = input('param.');
        $flag = $this->validate($param, 'MallValidate.goodsSpec');
        if (true !== $flag) {
            return json(msg(-1, '', $flag));
        }
        $spec = new SpecModel();
        $spec_list = $spec->getOneSpec($param['spec_id'], 'id,name,value');
        $spec_value = json_decode($spec_list['value'], true);

        return json(msg(1, ['spec_value' => $spec_value, 'spec_id' => $param['spec_id']], 'success'));
    }

    /**
     * 商品管理 图片上传[多图]
     * @return \think\response\Json
     */
    public function goodsUploadImage()
    {
        if (request()->isAjax()) {
            $file = request()->file('file');
            // 移动到框架应用根目录/public/uploads/ 目录下
            $info = $file->validate(['size' => 156780, 'ext' => 'jpg,png,gif'])->move(ROOT_PATH . 'public' . DS . 'uploads' . DS . 'goods');
            if ($info) {
                $src = request()->domain() . '/uploads' . '/goods' . '/' . date('Ymd') . '/' . $info->getFilename();
                return json(msg(0, ['src' => $src], ''));
            } else {
                // 上传失败获取错误信息
                return json(msg(-1, '', $file->getError()));
            }
        }
    }

    /**
     * 商品管理 图片上传[单图]
     * @return \think\response\Json
     */
    public function goodsUploadImg()
    {
        if (request()->isAjax()) {
            $file = request()->file('file');
            // 移动到框架应用根目录/public/uploads/ 目录下
            $info = $file->validate(['size' => 156780, 'ext' => 'jpg,png,gif'])->move(ROOT_PATH . 'public' . DS . 'uploads' . DS . 'goods');
            if ($info) {
                $src = request()->domain() . '/uploads' . '/goods' . '/' . date('Ymd') . '/' . $info->getFilename();
                return json(msg(0, ['src' => $src], ''));
            } else {
                // 上传失败获取错误信息
                return json(msg(-1, '', $file->getError()));
            }
        }
    }

    /**
     * 商品拼装操作按钮
     * @param $id
     * @return array
     */
    private function makeGoodsButton($id)
    {
        return [
            '编辑'    => [
                'auth'     => 'mall/goodsedit',
                'href'     => url('mall/goodsedit', ['goods_id' => $id]),
                'btnStyle' => 'primary',
                'icon'     => 'fa fa-paste'
            ],
            '删除'    => [
                'auth'     => 'mall/goodsdel',
                'href'     => "javascript:goodsDel(" . $id . ")",
                'btnStyle' => 'danger',
                'icon'     => 'fa fa-trash-o'
            ],
            '上架'    => [
                'auth'     => 'mall/goodsup',
                'href'     => "javascript:goodsUp(" . $id . ")",
                'btnStyle' => 'primary',
                'icon'     => 'fa fa-arrow-up',
            ],
            '下架'    => [
                'auth'     => 'mall/goodsdown',
                'href'     => "javascript:goodsDown(" . $id . ")",
                'btnStyle' => 'warning',
                'icon'     => 'fa fa-arrow-down',
            ],
            '货品图片集' => [
                'auth'     => 'mall/productsimg',
                'href'     => url('mall/productsimg', ['goods_id' => $id]),
                'btnStyle' => 'primary',
                'icon'     => 'fa fa-tasks'
            ]
        ];
    }

    /**
     * 货品管理 货品信息
     */
    public function productsImg()
    {
        if (request()->isAjax()) {
            $param = input('param.');
            $flag = $this->validate($param, 'MallValidate.productsImg');
            if (true !== $flag) {
                return json(msg(-1, '', $flag));
            }

            $limit = $param['pageSize'];
            $offset = ($param['pageNumber'] - 1) * $limit;

            $where = [];
            if (!empty($param['searchText'])) {
                $where['img_name'] = ['like', '%' . str_fun($param['searchText']) . '%'];
            }
            if (!empty($param['status'])) {
                $where['status'] = intval($param['status']);
            }
            $where['goods_id'] = intval($param['goods_id']);
            $where['status'] = ['neq', 3];

            $products_img = new ProductsImgModel();
            $selectResult = $products_img->getProductsImgByWhere($where, $offset, $limit);

            foreach ($selectResult as $key => $vo) {
                $buttons = $this->makeProductsImgButton($vo['id']);
                if ('启用' == $vo['status']) {
                    unset($buttons['启用']);
                } elseif ('禁用' == $vo['status']) {
                    unset($buttons['禁用']);
                } elseif ('删除' == $vo['status']) {
                    unset($buttons['删除']);
                }
                $selectResult[$key]['img'] = '<img src="' . $vo['img'] . '" width="40px" height="40px">';
                $selectResult[$key]['operate'] = showOperate($buttons);
            }

            $return['total'] = $products_img->getAllProductsImg($where);  // 总数据
            $return['rows'] = $selectResult;

            return json($return);
        }
        $this->assign('goods_id', intval(input('param.goods_id/d')));

        return $this->fetch();
    }

    /**
     * 货品图片集管理 新增
     */
    public function productsimgAdd()
    {
        if (request()->isPost()) {
            $param = input('param.');
            $result = $this->validate($param, 'MallValidate.productsimgAdd');
            if (true !== $result) {
                return json(msg(-1, '', $result));
            }
            $data = [
                'goods_id'    => $param['goods_id'],
                'img_name'    => str_fun($param['img_name']),
                'img'         => $param['img'],
                'status'      => 1,
                'create_time' => date('Y-m-d H:i:s')
            ];
            $products_img = new ProductsImgModel();
            $result = $products_img->addProductsImg($data);

            return json($result);
        }
        $post_data = input('param.');
        $flag = $this->validate($post_data, 'MallValidate.productsimgIndex');
        if (true !== $flag) {
            return json(msg(-1, '', $flag));
        }
        $this->assign('goods_id', intval($post_data['goods_id']));

        return $this->fetch();
    }

    /**
     * 货品管理 图片上传[单图]
     */
    public function productsUploadImg()
    {
        if (request()->isAjax()) {
            $file = request()->file('file');
            // 移动到框架应用根目录/public/uploads/ 目录下
            $info = $file->validate(['size' => 156780, 'ext' => 'jpg,png,gif'])->move(ROOT_PATH . 'public' . DS . 'uploads' . DS . 'goods' . DS . 'products');
            if ($info) {
                $src = request()->domain() . '/uploads' . '/goods' . '/products' . '/' . date('Ymd') . '/' . $info->getFilename();
                return json(msg(0, ['src' => $src], ''));
            } else {
                // 上传失败获取错误信息
                return json(msg(-1, '', $file->getError()));
            }
        }
    }

    /**
     * 货品图片管理 删除
     */
    public function productsimgDel()
    {
        $param = input('param.');
        $flag = $this->validate($param, 'MallValidate.productsimgDel');
        if (true !== $flag) {
            return json(msg(-1, '', $flag));
        }
        $products_img = new ProductsImgModel();
        $result = $products_img->delProductsImg($param['img_id']);

        return json($result);
    }

    /**
     * 货品图片集 启用
     */
    public function productsimgUp()
    {
        $param = input('param.');
        $flag = $this->validate($param, 'MallValidate.productsimgDel');
        if (true !== $flag) {
            return json(msg(-1, '', $flag));
        }
        $products_img = new ProductsImgModel();
        $result = $products_img->statusProductImg($param['img_id'], 1);

        return json($result);
    }

    /**
     * 货品图片集 禁用
     */
    public function productsimgDown()
    {
        $param = input('param.');
        $flag = $this->validate($param, 'MallValidate.productsimgDel');
        if (true !== $flag) {
            return json(msg(-1, '', $flag));
        }
        $products_img = new ProductsImgModel();
        $result = $products_img->statusProductImg($param['img_id'], 2);

        return json($result);
    }

    /**
     * 货品拼装操作按钮
     * @param $id
     * @return array
     */
    private function makeProductsImgButton($id)
    {
        return [
            '删除' => [
                'auth'     => 'mall/productsimgdel',
                'href'     => "javascript:productsImgDel(" . $id . ")",
                'btnStyle' => 'danger',
                'icon'     => 'fa fa-trash-o'
            ],
            '启用' => [
                'auth'     => 'mall/productsimgup',
                'href'     => "javascript:productsimgUp(" . $id . ")",
                'btnStyle' => 'primary',
                'icon'     => 'fa fa-arrow-up',
            ],
            '禁用' => [
                'auth'     => 'mall/productsimgdown',
                'href'     => "javascript:productsimgDown(" . $id . ")",
                'btnStyle' => 'warning',
                'icon'     => 'fa fa-arrow-down',
            ],
        ];
    }

    /**
     * 商品订单管理
     */
    public function orderIndex()
    {
        if (request()->isAjax()) {
            $param = input('param.');
            $flag = $this->validate($param, 'MallValidate.orderIndex');
            if (true !== $flag) {
                return json(msg(-1, '', $flag));
            }

            $limit = $param['pageSize'];
            $offset = ($param['pageNumber'] - 1) * $limit;

            $where = [];
            if (!empty($param['order_no'])) {
                $where['order_no'] = str_fun($param['order_no']);
            }
            if (!empty($param['goods_name'])) {
                $where['goods_name'] = ['like', '%' . str_fun($param['goods_name']) . '%'];
            }
            if (!empty($param['member_id'])) {
                $where['memberid'] = str_fun($param['member_id']);
            }
            if (!empty($param['order_status'])) {
                $where['status'] = intval($param['order_status']);
            }
            if (!empty($param['pay_status'])) {
                $where['pay_status'] = intval($param['pay_status']) == -1 ? 0 : intval($param['pay_status']);
            }
            if (!empty($param['distribution_status'])) {
                $where['distribution_status'] = intval($param['distribution_status']) == -1 ? 0 : intval($param['distribution_status']);
            }
            $start = @$param['start'] ? str_fun(date('Y-m-d',strtotime($param['start']))) : 0;
            $end = @$param['end'] ? str_fun(date('Y-m-d',strtotime($param['end']))) . ' 23:59:59' : date('Y-m-d') . ' 23:59:59';
            $where['create_time'] = ['between time', [$start, $end]];

            $order = new OrderModel();
            $selectResult = $order->getOrderByWhere($where, $offset, $limit);

            foreach ($selectResult as $key => $vo) {
                $buttons = $this->makeOrderButton($vo['id']);
                if ('待付款' == $vo['status'] || '已支付' != $vo['pay_status'] || '已发货' == $vo['distribution_status']) {
                    unset($buttons['发货']);
                }
                $spec_arr = json_decode($vo['spec_array'], true);
                $spec_str = '';
                foreach ($spec_arr as $k => $v) {
                    $spec_str .= $v['name'] . ':' . $v['tip'] . ';';
                }
                $selectResult[$key]['spec_array'] = $spec_str;
                $selectResult[$key]['goods_img'] = '<img src="' . $vo['goods_img'] . '" width="40px" height="40px">';
                $selectResult[$key]['products_img'] = '<img src="' . $vo['products_img'] . '" width="40px" height="40px">';
                $selectResult[$key]['operate'] = showOperate($buttons);
            }

            $return['total'] = $order->getAllOrder($where);  // 总数据
            $return['rows'] = $selectResult;

            return json($return);
        }

        return $this->fetch();
    }

    /**
     * 订单管理 发货
     */
    public function orderEdit()
    {
        $order = new OrderModel();
        if (request()->isPost()) {
            $param = input('param.');
            $result = $this->validate($param, 'MallValidate.orderEdit');
            if (true !== $result) {
                return json(msg(-1, '', $result));
            }
            $param['express_name'] = str_fun($param['express_name']);
            $param['express_code'] = str_fun($param['express_code']);
            $param['distribution_status'] = 1;
            $param['send_time'] = date('Y-m-d H:i:s');
            $rec = $order->editOrder($param);

            return json($rec);
        }
        $post_data = input('param.');
        $flag = $this->validate($post_data, 'MallValidate.orderEdit');
        if (true !== $flag) {
            return json(msg(-1, '', $flag));
        }
        $this->assign('order_id', intval($post_data['order_id']));

        return $this->fetch();
    }

    /**
     * 订单管理 导出
     * @throws \Exception
     */
    public function orderExport()
    {
        $param = input('param.');
        $where = [];
        if (!empty($param['order_no'])) {
            $where['order_no'] = str_fun($param['order_no']);
        }
        if (!empty($param['goods_name'])) {
            $where['goods_name'] = ['like', '%' . str_fun($param['goods_name']) . '%'];
        }
        if (!empty($param['member_id'])) {
            $where['memberid'] = str_fun($param['member_id']);
        }
        if (!empty($param['order_status'])) {
            $where['status'] = intval($param['order_status']);
        }
        if (!empty($param['pay_status'])) {
            $where['pay_status'] = intval($param['pay_status']) == -1 ? 0 : intval($param['pay_status']);
        }
        if (!empty($param['distribution_status'])) {
            $where['distribution_status'] = intval($param['distribution_status']) == -1 ? 0 : intval($param['distribution_status']);
        }
        $start = @$param['start'] ? str_fun(date('Y-m-d',strtotime($param['start']))) : 0;
        $end = @$param['end'] ? str_fun(date('Y-m-d',strtotime($param['end']))) . ' 23:59:59' : date('Y-m-d') . ' 23:59:59';
        $where['create_time'] = ['between time', [$start, $end]];
        $order = new OrderModel();
        $selectResult = $order->where($where)->order('id desc')->select()->toArray();
        foreach ($selectResult as $key => $vo) {
            $spec_arr = json_decode($vo['spec_array'], true);
            $spec_str = '';
            if (!empty($spec_arr)) {
                foreach ($spec_arr as $k => $v) {
                    $spec_str .= $v['name'] . ':' . $v['tip'] . ';';
                }
            }
            $selectResult[$key]['spec_array'] = $spec_str;
        }
        $header = [
            'id'                    => '订单ID',
            'order_no'              => '订单编号',
            'memberid'              => '会员账号',
            'membername'            => '会员名称',
            'goods_id'              => '商品ID',
            'goods_name'            => '商品名称',
            'products_id'           => '货品ID',
            'spec_array'            => '货品规格',
            'pay_type'              => '账户类型',
            'status'                => '订单状态',
            'pay_status'            => '支付状态',
            'distribution_status'   => '发货状态',
            'accept_name'           => '收人姓名',
            'address'               => '收货地址',
            'address_info'          => '收货地址详情',
            'mobile'                => '联系电话',
            'payable_amount'        => '应付商品金额',
            'real_amount'           => '实付商品金额',
            'payable_freight'       => '总运费金额',
            'real_freight'          => '实付运费金额',
            'pay_time'              => '付款时间',
            'send_time'             => '发货时间',
            'create_time'           => '下单时间',
            'completion_time'       => '订单完成时间',
            'order_amount'          => '订单总金额',
            'accept_time'           => '用户收货时间',
            'sfdate_begin'          => '锁仓释放时间',
            'shouyi_yuqi_num'       => '锁仓释放总数',
            'shouyi_yuqi_keyong'    => '锁仓释放可用',
            'shouyi_yuqi_shifang'   => '锁仓释放数量',
            'shouyi_yuqi_beinum'    => '锁仓赠送倍数',
            'shouyi_yuqi_daynum'    => '锁仓相隔天数释放',
            'order_num'             => '订单数量',
            'goods_sell_price'      => '商品销售价',
            'products_sell_price'   => '货品销售价',
            'memo'                  => '订单备注',
            'goods_market_price'    => '商品市场价',
            'products_market_price' => '货品市场价',
            'express_name'          => '快递名称',
            'express_code'          => '快递单号',
            'qu_xiao_time'          => '取消订单时间'
        ];
        get_excel($header, $selectResult);
    }

    /**
     * 订单拼装操作按钮
     * @param $id
     * @return array
     */
    private function makeOrderButton($id)
    {
        return [
            '发货' => [
                'auth'     => 'mall/orderedit',
                'href'     => url('mall/orderedit', ['order_id' => $id]),
                'btnStyle' => 'primary',
                'icon'     => 'fa fa-paste'
            ]
        ];
    }

    /**
     * 锁仓聚合管理 明细
     */
    public function gather()
    {
        if (request()->isAjax()) {
            $param = input('param.');
            $flag = $this->validate($param, 'MallValidate.gather');
            if (true !== $flag) {
                return json(msg(-1, '', $flag));
            }

            $limit = $param['pageSize'];
            $offset = ($param['pageNumber'] - 1) * $limit;

            $where = [];
            $where['account_type'] = 'wuc';
            if (!empty($param['member_id'])) {
                $where['memberid'] = str_fun($param['member_id']);
            }
            $start = @$param['start'] ? strtotime(str_fun($param['start']) . " 00:00:01") : 0;
            $end = @$param['end'] ? strtotime(str_fun($param['end']) . " 23:59:59") : time();
            $where['lrdate'] = ['between time', [$start, $end]];

            $guiji = new GuijiModel();
            $selectResult = $guiji->getGuijiByWhere($where, $offset, $limit);

            $return['total'] = $guiji->getAllGuiji($where);  // 总数据
            $return['rows'] = $selectResult;

            return json($return);
        }

        return $this->fetch();
    }

    /**
     * 锁仓聚合明细 导出
     * @throws \Exception
     */
    public function gatherExport()
    {
        $param = input('param.');
        $where = [];
        $where['account_type'] = 'wuc';
        if (!empty($param['member_id'])) {
            $where['memberid'] = str_fun($param['member_id']);
        }
        $start = @$param['start'] ? strtotime(str_fun($param['start']) . " 00:00:01") : 0;
        $end = @$param['end'] ? strtotime(str_fun($param['end']) . " 23:59:59") : time();
        $where['lrdate'] = ['between time', [$start, $end]];
        $guiji = new GuijiModel();
        $selectResult = $guiji->where($where)->order('id desc')->select()->toArray();
        $header = [
            'id'           => '聚合ID',
            'memberid'     => '会员账号',
            'membername'   => '会员名称',
            'account_zc'   => '转出账号',
            'num'          => '聚合数量',
            'dzmoney'      => '到账数量',
            'account_zr'   => '转入账户',
            'account_type' => '账户类型',
            'lrdate'       => '录入时间',
        ];
        get_excel($header, $selectResult);
    }

    /**
     * 锁仓释放管理 明细
     */
    public function along()
    {
        if (request()->isAjax()) {
            $param = input('param.');
            $flag = $this->validate($param, 'MallValidate.along');
            if (true !== $flag) {
                return json(msg(-1, '', $flag));
            }

            $limit = $param['pageSize'];
            $offset = ($param['pageNumber'] - 1) * $limit;

            $where = [];
            if (!empty($param['member_id'])) {
                $where['memberid'] = str_fun($param['member_id']);
            }
            if (!empty($param['account_type'])) {
                $where['account_type'] = str_fun($param['account_type']);
            }
            $start = @$param['start'] ? str_fun(date('Y-m-d',strtotime($param['start']))) : 0;
            $end = @$param['end'] ? str_fun(date('Y-m-d',strtotime($param['end']))) . ' 23:59:59' : date('Y-m-d') . ' 23:59:59';
            $where['op_time'] = ['between time', [$start, $end]];

            $wuc_log = new WucYuqiLogModel();
            $selectResult = $wuc_log->getWucYuqiLogByWhere($where, $offset, $limit);

            $return['total'] = $wuc_log->getAllWucYuqiLog($where);  // 总数据
            $return['rows'] = $selectResult;

            return json($return);
        }

        return $this->fetch();
    }

    /**
     * 锁仓释放明细 导出
     * @throws \Exception
     */
    public function alongExport()
    {
        $param = input('param.');
        $where = [];
        if (!empty($param['member_id'])) {
            $where['memberid'] = str_fun($param['member_id']);
        }
        if (!empty($param['account_type'])) {
            $where['account_type'] = str_fun($param['account_type']);
        }
        $start = @$param['start'] ? str_fun(date('Y-m-d',strtotime($param['start']))) : 0;
        $end = @$param['end'] ? str_fun(date('Y-m-d',strtotime($param['end']))) . ' 23:59:59' : date('Y-m-d') . ' 23:59:59';
        $where['op_time'] = ['between time', [$start, $end]];
        $wuc_log = new WucYuqiLogModel();
        $selectResult = $wuc_log->where($where)->order('id desc')->select()->toArray();
        $header = [
            'id'           => '明细ID',
            'memberid'     => '会员账号',
            'membername'   => '会员名称',
            'type'         => '类型',
            'yue'          => '余额(发生之前)',
            'num'          => '数量',
            'from_type'    => '来源类型',
            'orderid'      => '关联订单ID',
            'account_type' => '账户类型',
            'op_time'      => '操作时间',
        ];
        get_excel($header, $selectResult);
    }
}
