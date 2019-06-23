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
namespace app\ms_admin\validate;

use think\Db;
use think\Validate;

class MallValidate extends Validate
{
    protected $rule = [
        'pageSize|页码'                  => 'require|number|gt:0',
        'pageNumber|页数'                => 'require|number|gt:0',
        'title|轮播图标题'                  => 'require|chsAlphaNum',
        'url|轮播图关联ID'                  => 'require|number|gt:0',
        'sort|排序'                      => 'require|number|gt:0',
        'type|轮播图类型'                   => 'require|in:banner,优惠区,新闻',
        'img|缩略图'                      => 'require',
        'banner_id|轮播图ID'              => 'require|number|gt:0|checkBanner:',
        'name|国家分类名称'                  => 'require|chs',
        'status|分类状态'                  => 'require|number|gt:0|in:1,2',
        'country_id|商品国家分类ID'          => 'require|number|gt:0|checkCountry:',
        'country_del_id|删除商品国家分类ID'    => 'require|number|gt:0|checkCountry:|checkDelCountry:',
        'typeid|商品分类编号'                => 'require|regex:^[T]\d{5}|unique:shop_goods_type',
        'typename|商品分类名称'              => 'require',
        'goods_type_id|商品分类ID'         => 'require|number|gt:0|checkGoodsType:',
        'type_id|分类编号'                 => 'require|regex:^[T]\d{5}',
        'goods_type_del_id|删除分类ID'     => 'require|number|gt:0|checkGoodsType:|checkDelGoodsType:',
        'spec_name|规格名称'               => 'require|checkSpecName:',
        'spec_memo|规格描述'               => 'require|array',
        'spec_num|规格值'                 => 'require|array',
        'spec_id|规格ID'                 => 'require|number|gt:0|checkSpec:',
        'goods_name|商品名称'              => 'require',
        'county_type_id|商品国家分类'        => 'require|number|gt:0|checkCountry:',
        'pay_type|账户类型'                => 'require|in:msth',
        'sell_price|销售价格'              => 'require|number|gt:0',
        'market_price|市场价格'            => 'require|number|gt:0',
        'rebate_num|锁仓倍数'              => 'require|number|gt:0',
        'store_nums|商品库存'              => 'require|number|gt:0',
        'is_delivery_fee|运费状态'         => 'require|number|in:0,1',
        'delivery_fee|运费'              => 'requireIf:is_delivery_fee,0|number|checkDeliveryFee:',
        'pc_src|商品图片'                  => 'require|array',
        'spec_arr|规格信息'                => 'require|array',
        'products_sell_price|货品销售价格'   => 'require|array',
        'products_market_price|货品市场价格' => 'require|array',
        'products_store_nums|货品库存'     => 'require|array',
        'products_img|货品图片'            => 'require|array',
        'content|商品详情'                 => 'require',
        'goods_id|商品ID'                => 'require|number|gt:0|checkGoods:',
        'img_name|图片名称'                => 'require',
        'img_id|货品图片ID'                => 'require|number|gt:0|checkProductsImg:',
        'order_id|订单ID'                => 'require|number|gt:0|checkOrder:'
    ];

    protected $message = [

    ];

    protected $scene = [
        'banner'           => ['pageSize', 'pageNumber'],
        'bannerAdd'        => ['title', 'url', 'sort', 'type', 'img'],
        'bannerIndex'      => ['banner_id'],
        'bannerEdit'       => ['banner_id', 'title', 'url', 'sort', 'type', 'img'],
        'country'          => ['pageSize', 'pageNumber'],
        'countryAdd'       => ['name', 'sort', 'status', 'img'],
        'countryIndex'     => ['country_id'],
        'countryEdit'      => ['country_id', 'name', 'sort', 'status', 'img'],
        'countryDel'       => ['country_del_id'],
        'goodstype'        => ['pageSize', 'pageNumber'],
        'goodstypeAdd'     => ['typeid', 'typename', 'sort', 'status'],
        'goodstypeIndex'   => ['goods_type_id'],
        'goodstypeEdit'    => ['goods_type_id', 'type_id', 'typename', 'sort', 'status'],
        'goodstypeDel'     => ['goods_type_del_id'],
        'spec'             => ['pageSize', 'pageNumber'],
        'specAdd'          => ['spec_name', 'spec_memo', 'spec_num'],
        'specIndex'        => ['spec_id'],
        'specEdit'         => ['spec_id', 'spec_name', 'spec_memo', 'spec_num'],
        'specDel'          => ['spec_id'],
        'goods'            => ['pageSize', 'pageNumber'],
        'goodsSpec'        => ['spec_id'],
        'goodsAdd'         => ['goods_name', 'goods_type_id', 'county_type_id', 'pay_type', 'sell_price', 'market_price', 'rebate_num', 'store_nums', 'img', 'is_delivery_fee', 'delivery_fee', 'pc_src', 'spec_arr', 'products_sell_price', 'products_market_price', 'products_store_nums', 'content'],
        'goodsIndex'       => ['goods_id'],
        'goodsEdit'        => ['goods_id', 'goods_name', 'goods_type_id', 'county_type_id', 'pay_type', 'sell_price', 'market_price', 'rebate_num', 'store_nums', 'img', 'is_delivery_fee', 'delivery_fee', 'pc_src', 'spec_arr', 'products_sell_price', 'products_market_price', 'products_store_nums', 'products_img', 'content'],
        'productsImg'      => ['pageSize', 'pageNumber', 'goods_id'],
        'productsimgIndex' => ['goods_id'],
        'productsimgAdd'   => ['goods_id', 'img_name', 'img'],
        'productsimgDel'   => ['img_id'],
        'orderIndex'       => ['pageSize', 'pageNumber'],
        'orderEdit'        => ['order_id'],
        'gather'           => ['pageSize', 'pageNumber'],
        'along'            => ['pageSize', 'pageNumber']
    ];

