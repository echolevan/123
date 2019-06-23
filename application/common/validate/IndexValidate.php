<?php
/**
 * Created by PhpStorm.
 * User: LTSS
 * Date: 2018/11/9
 * Time: 10:14
 */

namespace app\common\validate;


use eth_verify_address\EthUtil;
use think\Db;
use think\Env;
use think\Validate;
use app\util\Tools;

class IndexValidate extends Validate
{
    protected $rule = [
        'cipher|交易密码'                  => 'require|length:6|number|gt:0',
        'confirm_cipher|确认交易密码'        => 'require|confirm:cipher',
        'captcha|图片验证码'                => 'require|captcha',
        'check_code|验证码信息'             => 'require|checkCode:',
        'check_type|验证码类型'             => 'require|verStrFun:|in:mobile',
        'profile_token|验证信息'           => 'require|checkToken:',
        'mobile|手机号'                   => ['regex' => '^[1][3-9]\d{9}$', 'requireIf:check_type,mobile', 'verStrFun:'],
        'r_check_code|验证码信息'           => 'require|rCheckCode:',
        'email|邮箱'                     => 'requireIf:check_type,email|email',
        'password|密码'                  => 'require|length:8,10|alphaNum|checkPassword:|verStrFun:',
        'confirm_password|确认密码'        => 'require|confirm:password',
        'autograph|签名'                 => 'require|chsAlpha|length:5,10|verStrFun:',
        'nick_name|昵称'                 => 'require|chsAlpha|length:1,10|verStrFun:',
        'account_type|链储行类型'           => 'require|in:msth|alpha|verStrFun:|verChainStatus:',
        'laiyuan|储值类型'                 => 'require|in:MSTH预存,MSTH提取|chsAlpha|verStrFun:',
        'pay_pwd|交易密码'                 => 'require|length:6|number|gt:0|checkCipher:',
        'amount|存储数量'                  => 'require|number|gt:0|checkNum:',
        'pro_id|链储行套餐ID'               => 'require|number|gt:0|checkNum:|checkTaoCan:',
        'page|页数'                      => 'require|number|gt:0',
        'page_size|页码'                 => 'require|number|gt:0',
        'chain_order_id|明细ID'          => 'require|number|gt:0',
        't_order_id|存储订单ID'            => 'require|number|gt:0|checkChainOrder:',
        'c_type|通用账户类型'                => 'require|alpha|in:msth',
        'currency_id|通用账户日志ID'         => 'require|number|gt:0',
        'tx_num|提现数量'                  => ['require', 'number', 'gt:0', 'regex' => '/^([1-9]\d{0,15}|0)(\.\d{1,4})?$/', 'between:0.0001,100000', 'checkTxNum:'],
        'qc_num|圈存数量'                  => ['require', 'number', 'gt:0', 'regex' => '/^([1-9]\d{0,15}|0)(\.\d{1,4})?$/', 'between:0.0001,100000'],
        'to_address|钱包地址'              => 'require|alphaNum|length:42|checkToAddress:',
        'zz_num|转账数量'                  => ['require', 'number', 'gt:0', 'regex' => '/^([1-9]\d{0,15}|0)(\.\d{1,4})?$/', 'between:0.0001,100000'],
        'luck_num|移民星球'                => ['regex' => '/^\d+(,\d+)*$/', 'require', 'checkLuckNum:'],
        'count_num|移民数量'               => ['regex' => '/^\d+(,\d+)*$/', 'require', 'checkCountNum:'],
        'draw_id|奖期ID'                 => 'require|number|gt:0',
        'betting_id|投注ID'              => 'require|number|gt:0|checkBetting:',
        'address_id|地址ID'              => 'require|number|gt:0|inAddress:',
        'address|地址'                   => 'require|verStrFun:',
        'address_info|地址详情'            => 'require|verStrFun:',
        'address_name|收货人姓名'           => 'require|verStrFun:',
        'address_mobile|收货人电话'         => 'require|regex:^1[3-9]\d{9}|verStrFun:',
        'address_default|默认'           => 'require|number|in:0,1',
        'goods_id|商品ID'                => 'require|number|gt:0|checkNum:|checkGoodsProducts:',
        'products_id|货品ID'             => 'require|number|gt:0|checkNum:',
        'order_num|下单数量'               => 'require|number|gt:0|checkNum:',
        'order_id|订单ID'                => 'require|number|gt:0|checkOrder:',
        'order_status|订单状态'            => 'require|number|gt:0|in:1,2,3,4,5',
        'qbname|钱包地址标签'                => 'require|length:1,30|verStrFun:',
        'qklqbdz|钱包地址'                 => 'require|alphaNum|length:42|verStrFun:|checkQklAddress:',
        'check_cipher|交易密码'            => 'require|length:6|number|gt:0|checkCipher:',
        'suo_barn_id|锁仓明细ID'           => 'require|number|gt:0',
        'gui_ji_id|归集明细ID'             => 'require|number|gt:0',
        'zhz_num|转账金额'                 => 'require|number|gt:0|checkNum:|checkZhzNum:',
        'to_member_id|接收会员编号'          => 'require|length:6,10|alphaNum|checkPassword:|verStrFun:|checkImTransfer:',
        'det_id|详情ID'                  => 'require|number|gt:0|checkNum:|checkAssemble:',
        'wallet_id|钱包明细ID'             => 'require|number|gt:0|checkNum:|checkWalletDet:',
        'lian_address_id|钱包地址ID'       => 'require|number|gt:0|checkNum:|checkLianAddress:',
        'storage_type|存储类型'            => 'require|in:MSTH预存,MSTH提取',
        'storage_num|存储数量'             => 'require|number|gt:0',
        'rand|校验信息'                    => 'require',
        'notional_pooling_type|聚合类型'   => 'require|in:释放通证,收益通证,奖励账户,可用业绩,返证账户',
        'store_pro_id|储值套餐ID'          => 'require|number|gt:0|checkNum:|checkStoreTaoCan:',
        'charge_id|充证ID'               => 'require|number|gt:0',
        'interturn_id|互转ID'            => 'require|number|gt:0',
        'reversion_id|消费返证ID'          => 'require|number|gt:0',
        'cross_rotation_type|互转类型'     => 'require|in:MSTH提取转MSTH预存,MSTH预存,MSTH提取',
        'business_mobile|商家手机号'        => 'require|regex:^1[3-9]\d{9}|verStrFun:|checkBusinessUser:',
        'business_name|商家名称'           => 'require|chsAlphaNum|length:1,20|verStrFun:|checkBusinessName:',
        'business_content|商家简介'        => 'require|length:1,100|verStrFun:',
        'business_type_id|商家类型ID'      => 'require|number|gt:0|checkBusinessType:',
        'business_address|商家地址'        => 'require|chs|verStrFun:',
        'business_address_info|商家地址详情' => 'require|chsAlphaNum|length:1,30|verStrFun:',
        'business_memo|商家备注'           => 'chsAlphaNum|length:1,20|verStrFun:',
        'business_long|商家经度'           => 'require|number|verStrFun:',
        'business_lat|商家纬度'            => 'require|number|verStrFun:',
        'up_mobile|商家手机号'              => 'require|regex:^1[3-9]\d{9}|verStrFun:|checkUpBusinessUser:',
        'up_name|商家名称'                 => 'require|chsAlphaNum|verStrFun:|checkUpBusinessName:',
        'up_content|商家简介'              => 'require|length:1,100|verStrFun:',
        'business_member_id|商家账号'      => 'require|length:6,10|alphaNum|checkPassword:|verStrFun:|checkInBusiness:',
        'pay_type|付款账户'                => 'require|verStrFun:|in:MSTH提取',
        'pay_num|付款数量'                 => 'require|number|gt:0|between:1,100000|checkNum:|checkPayNum:',
        'pay_memo|付款备注'                => 'chsAlphaNum|length:1,20|verStrFun:',
        'business_id|商家ID'             => 'require|number|gt:0',
        'shop_order_id|商家订单ID'         => 'require|number|gt:0|checkShopOrder:',
        'news_id|消息ID'                 => 'require|number|gt:0|checkNum:',
        'carry_id|提证明细'                => 'require|number|gt:0|checkNum:',
        'memo|商城支付备注'                  => 'verStrFun:',
        'reward_id|奖励明细ID'             => 'require|number|gt:0|checkNum:',
        'to_memberid|转入账户' => 'require|inMember:',
    ];

