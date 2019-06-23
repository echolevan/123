<?php

namespace app\api\controller;

use Endroid\QrCode\QrCode;
use eth\driver\Api;
use think\Db;
use think\Env;
use think\Lang;
use think\Log;
use think\Request;

class Index extends Common
{
    /**
     * 查看是否设置手机号
     */
    public function issetMobile()
    {
        if (empty($this->app_member['mobile'])) {
            return json(arr_msg(-1, '', '请设置手机号'));
        }

        return json(arr_msg(1, '', 'success'));
    }

    /**
     * 设置手机号
     */
    public function setMobile()
    {
        $param = input('param.');
        $flag = $this->validate($param, 'IndexValidate.setMobile');
        if (true !== $flag) {
            return json(arr_msg(-1, '', $flag));
        }
        if (!empty($this->app_member['mobile'])) {
            return json(arr_msg(-1, '', '您已经设置过手机号了无需再次设置手机号'));
        }
        try {
            $result = Db::name('user')->where('memberid', 'eq', $this->app_member['memberid'])->update(['mobile' => $param['mobile'], 'mobile_code' => '86']);
            if (!$result) {
                return json(arr_msg(-1, '', '设置手机号失败'));
            }

            return json(arr_msg(1, '', '设置手机号成功'));
        } catch (\Exception $e) {
            return json(arr_msg(-1, '', $e->getMessage()));
        }
    }

    /**
     * 查看会员是否设置交易密码
     */
    public function twoLevelCipher()
    {
        if (!$this->app_member['paypassword_zhifu']) {
            return json(arr_msg(-1, '', '请设置交易密码'));
        }

        return json(arr_msg(1, '', '已设置交易密码'));
    }

    /**
     * 设置二级密码
     * @throws \Exception
     */
    public function setTwoLevelCipher()
    {
        $param = input("param.");
        $flag = $this->validate($param, "IndexValidate.setTwoLevelCipher");
        if (true !== $flag) {
            return json(arr_msg(-1, '', $flag));
        }

        if ($this->app_member['paypassword_zhifu']) {
            return json(arr_msg(-1, '', '您已设置过交易密码'));
        }
        $password = strtoupper(substr(md5($param['cipher']), 8, 16));
        $flag = Db::table("t_user")->where('memberid', 'eq', $this->app_member['memberid'])->update(['paypassword_zhifu' => $password]);
        if (!$flag) {
            return json(arr_msg(-2, '', '设置失败'));
        }

        return json(arr_msg(1, '', '设置交易密码成功'));
    }

    /**
     * 首页
     * @throws \Exception
     */
    public function index()
    {
        $notice_cache = cache('notice_status' . $this->app_member['memberid']);
        $notice_status = 1;
        $notice_arr = Db::table('t_articles')->where('keywords', 'eq', '首页公告')->find();
        if (empty($notice_cache) && !empty($notice_arr)) {
            $notice_status = 0;
            cache('notice_status' . $this->app_member['memberid'], 1);
        }
        $banner = Db::name('shop_banner')->field('id,title,url,img')->where('type', 'eq', '新闻')->order('sort asc')->select();
        $wei_num = config('wei_shu');
        $dang_qain_chu_zhi = Db::name('user_project')->where('tz_status','eq',1)->where('memberid','eq',$this->app_member['memberid'])->sum('tz_money');
        $result = [
            'member_id'         => $this->app_member['memberid'],
            'member_name'       => $this->app_member['membername'],
            'notice_status'     => $notice_status,
            'notice_content'    => req_img(htmlspecialchars_decode(htmlspecialchars_decode($notice_arr['content']))),
            'msth_yucun'        => format_num($this->app_member['into_lian_keyong']),
            'msth_tiqu'         => format_num($this->app_member['lian_num_keyong']),
            'dang_qian_chu_zhi' => format_num($dang_qain_chu_zhi),
            'ke_yong_jiang_li'  => format_num($this->app_member['cunchu_jiangli_account']),
            'shi_fang_num'      => format_num($this->app_member['paidan_shouyi_keyong']),
            'shou_yi_num'       => format_num($this->app_member['suanli_num_keyong']),
            'banner'            => $banner
        ];

        return json(arr_msg(1, $result, 'success'));
    }