    /**
     * 验证轮播图信息
     * @throws \Exception
     */
    protected function checkBanner($banner_id)
    {
        $banner = Db::table('t_shop_banner')->where('id', 'eq', $banner_id)->find();
        if (empty($banner)) {
            return '轮播图信息不存在';
        }

        return true;
    }

    /**
     * 验证商品国家分类
     * @throws \Exception
     */
    protected function checkCountry($country_id)
    {
        $country = Db::table('t_shop_country_type')->where('id', 'eq', $country_id)->find();
        if (empty($country)) {
            return '商品国家分类信息不存在';
        }

        return true;
    }

    /**
     * 验证是否可删除国家分类
     * @throws \Exception
     */
    protected function checkDelCountry($country_del_id)
    {
        $goods = Db::table('t_shop_goods')->where('county_type_id', 'eq', $country_del_id)->find();
        if (!empty($goods)) {
            return '商品国家分类绑定商品，不允许删除';
        }

        return true;
    }

    /**
     * 验证商品分类
     * @throws \Exception
     */
    protected function checkGoodsType($goods_type_id)
    {
        $goods_type = Db::table('t_shop_goods_type')->where('id', 'eq', $goods_type_id)->find();
        if (empty($goods_type)) {
            return '商品分类信息不存在';
        }

        return true;
    }

    /**
     * 验证是否删除商品分类
     * @throws \Exception
     */
    protected function checkDelGoodsType($goods_type_del_id)
    {
        $goods = Db::table('t_shop_goods')->where('goods_type_id', 'eq', $goods_type_del_id)->find();
        if (!empty($goods)) {
            return '商品分类绑定商品，不允许删除';
        }

        return true;
    }

    /**
     * 验证规格名称是否存在
     * @throws \Exception
     */
    protected function checkSpecName($spec_name, $rule, $data)
    {
        if (1 == $data['ver_status']) {
            $spec = Db::table('t_shop_spec')->where('name', 'eq', $spec_name)->where('is_del', 'eq', 0)->find();
        } else {
            $spec = Db::table('t_shop_spec')->where('name', 'eq', $spec_name)->where('is_del', 'eq', 0)->where('id', 'neq', $data['spec_id'])->find();
        }
        if (!empty($spec)) {
            return '商品规格名称已存在，请重新输入';
        }

        return true;
    }

    /**
     * 验证规格信息
     * @throws \Exception
     */
    protected function checkSpec($spec_id)
    {
        $spec = Db::table('t_shop_spec')->where('id', 'eq', $spec_id)->where('is_del', 'eq', 0)->find();
        if (empty($spec)) {
            return '商品规格信息不存在';
        }

        return true;
    }

    /**
     * 验证正整数
     */
    protected function checkNum($num)
    {
        if (!preg_match('/^[1-9][0-9]*$/', $num)) {
            return '数量必须为正整数！';
        }

        return true;
    }

    /**
     * 验证商品
     * @throws \Exception
     */
    protected function checkGoods($goods_id)
    {
        $goods = Db::table('t_shop_goods')->where('id', 'eq', $goods_id)->find();
        if (empty($goods)) {
            return '商品信息不存在';
        }

        return true;
    }

    /**
     * 验证货品图片信息
     * @throws \Exception
     */
    protected function checkProductsImg($img_id)
    {
        $products_img = Db::table('t_shop_products_img')->where('id', 'eq', $img_id)->find();
        if (empty($products_img)) {
            return '货品图片信息不存在';
        }
        $products = Db::table('t_shop_products')->where('products_img_id', 'eq', $img_id)->find();
        if (!empty($products)) {
            return '货品图片绑定了货品信息不允许操作';
        }

        return true;
    }

    /**
     * 验证订单信息
     * @throws \Exception
     */
    protected function checkOrder($order_id)
    {
        $order = Db::table('t_shop_order')->where('id', 'eq', $order_id)->find();
        if (empty($order)) {
            return '订单信息不存在';
        }
        if (2 != $order['status'] || 1 != $order['pay_status']) {
            return '订单状态不允许操作';
        }

        return true;
    }

    /**
     * 验证运费
     */
    protected function checkDeliveryFee($fee, $rule, $data)
    {
        if (0 == $data['is_delivery_fee'] && $fee <= 0) {
            return '收运费时运费必须大于0';
        }
        if (1 == $data['is_delivery_fee'] && $fee != 0) {
            return '免运费时运费必须等于0';
        }

        return true;
    }

}