    protected $message = [
        'cipher.length'            => '交易密码请输入6位数字',
        'confirm_cipher.confirm'   => '两次输入的交易密码不一致',
        'mobile.regex'             => '请输入正确的手机号',
        'password.checkPassword'   => '请输入8-10位英文加数字的密码',
        'password.length'          => '请输入8-10位英文加数字的密码',
        'confirm_password.confirm' => '两次输入的密码不一致',
        'autograph.length'         => '会员签名请输入5-10位中文或英文',
        'pay_pwd.length'           => '交易密码请输入6位数字',
        'amount.gt'                => '存储数量必须大于0',
        'rand.require'             => '校验信息已失效，请重新操作',

    ];

    protected $scene = [
        'liftChainCheck'      => ['num', 'check_code', 'check_type'],
        'liftChain'           => ['num', 'check_cipher', 'rand'],
        'storageDetail'       => ['record_id'],
        'crossRotationCheck'  => ['to_memberid', 'cross_rotation_type', 'num', 'check_code', 'check_type'],
        'crossRotation'       => ['to_memberid', 'cross_rotation_type', 'num', 'check_cipher', 'rand'],
        'notionalPooling'     => ['notional_pooling_type', 'num'],
        'notionalPoolingList' => ['page', 'page_size'],
        'storageList'         => ['page', 'page_size'],
        'storage'             => ['storage_type', 'storage_num', 'check_cipher', 'rand'],
        'storageCheck'        => ['storage_type', 'storage_num'],
        'setTwoLevelCipher'   => ['cipher', 'confirm_cipher', 'captcha'],
        'editProfile'         => ['check_code', 'check_type'],
        'sendSms'             => ['check_type', 'captcha'],
        'editMobile'          => ['profile_token', 'mobile', 'r_check_code', 'check_type'],
        'editEmail'           => ['profile_token', 'email', 'r_check_code', 'check_type'],
        'editPassword'        => ['password', 'confirm_password', 'profile_token'],
        'editPayPassword'     => ['cipher', 'confirm_cipher', 'profile_token'],
        'personSet'           => ['autograph', 'nick_name'],
//        'chainStoreInit'     => ['account_type'],
        'chainStoreSub'       => ['pay_pwd', 'amount', 'pro_id', 'laiyuan'],
        'chainStoreMy'        => ['page', 'page_size'],
        'chainStoreDet'       => ['chain_order_id'],
        'chainStoreDraw'      => ['t_order_id', 'pay_pwd'],
        'currencyInit'        => ['c_type', 'page', 'page_size'],
        'currencyDet'         => ['currency_id', 'c_type'],
        'currencyTxInit'      => ['c_type'],
        'currencyTxSub'       => ['c_type', 'tx_num', 'pay_pwd'],
        'walletInit'          => ['c_type', 'page', 'page_size'],
        'walletQcInit'        => ['c_type'],
        'walletQcSub'         => ['c_type', 'qc_num', 'pay_pwd'],
        'walletZzInit'        => ['c_type'],
        'walletZzSub'         => ['c_type', 'to_address', 'zz_num', 'pay_pwd', 'check_type', 'check_code'],
        'welfareInit'         => ['c_type'],
        'welfareSub'          => ['c_type', 'luck_num', 'count_num', 'draw_id', 'pay_pwd'],
        'welfareOpen'         => ['c_type', 'page', 'page_size'],
        'welfareBetting'      => ['c_type', 'page', 'page_size'],
        'welfareBettingDet'   => ['c_type', 'betting_id'],
        'mallAddressAdd'      => ['address', 'address_info', 'address_name', 'address_mobile', 'address_default'],
        'mallAddressRead'     => ['address_id'],
        'mallAddressEdit'     => ['address', 'address_info', 'address_name', 'address_mobile', 'address_id', 'address_default'],
        'verPayPwd'           => ['pay_pwd'],
        'mallAddressDel'      => ['address_id'],
        'mallAddressDefault'  => ['address_id'],
        'mallPlaceOrder'      => ['goods_id', 'products_id', 'order_num'],
        'mallOrderDet'        => ['order_id'],
        'mallPay'             => ['order_id', 'pay_pwd', 'memo'],
        'mallMyOrder'         => ['order_status', 'page', 'page_size'],
        'mallExpress'         => ['order_id'],
        'mallTakeGoods'       => ['order_id'],
        'mallDelOrder'        => ['order_id'],
        'mallGuiJi'           => ['c_type', 'pay_pwd'],
        'myTeam'              => ['page', 'page_size'],
        'addressAdd'          => ['qbname', 'qklqbdz', 'check_cipher', 'check_code', 'check_type'],
        'suoBarnInit'         => ['c_type', 'page', 'page_size'],
        'suoBarnDet'          => ['c_type', 'suo_barn_id'],
        'guiJiInit'           => ['c_type', 'page', 'page_size'],
        'guiJiDet'            => ['c_type', 'gui_ji_id'],
        'transferImSub'       => ['zhz_num', 'to_member_id', 'pay_pwd'],
        'mallAssembleInit'    => ['page', 'page_size', 'c_type'],
        'mallAssembleDet'     => ['det_id', 'c_type'],
        'walletDet'           => ['wallet_id', 'c_type'],
        'addressDel'          => ['lian_address_id', 'check_cipher'],
        'setMobile'           => ['mobile', 'check_type', 'r_check_code'],
        'chainStoreList'      => ['store_pro_id'],
        'chargeCardList'      => ['page', 'page_size'],
        'chargeCardDet'       => ['charge_id'],
        'carryCardList'       => ['page', 'page_size'],
        'carryCardDet'        => ['carry_id'],
        'interturnList'       => ['page', 'page_size'],
        'interturnDet'        => ['interturn_id'],
        'reversionList'       => ['page', 'page_size'],
        'reversionDet'        => ['reversion_id'],
        'becomeBusiness'      => ['business_mobile', 'business_name', 'business_content', 'business_type_id', 'business_address', 'business_address_info', 'business_memo', 'business_long', 'business_lat'],
        'upBusiness'          => ['up_mobile', 'up_name', 'up_content'],
        'payBusinessInit'     => ['business_member_id'],
        'payBusinessSub'      => ['business_member_id', 'pay_type', 'pay_num', 'pay_pwd', 'check_type', 'check_code', 'pay_memo'],
        'businessDet'         => ['business_id'],
        'myShop'              => ['page', 'page_size'],
        'shopDet'             => ['shop_order_id'],
        'newsList'            => ['page', 'page_size'],
        'newsDet'             => ['news_id'],
        'rewardList'          => ['page', 'page_size'],
        'rewardDet'           => ['reward_id'],
        'checkBusinessSub'    => ['business_member_id', 'pay_type', 'pay_num', 'check_type', 'check_code', 'pay_memo']
    ];