    /**
     * 生成生成收款二维码
     *
     * @return string
     */
    public function makeAddressQrcode()
    {
        $width = input("width", 300, 'int');
        $text = urldecode($this->app_member['npqbdz_wuc']);
        if (!$text) {
            return json(arr_msg(-1, '', '您还没有生成钱包地址，请及时联系管理员！'));
        }
        $root_path = ROOT_PATH . 'public';
        $path = '/static/addressqrcode';
        if (!file_exists(($root_path . $path))) {
            mkdir(($root_path . $path));
        }
        $request = Request::instance();
        $file_name = md5($this->app_member['memberid'] . $this->app_member['npqbdz_wuc'] . 'msth') . '.png';
        $file_path = $request->domain() . $path . '/' . $file_name;
        if (!file_exists($file_path)) {
            $qr_code = new QrCode($text);
            $qr_code->setSize($width);
            $qr_code->writeFile($root_path . $path . '/' . $file_name);
        }

        return json(arr_msg(1, ['qr_code' => $file_path, 'address' => $this->app_member['npqbdz_wuc'], 'msth_yucun' => format_num($this->app_member['into_lian_keyong'])], 'success'));
    }

    /**
     * 生成分享海报二维码
     */
    public function makeShareQrcode()
    {
        $request = Request::instance();
        $width = input("width", 300, 'int');
        $path_url = url('index/index/index', [], 'html', $request->domain()) . '?per=' . $this->app_member['memberid'];
        $text = urldecode($path_url);
        if (!$text) {
            return json(arr_msg(-1, '', '无数据'));
        }
        $root_path = ROOT_PATH . 'public';
        $path = '/static/shareqrcode';
        if (!file_exists(($root_path . $path))) {
            mkdir(($root_path . $path));
        }
        $file_name = md5($this->app_member['memberid'] . 'url' . 'msth') . '.png';
        $file_path = $request->domain() . $path . '/' . $file_name;
        if (!file_exists($file_path)) {
            $qr_code = new QrCode($text);
            $qr_code->setSize($width);
            $qr_code->writeFile($root_path . $path . '/' . $file_name);
        }

        return json(arr_msg(1, ['qr_code' => $file_path, 'path_url' => $path_url], 'success'));
    }

    /**
     * 链储行列表
     * @throws \Exception
     */
    public function chainStoreInit()
    {
        if ($this->sys[1004001] != '开启') {
            return json(arr_msg(-1, '', '此功能暂时关闭，请稍后！'));
        }
        $project = Db::table('t_save_project')->field('id,min_num,max_num,save_days,rixi_bili,yuexi_bili,c_time')->where('status', 'eq', 1)->order('id desc')->select()->each(function ($model) {
            $model = [
                'id'         => $model['id'],
                'min_num'    => $model['min_num'],
                'max_num'    => $model['max_num'],
                'save_days'  => $model['save_days'],
                'rixi_bili'  => format_num($model['rixi_bili']) * 100,
                'yuexi_bili' => format_num($model['yuexi_bili']) * 100,
                'bian_hao'   => date('YmdHis', strtotime($model['c_time'])) . '_' . $model['id']
            ];

            return $model;
        });
        if (empty($project)) {
            return json(arr_msg(-1, '', '未获取到此投资套餐'));
        }
        $articles = Db::table('t_articles')->field('content')->where('keywords', 'eq', '链储行')->find();

        $result = [
            'taocan'   => $project,
            'status'   => $this->sys[1004001],
            'articles' => htmlspecialchars_decode(htmlspecialchars_decode($articles['content']))
        ];

        return json(arr_msg(1, $result, 'success'));
    }

    /**
     * 链储行说明
     */
    public function chainStoreContent()
    {
        try {
            $articles = Db::table('t_articles')->field('content')->where('keywords', 'eq', '链储行')->find();
            if (empty($articles)) {
                return json(arr_msg(-1, '', '不存在合约说明'));
            }
            $articles['content'] = htmlspecialchars_decode(htmlspecialchars_decode($articles['content']));
        } catch (\Exception $e) {
            return json(arr_msg(-1, '', $e->getMessage()));
        }

        return json(arr_msg(1, ['list' => $articles], 'success'));
    }

    /**
     * 链储行初始化
     * @throws \Exception
     */
    public function chainStoreList()
    {
        $param = input('param.');
        $param['chain_status'] = $this->sys[1004001];
        $flag = $this->validate($param, 'IndexValidate.chainStoreList');
        if (true !== $flag) {
            return json(arr_msg(-1, '', $flag));
        }
        $app_project = Db::table('t_save_project')->field('id,min_num,max_num,save_days,rixi_bili,yuexi_bili,c_time')->where('id', 'eq', $param['store_pro_id'])->where('status', 'eq', 1)->find();
        $app_project['rixi_bili'] = format_num($app_project['rixi_bili']) * 100;
        $app_project['yuexi_bili'] = format_num($app_project['yuexi_bili']) * 100;

        return json(arr_msg(1, ['taocan' => $app_project, 'msth_yucun' => format_num($this->app_member['into_lian_keyong']), 'msth_tiqu' => format_num($this->app_member['lian_num_keyong'])], 'success'));
    }