    /**
     * 是否是会员
     *
     * @param $referee_memberid
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    protected function inMember($referee_memberid)
    {
        $member = Db::name("user")->where(['memberid' => $referee_memberid])->find();
        if (!$member) {
            return false;
        }

        return true;
    }
    /**
     * 校验验证码
     * @param $code
     * @param $rule
     * @param $data
     * @return bool
     * @throws \Exception
     */
    protected function checkCode($code, $rule, $data)
    {
        $type = $data['check_type'];
        $token = $data['token'];
        $memberid = cache($token);
        $member = Db::table("t_user")->where('memberid', 'eq', $memberid)->find();
        if ($type == 'mobile') {
            $key = $member['mobile'];
            $msg = "手机验证码不正确";
        } else {
            $key = $member['email'];
            $msg = "邮箱验证码不正确";
        }
        $debug = Env::get('app_debug');
        if ($debug && $code == '618660') {
            return true;
        }
        if (cache($key . 'code') != $code) {
            return $msg;
        }

        return true;
    }

    /**
     * 验证编辑
     * @param $profile_token
     * @param $rule
     * @param $data
     * @return bool|string
     */
    protected function checkToken($profile_token, $rule, $data)
    {
        $token = $data['token'];
        $memberid = cache($token);
        if ($profile_token != cache("profile_" . $memberid)) {
            return '验证信息已超时，请返回上一页重新提交';
        }

        return true;
    }