    /**
     * 链储行提交
     * @throws \Exception
     */
    public function chainStoreSub()
    {
        $param = input('param.');
        $param['chain_status'] = $this->sys[1004001];
        $flag = $this->validate($param, 'IndexValidate.chainStoreSub');
        if (true !== $flag) {
            return json(arr_msg(-1, '', $flag));
        }
        $data = [
            'member_id' => $this->app_member['memberid'],
            'pro_id'    => $param['pro_id'],
            'amount'    => $param['amount'],
            'laiyuan'   => $param['laiyuan']
        ];
        $procedure_name = 't_touzi';
        $result = db_query($procedure_name, $data);
        if (1 != $result['code']) {
            Log::info('时间：' . date('Y-m-d H:i:s') . '-会员账号：' . $this->app_member['memberid'] . '-请求参数：' . json_encode($data) . '-app_touzi存储过程返回:' . $result['msg']);
            return json($result);
        }

        return json(arr_msg(1, '', '存储成功'));
    }

    /**
     * 我的存储
     * @throws \Exception
     */
    public function chainStoreMy()
    {
        $param = input('param.');
        $flag = $this->validate($param, 'IndexValidate.chainStoreMy');
        if (true !== $flag) {
            return json(arr_msg(-1, '', $flag));
        }
        $limit = $param['page_size'];
        $offset = ($param['page'] - 1) * $limit;
        $count = Db::table('t_user_project')->alias('p')->join('t_save_project s', 'p.pro_id = s.id')->where('p.memberid', 'eq', $this->app_member['memberid'])->field('p.id,p.pro_id,p.c_days,p.tz_money,s.save_days,s.rixi_bili,s.yuexi_bili,p.account_type,s.c_time')->count('p.id');
        $chain_list = Db::table('t_user_project')->alias('p')->join('t_save_project s', 'p.pro_id = s.id')->where('p.memberid', 'eq', $this->app_member['memberid'])->field('p.id,p.pro_id,p.c_days,p.tz_money,s.save_days,s.rixi_bili,s.yuexi_bili,p.account_type,s.tiqianzhiqu,p.jisuanyeji_status,p.tz_status,s.c_time')->limit($offset, $limit)->order("p.id desc")->select()->each(function ($model) {
            $model['tz_money'] = format_num($model['tz_money']) * 1;
            $model['rixi_bili'] = format_num($model['rixi_bili']) * 100;
            $model['yuexi_bili'] = format_num($model['yuexi_bili']) * 100;
            $model['bian_hao'] = date('YmdHis', strtotime($model['c_time'])) . '_' . $model['pro_id'];
            $status = 0;
            if ('开启' != $this->sys[1004002] || 1 != $model['tiqianzhiqu'] || 1 != $model['jisuanyeji_status'] || 1 != $model['tz_status'] || $model['c_days'] >= $model['save_days']) {
                $status = 1;
            }
            $model['ver_status'] = $status;

            return $model;
        });
        $dang_qain_chu_zhi = Db::name('user_project')->where('tz_status','eq',1)->where('memberid','eq',$this->app_member['memberid'])->sum('tz_money');

        return json(arr_msg(1, ['list' => $chain_list, 'pages' => ceil($count / $limit), 'dang_qian_chu_zhi' => format_num($dang_qain_chu_zhi), 'ke_yong_jiang_li' => format_num($this->app_member['cunchu_jiangli_account'])], 'success'));
    }

    /**
     * 存储提前支取
     */
    public function chainStoreDraw()
    {
        $param = input('param.');
        $param['tq_status'] = $this->sys[1004002];
        $flag = $this->validate($param, 'IndexValidate.chainStoreDraw');
        if (true !== $flag) {
            return json(arr_msg(-1, '', $flag));
        }
        $data = [
            'id'       => $param['t_order_id'],
            'memberid' => $this->app_member['memberid']
        ];
        $procedure_name = 't_tiqianzhiqu';
        $result = db_query($procedure_name, $data);
        if (1 != $result['code']) {
            Log::info('时间：' . date('Y-m-d H:i:s') . '-会员账号：' . $this->app_member['memberid'] . '-请求参数：' . json_encode($data) . '-app_tiqianzhiqu存储过程返回:' . $result['msg']);
            return json($result);
        }

        return json(arr_msg(1, '', '提前支取成功'));
    }

    /**
     * 验证交易密码是否一致
     */
    public function verPayPwd()
    {
        $param = input('param.');
        $flag = $this->validate($param, 'IndexValidate.verPayPwd');
        if (true !== $flag) {
            return json(arr_msg(-1, '', $flag));
        }

        return json(arr_msg(1, '', 'success'));
    }