    /**
     * 校验验证码
     * @param $code
     * @param $rule
     * @param $data
     * @return bool
     */
    protected function rCheckCode($code, $rule, $data)
    {
        $type = $data['check_type'];
        if ($type == 'mobile') {
            $key = $data['mobile'];
            $msg = "手机验证码不正确";
        } else {
            $key = $data['email'];
            $msg = "邮箱验证码不正确";
        }
        $debug = Env::get('app_debug');
        if ($debug && $code == '618660') {
            return true;
        }
        if (cache($key . 'code') != $code) {
            return $msg;
        }

        return true;
    }

    /**
     * 验证英文加数字
     * @param $password
     * @return bool
     */
    protected function checkPassword($password)
    {
        $arr = str_split($password);
        $num = $alpha = false;
        foreach ($arr as $item) {
            if (is_numeric($item)) {
                $num = true;
                break;
            }
        }
        foreach ($arr as $item) {
            if (ctype_alpha($item)) {
                $alpha = true;
                break;
            }
        }
        if ($num && $alpha) {
            return true;
        }

        return false;
    }

    /**
     * 检查验证交易密码
     */
    protected function checkCipher($value, $rule, $data)
    {
        $token = $data['token'];
        $memberid = cache($token);
        try {
            $member = Db::table("t_user")->where('memberid', 'eq', $memberid)->find();
            if ($member['login_error_num_pay'] >= 5) {
                return '今日密码已锁定，请明日再操作！';
            }
            $password = strtoupper(substr(md5($value), 8, 16));
            if ($password != $member['paypassword_zhifu']) {
                if ($member['login_error_num_pay'] == 4) {
                    Db::table('t_log_errors')->insert([
                        'memberid' => $member['memberid'],
                        'ip'       => \request()->ip(),
                        'type'     => 'app_pay'
                    ]);
                }
                Db::name('log_login')->insert([
                    'memberid' => $memberid,
                    'ip'       => request()->ip(),
                    'status'   => 'fail',
                    'times'    => $member['login_error_num_pay'] + 1,
                    'type'     => '交易'
                ]);
                Db::table('t_user')->where('memberid', 'eq', $memberid)->setInc('login_error_num_pay');
                $yu_num = 5 - ($member['login_error_num_pay'] + 1);
                return '交易密码不正确，您当天还有' . $yu_num . '次';
            }
            Db::table('t_user')->where('memberid', 'eq', $memberid)->update(['login_error_num_pay' => 0]);
        } catch (\Exception $e) {
            return $e->getMessage();
        }

        return true;
    }

    /**
     * 检查验证储值套餐
     * @throws \Exception
     */
    protected function checkStoreTaoCan($value, $rule, $data)
    {
        $chain_status = $data['chain_status'];
        if ('开启' <> $chain_status) {
            return '此功能暂时关闭，请稍后再试！';
        }
        $token = $data['token'];
        $member_id = cache($token);
        $app_member = Db::table('t_user')->where('memberid', 'eq', $member_id)->where('status', 'eq', '已审')->find();
        if (empty($app_member)) {
            return '会员不存在或此会员已禁用！';
        }
        $app_project = Db::table('t_save_project')->where('id', 'eq', $value)->where('status', 'eq', 1)->find();
        if (empty($app_project)) {
            return '存储套餐不存在或此存储套餐已禁用';
        }

        return true;
    }

    /**
     * 检查验证链储行套餐
     * @throws \Exception
     */
    protected function checkTaoCan($value, $rule, $data)
    {
        $chain_status = $data['chain_status'];
        if ('开启' <> $chain_status) {
            return '此功能暂时关闭，请稍后再试！';
        }
        $token = $data['token'];
        $member_id = cache($token);
        $app_member = Db::table('t_user')->where('memberid', 'eq', $member_id)->where('status', 'eq', '已审')->find();
        if (empty($app_member)) {
            return '会员不存在或此会员已禁用！';
        }
        if (0 > $data['amount']) {
            return '输入存储数量不正确！';
        }
        if (!preg_match('/^[1-9][0-9]*$/', $data['amount'])) {
            return '存储数量必须为正整数！';
        }
        $app_project = Db::table('t_save_project')->where('id', 'eq', $value)->where('status', 'eq', 1)->find();
        if (empty($app_project)) {
            return '存储套餐不存在或此存储套餐已禁用';
        }
        if ($data['amount'] < $app_project['min_num'] || $data['amount'] > $app_project['max_num']) {
            return '存储数量设定的范围：' . $app_project['min_num'] . '-' . $app_project['max_num'];
        }
        $wuc_bei_num = $data['amount'] % 100;
        if (0 != $wuc_bei_num) {
            return '存储数量必须为100的倍数！';
        }
        if ('MSTH预存' == $data['laiyuan'] && $data['amount'] > $app_member['into_lian_keyong']) {
            return '当前MSTH预存余额不足！';
        }
        if ('MSTH提取' == $data['laiyuan'] && $data['amount'] > $app_member['lian_num_keyong']) {
            return '当前MSTH提取余额不足！';
        }

        return true;
    }

    /**
     * 验证链储行是否开启
     */
    protected function verChainStatus($value, $rule, $data)
    {
        $sys_param = Db::table('t_sys_parameter')->where('s_key', 'eq', '1004001')->value('s_value');
        if ('开启' != $sys_param) {
            return '此功能暂时关闭，请稍后！';
        }

        return true;
    }