    /**
     * 商品下单接口
     */
    public function mallPlaceOrder()
    {
        $param = input('param.');
        $flag = $this->validate($param, 'IndexValidate.mallPlaceOrder');
        if (true !== $flag) {
            return json(arr_msg(-1, '', $flag));
        }
        if ('开启' != $this->sys[1007004]) {
            return json(arr_msg(-1, '', '此功能暂时关闭，请稍后再试'));
        }
        $data = [
            'goods_id'    => $param['goods_id'],
            'products_id' => $param['products_id'],
            'member_id'   => $this->app_member['memberid'],
            'num'         => $param['order_num']
        ];
        $procedure_name = 'app_shop_place_order';
        $result = db_query($procedure_name, $data);
        if (1 != $result['code']) {
            return json($result);
        }
        return json(arr_msg(1, ['order_id' => $result['data']['v']], '下单成功'));
    }

    /**
     * 下单详情
     */
    public function mallOrderDet()
    {
        $param = input('param.');
        $flag = $this->validate($param, 'IndexValidate.mallOrderDet');
        if (true !== $flag) {
            return json(arr_msg(-1, '', $flag));
        }
        if ('开启' != $this->sys[1007004]) {
            return json(arr_msg(-1, '', '此功能暂时关闭，请稍后再试'));
        }
        try {
            $address = Db::table('t_shop_user_address')->field('name,mobile,address,address_info')->where('memberid', 'eq', $this->app_member['memberid'])->where('default', 'eq', 1)->find();
            $address['address_det'] = $address['address'] . $address['address_info'];
            if (empty($address)) {
                return json(arr_msg(-1, '', '默认地址不允许为空'));
            }
            $order = Db::table('t_shop_order')->field('id,goods_id,goods_name,goods_img,products_id,products_img,payable_amount,real_amount,payable_freight,real_freight,order_num,order_amount,products_sell_price')->where('id', 'eq', $param['order_id'])->where('memberid', 'eq', $this->app_member['memberid'])->find();
            $check_freight = 0;
            if (0 < $order['real_freight']) {
                $check_freight = 1;
            }
            $order['check_freight'] = $check_freight;

            return json(arr_msg(1, ['order' => $order, 'address' => $address], 'success'));
        } catch (\Exception $e) {
            return json(arr_msg(-1, '', $e->getMessage()));
        }
    }

    /**
     * 商城支付接口
     */
    public function mallPay()
    {
        $param = input('param.');
        $flag = $this->validate($param, 'IndexValidate.mallPay');
        if (true !== $flag) {
            return json(arr_msg(-1, '', $flag));
        }
        if ('开启' != $this->sys[1007004]) {
            return json(arr_msg(-1, '', '此功能暂时关闭，请稍后再试'));
        }
        try {
            $order = Db::table('t_shop_order')->where('id', 'eq', $param['order_id'])->where('memberid', 'eq', $this->app_member['memberid'])->find();
        } catch (\Exception $e) {
            return json(arr_msg(-1, '', '订单数据查询失败'));
        }
        if ($order['pay_status'] != 0 || $order['status'] != 1) {
            return json(arr_msg(-1, '', '订单已经支付'));
        }
        if ($order['order_amount'] <= 0) {
            return json(arr_msg(-1, '', '订单支付金额不正确，无法进行支付'));
        }
        if ($order['order_amount'] > $this->app_member['lian_num_keyong']) {
            return json(arr_msg(-1, '', 'MSTH提取账户余额不足'));
        }
        $data = [
            'order_id'  => $param['order_id'],
            'member_id' => $this->app_member['memberid']
        ];
        $procedure_name = 'app_shop_pay_order';
        $result = db_query($procedure_name, $data);
        if (1 != $result['code']) {
            return json($result);
        }
        try {
            $memo = str_fun($param['memo']);
            Db::table('t_shop_order')->where('id', 'eq', $param['order_id'])->update(['memo' => $memo]);
        } catch (\Exception $e) {
            return json(arr_msg(-1, '', '存入备注失败'));
        }

        return json(arr_msg(1, '', '支付成功'));
    }

    /**
     * IM 转账提交
     */
    public function transferImSub()
    {
        $param = input('param.');
        $flag = $this->validate($param, 'IndexValidate.transferImSub');
        if (true !== $flag) {
            return json(arr_msg(-1, '', $flag));
        }
        $data = [
            'from_memberid' => $this->app_member['memberid'],
            'to_memberid'   => str_fun($param['to_member_id']),
            'amount'        => floatval($param['zhz_num'])
        ];
        $procedure_name = 't_im_zhuanzhang';
        $result = db_query($procedure_name, $data);
        if (1 != $result['code']) {
            return json($result);
        }

        return json(arr_msg(1, '', '转账成功'));
    }
}