    /**
     * 链储行存储提前支取验证
     * @throws \Exception
     */
    protected function checkChainOrder($order_id, $rule, $data)
    {
        if ('开启' != $data['tq_status']) {
            return '此功能暂时关闭，请稍后！';
        }
        $token = $data['token'];
        $member_id = cache($token);
        $member = Db::table('t_user')->where('memberid', 'eq', $member_id)->where('status', 'eq', '已审')->find();
        if (empty($member)) {
            return '会员账号不存在或会员状态已禁用';
        }
        $chain_list = Db::table('t_user_project')->alias('p')->join('t_save_project s', 'p.pro_id = s.id')->where('p.memberid', 'eq', $member_id)->where('p.id', 'eq', $order_id)->where('p.jisuanyeji_status', 'eq', 1)->where('p.tz_status', 'eq', 1)->field('p.id,p.pro_id,p.tz_money,p.c_time,s.save_days,s.rixi_bili,s.yuexi_bili,s.tiqianzhiqu')->find();
        if (empty($chain_list)) {
            return '存储记录不存在';
        }
        if (1 != $chain_list['tiqianzhiqu']) {
            return '此存储套餐不允许提前支取';
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
     * 验证提现数量
     * @throws \Exception
     */
    protected function checkTxNum($tx_num, $rule, $data)
    {
        if ('开启' != $data['sys_status']) {
            return '此功能暂时关闭，请稍后再试';
        }
        if (empty($data['sys_address'])) {
            return '主钱包地址不正确！';
        }
        if (0 > $tx_num) {
            return '提现数量必须大于0！';
        }
        $token = $data['token'];
        $member_id = cache($token);
        $member = Db::table('app_account')->where('memberid', 'eq', $member_id)->where('status', 'eq', 1)->find();
        if (empty($member)) {
            return '会员不存在';
        }
        if (empty($member['address'])) {
            return '会员钱包地址不正确！';
        }
        if ('eth' == $data['c_type'] && $tx_num > $member['eth_account']) {
            return 'ETH通用账户余额不足';
        }
        if ('wuc' == $data['c_type'] && $tx_num > $member['wuc_account']) {
            return 'MSTH通用账户余额不足';
        }

        return true;
    }

    /**
     * 验证转账地址
     * @throws \Exception
     */
    protected function checkToAddress($to_address, $rule, $data)
    {
        $token = $data['token'];
        $member_id = cache($token);
        $address_list = Db::table('app_b_qianbaoaddress')->where('memberid', 'eq', $member_id)->where('qklqbdz', 'eq', $to_address)->where('status', 'eq', '已验证')->find();
        if (empty($address_list)) {
            return '钱包地址不存在或此钱包地址未验证';
        }

        return true;
    }

    /**
     * 验证移民星球规则
     */
    protected function checkLuckNum($luck_num)
    {
        $luck_num_data = explode(',', $luck_num);
        if (empty($luck_num_data)) {
            return '请选择移民星球';
        }
        $ver_status = 0;
        foreach ($luck_num_data as $key => $value) {
            if (0 > $value || 10 < $value) {
                $ver_status = 1;
                break;
            }
        }
        if (0 != $ver_status) {
            return '请选择正确的移民星球';
        }

        return true;
    }

    /**
     * 验证移民数量规则
     * @throws \Exception
     */
    protected function checkCountNum($count_num, $rule, $data)
    {
        $token = $data['token'];
        $member_id = cache($token);
        $member = Db::table('app_account')->where('memberid', 'eq', $member_id)->where('status', 'eq', 1)->find();
        if (empty($member)) {
            return '会员不存在';
        }
        $draw_list = Db::table('app_luck_draw')->field('id,price')->where('id', 'eq', $data['draw_id'])->where('status', 'eq', 1)->where('start_time', '<=', date('Y-m-d H:i:s'))->where('end_time', '>=', date('Y-m-d H:i:s'))->where('account_type', 'eq', $data['c_type'])->find();
        if (empty($draw_list)) {
            return '暂未开始新的奖期';
        }
        $count_num_data = explode(',', $count_num);
        $total_count_num = 0;
        $ver_state = 0;
        foreach ($count_num_data as $k => $v) {
            $total_count_num += $v;
            if (0 > $v) {
                $ver_state = 1;
            }
        }
        if (0 != $ver_state) {
            return '移民数量不正确';
        }
        if ('eth' == $data['c_type'] && $total_count_num > $member['eth_account']) {
            return 'ETH通用账户余额不足';
        }
        if ('wuc' == $data['c_type'] && $total_count_num > $member['wuc_account']) {
            return 'MSTH通用账户余额不足';
        }

        return true;
    }

    /**
     * 收货地址验证
     * @throws \Exception
     */
    protected function inAddress($address_id)
    {
        $address = Db::table('t_shop_user_address')->where('id', 'eq', $address_id)->find();
        if (empty($address)) {
            return '选择的地址不正确';
        }

        return true;
    }

    /**
     * 验证商品及货品
     * @throws \Exception
     */
    protected function checkGoodsProducts($goods_id, $rule, $data)
    {
        try {
            $member_id = cache($data['token']);
            $member = Db::table('t_user')->where('memberid', 'eq', $member_id)->where('status', 'eq', '已审')->find();
            if (empty($member)) {
                return '未获取到会员信息或此会员已冻结';
            }
            if (intval($data['products_id']) <= 0) {
                return '未获取到货品信息';
            }
            if (intval($data['order_num']) <= 0) {
                return '未获取到下单的数量';
            }
            $goods = Db::table('t_shop_goods')->where('id', 'eq', intval($goods_id))->where('is_del', 'eq', 2)->find();
            if (empty($goods)) {
                return '商品信息不存在或此商品信息已下架！';
            }
            $products = Db::table('t_shop_products')->where('goods_id', 'eq', intval($goods_id))->where('id', 'eq', intval($data['products_id']))->find();
            if (empty($products)) {
                return '货品信息不存在';
            }
            $total_num = $products['sell_price'] * intval($data['order_num']);
            if ($goods['is_delivery_fee'] == 0 && $goods['delivery_fee'] > 0) {
                $total_num = ($products['sell_price'] * intval($data['order_num'])) + $goods['delivery_fee'];
            }
            if ($total_num <= 0) {
                return '下单金额不正确';
            }
            if ($total_num > $member['lian_num_keyong']) {
                return '您的MSTH余额不足，请稍后下单';
            }
        } catch (\Exception $e) {
            return $e->getMessage();
        }

        return true;
    }

    /**
     * 验证订单信息
     * @throws \Exception
     */
    protected function checkOrder($order_id, $rule, $data)
    {
        $member_id = cache($data['token']);
        $order = Db::table('t_shop_order')->where('id', 'eq', $order_id)->where('memberid', 'eq', $member_id)->find();
        if (empty($order)) {
            return '订单不存在';
        }
        if ($order['order_num'] <= 0) {
            return '订单数量不正确';
        }
        if ($order['order_amount'] <= 0) {
            return '订单总金额不正确';
        }
        $total_num = $order['products_sell_price'] * intval($order['order_num']);
        if ($order['real_freight'] > 0) {
            $total_num = ($order['products_sell_price'] * intval($order['order_num'])) + $order['real_freight'];
        }
        if ($total_num != $order['order_amount']) {
            return '订单应付金额不正确';
        }

        return true;
    }

    /**
     * 验证钱包地址正确
     * @throws \Exception
     */
    protected function checkQklAddress($address, $rule, $data)
    {
        $member_id = cache($data['token']);
        $address_str = $address;
        try {
            $flag = EthUtil::isAddress($address_str);
            if (!$flag) {
                return '钱包地址不正确';
            }
            $address_list = Db::name("b_qianbaoaddress")->where('memberid', 'eq', $member_id)->where('status', 'eq', '已验证')->where('qklqbdz', 'eq', $address_str)->find();
            if (!empty($address_list)) {
                return '此钱包地址已经存在无需再次添加';
            }
            //新增修改一个钱包地址最大绑定账户次数
            $address_num = Db::name('b_qianbaoaddress')->where('qklqbdz', 'eq', $address_str)->count('id');
            $sys_val = Db::name('sys_parameter')->where('s_key','eq','1009001')->value('s_value');
            if (!empty($sys_val)) {
                if ($address_num >= $sys_val) {
                    return '此钱包地址已经达到账号最大绑定次数';
                }
            }
        } catch (\InvalidArgumentException $i) {
            return $i->getMessage();
        }

        return true;
    }

    /**
     * 验证IM转账
     * @throws \Exception
     */
    protected function checkImTransfer($zhz_num, $rule, $data)
    {
        $member_id = cache($data['token']);
        try {
            $from_member = Db::table('t_user')->where('memberid', 'eq', $member_id)->where('status', 'eq', '已审')->find();
            if (empty($from_member)) {
                return '发起账户不存在';
            }
            $to_member = Db::table('t_user')->where('memberid', 'eq', $data['to_member_id'])->where('status', 'eq', '已审')->find();
            if (empty($to_member)) {
                return '接收账户不存在';
            }
            if ($from_member['lian_num_keyong'] < $data['zhz_num']) {
                return 'MSTH提取账户余额不足';
            }

        } catch (\Exception $e) {
            return $e->getMessage();
        }

        return true;
    }

    /**
     * 验证锁仓明细
     * @throws \Exception
     */
    protected function checkAssemble($det_id, $rule, $data)
    {
        $member_id = cache($data['token']);
        $table_name = 'app_log_wuc_yuqi';
        if ('eth' == $data['c_type']) {
            $table_name = 'app_log_eth_yuqi';
        }
        $list = Db::table($table_name)->where('memberid', 'eq', $member_id)->where('id', 'eq', $data['det_id'])->find();
        if (empty($list)) {
            return '锁仓详情信息不存在';
        }

        return true;
    }

    /**
     * 验证钱包明细详情
     * @throws \Exception
     */
    protected function checkWalletDet($wallet_id, $rule, $data)
    {
        $member_id = cache($data['token']);
        try {
            $member = Db::table('app_account')->where('memberid', 'eq', $member_id)->where('status', 'eq', 1)->find();
            if (empty($member)) {
                return '会员不存在';
            }
            $where['token_address'] = str_fun($data['c_type']);
            if ('eth' != $data['c_type']) {
                $where['token_address'] = ['neq', 'eth'];
            }
            $transactions = Db::table('app_transactions')->where('from_address|to_address', 'eq', $member['address'])->where($where)->where('id', 'eq', $wallet_id)->find();
            if (empty($transactions)) {
                return '交易信息不存在';
            }
        } catch (\Exception $e) {
            return $e->getMessage();
        }

        return true;
    }

    /**
     * 验证投注信息
     * @throws \Exception
     */
    protected function checkBetting($betting_id, $rule, $data)
    {
        $member_id = cache($data['token']);
        $betting = Db::table('app_user_luck_draw')->where('memberid', 'eq', $member_id)->where('id', 'eq', $betting_id)->where('account_type', 'eq', str_fun($data['c_type']))->find();
        if (empty($betting)) {
            return '链公益投注详情不存在';
        }

        return true;
    }

    /**
     * 验证钱包地址是否存在
     * @throws \Exception
     */
    protected function checkLianAddress($lian_address_id, $rule, $data)
    {
        $member_id = cache($data['token']);
        try {
            $address_list = Db::name("b_qianbaoaddress")->where('memberid', 'eq', $member_id)->where('id', 'eq', $lian_address_id)->find();
            if (empty($address_list)) {
                return '您删除地址不存在，请核对后再次操作！';
            }
            $transactions_no_sh = Db::table('t_yw_qkl_dabi')->where('memberid', 'eq', $member_id)->where('status', 'eq', '未审')->count('id');
            if ($transactions_no_sh > 0) {
                return '此地址交易有未审核状态，不允许删除！';
            }
            $transactions_no_success = Db::table('t_yw_qkl_dabi')->where('memberid', 'eq', $member_id)->where('status', 'neq', '已打款')->count('id');
            if ($transactions_no_success > 0) {
                return '此地址交易有不成功状态，不允许删除！';
            }
        } catch (\Exception $e) {
            return $e->getMessage();
        }

        return true;
    }

    /**
     * IM 转账数量验证
     * @throws \Exception
     */
    protected function checkZhzNum($zhz_num, $rule, $data)
    {
        $member_id = cache($data['token']);
        try {
            $member = Db::table('t_user')->where('memberid', 'eq', $member_id)->where('status', 'eq', '已审')->find();
            if (empty($member)) {
                return '会员不存在';
            }
            if ($zhz_num > $member['lian_num_keyong']) {
                return '当前MSTH提取账户余额不足！';
            }
        } catch (\Exception $e) {
            return $e->getMessage();
        }

        return true;
    }

    /**
     * 验证字符是否存在特殊字符
     * @param $str
     * @return bool
     */
    protected function verStrFun($str)
    {
        $str = strtolower($str);
        //正则匹配
        $ver_regular = true;
        $_arr_dangerRegs = [
            "/<(script|frame|iframe|bgsound|link|object|applet|embed|blink|style|layer|ilayer|base|meta)\s+\S*>/i",
            "/on(afterprint|beforeprint|beforeunload|error|haschange|load|message|offline|online|pagehide|pageshow|popstate|redo|resize|storage|undo|unload|blur|change|contextmenu|focus|formchange|forminput|input|invalid|reset|select|submit|keydown|keypress|keyup|click|dblclick|drag|dragend|dragenter|dragleave|dragover|dragstart|drop|mousedown|mousemove|mouseout|mouseover|mouseup|mousewheel|scroll|abort|canplay|canplaythrough|durationchange|emptied|ended|error|loadeddata|loadedmetadata|loadstart|pause|play|playing|progress|ratechange|readystatechange|seeked|seeking|stalled|suspend|timeupdate|volumechange|waiting)\s*=\s*(\"|')?\S*(\"|')?/i",
            "/\w+\s*=\s*(\"|')?(java|vb)script:\S*(\"|')?/i",
            "/(document|location)\s*\.\s*\S*/i",
            "/(eval|alert|prompt|msgbox)\s*\(.*\)/i",
            "/expression\s*:\s*\S*/i",
            "/show\s+(databases|tables|index|columns)/i",
            "/create\s+(database|table|(unique\s+)?index|view|procedure|proc)/i",
            "/alter\s+(database|table)/i",
            "/drop\s+(database|table|index|view|column)/i",
            "/backup\s+(database|log)/i",
            "/truncate\s+table/i",
            "/replace\s+view/i",
            "/(add|change)\s+column/i",
            "/(select|update|delete)\s+\S*\s+from/i",
            "/insert\s+into/i",
            "/load_file\s*\(.*\)/i",
            "/(outfile|infile)\s+(\"|')?\S*(\"|')/i",
        ];
        foreach ($_arr_dangerRegs as $item) {
            if (preg_match($item, $str)) {
                $ver_regular = false;
                break;
            }
        }
        //字符串验证
        $ver_string = true;
        $str_ver_arr = ["%20", "%27", "%2527", "*", '"', ";", "<", ">", "{", "}", "and", "execute", "update", "count", "chr", "mid", "master", "truncate", "char", "declare", "select", "create", "delete", "insert", " ", "'", "or", "=", "\'", "\/\*", "\.\.\/", "\.\/", "union", "and", "order", "or", "into", "load_file", "outfile"];
        foreach ($str_ver_arr as $value) {
            if (stripos($str, $value) !== false) {
                $ver_string = false;
                break;
            }
        }
        if (!$ver_regular || !$ver_string) {
            \think\Log::write(dump($str, false), 'error');
            return '存在特殊字符，请修改后再次操作！';
        }

        return true;
    }

    /**
     * 验证成为商家会员以及状态
     * @param $mobile
     * @param $rule
     * @param $data
     * @return bool|string
     */
    protected function checkBusinessUser($mobile, $rule, $data)
    {
        $member_id = cache($data['token']);
        try {
            $sys_param = Db::table('t_sys_parameter')->where('s_key', 'eq', '1008003')->value('s_value');
            if ($sys_param <> '开启') {
                return '此功能暂未开启，请稍后再试';
            }
            $member = Db::table('t_user')->where('memberid', 'eq', $member_id)->where('status', 'eq', '已审')->where('is_business', 'eq', '否')->find();
            if (empty($member)) {
                return '会员不存在或此会员已经成为商家';
            }
            $business = Db::name('business')->where('memberid', 'eq', $member_id)->find();
            if (!empty($business)) {
                return '此会员已存在商家信息';
            }

        } catch (\Exception $e) {
            return $e->getMessage();
        }

        return true;
    }

    /**
     * 检测商家类型信息
     * @param $type_id
     * @return bool|string
     */
    protected function checkBusinessType($type_id)
    {
        try {
            $type = Db::name('business_type')->where('id', 'eq', $type_id)->where('status', 'eq', '启用')->find();
            if (empty($type)) {
                return '未获取到商家类型信息';
            }
        } catch (\Exception $e) {
            return $e->getMessage();
        }

        return true;
    }

    /**
     * 检测修改商家会员信息
     * @param $mobile
     * @param $rule
     * @param $data
     * @return bool|string
     */
    protected function checkUpBusinessUser($mobile, $rule, $data)
    {
        $member_id = cache($data['token']);
        try {
            $sys_param = Db::table('t_sys_parameter')->where('s_key', 'eq', '1008003')->value('s_value');
            if ($sys_param <> '开启') {
                return '此功能暂未开启，请稍后再试';
            }
            $member = Db::table('t_user')->where('memberid', 'eq', $member_id)->where('status', 'eq', '已审')->where('is_business', 'eq', '是')->find();
            if (empty($member)) {
                return '您还未成为商家或您的商家被冻结';
            }
            $business = Db::name('business')->where('memberid', 'eq', $member_id)->where('status', 'eq', '已审')->find();
            if (empty($business)) {
                return '您的商家信息被冻结或不存在';
            }

        } catch (\Exception $e) {
            return $e->getMessage();
        }

        return true;
    }

    /**
     * 检验付款商家
     * @param $business_member_id
     * @param $rule
     * @param $data
     * @return bool|string
     */
    protected function checkInBusiness($business_member_id, $rule, $data)
    {
        $member_id = cache($data['token']);
        try {
            $sys_param = Db::table('t_sys_parameter')->where('s_key', 'eq', '1008003')->value('s_value');
            if ($sys_param <> '开启') {
                return '此功能暂未开启，请稍后再试';
            }
            if ($member_id == $business_member_id) {
                return '请不要商家自身进行付款';
            }
            $business_member = Db::table('t_user')->where('memberid', 'eq', $business_member_id)->where('status', 'eq', '已审')->where('is_business', 'eq', '是')->find();
            if (empty($business_member)) {
                return '此账户商家不存在或被冻结';
            }
            $business = Db::name('business')->where('memberid', 'eq', $business_member_id)->where('status', 'eq', '已审')->find();
            if (empty($business)) {
                return '此账户商家信息被冻结或不存在';
            }

        } catch (\Exception $e) {
            return $e->getMessage();
        }

        return true;
    }

    /**
     * 检验付款数量
     * @param $pay_num
     * @param $rule
     * @param $data
     * @return bool|string
     */
    protected function checkPayNum($pay_num, $rule, $data)
    {
        $member_id = cache($data['token']);
        try {
            $sys_param = Db::table('t_sys_parameter')->where('s_key', 'eq', '1008003')->value('s_value');
            if ($sys_param <> '开启') {
                return '此功能暂未开启，请稍后再试';
            }
            $member = Db::table('t_user')->where('memberid', 'eq', $member_id)->where('status', 'eq', '已审')->find();
            if (empty($member)) {
                return '您的会员信息不存在';
            }
            if ($pay_num <= 0) {
                return '请输入正确的付款数量';
            }
            if ($data['pay_type'] != 'MSTH提取') {
                return '付款账户类型不正确';
            }
            if ($member['lian_num_keyong'] < 0) {
                return 'MSTH提取账户余额不正确';
            }
            if ($data['pay_type'] == 'MSTH提取' && $pay_num > $member['lian_num_keyong']) {
                return 'MSTH提取账户余额不足';
            }
        } catch (\Exception $e) {
            return $e->getMessage();
        }

        return true;
    }

    /**
     * 检验商家订单
     */
    protected function checkShopOrder($order_id, $rule, $data)
    {
        $member_id = cache($data['token']);
        try {
            $order = Db::name('business_order')->field('id')->where('business_memberid', 'eq', $member_id)->where('status', 'eq', '已支付')->where('pay_status', 'eq', 1)->where('id', 'eq', $order_id)->find();
            if (empty($order)) {
                return '此订单不存在';
            }
        } catch (\Exception $e) {
            return $e->getMessage();
        }

        return true;
    }

    /**
     * 检验商家名称
     */
    protected function checkBusinessName($business_name)
    {
        try {
            $business = Db::name('business')->field('id')->where('business_name', 'eq', $business_name)->find();
            if (!empty($business)) {
                return '存在此商家名称，请更换后再次申请';
            }
        } catch (\Exception $e) {
            return $e->getMessage();
        }

        return true;
    }

    /**
     * 检验编辑商家名称
     */
    protected function checkUpBusinessName($up_name, $rule, $data)
    {
        $member_id = cache($data['token']);
        try {
            $business = Db::name('business')->where('memberid', 'neq', $member_id)->where('business_name', 'eq', $up_name)->where('status', 'eq', '已审')->find();
            if (!empty($business)) {
                return '存在此商家名称，请更换后再次编辑';
            }
        } catch (\Exception $e) {
            return $e->getMessage();
        }

        return true;
    }
}